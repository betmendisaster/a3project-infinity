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
   public function create(){
    
        // validasi absen untuk absen pulang
        $today = date("Y-m-d");
        $namahari = $this->getHari();
        $nrp = Auth::guard('karyawan')->user()->nrp;
        
        // PERUBAHAN: Cek bugar selamat berdasarkan hari kerja (hari absen in), bukan hari kalender
        $cek = DB::table('presensi')->where('tgl_presensi', $today)->where('nrp', $nrp)->count();
        $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($today)));
        $cekKemarin = DB::table('presensi')->where('tgl_presensi', $kemarin)->where('nrp', $nrp)->whereNull('jam_out')->count();
        
        if ($cek == 0 && $cekKemarin > 0) {
            // Shift malam: Sudah absen in hari kemarin, cek bugar untuk hari kemarin
            $cekBugar = DB::table('bugar_selamat')->where('nrp', $nrp)->where('tgl_presensi', $kemarin)->count();
            if($cekBugar == 0){
                return redirect('/presensi/bugar-selamat');
            }
        } else {
            // Shift normal atau belum absen: Cek bugar untuk hari ini
            $cekBugar = DB::table('bugar_selamat')->where('nrp', $nrp)->where('tgl_presensi', $today)->count();
            if($cekBugar == 0){
                return redirect('/presensi/bugar-selamat');
            }
        }
        
        $kode_dept = Auth::guard('karyawan')->user()->kode_dept;
        $cek = DB::table('presensi')->where('tgl_presensi', $today)->where('nrp', $nrp)->count();
        $kode_cabang = Auth::guard('karyawan')->user()->kode_cabang;
        $lok_site = DB::table('cabang')->where('kode_cabang',$kode_cabang)->first();
        $jamKerja = DB::table('settings_jam_kerja')
            ->join('jam_kerja','settings_jam_kerja.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->where('nrp',$nrp)->where('hari',$namahari)->first();

        if($jamKerja == null){
            $jamKerja = DB::table('settings_jk_dept_detail')
                ->join('settings_jk_dept','settings_jk_dept_detail.kode_jk_dept','=','settings_jk_dept.kode_jk_dept')
                ->join('jam_kerja','settings_jk_dept_detail.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                ->where('kode_dept',$kode_dept)->where('hari',$namahari)->first();
            }
        // Tambahkan ini setelah $cekKemarin = DB::table('presensi')->where('tgl_presensi', $kemarin)->where('nrp', $nrp)->whereNull('jam_out')->count();
        if ($cekKemarin > 0) {
            // Ambil jamKerja kemarin untuk validasi
            $namahariKemarin = $this->getHariFromDate($kemarin);
            $jamKerjaKemarin = DB::table('settings_jam_kerja')
                ->join('jam_kerja','settings_jam_kerja.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                ->where('nrp',$nrp)->where('hari',$namahariKemarin)->first();
            if ($jamKerjaKemarin == null) {
                $jamKerjaKemarin = DB::table('settings_jk_dept_detail')
                    ->join('settings_jk_dept','settings_jk_dept_detail.kode_jk_dept','=','settings_jk_dept.kode_jk_dept')
                    ->join('jam_kerja','settings_jk_dept_detail.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                    ->where('kode_dept',$kode_dept)->where('hari',$namahariKemarin)->first();
            }
            
            // Jika shift malam (jam_pulang < jam_masuk) dan waktu sekarang > akhir_jam_pulang, anggap shift selesai tanpa update jam_out
            if ($jamKerjaKemarin && $jamKerjaKemarin->jam_pulang < $jamKerjaKemarin->jam_masuk && $jam > $jamKerjaKemarin->akhir_jam_pulang) {
                // Jangan update jam_out, biarkan null. Hanya set agar bisa absen in hari ini
                $cekKemarin = 0;  // Anggap shift selesai, sehingga hari ini bisa absen in
                // Opsional: Tambahkan flash message untuk user
                session()->flash('warning', 'Shift kemarin dianggap selesai karena timeout. Jam out tidak tercatat.');
            }
        }
        
        if($jamKerja == null){
            return view ('layouts.presensi.notifJadwal');                
        } else {
            return view('layouts.presensi.create', compact('cek','lok_site','jamKerja'));
        }   
    }

    public function store (Request $request){
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $kode_cabang = Auth::guard('karyawan')->user()->kode_cabang;
        $tgl_presensi = date("Y-m-d");
        $jam = date("H:i:s");
        $kode_dept = Auth::guard('karyawan')->user()->kode_dept;
        // LOKASI 1
        $lok_site = DB::table('cabang')->where('kode_cabang',$kode_cabang)->first();
        $lok = explode(",",$lok_site->lokasi_cabang);
        $latitudeSite = $lok[0];
        $longitudeSite = $lok[1];
        $lokasi = $request->lokasi;
        $lokasiUser = explode("," , $lokasi);
        $latitudeUser = $lokasiUser[0];
        $longitudeUser = $lokasiUser[1];

        $jarak  = $this->distance($latitudeSite, $longitudeSite, $latitudeUser, $longitudeUser);
        $radius = round($jarak['meters']);
        // CEK SHIFT KERJA KARYAWAN
        $namahari = $this->getHari();
        $jamKerja = DB::table('settings_jam_kerja')
            ->join('jam_kerja','settings_jam_kerja.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->where('nrp',$nrp)->where('hari',$namahari)->first();
        
        if($jamKerja == null){
            $jamKerja = DB::table('settings_jk_dept_detail')
                ->join('settings_jk_dept','settings_jk_dept_detail.kode_jk_dept','=','settings_jk_dept.kode_jk_dept')
                ->join('jam_kerja','settings_jk_dept_detail.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                ->where('kode_dept',$kode_dept)->where('hari',$namahari)->first();
            }

        $cek = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('nrp', $nrp)->count();
        if($cek >0){
            $ket = "out";
        }else {
            $ket = "in";
        }
        $image = $request->image;
        // mengubah folder default ada di config/filesystems.php
        $folderPath = "uploads/absensi/";
        $formatName = $nrp . "-" . $tgl_presensi ."-". $ket;
        $image_parts = explode(";base64" ,$image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png";
        $file = $folderPath . $fileName;
       
        $cek = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('nrp', $nrp)->count();
        if($radius > $lok_site->radius_cabang){
            echo "error|Maaf anda berada diluar radius absensi, jarak anda adalah ".$radius." Meter dari lokasi absensi|radius";
        }else {
            if($cek > 0){
                // PERUBAHAN: Tambahkan validasi awal dan akhir jam pulang
                if($jam < $jamKerja->awal_jam_pulang){ // BARU: Belum waktunya absen out
                    echo "error|Belum Waktunya Melakukan Absen Out|out";
                } elseif($jam > $jamKerja->akhir_jam_pulang){ // BARU: Sudah terlambat absen out
                    echo "error|Waktu Absen Out Sudah Habis|out";
                } else {
                    $data_pulang= [
                        'jam_out' => $jam,
                        'foto_out' => $fileName,
                        'lokasi_out' => $lokasi
                    ];
                    $update = DB::table('presensi')->where('tgl_presensi',$tgl_presensi)->where('nrp',$nrp)->update($data_pulang); 
                    if ($update) {
                        echo "success|Terimakasih, Selamat Beristirahat, Hati - Hati di Jalan~|out";
                        Storage::put($file, $image_base64);
                    }else {
                        echo "error|Maaf absen gagal, silahkan hubungi tim IT|out";
                    }
                }              
            } else {
                if ($jam < $jamKerja->awal_jam_masuk){
                    echo "error|Belum Waktunya Melakukan Presensi|in";
                } else if($jam >$jamKerja->akhir_jam_masuk){
                    echo "error|Waktu Untuk Take Absen In Sudah Habis|in";
                } else{
                    $data = [
                        'nrp' => $nrp,
                        'tgl_presensi' => $tgl_presensi,
                        'jam_in' => $jam,
                        'foto_in' => $fileName,
                        'lokasi_in' => $lokasi,
                        'kode_jam_kerja' => $jamKerja->kode_jam_kerja,
                        'status' => 'h'
                    ];
                    $simpan = DB::table('presensi')->insert($data);
                    if ($simpan) {
                        echo "success|Terimakasih, Selamat Bekerja~|in";
                        Storage::put($file, $image_base64);
                    }else {
                        echo "error|Maaf absen gagal, silahkan hubungi tim IT|in";
                    }
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
        $foto = $request->foto;
        $password = Hash::make($request->password);
        $karyawan = DB::table('karyawan')->where('nrp', $nrp)->first();
        if ($request->hasFile('foto')){
            $foto = $nrp .".". $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = $karyawan->foto;
        }
    
        if (empty($request->password)) {
        $data = [
            'nama' => $nama,
            'telp' => $telp,
            'foto' => $foto
        ];
    } else {
        $data = [
            'nama' => $nama,
            'telp' => $telp,
            'password' => $password,
            'foto' => $foto
        ];
    }

    $update = DB::table('karyawan')->where('nrp', $nrp)->update($data);
    if($update){
        if($request->hasFile('foto')){
            $folderPath = "uploads/karyawan/fotoProfile/";
            $request->file('foto')->storeAs($folderPath, $foto);
        }
        return Redirect::back()->with(['success' => 'Profile berhasil di Upadate']);
    }else {
        return Redirect::back()->with(['error' => 'Profile gagal di Update']);
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
    
    // Cek apakah sudah isi untuk hari ini
    $cek = DB::table('bugar_selamat')->where('nrp', $nrp)->where('tgl_presensi', $today)->count();
    
    if ($cek > 0) {
        // Jika sudah isi, redirect ke presensi
        return redirect('/presensi/create');
    }
    
    return view('layouts.presensi.bugar.bugarSelamat');
    }
    
        // Fungsi untuk menyimpan data bugar selamat
    public function storeBugarSelamat(Request $request) {
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $today = date("Y-m-d");
        $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($today)));

        // PERUBAHAN: Tentukan hari kerja untuk bugar selamat berdasarkan shift
        $cekKemarin = DB::table('presensi')->where('nrp', $nrp)->where('tgl_presensi', $kemarin)->whereNull('jam_out')->count();
        $tglBugar = $cekKemarin > 0 ? $kemarin : $today; // Jika shift malam (ada record kemarin jam_out null), gunakan hari kemarin; jika tidak, hari ini

        // Validasi input
        $request->validate([
            'jam_tidur' => 'required|integer|min:1|max:24',
            'minum_obat' => 'required|in:ya,tidak',
        ]);

        // PERUBAHAN: Cek apakah sudah isi untuk hari kerja tersebut (bukan hari kalender)
        $cek = DB::table('bugar_selamat')->where('nrp', $nrp)->where('tgl_presensi', $tglBugar)->count();

        if ($cek > 0) {
            // Jika sudah isi, redirect ke presensi
            return redirect('/presensi/create')->with(['warning' => 'Anda sudah mengisi data Bugar Selamat untuk shift ini.']);
        }
        
        // Simpan data
        $data = [
            'nrp' => $nrp,
            'tgl_presensi' => $tglBugar, // PERUBAHAN: Gunakan hari kerja, bukan $today
            'jam_tidur' => $request->jam_tidur,
            'minum_obat' => $request->minum_obat,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            $simpan = DB::table('bugar_selamat')->insert($data);
            if ($simpan) {
                return response()->json(['success' => 'Data Bugar Selamat berhasil disimpan.']);
            } else {
                return response()->json(['error' => 'Gagal menyimpan data Bugar Selamat. Silakan coba lagi.'], 500);
            }
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Error menyimpan data Bugar Selamat: ' . $e->getMessage());
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

    public function updateShiftAjax(Request $request){
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $today = date("Y-m-d");
        $hariSekarang = $this->getHari();

        // Cek apakah sudah absen "in" hari ini
        $cek = DB::table('presensi')->where('tgl_presensi', $today)->where('nrp', $nrp)->count();
        if($cek > 0){
            return response()->json(['error' => 'Anda sudah absen hari ini, tidak bisa ganti shift.'], 400);
        }

        $kode_jam_kerja = $request->kode_jam_kerja;

        // Validasi input
        if(empty($kode_jam_kerja)){
            return response()->json(['error' => 'Pilih shift untuk hari ini.'], 400);
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
            return response()->json(['success' => 'Shift untuk hari ini berhasil diganti.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal mengganti shift.'], 500);
        }
    }
}