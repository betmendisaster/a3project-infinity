<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menghitung waktu timeout berdasarkan akhir_jam_pulang dikurangi 2 jam.
     * Timeout digunakan untuk menentukan kapan shift dianggap selesai tanpa jam_out,
     * sehingga bugar selamat beralih ke hari ini lebih awal.
     *
     * @param string $akhirJamPulang Waktu akhir jam pulang dalam format "H:i:s" (misal "05:00:00").
     * @param bool $isShiftMalam True jika shift malam (jam_pulang < jam_masuk), false jika normal.
     * @return string|null Waktu timeout dalam format "H:i:s" (misal "03:00:00"), atau null jika error.
     */
    private function calculateTimeoutTime($akhirJamPulang, $isShiftMalam) {
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $akhirJamPulang)) {
            \Log::error("Format akhirJamPulang tidak valid: $akhirJamPulang");
            return null;
        }

        try {
            $timestampAkhir = strtotime($akhirJamPulang);
            if ($timestampAkhir === false) {
                \Log::error("Gagal mengkonversi akhirJamPulang ke timestamp: $akhirJamPulang");
                return null;
            }

            $timestampTimeout = strtotime("-2 hours", $timestampAkhir);
            if ($timestampTimeout === false) {
                \Log::error("Gagal menghitung timeout untuk akhirJamPulang: $akhirJamPulang");
                return null;
            }

            $timeoutTime = date("H:i:s", $timestampTimeout);
            \Log::info("Timeout calculated: akhirJamPulang=$akhirJamPulang, isShiftMalam=$isShiftMalam, timeoutTime=$timeoutTime");
            return $timeoutTime;
        } catch (\Exception $e) {
            \Log::error("Exception in calculateTimeoutTime: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengembalikan nama hari dalam bahasa Indonesia berdasarkan string tanggal.
     *
     * @param string $dateString Tanggal dalam format "Y-m-d".
     * @return string Nama hari (misal "Senin").
     */
    private function getHariFromDate($dateString) {
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

    public function index()
    {  
        $hariIni = date("Y-m-d");
        $bulanIni = date("m") * 1; // 1 atau Januari
        $tahunIni = date('Y'); // 2025
        $jam = date("H:i:s"); // 16:20:25
        $nrp = Auth::guard('karyawan')->user()->nrp;
        
        // Hitung hari kerja aktif untuk presensi (sama seperti di PresensiController::create())
        $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($hariIni)));
        $cekKemarin = DB::table('presensi')->where('tgl_presensi', $kemarin)->where('nrp', $nrp)->whereNull('jam_out')->count();
        
        $isTimeout = false;
        $activePresensiDate = $hariIni; // Default: hari ini
        
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
                
                if ($timeoutTime !== null) {
                    if ($isShiftMalam) {
                        $isTimeout = ($jam > $timeoutTime);
                    } else {
                        $isTimeout = true; // Hari kemarin sudah lewat
                    }
                }
                
                if (!$isTimeout) {
                    $activePresensiDate = $kemarin; // Gunakan kemarin jika belum timeout
                }
            }
        }
        
        // Ambil presensi berdasarkan hari kerja aktif
        $presensiHariIni = DB::table('presensi')->where('nrp', $nrp)->where('tgl_presensi', $activePresensiDate)->first();
        
        // Data histori bulan ini untuk modal (sudah ada, tapi pastikan join lengkap)
        $historiBulanIni = DB::table('presensi')
            ->select('presensi.*','keterangan','jam_kerja.*','doc_cis','nama_cuti')
            ->leftJoin('jam_kerja','presensi.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->leftJoin('cis','presensi.kode_izin','=','cis.kode_izin')
            ->leftJoin('master_cuti','cis.kode_cuti','=','master_cuti.kode_cuti')  // Perbaikan: Join ke master_cuti berdasarkan kode_cuti, bukan kode_izin
            ->where('presensi.nrp', $nrp)
            ->whereRaw('MONTH(tgl_presensi)="' . $bulanIni . '"')
            ->whereRaw('YEAR(tgl_presensi)="' . $tahunIni . '"')
            ->orderBy('tgl_presensi')
            ->get();
        
        // Rekap presensi bulan ini
        $rekapPresensi = DB::table('presensi')
            ->selectRaw('COUNT(nrp) as totHadir, SUM(IF(jam_in > jam_masuk ,1,0)) as totLate' )
            ->leftJoin('jam_kerja','presensi.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->where('nrp', $nrp)
            ->whereRaw('MONTH(tgl_presensi)="' . $bulanIni . '"')
            ->whereRaw('YEAR(tgl_presensi)="' . $tahunIni . '"')
            ->first();

        $namaBulan = ["","Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November","Desember"];
        
        // Rekap CIS bulan ini
        $rekapCis = DB::table('cis')
            ->selectRaw('SUM(IF(status="i",1,0)) as jmlIzin,SUM(IF(status="s",1,0)) as jmlSakit')
            ->where('nrp',$nrp)
            ->whereRaw('MONTH(tgl_izin_dari)="' . $bulanIni . '"')
            ->whereRaw('YEAR(tgl_izin_dari)="' . $tahunIni . '"')
            ->where('status_approved',1)
            ->first();

        // Kirim semua data ke view
        return view("dashboard.dashboard", compact('presensiHariIni', 'historiBulanIni', 'namaBulan', 'bulanIni', 'tahunIni', 'rekapPresensi', 'rekapCis'));
    }

    // Method dashboardadmin() tetap sama, tidak perlu perubahan
    public function dashboardadmin(){
        $totalUsers = DB::table('karyawan')->count();
        $hariIni = date("Y-m-d");
        $rekapPresensi = DB::table('presensi')
            ->selectRaw('COUNT(nrp) as totHadir, SUM(IF(jam_in > "06:50",1,0)) as totLate' )
            ->where('tgl_presensi',$hariIni)
            ->first();

        $rekapCis = DB::table('cis')
        ->selectRaw('SUM(IF(status="i",1,0)) as jmlIzin,SUM(IF(status="s",1,0)) as jmlSakit')
        ->where('tgl_izin_dari',$hariIni)
        ->where('status_approved',1)
        ->first();
        return view('dashboard.dashboardadmin', compact('rekapPresensi', 'totalUsers','rekapCis'));
    }
}