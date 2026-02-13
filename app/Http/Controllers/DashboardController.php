<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // private function calculateTimeoutTime($akhirJamPulang, $isShiftMalam) {
    //     if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $akhirJamPulang)) {
    //         \Log::error("Format akhirJamPulang tidak valid: $akhirJamPulang");
    //         return null;
    //     }

    //     try {
    //         $timestampAkhir = strtotime($akhirJamPulang);
    //         if ($timestampAkhir === false) return null;

    //         $timestampTimeout = strtotime("-2 hours", $timestampAkhir);
    //         if ($timestampTimeout === false) return null;

    //         return date("H:i:s", $timestampTimeout);
    //     } catch (\Exception $e) {
    //         \Log::error("Exception in calculateTimeoutTime: " . $e->getMessage());
    //         return null;
    //     }
    // }

    private function getHariFromDate($dateString) {
        $day = date("D", strtotime($dateString));
        return match($day) {
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu',
            default => 'Tidak di Ketahui',
        };
    }

    // PERBAIKAN: Tambahkan fungsi baru untuk cek shift selesai (untuk bottom navbar)
    public function getShiftStatus() {
        $nrp = Auth::guard('karyawan')->user()->nrp;
        $today = date("Y-m-d");
        $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($today)));
        $jamSekarang = date("H:i:s");
        
        // Cek presensi hari ini
        $presensiHariIni = DB::table('presensi')->where('tgl_presensi', $today)->where('nrp', $nrp)->first();
        // Cek presensi kemarin (untuk shift malam)
        $presensiKemarin = DB::table('presensi')->where('tgl_presensi', $kemarin)->where('nrp', $nrp)->first();
        
        $shiftSelesai = false;
        if (($presensiHariIni && !is_null($presensiHariIni->jam_out)) || ($presensiKemarin && !is_null($presensiKemarin->jam_out))) {
            // Ambil akhir_jam_pulang dari jamKerja terkait
            $hariKerja = $presensiHariIni ? $today : $kemarin;
            $namahariKerja = $this->getHariFromDate($hariKerja);
            $jamKerja = DB::table('settings_jam_kerja')
                ->join('jam_kerja','settings_jam_kerja.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                ->where('nrp',$nrp)->where('hari',$namahariKerja)->first();
            if(!$jamKerja){
                $jamKerja = DB::table('settings_jk_dept_detail')
                    ->join('settings_jk_dept','settings_jk_dept_detail.kode_jk_dept','=','settings_jk_dept.kode_jk_dept')
                    ->join('jam_kerja','settings_jk_dept_detail.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                    ->where('kode_dept', Auth::guard('karyawan')->user()->kode_dept)->where('hari',$namahariKerja)->first();
            }
            if ($jamKerja && $jamSekarang <= $jamKerja->akhir_jam_pulang) {
                $shiftSelesai = true; // Block jika shift selesai sebelum akhir_jam_pulang
            } // Jika >= akhir_jam_pulang, reset, tidak block
        }
        
        return $shiftSelesai;
    }
    public function index()
    {
        $hariIni = date("Y-m-d");
        $bulanIni = (int) date("m");
        $tahunIni = date("Y");
        $jam = date("H:i:s");
        $nrp = Auth::guard('karyawan')->user()->nrp;

        // ===========================
        // Mengecek presensi kemarin
        // ===========================
        $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($hariIni)));
        $cekKemarin = DB::table('presensi')
            ->where('tgl_presensi', $kemarin)
            ->where('nrp', $nrp)
            ->count();  // PERBAIKAN: Hitung semua presensi kemarin, bukan hanya yang jam_out null

        $isTimeout = false;
        $activePresensiDate = $hariIni;

        if ($cekKemarin > 0) {  // PERBAIKAN: Jika ada presensi kemarin, set activePresensiDate ke kemarin
            $namahariKemarin = $this->getHariFromDate($kemarin);

            $jamKerjaKemarin = DB::table('settings_jam_kerja')
                ->join('jam_kerja','settings_jam_kerja.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                ->where('nrp',$nrp)->where('hari',$namahariKemarin)->first();

            if (!$jamKerjaKemarin) {
                $jamKerjaKemarin = DB::table('settings_jk_dept_detail')
                    ->join('settings_jk_dept','settings_jk_dept_detail.kode_jk_dept','=','settings_jk_dept.kode_jk_dept')
                    ->join('jam_kerja','settings_jk_dept_detail.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
                    ->where('kode_dept', Auth::guard('karyawan')->user()->kode_dept)
                    ->where('hari',$namahariKemarin)
                    ->first();
            }

            if ($jamKerjaKemarin) {
                $akhirJamPulang = $jamKerjaKemarin->akhir_jam_pulang;
                $jamMasuk = $jamKerjaKemarin->jam_masuk;
                $jamPulang = $jamKerjaKemarin->jam_pulang;
                $isShiftMalam = ($jamPulang < $jamMasuk);

                // PERBAIKAN: Hapus timeout +2 jam, gunakan akhir_jam_pulang langsung
                if ($isShiftMalam) {
                    $isTimeout = ($jam > $akhirJamPulang);
                } else {
                    $isTimeout = true; // Shift normal sudah lewat hari
                }

                // PERBAIKAN: Tambah logging untuk debug
                \Log::info("Dashboard shift malam check: nrp=$nrp, kemarin=$kemarin, isShiftMalam=$isShiftMalam, akhirJamPulang=$akhirJamPulang, jam=$jam, isTimeout=$isTimeout");

                if (!$isTimeout) {
                    $activePresensiDate = $kemarin;
                }
            } else {
                // PERBAIKAN: Jika jamKerjaKemarin null, anggap timeout
                $isTimeout = true;
                \Log::warning("Jam kerja kemarin tidak ditemukan untuk nrp=$nrp, hari=$namahariKemarin");
            }
        }

        // ===========================
        // Ambil presensi hari aktif
        // ===========================
        try {
            $presensiHariIni = DB::table('presensi')
                ->where('nrp', $nrp)
                ->where('tgl_presensi', $activePresensiDate)
                ->first();

            // PERBAIKAN: Jika tidak ada atau jam_out null, cek presensi hari ini untuk shift malam (absen out hari ini)
            if (!$presensiHariIni || is_null($presensiHariIni->jam_out)) {
                $presensiHariIni = DB::table('presensi')
                    ->where('nrp', $nrp)
                    ->where('tgl_presensi', date("Y-m-d"))
                    ->first();
            }

            // PERBAIKAN: Jika masih null, cari presensi terakhir dengan jam_out null (untuk shift malam yang belum selesai)
            if (!$presensiHariIni) {
                $presensiHariIni = DB::table('presensi')
                    ->where('nrp', $nrp)
                    ->whereNull('jam_out')
                    ->orderBy('tgl_presensi', 'desc')
                    ->first();
            }

            // PERBAIKAN: Jika jam_out masih null, gabungkan dari hari ini (untuk shift malam)
            if ($presensiHariIni && is_null($presensiHariIni->jam_out)) {
                $presensiOut = DB::table('presensi')
                    ->where('nrp', $nrp)
                    ->where('tgl_presensi', date("Y-m-d"))
                    ->whereNotNull('jam_out')
                    ->first();
                if ($presensiOut) {
                    $presensiHariIni->jam_out = $presensiOut->jam_out;
                    $presensiHariIni->foto_out = $presensiOut->foto_out;
                    $presensiHariIni->lokasi_out = $presensiOut->lokasi_out;
                }
            }
        } catch (\Exception $e) {
            // PERBAIKAN: Log error dan set null untuk menghindari crash
            \Log::error("Error fetching presensiHariIni: " . $e->getMessage());
            $presensiHariIni = null;
        }
        \Log::info("Dashboard presensi aktif: nrp=$nrp, activePresensiDate=$activePresensiDate, presensiHariIni=" . ($presensiHariIni ? 'ada' : 'null'));
        \Log::info("Dashboard jam_in: " . ($presensiHariIni->jam_in ?? 'null') . ", jam_out: " . ($presensiHariIni->jam_out ?? 'null'));  // PERBAIKAN: Tambah logging untuk debug jam_out

        // ===========================
        // Histori presensi bulan ini
        // ===========================
        $historiBulanIni = DB::table('presensi')
            ->select('presensi.*','jam_kerja.*')
            ->leftJoin('jam_kerja','presensi.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->where('presensi.nrp', $nrp)
            ->whereMonth('tgl_presensi', $bulanIni)
            ->whereYear('tgl_presensi', $tahunIni)
            ->orderBy('tgl_presensi')
            ->get();

        // ===========================
        // Rekap presensi bulan ini
        // ===========================
        $rekapPresensi = DB::table('presensi')
            ->selectRaw('COUNT(nrp) as totHadir, SUM(IF(jam_in > jam_masuk ,1,0)) as totLate')
            ->leftJoin('jam_kerja','presensi.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->where('nrp', $nrp)
            ->whereMonth('tgl_presensi', $bulanIni)
            ->whereYear('tgl_presensi', $tahunIni)
            ->first();

        // ===========================
        // Rekap Cuti/Izin/Sakit bulan ini
        // ===========================
        $rekapCis = DB::table('cis')
        ->selectRaw('
            SUM(CASE WHEN status = "I" THEN 1 ELSE 0 END) as jmlIzin,
            SUM(CASE WHEN status = "S" THEN 1 ELSE 0 END) as jmlSakit
        ')
        ->where('nrp', $nrp)
        ->whereMonth('tgl_izin_dari', $bulanIni)
        ->whereYear('tgl_izin_dari', $tahunIni)
        ->first();

        // ===========================
        // Array nama bulan
        // ===========================
        $namaBulan = [
            1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 
            5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 
            9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
        ];

        // PERBAIKAN: Query untuk modal (pindah dari view untuk keamanan)
        $presensiModalIn = DB::table('presensi')->where('nrp', $nrp)->where('tgl_presensi', $activePresensiDate)->whereNotNull('foto_in')->first();
        if (!$presensiModalIn) {
            $presensiModalIn = DB::table('presensi')->where('nrp', $nrp)->whereNotNull('foto_in')->orderBy('tgl_presensi', 'desc')->first();
        }

        $presensiModalOut = DB::table('presensi')->where('nrp', $nrp)->where('tgl_presensi', $activePresensiDate)->whereNotNull('foto_out')->first();
        if (!$presensiModalOut) {
            $presensiModalOut = DB::table('presensi')->where('nrp', $nrp)->whereNotNull('foto_out')->orderBy('tgl_presensi', 'desc')->first();
        }

        return view("dashboard.dashboard", compact(
            'presensiHariIni', 'historiBulanIni', 'namaBulan', 
            'bulanIni', 'tahunIni', 'rekapPresensi', 'rekapCis', 'activePresensiDate', 'presensiModalIn', 'presensiModalOut'  // Tambahkan ini
        ));
    } 
    public function dashboardadmin()
    {
        $totalUsers = DB::table('karyawan')->count();
        $bulanIni = (int) date("m");
        $tahunIni = (int) date("Y");
        $hariIni = date("Y-m-d");

        // Rekap Kehadiran Hari Ini
        $rekapPresensi = DB::table('presensi')
            ->selectRaw('COUNT(nrp) as totHadir, SUM(IF(jam_in > "06:50",1,0)) as totLate')
            ->where('tgl_presensi', $hariIni)
            ->first();

        // Data tren kehadiran bulanan (total hadir per hari)
        $historiBulanIni = DB::table('presensi')
            ->selectRaw('tgl_presensi, COUNT(nrp) as totHadir')
            ->whereMonth('tgl_presensi', $bulanIni)
            ->whereYear('tgl_presensi', $tahunIni)
            ->groupBy('tgl_presensi')
            ->orderBy('tgl_presensi')
            ->get();

        // Membuat array labels dan values untuk Chart.js
        $labels = [];
        $values = [];
        $historiAssoc = $historiBulanIni->keyBy(function($item) {
            return date('Y-m-d', strtotime($item->tgl_presensi));
        });

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulanIni, $tahunIni);

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $tahunIni, $bulanIni, $d);
            $hari = date('D', strtotime($date));

            $namaHari = match($hari) {
                'Sun' => 'Minggu',
                'Mon' => 'Sen',
                'Tue' => 'Sel',
                'Wed' => 'Rab',
                'Thu' => 'Kam',
                'Fri' => 'Jum',
                'Sat' => 'Sab',
                default => ''
            };

            $labels[] = date('j M', strtotime($date)) . " ($namaHari)";
            $values[] = $historiAssoc[$date]->totHadir ?? 0;
        }

        return view('dashboard.dashboardadmin', compact(
            'totalUsers',
            'rekapPresensi',
            'labels',
            'values'
        ));
    }
}