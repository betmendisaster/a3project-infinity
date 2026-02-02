<?php

namespace App\Http\Controllers;

use App\Models\Cis;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;

class PresensiController extends Controller
{
    public function getHari(){
        $hari = date("D");

        switch ($hari) {
            case 'Sun':
                $today = "Minggu";
                break;

            case 'Mon':
                $today = "Senin";
                break;

            case 'Tue':
                $today = "Selasa";
                break;
                
            case 'Wed':
                $today = "Rabu";
                break;

            case 'Thu':
                $today = "Kamis";
                break;

            case 'Fri':
                $today = "Jumat";
                break;

            case 'Sat':
                $today = "Sabtu";
                break;

            default:
                $today = "Tidak di Ketahui";
                break;
            }

            return $today;
        }

    public function getTanggalSekarang(){
        return date("d-m-Y"); // Format dd-mm-yyyy, e.g., "15-10-2023". Ubah jika perlu, e.g., "Y-m-d" untuk yyyy-mm-dd
    }

    public function getHariFromDate($dateString) {
    $day = date("D", strtotime($dateString));
    switch ($day) {
        case 'Sun': return "Minggu";
        case 'Mon': return "Senin";
        case 'Tue': return "Selasa";
        case 'Wed': return "Rabu";
        case 'Thu': return "Kamis";
        case 'Fri': return "Jumat";
        case 'Sat': return "Sabtu";
        default: return "Tidak di Ketahui";
        }
    }

    private function calculateTimeoutTime($akhirJamPulang, $isShiftMalam) {
        // Validasi input: Pastikan format waktu benar
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $akhirJamPulang)) {
            \Log::error("Format akhirJamPulang tidak valid: $akhirJamPulang");
            return null; // Return null jika format salah
        }

        try {
            // Konversi akhir_jam_pulang ke timestamp
            $timestampAkhir = strtotime($akhirJamPulang);
            if ($timestampAkhir === false) {
                \Log::error("Gagal mengkonversi akhirJamPulang ke timestamp: $akhirJamPulang");
                return null;
            }

            // Kurangi 2 jam
            $timestampTimeout = strtotime("-2 hours", $timestampAkhir);
            if ($timestampTimeout === false) {
                \Log::error("Gagal menghitung timeout untuk akhirJamPulang: $akhirJamPulang");
                return null;
            }

            // Konversi kembali ke format "H:i:s"
            $timeoutTime = date("H:i:s", $timestampTimeout);

            // Logging untuk debug (opsional, hapus di production)
            \Log::info("Timeout calculated: akhirJamPulang=$akhirJamPulang, isShiftMalam=$isShiftMalam, timeoutTime=$timeoutTime");

            return $timeoutTime;
        } catch (\Exception $e) {
            // Tangani exception jika ada (misal error strtotime)
            \Log::error("Exception in calculateTimeoutTime: " . $e->getMessage());
            return null;
        }
    }

public function create(){
    $today = date("Y-m-d");
    $namahari = $this->getHari();
    $nrp = Auth::guard('karyawan')->user()->nrp;
    
    $kode_dept = Auth::guard('karyawan')->user()->kode_dept;
    
    // PERBAIKAN SHIFT MALAM: Cek apakah ada presensi hari kemarin yang belum out dan shift malam
    $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($today)));
    $presensiKemarin = DB::table('presensi')->where('tgl_presensi', $kemarin)->where('nrp', $nrp)->whereNull('jam_out')->first();
    $isShiftMalamKemarin = $presensiKemarin && $presensiKemarin->is_shift_malam == 1;
    
    // Jika shift malam kemarin belum out, gunakan hari kemarin sebagai hari kerja
    if ($isShiftMalamKemarin) {
        $hariKerja = $kemarin;
        $namahariKerja = $this->getHariFromDate($kemarin);
    } else {
        $hariKerja = $today;
        $namahariKerja = $namahari;
    }
    
    // Ubah: Ambil data presensi lengkap berdasarkan hari kerja
    $presensiHariIni = DB::table('presensi')->where('tgl_presensi', $hariKerja)->where('nrp', $nrp)->first();
    
    // Jika sudah ada jam_out, redirect dengan pesan (shift sudah selesai)
    if ($presensiHariIni && !is_null($presensiHariIni->jam_out)) {
        return redirect('/dashboard')->with(['warning' => 'Shift Anda hari ini sudah selesai. Tidak perlu absen out lagi.']);
    }
    
    $kode_cabang = Auth::guard('karyawan')->user()->kode_cabang;
    $lok_site = DB::table('cabang')->where('kode_cabang',$kode_cabang)->first();
    $jamKerja = DB::table('settings_jam_kerja')
        ->join('jam_kerja','settings_jam_kerja.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
        ->where('nrp',$nrp)->where('hari',$namahariKerja)->first(); // Gunakan hari kerja

    if($jamKerja == null){
        $jamKerja = DB::table('settings_jk_dept_detail')
            ->join('settings_jk_dept','settings_jk_dept_detail.kode_jk_dept','=','settings_jk_dept.kode_jk_dept')
            ->join('jam_kerja','settings_jk_dept_detail.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->where('kode_dept',$kode_dept)->where('hari',$namahariKerja)->first(); // Gunakan hari kerja
    }
    
    // PERUBAHAN: Cek bugar selamat berdasarkan hari kerja (hari absen in), bukan hari kalender
    $cekKemarin = DB::table('presensi')->where('tgl_presensi', $kemarin)->where('nrp', $nrp)->whereNull('jam_out')->count();
    
    $jam = date("H:i:s");
    $isTimeout = false;
    $tglBugar = $hariKerja; // Default: hari kerja (bukan hari ini)
    
    if ($cekKemarin > 0 && !$isShiftMalamKemarin) { // Hanya jika bukan shift malam kemarin
        $namahariKemarin = $this->getHariFromDate($kemarin);
        $jamKerjaKemarin = DB::table('settings_jam_kerja')
            ->join('jam_kerja','settings_jam_kerja.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->where('nrp',$nrp)->where('hari',$namahariKemarin)->first();
        if ($jamKerjaKemarin == null) {
            $jamKerjaKemarin = DB::table('settings_jk_dept_detail')
                ->join('settings_jk_dept','settings_jk_dept_detail.kode_jk_dept','=','settings_jk_dept.kode_jk_dept')
                ->join('jam_kerja','settings_jk_dept_detail.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                ->where('kode_dept', Auth::guard('karyawan')->user()->kode_dept)->where('hari',$namahariKemarin)->first();
        }
        
        if ($jamKerjaKemarin) {
            $akhirJamPulang = $jamKerjaKemarin->akhir_jam_pulang;
            $jamMasuk = $jamKerjaKemarin->jam_masuk;
            $jamPulang = $jamKerjaKemarin->jam_pulang;
            $isShiftMalam = ($jamPulang < $jamMasuk);
            
            $timeoutTime = $this->calculateTimeoutTime($akhirJamPulang, $isShiftMalam);
            
            if ($isShiftMalam) {
                $isTimeout = ($jam > $timeoutTime);
            } else {
                $isTimeout = true; // Hari kemarin sudah lewat
            }
            
            if (!$isTimeout) {
                $tglBugar = $kemarin; // Gunakan kemarin jika belum timeout
            }
        }
    }
    
    $cekBugar = DB::table('bugar_selamat')->where('nrp', $nrp)->where('tgl_presensi', $tglBugar)->count();
    if ($cekBugar == 0) {
        return redirect('/presensi/bugar-selamat');
    }
    
    if($jamKerja == null){
        return view ('layouts.presensi.notifJadwal');                
    } else {
        // Pass $presensiHariIni ke view untuk pengecekan di frontend
        return view('layouts.presensi.create', compact('presensiHariIni','lok_site','jamKerja'));
    }   
}
    public function store(Request $request)
{
    $request->validate([
        'lokasi' => 'required|string',
        'image' => 'required|string', // Base64
    ]);

    $nrp = Auth::guard('karyawan')->user()->nrp;
    $kode_cabang = Auth::guard('karyawan')->user()->kode_cabang;
    $tgl_presensi = date("Y-m-d");
    $jam = date("H:i:s");
    $kode_dept = Auth::guard('karyawan')->user()->kode_dept;

    // PERBAIKAN: Cek shift malam kemarin yang belum out
    $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($tgl_presensi)));
    $presensiKemarin = DB::table('presensi')->where('tgl_presensi', $kemarin)->where('nrp', $nrp)->whereNull('jam_out')->first();
    $isShiftMalamKemarin = $presensiKemarin && $presensiKemarin->is_shift_malam == 1;

    // Jika shift malam kemarin belum out, prioritaskan absen out untuk hari kemarin
    if ($isShiftMalamKemarin) {
        // Gunakan data presensi kemarin sebagai $cek
        $cek = $presensiKemarin;
        $hariPresensi = $kemarin; // Hari absen in adalah kemarin
    } else {
        // Shift normal: Cek presensi hari ini
        $cek = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('nrp', $nrp)->first();
        $hariPresensi = $tgl_presensi;
    }

    // LOKASI 1 (Utama) - Tetap sama
    $lok_site = DB::table('cabang')->where('kode_cabang', $kode_cabang)->first();
    $lok = explode(",", $lok_site->lokasi_cabang);
    $latitudeSite = $lok[0];
    $longitudeSite = $lok[1];
    $lokasi = $request->lokasi;
    $lokasiUser = explode(",", $lokasi);
    $latitudeUser = $lokasiUser[0];
    $longitudeUser = $lokasiUser[1];

    $jarak1 = $this->distance($latitudeSite, $longitudeSite, $latitudeUser, $longitudeUser);
    $radius1 = round($jarak1['meters']);

    // LOKASI 2 (Opsional, jika ada) - Tetap sama
    $isValidLocation = false;
    $radius2 = null;
    if ($lok_site->lokasi2_cabang) {
        $lok2 = explode(",", $lok_site->lokasi2_cabang);
        $latitudeSite2 = $lok2[0];
        $longitudeSite2 = $lok2[1];
        $jarak2 = $this->distance($latitudeSite2, $longitudeSite2, $latitudeUser, $longitudeUser);
        $radius2 = round($jarak2['meters']);
        if ($radius2 <= $lok_site->radius_cabang) {
            $isValidLocation = true;
        }
    }

    // Cek apakah dalam radius salah satu lokasi - Tetap sama
    if ($radius1 > $lok_site->radius_cabang && !$isValidLocation) {
        $errorMsg = "Maaf anda berada diluar radius absensi, jarak anda adalah " . $radius1 . " Meter dari lokasi utama";
        if ($radius2 !== null) {
            $errorMsg .= " dan " . $radius2 . " Meter dari lokasi kedua";
        }
        $errorMsg .= " dari lokasi absensi|radius";
        echo "error|" . $errorMsg;
        return;
    }

    $image = $request->image;
    $folderPath = "uploads/absensi/";
    $formatName = $nrp . "-" . $hariPresensi . "-" . ($cek ? "out" : "in"); // Gunakan $hariPresensi
    $image_parts = explode(";base64", $image);
    $image_base64 = base64_decode($image_parts[1]);
    $fileName = $formatName . ".png";
    $file = $folderPath . $fileName;

    if ($cek) {
        // Tambahan: Cek apakah jam_out sudah ada
        if (!is_null($cek->jam_out)) {
            echo "error|Anda sudah absen out hari ini. Shift sudah selesai.|out";
            return;
        }

        // PERBAIKAN SHIFT MALAM: Jika shift malam, ambil jamKerja dari hari presensi (hari in), bukan hari saat ini
        $isShiftMalam = $cek->is_shift_malam == 1; // Gunakan flag dari DB
        if ($isShiftMalam) {
            // Untuk shift malam, gunakan hari dari tgl_presensi (hari in)
            $hariPresensi = $cek->tgl_presensi; // Sudah diatur di atas
            $jamKerja = DB::table('settings_jam_kerja')
                ->join('jam_kerja', 'settings_jam_kerja.kode_jam_kerja', '=', 'jam_kerja.kode_jam_kerja')
                ->where('nrp', $nrp)->where('hari', $this->getHariFromDate($hariPresensi))->first();

            if (!$jamKerja) {
                $jamKerja = DB::table('settings_jk_dept_detail')
                    ->join('settings_jk_dept', 'settings_jk_dept_detail.kode_jk_dept', '=', 'settings_jk_dept.kode_jk_dept')
                    ->join('jam_kerja', 'settings_jk_dept_detail.kode_jam_kerja', '=', 'jam_kerja.kode_jam_kerja')
                    ->where('kode_dept', $kode_dept)->where('hari', $this->getHariFromDate($hariPresensi))->first();
            }
        } else {
            // Shift normal: Gunakan hari saat ini
            $namahari = $this->getHari();
            $jamKerja = DB::table('settings_jam_kerja')
                ->join('jam_kerja', 'settings_jam_kerja.kode_jam_kerja', '=', 'jam_kerja.kode_jam_kerja')
                ->where('nrp', $nrp)->where('hari', $namahari)->first();

            if (!$jamKerja) {
                $jamKerja = DB::table('settings_jk_dept_detail')
                    ->join('settings_jk_dept', 'settings_jk_dept_detail.kode_jk_dept', '=', 'settings_jk_dept.kode_jk_dept')
                    ->join('jam_kerja', 'settings_jk_dept_detail.kode_jam_kerja', '=', 'jam_kerja.kode_jam_kerja')
                    ->where('kode_dept', $kode_dept)->where('hari', $namahari)->first();
            }
        }

        // Validasi absen out
        $canAbsenOut = true;
        if ($isShiftMalam) {
            // Untuk shift malam: Cek timeout, tapi PERBAIKAN: Jika sudah lewat timeout, masih izinkan absen out dengan peringatan
            $timeoutTime = $this->calculateTimeoutTime($jamKerja->akhir_jam_pulang, $isShiftMalam);
            if ($timeoutTime === null) {
                echo "error|Gagal menghitung waktu timeout untuk shift malam. Hubungi tim IT.|out";
                return;
            }
            $canAbsenOut = ($jam <= $timeoutTime);
            if (!$canAbsenOut) {
                // PERBAIKAN: Izinkan absen out tapi beri peringatan terlambat
                \Log::warning("Absen out shift malam terlambat: nrp=$nrp, jam=$jam, timeout=$timeoutTime");
                // Tetap lanjutkan, tapi bisa tambah flag terlambat jika perlu
            }
        } else {
            $canAbsenOut = ($jam >= $jamKerja->awal_jam_pulang && $jam <= $jamKerja->akhir_jam_pulang);
        }

        if (!$canAbsenOut && !$isShiftMalam) { // Untuk shift normal, tetap cegah jika tidak dalam waktu
            echo "error|Belum waktunya atau sudah terlambat absen out|out";
            return;
        }

        $data_pulang = [
            'jam_out' => $jam,
            'foto_out' => $fileName,
            'lokasi_out' => $lokasi,
            'is_shift_malam' => $isShiftMalam ? 1 : 0, // Pastikan flag tetap
        ];
        $update = DB::table('presensi')->where('tgl_presensi', $hariPresensi)->where('nrp', $nrp)->update($data_pulang); // Gunakan $hariPresensi
        if ($update) {
            $pesan = $isShiftMalam && !$canAbsenOut ? "Terimakasih, Selamat Beristirahat (Absen Out Terlambat), Hati - Hati di Jalan~" : "Terimakasih, Selamat Beristirahat, Hati - Hati di Jalan~";
            echo "success|$pesan|out";
            Storage::put($file, $image_base64);
        } else {
            echo "error|Maaf absen gagal, silahkan hubungi tim IT|out";
        }
    } else {
        // Absen in: Tetap seperti sebelumnya, tapi pastikan bukan shift malam yang belum selesai
        if ($isShiftMalamKemarin) {
            echo "error|Shift malam kemarin belum selesai. Lakukan absen out terlebih dahulu.|in";
            return;
        }

        $namahari = $this->getHari();
        $jamKerja = DB::table('settings_jam_kerja')
            ->join('jam_kerja', 'settings_jam_kerja.kode_jam_kerja', '=', 'jam_kerja.kode_jam_kerja')
            ->where('nrp', $nrp)->where('hari', $namahari)->first();

        if ($jamKerja == null) {
            $jamKerja = DB::table('settings_jk_dept_detail')
                ->join('settings_jk_dept', 'settings_jk_dept_detail.kode_jk_dept', '=', 'settings_jk_dept.kode_jk_dept')
                ->join('jam_kerja', 'settings_jk_dept_detail.kode_jam_kerja', '=', 'jam_kerja.kode_jam_kerja')
                ->where('kode_dept', $kode_dept)->where('hari', $namahari)->first();
        }

        if ($jam < $jamKerja->awal_jam_masuk) {
            echo "error|Belum Waktunya Melakukan Presensi|in";
        } elseif ($jam > $jamKerja->akhir_jam_masuk) {
            echo "error|Waktu Untuk Take Absen In Sudah Habis|in";
        } else {
            $isShiftMalam = ($jamKerja->jam_pulang < $jamKerja->jam_masuk); // Deteksi shift malam
            $data = [
                'nrp' => $nrp,
                'tgl_presensi' => $tgl_presensi,
                'jam_in' => $jam,
                'foto_in' => $fileName,
                'lokasi_in' => $lokasi,
                'kode_jam_kerja' => $jamKerja->kode_jam_kerja,
                'status' => 'h',
                'is_shift_malam' => $isShiftMalam ? 1 : 0, // Tambah flag
            ];
            $simpan = DB::table('presensi')->insert($data);
            if ($simpan) {
                echo "success|Terimakasih, Selamat Bekerja~|in";
                Storage::put($file, $image_base64);
            } else {
                echo "error|Maaf absen gagal, silahkan hubungi tim IT|in";
            }
        }
    }
}
    // untuk menghitung jarak titik koordinat absensi
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }

    // Update Profile Karyawan
    public function editProfile(){
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $karyawan = DB::table('karyawan')->where('nrp', $nrp)->first();
        return view('layouts.presensi.editProfile', compact('karyawan'));

    }
    // $folderPath = "uploads/absensi/";
    // $formatName = $nrp . "-" . $tgl_presensi ."-". $ket;
    // $image_parts = explode(";base64" ,$image);
    // $image_base64 = base64_decode($image_parts[1]);
    // $fileName = $formatName . ".png";
    // $file = $folderPath . $fileName;

    public function updateProfile(Request $request){
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $nama = $request->nama;
        $telp = $request->telp;
        $password = $request->password;
        $karyawan = DB::table('karyawan')->where('nrp', $nrp)->first();

        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'telp' => 'required|string|max:15',
            'password' => 'nullable|string|min:6',
            'foto_cropped' => 'nullable|string', // Base64 string
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // Max 10MB (tingkatkan dari 5MB)
        ]);

        // Tangani foto
        $folderPath = "uploads/karyawan/fotoProfile/";
        $foto = $karyawan->foto; // Default: foto lama

        try {
            if ($request->foto_cropped) {
                // Decode base64 (dari Cropper.js)
                $imageData = $request->foto_cropped;
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $imageData = str_replace(' ', '+', $imageData);
                    $decodedImage = base64_decode($imageData);

                    if ($decodedImage === false) {
                        return Redirect::back()->with(['error' => 'Foto cropped tidak valid.']);
                    }

                    // Hapus foto lama jika ada
                    if ($karyawan->foto && Storage::exists($folderPath . $karyawan->foto)) {
                        Storage::delete($folderPath . $karyawan->foto);
                    }

                    // Simpan sebagai JPG dengan nama unik
                    $foto = $nrp . "_" . time() . ".jpg";
                    Storage::put($folderPath . $foto, $decodedImage);
                } else {
                    return Redirect::back()->with(['error' => 'Format foto cropped tidak valid.']);
                }
            } elseif ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($karyawan->foto && Storage::exists($folderPath . $karyawan->foto)) {
                    Storage::delete($folderPath . $karyawan->foto);
                }

                // Simpan file upload dengan nama unik
                $extension = $request->file('foto')->getClientOriginalExtension();
                $foto = $nrp . "_" . time() . "." . $extension;
                $request->file('foto')->storeAs($folderPath, $foto);
            }
            // Jika tidak ada foto baru, gunakan foto lama

        } catch (\Exception $e) {
            return Redirect::back()->with(['error' => 'Gagal memproses foto: ' . $e->getMessage()]);
        }

        // Siapkan data update
        $data = [
            'nama' => $nama,
            'telp' => $telp,
            'foto' => $foto
        ];

        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        // Update database
        $update = DB::table('karyawan')->where('nrp', $nrp)->update($data);
        if($update){
            return Redirect::back()->with(['success' => 'Profile berhasil diupdate']);
        }else {
            return Redirect::back()->with(['error' => 'Profile gagal diupdate']);
        }
    }
    public function histori(){
        $namaBulan = ["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
        
        return view('layouts.presensi.histori', compact('namaBulan'));
    }

    // public function getHistori(Request $request){
    //     $bulan = $request->bulan;
    //     $tahun = $request->tahun;
    //     $nik = Auth::guard('karyawan')->user()->nik;

    //     $histori = DB::table('presensi')
    //     ->whereRaw('MONTH(tgl_presensi)="'.$bulan.'"')
    //     ->whereRaw('YEAR(tgl_presensi)="'.$tahun.'"')
    //     ->whereRaw('nik',$nik)
    //     ->orderBy('tgl_presensi')
    //     ->get();

    //     return view('presensi.getHistori', compact('histori'));
    // }

    public function maintenance(){

        return  view('layouts.presensi.maintenance');
    }

    // monitoring contr
    public function monitoring(){
        return view ('layouts.presensi.monitoring');
    }

    public function getPresensi(Request $request){
        $tanggal = $request->tanggal;
        $presensi = DB::table('presensi')
        ->select('presensi.*','nama','karyawan.kode_dept','jam_masuk','nama_jam_kerja','jam_masuk','jam_pulang','keterangan')
        ->leftJoin('jam_kerja','presensi.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
        ->leftJoin('cis','presensi.kode_izin','=','cis.kode_izin')
        ->join('karyawan','presensi.nrp','=','karyawan.nrp')
        ->join('department','karyawan.kode_dept','=','department.kode_dept')
        ->where('tgl_presensi',$tanggal)
        ->get();

        return view('layouts.presensi.getPresensi',compact('presensi'));
    }

    public function showLocation(Request $request) {
        $id = $request->id;
        $presensi = DB::table('presensi')
            ->join('karyawan', 'presensi.nrp', '=', 'karyawan.nrp')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang') // Asumsi nama lokasi dari cabang
            ->where('presensi.id', $id)
            ->select(
            'presensi.*',
            'karyawan.nama',
            'karyawan.nrp',
            'cabang.nama_cabang as nama_lokasi' // Jika ada kolom nama_cabang; sesuaikan jika berbeda
            )
         ->first();

        // Jika tidak ada nama lokasi dari cabang, bisa tambahkan logika lain, e.g., dari tabel lokasi_presensi jika ada
        // Contoh: Jika ada tabel lokasi_presensi dengan id = presensi.lokasi_in (tapi lokasi_in adalah koordinat, jadi mungkin tidak)
        // Jika perlu, tambahkan: ->leftJoin('lokasi_presensi', 'presensi.lokasi_in', '=', 'lokasi_presensi.id')

        return view('layouts.presensi.showLocation', compact('presensi'));
    }

    public function report(){
        $namaBulan = ["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
        $karyawan = DB::table('karyawan')->orderBy('nama')->get();
        return view('layouts.presensi.report',compact('namaBulan','karyawan'));
    }

    public function cetakReport(Request $request){
        $nrp = $request->nrp;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namaBulan = ["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
        $karyawan = DB::table('karyawan')->where('nrp', $nrp)
        ->join('department','karyawan.kode_dept','=','department.kode_dept')
        ->first();

        $presensi = DB::table('presensi')
        ->select('presensi.*','keterangan','jam_kerja.*')
        ->leftJoin('jam_kerja','presensi.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
        ->leftJoin('cis','presensi.kode_izin','=','cis.kode_izin')
        ->where('presensi.nrp', $nrp)
        ->whereRaw('MONTH(tgl_presensi)="' . $bulan . '"')
        ->whereRaw('YEAR(tgl_presensi)="' . $tahun . '"')
        ->orderBy('tgl_presensi')
        ->get();

        if(isset($_POST['exportExcel'])){
            $nrp = $request->nrp;
            $time = date("d-M-Y H:i:s");
             // Fungsi Header dengan mengirimkan raw data excel
            header("Content-type: application/vnd-ms-excel");
            // Mendefinisikan nama dile exksport "hasil-export.xls"+
            header("Content-Disposition: attachment; filename=Rekap Presensi $nrp $time.xls");
            return view('layouts.presensi.cetakReportExcel',compact('bulan','tahun','namaBulan','karyawan','presensi'));
    }        
        return view('layouts.presensi.cetakReport',compact('bulan','tahun','namaBulan','karyawan','presensi'));
    }

    public function rekapReport(){
        $namaBulan = ["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

        return view('layouts.presensi.rekapReport',compact('namaBulan'));
    }

    public function cetakRekap(Request $request){
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $dari = $tahun . "-" . $bulan . "-01";
        $sampai = date("Y-m-t", strtotime($dari));
        $namaBulan = ["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
        
        $select_date = "";
        $field_date = "";
        $i = 1;
        while(strtotime($dari) <= strtotime($sampai)){
            
            $rangeTanggal[] = $dari;
            $select_date .= "MAX(IF(tgl_presensi = '$dari',
                        CONCAT(
                        IFNULL(jam_in,'NA'),'|',
                        IFNULL(jam_out,'NA'),'|',
                        IFNULL(presensi.status,'NA'),'|',
                        IFNULL(nama_jam_kerja,'NA'),'|',
                        IFNULL(jam_masuk,'NA'),'|',
                        IFNULL(jam_pulang,'NA'),'|',
                        IFNULL(presensi.kode_izin,'NA'),'|',
                        IFNULL(keterangan,'NA'),'|'
                        ), NULL)) as tgl_". $i . ",";
            
            $field_date .= "tgl_" . $i . ",";
            $i++;
            $dari = date("Y-m-d", strtotime("+1 day", strtotime($dari)));
        }
            $jmlHari = count($rangeTanggal);
            $lastRange = $jmlHari - 1;
            $sampai = $rangeTanggal[$lastRange];
            
            if($jmlHari==30){
                array_push($rangeTanggal, NULL);
            }else if($jmlHari==29) {
                array_push($rangeTanggal, NULL, NULL);
            }else if($jmlHari==28) {
                array_push($rangeTanggal, NULL, NULL, NULL);
            }

            $query = Karyawan::query();
            $query->selectRaw("$field_date karyawan.nrp, nama, jabatan"
            );

            $query->leftJoin(
                DB::raw("(
                SELECT 
                $select_date
                presensi.nrp
                    FROM presensi
                    LEFT JOIN jam_kerja ON presensi.kode_jam_kerja = jam_kerja.kode_jam_kerja
                    LEFT JOIN cis ON presensi.kode_izin = cis.kode_izin
                    WHERE tgl_presensi BETWEEN '$rangeTanggal[0]' AND '$sampai'
                    GROUP BY nrp
                ) presensi"),
                function($join){
                    $join->on('karyawan.nrp','=','presensi.nrp');
                }
        );

        $query->orderBy('nrp');
        $rekap = $query->get();

        // Tambahan: Logika untuk menangani shift malam agar out tetap di hari in (tidak zig-zag)
        foreach ($rekap as $r) {
            for ($i = 1; $i <= $jmlHari; $i++) {
                $tgl = "tgl_" . $i;
                if (!empty($r->$tgl)) {
                    $datapresensi = explode("|", $r->$tgl);
                    $jam_pulang = $datapresensi[5] ?? '';
                    $jam_masuk = $datapresensi[4] ?? '';
                    $jam_out = $datapresensi[1] ?? '';
                    
                    // Jika shift malam (jam_pulang < jam_masuk) dan ada out, pastikan out tetap di hari ini
                    if ($jam_pulang < $jam_masuk && !empty($jam_out) && $i < $jmlHari) {
                        // Jika hari berikutnya kosong, pindah out ke hari ini (opsional, tergantung logika view)
                        // Untuk Excel, ini akan ditangani di view
                    }
                }
            }
        }

        if(isset($_POST['exportExcel'])){
            $time = date("d-M-Y H:i:s");
            // Fungsi Header dengan mengirimkan raw data excel
            header("Content-type: application/vnd-ms-excel");
            // Mendefinisikan nama dile exksport "hasil-export.xls"+
            header("Content-Disposition: attachment; filename=Rekap Presensi Karyawan $time.xls");
            
        }
        return view('layouts.presensi.cetakRekapReport',compact('bulan','tahun','namaBulan','rekap','rangeTanggal','jmlHari'));
    }

    public function dailyReport(){
        $namaBulan = ["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

        return view('layouts.presensi.dailyReport',compact('namaBulan'));
    }

    public function cetakDailyReport(Request $request){
        $tahunSekarang = date('Y'); // Hitung tahun sekarang
        $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
        ]);

        $tanggal = $request->tanggal; //format YYYY-MM-DD
        $bulan = date('m', strtotime($tanggal));
        $tahun = date('Y', strtotime($tanggal));
        $namaBulan = ["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

        //Query data presensi untuk tanggal spesifik
        $query = Karyawan::query();
        $query->select(
            'karyawan.nrp',
            'karyawan.nama',
            'karyawan.jabatan',
            'karyawan.kode_dept',
            'presensi.tgl_presensi',
            'presensi.jam_in',
            'presensi.jam_out',
            'presensi.status',
            'jam_kerja.nama_jam_kerja',
            'jam_kerja.jam_masuk',
            'jam_kerja.jam_pulang',
            'cis.kode_izin',
            'cis.keterangan'
        );

        $query->leftJoin('presensi', function($join) use ($tanggal) {
            $join->on('karyawan.nrp', '=', 'presensi.nrp')
                ->where('presensi.tgl_presensi', '=', $tanggal);
        });

        $query->leftJoin('jam_kerja', 'presensi.kode_jam_kerja', '=', 'jam_kerja.kode_jam_kerja');
        $query->leftJoin('cis', 'presensi.kode_izin', '=', 'cis.kode_izin');

        $query->orderBy('karyawan.nrp');

        $rekap = $query->get(); // Data harian per karyawan

        // Tambahan: Logika untuk menangani shift malam agar out tetap di hari in (tidak zig-zag)
        foreach ($rekap as $r) {
            if (!empty($r->jam_pulang) && !empty($r->jam_masuk) && $r->jam_pulang < $r->jam_masuk && !empty($r->jam_out)) {
                // Jika shift malam dan ada out, pastikan out tetap di hari ini (sudah ditangani oleh query, tapi tambahkan validasi)
                // Untuk Excel, ini akan ditangani di view
            }
        }

        // Export to Excel jika diminta
        if(isset($_POST['exportExcel'])){
            $time = date("d-M-Y H:i:s");
                // Fungsi Header dengan mengirimkan raw data excel
            header("Content-type: application/vnd-ms-excel");
                // Mendefinisikan nama dile exksport "hasil-export.xls"+
            header("Content-Disposition: attachment; filename=Rekap Presensi Harian $tanggal $time.xls");
        }
        return view('layouts.presensi.cetakDailyReport',compact('tanggal','bulan','tahun','namaBulan','rekap'));
    }

     public function izin(Request $request){
        $nrp = Auth::guard('karyawan')->user()->nrp;

        if(!empty($request->bulan) && !empty($request->tahun)){
        $data_izin = DB::table('cis')
            ->leftJoin('master_cuti','cis.kode_cuti','=','master_cuti.kode_cuti')
            ->orderBy('tgl_izin_dari','desc')
            ->where('nrp',$nrp)
            ->whereRaw('MONTH(tgl_izin_dari)="'.$request->bulan.'"')
            ->whereRaw('YEAR(tgl_izin_dari)="'.$request->tahun.'"')
            ->get();
        } else {
        $data_izin = DB::table('cis')
                    ->leftJoin('master_cuti','cis.kode_cuti','=','master_cuti.kode_cuti')
                    ->orderBy('tgl_izin_dari','desc')
                    ->where('nrp',$nrp)->limit(5)->orderBy('tgl_izin_dari','desc')
                    ->get();
        }
        
        $namaBulan = ["","Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November","Desember"];
        return  view('layouts.presensi.cis.izin',compact('data_izin','namaBulan'));
    }

    public function buatIzin(){
        return view('layouts.presensi.cis.buatIzin');
    }

    public function storeIzin(Request $request){
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $tgl_izin = $request->tgl_izin;
        $status = $request->status;
        $keterangan = $request->keterangan;

        $data = [
            'nrp' => $nrp,
            'tgl_izin' => $tgl_izin,
            'status' => $status,
            'keterangan' => $keterangan
        ];

        $simpan = DB::table('cis')->insert($data);

        if($simpan) {
            return redirect('/presensi/cis/izin')->with(['success' => 'Data Berhasil di Kirim']);
        } else {
            return redirect('/presensi/cis/izin')->with(['success' => 'Data Gagal di Kirim']);
        }
    }

    public function monitoringCis(Request $request){
        $query = Cis::query();
        $query->select('kode_izin','tgl_izin_dari','tgl_izin_sampai','cis.nrp','nama','jabatan','status','status_approved','keterangan');
        $query->join('karyawan','cis.nrp','=','karyawan.nrp');
        if(!empty($request->dari) && !empty($request->sampai)) {
            $query->whereBetween('tgl_izin',[$request->dari, $request->sampai]);
        }

        if(!empty($request->nrp)) {
            $query->where('cis.nrp',$request->nrp);
        }
        
        if(!empty($request->nama)) {
            $query->where('nama','like','%' . $request->nama . '%');
        }
        
        if($request->status_approved === '0'|| $request->status_approved === '1'|| $request->status_approved === '2' ){
            $query->where('status_approved',$request->status_approved);
        }
        $query->orderBy('tgl_izin_dari','desc');
        $cis = $query->paginate(10);
        $cis -> appends($request->all());
        
        return view('layouts.presensi.cis.monitoringCis',compact('cis'));
    }

    public function approveCis(Request $request){
        $status_approved = $request->status_approved;
        $kode_izin = $request->kode_izin_form;
        $dataIzin = DB::table('cis')->where('kode_izin',$kode_izin)->first();
        $nrp = $dataIzin->nrp;
        $tgl_dari = $dataIzin->tgl_izin_dari;
        $tgl_sampai = $dataIzin->tgl_izin_sampai;
        $status = $dataIzin->status;

        DB::beginTransaction();
        try {
            if($status_approved == "1"){
                while(strtotime($tgl_dari) <= strtotime($tgl_sampai)){

                DB::table('presensi')->insert([
                    'nrp' => $nrp,
                    'tgl_presensi'=> $tgl_dari,
                    'status' => $status,
                    'kode_izin' => $kode_izin
                ]);
                $tgl_dari = date("Y-m-d", strtotime("+1 days",strtotime($tgl_dari)));
            }
        }
            
            DB::table('cis')->where('kode_izin', $kode_izin)->update([
                'status_approved' => $status_approved
            ]);
            DB::commit();
            return Redirect::back()->with(['success' => 'Data Berhasil di Proses']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => 'Data Gagal di Proses']);
        }
    }

    public function cancelCis($kode_izin){
        
        DB::beginTransaction();
        try {
            DB::table('cis')->where('kode_izin',$kode_izin)->update([
            'status_approved' => 0
        ]);
        DB::table('presensi')->where('kode_izin',$kode_izin)->delete();
        DB::commit();
        return Redirect::back()->with(['success'=>'Data Berhasil di Batalkan']);
        } catch (\Exception $e) {
            DB::rollBack();
        return Redirect::back()->with(['warning'=>'Data Gagal di Batalkan']);
        }

        // $update = DB::table('cis')->where('id', $id)->update([
        //     'status_approved' => 0
        // ]);
        // if($update) {
        //     return Redirect::back()->with(['success' => 'Data Berhasil di Update']);
        // } else {
        //     return Redirect::back()->with(['warning' => 'Data Gagal di Update']);
        // }
    }

    public function showact($kode_izin){

        $dataIzin = DB::table('cis')->where('kode_izin',$kode_izin)->first();
        return view('layouts.presensi.cis.showact',compact('dataIzin'));
    }

    public function deleteIzin($kode_izin){
        
        $cekDataIzin = DB::table('cis')->where('kode_izin',$kode_izin)->first();
        $doc_cis = $cekDataIzin->doc_cis;
        try {
            DB::table('cis')->where('kode_izin',$kode_izin)->delete();
            if ($doc_cis != null){
                Storage::delete('/uploads/cis/'.$doc_cis);
            }
            return redirect('/presensi/cis/izin')->with(['success' => 'Data berhasil di Hapus']);
        } catch (\Exception $e) {
            //throw $th;
            return redirect('/presensi/cis/izin')->with(['warning' => 'Data Gagal di Hapus']);
        }
    }

    public function bugarSelamat() {
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $today = date("Y-m-d");
        $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($today)));
        
        $cekKemarin = DB::table('presensi')->where('nrp', $nrp)->where('tgl_presensi', $kemarin)->whereNull('jam_out')->count();
        $jam = date("H:i:s");
        $isTimeout = false;
        $tglBugar = $today;
        
        if ($cekKemarin > 0) {
            $namahariKemarin = $this->getHariFromDate($kemarin);
            $jamKerjaKemarin = DB::table('settings_jam_kerja')
                ->join('jam_kerja','settings_jam_kerja.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                ->where('nrp',$nrp)->where('hari',$namahariKemarin)->first();
            if ($jamKerjaKemarin == null) {
                $jamKerjaKemarin = DB::table('settings_jk_dept_detail')
                    ->join('settings_jk_dept','settings_jk_dept_detail.kode_jk_dept','=','settings_jk_dept.kode_jk_dept')
                    ->join('jam_kerja','settings_jk_dept_detail.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                    ->where('kode_dept', Auth::guard('karyawan')->user()->kode_dept)->where('hari',$namahariKemarin)->first();
            }
            
            if ($jamKerjaKemarin) {
                $akhirJamPulang = $jamKerjaKemarin->akhir_jam_pulang;
                $jamMasuk = $jamKerjaKemarin->jam_masuk;
                $jamPulang = $jamKerjaKemarin->jam_pulang;
                $isShiftMalam = ($jamPulang < $jamMasuk);
                
                $timeoutTime = $this->calculateTimeoutTime($akhirJamPulang, $isShiftMalam);
                
                if ($isShiftMalam) {
                    $isTimeout = ($jam > $timeoutTime);
                } else {
                    $isTimeout = true; // Hari kemarin sudah lewat
                }
                
                if (!$isTimeout) {
                    $tglBugar = $kemarin;
                }
            }
        }
        
        $cek = DB::table('bugar_selamat')->where('nrp', $nrp)->where('tgl_presensi', $tglBugar)->count();
        if ($cek > 0) {
            return redirect('/presensi/create')->with(['warning' => 'Anda sudah mengisi data Bugar Selamat untuk shift ini.']);
        }
        
        return view('layouts.presensi.bugar.bugarSelamat');
    }
    
    // Fungsi untuk menyimpan data bugar selamat
    public function storeBugarSelamat(Request $request) {
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $today = date("Y-m-d");
        $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($today)));

        $cekKemarin = DB::table('presensi')->where('nrp', $nrp)->where('tgl_presensi', $kemarin)->whereNull('jam_out')->count();
        $jam = date("H:i:s");
        $isTimeout = false;
        $tglBugar = $today;
        
        if ($cekKemarin > 0) {
            $namahariKemarin = $this->getHariFromDate($kemarin);
            $jamKerjaKemarin = DB::table('settings_jam_kerja')
                ->join('jam_kerja','settings_jam_kerja.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                ->where('nrp',$nrp)->where('hari',$namahariKemarin)->first();
            if ($jamKerjaKemarin == null) {
                $jamKerjaKemarin = DB::table('settings_jk_dept_detail')
                    ->join('settings_jk_dept','settings_jk_dept_detail.kode_jk_dept','=','settings_jk_dept.kode_jk_dept')
                    ->join('jam_kerja','settings_jk_dept_detail.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                    ->where('kode_dept', Auth::guard('karyawan')->user()->kode_dept)->where('hari',$namahariKemarin)->first();
            }
            
            if ($jamKerjaKemarin) {
                $akhirJamPulang = $jamKerjaKemarin->akhir_jam_pulang;
                $jamMasuk = $jamKerjaKemarin->jam_masuk;
                $jamPulang = $jamKerjaKemarin->jam_pulang;
                $isShiftMalam = ($jamPulang < $jamMasuk);
                
                $timeoutTime = $this->calculateTimeoutTime($akhirJamPulang, $isShiftMalam);
                
                if ($isShiftMalam) {
                    $isTimeout = ($jam > $timeoutTime);
                } else {
                    $isTimeout = true;
                }
                
                if (!$isTimeout) {
                    $tglBugar = $kemarin;
                }
            }
        }
        
        // Validasi input dengan pesan custom
        $request->validate([
            'jam_tidur' => 'required|integer|min:1|max:24',
            'minum_obat' => 'required|in:ya,tidak',
        ], [
            'jam_tidur.required' => 'Jam tidur wajib diisi.',
            'jam_tidur.integer' => 'Jam tidur harus berupa angka.',
            'jam_tidur.min' => 'Jam tidur minimal 1 jam.',
            'jam_tidur.max' => 'Jam tidur maksimal 24 jam.',
            'minum_obat.required' => 'Pilihan minum obat wajib dipilih.',
            'minum_obat.in' => 'Pilihan minum obat tidak valid.',
        ]);
        
        $cek = DB::table('bugar_selamat')->where('nrp', $nrp)->where('tgl_presensi', $tglBugar)->count();
        if ($cek > 0) {
            \Log::info("Bugar Selamat sudah diisi untuk nrp: $nrp, tgl: $tglBugar");
            return response()->json(['error' => 'Anda sudah mengisi data Bugar Selamat untuk shift ini.'], 400);
        }
        
        $data = [
            'nrp' => $nrp,
            'tgl_presensi' => $tglBugar,
            'jam_tidur' => $request->jam_tidur,
            'minum_obat' => $request->minum_obat,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            $simpan = DB::table('bugar_selamat')->insert($data);
            if ($simpan) {
                \Log::info("Bugar Selamat berhasil disimpan untuk nrp: $nrp, tgl: $tglBugar");
                return response()->json(['success' => 'Data Bugar Selamat berhasil disimpan.']);
            } else {
                \Log::error("Gagal menyimpan Bugar Selamat untuk nrp: $nrp, tgl: $tglBugar - Insert gagal");
                return response()->json(['error' => 'Gagal menyimpan data Bugar Selamat. Silakan coba lagi.'], 500);
            }
        } catch (\Exception $e) {
            \Log::error("Exception di storeBugarSelamat: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem. Silakan coba lagi.'], 500);
        }
    }
    
    // public function gantiShift(){
    //     $nrp = Auth::guard('karyawan')->user()->nrp;
    //     $today = date("Y-m-d");
    //     $hariSekarang = $this->getHari(); // Menggunakan method getHari yang sudah ada
    //     $tanggalSekarang = date("d-m-Y");
    //     // Cek apakah sudah absen "in" hari ini
    //     $cek = DB::table('presensi')->where('tgl_presensi', $today)->where('nrp', $nrp)->count();
    //     if($cek > 0){
    //         return redirect('/presensi/create')->with(['warning' => 'Anda sudah absen hari ini, tidak bisa ganti shift.']);
    //     }

    //     // Ambil daftar shift yang tersedia dari admin
    //     $jamKerja = DB::table('jam_kerja')->orderBy('nama_jam_kerja')->get();

    //     // Ambil setting shift untuk hari ini saja (jika ada)
    //     $currentShift = DB::table('settings_jam_kerja')
    //         ->where('nrp', $nrp)
    //         ->where('hari', $hariSekarang)
    //         ->first();

    //     return view('layouts.presensi.gantiShift', compact('jamKerja', 'currentShift', 'hariSekarang','tanggalSekarang'));
    // }

    public function updateShiftAjax(Request $request)
    {
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $today = date("Y-m-d");
        $hariSekarang = $this->getHari();

        // Cek apakah sudah absen "in" hari ini
        $cek = DB::table('presensi')->where('tgl_presensi', $today)->where('nrp', $nrp)->count();
        if ($cek > 0) {
            return response()->json(['error' => 'Anda sudah absen hari ini, tidak bisa ganti shift.'], 400);
        }

        $kode_jam_kerja = $request->kode_jam_kerja;

        // Validasi input
        if (empty($kode_jam_kerja)) {
            return response()->json(['error' => 'Pilih shift untuk hari ini.'], 400);
        }

        // Cek apakah kode_jam_kerja valid
        $jamKerjaExists = DB::table('jam_kerja')->where('kode_jam_kerja', $kode_jam_kerja)->exists();
        if (!$jamKerjaExists) {
            return response()->json(['error' => 'Shift tidak valid.'], 400);
        }

        DB::beginTransaction();
        try {
            // Hapus setting lama untuk hari ini (jika ada)
            DB::table('settings_jam_kerja')->where('nrp', $nrp)->where('hari', $hariSekarang)->delete();

            // Insert setting baru untuk hari ini
            DB::table('settings_jam_kerja')->insert([
                'nrp' => $nrp,
                'hari' => $hariSekarang,
                'kode_jam_kerja' => $kode_jam_kerja
            ]);

            DB::commit();

            // Reload halaman atau kirim response sukses
            return response()->json(['success' => 'Shift untuk hari ini berhasil diganti. Silakan refresh halaman untuk melihat perubahan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error ganti shift: " . $e->getMessage()); // Log untuk debug
            return response()->json(['error' => 'Gagal mengganti shift. Coba lagi.'], 500);
        }
    }
}