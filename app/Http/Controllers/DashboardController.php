<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function calculateTimeoutTime($akhirJamPulang, $isShiftMalam) {
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $akhirJamPulang)) {
            \Log::error("Format akhirJamPulang tidak valid: $akhirJamPulang");
            return null;
        }

        try {
            $timestampAkhir = strtotime($akhirJamPulang);
            if ($timestampAkhir === false) return null;

            $timestampTimeout = strtotime("-2 hours", $timestampAkhir);
            if ($timestampTimeout === false) return null;

            return date("H:i:s", $timestampTimeout);
        } catch (\Exception $e) {
            \Log::error("Exception in calculateTimeoutTime: " . $e->getMessage());
            return null;
        }
    }

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
            ->whereNull('jam_out')
            ->count();

        $isTimeout = false;
        $activePresensiDate = $hariIni;

        if ($cekKemarin > 0) {
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

                $timeoutTime = $this->calculateTimeoutTime($akhirJamPulang, $isShiftMalam);

                if ($timeoutTime !== null) {
                    if ($isShiftMalam) {
                        $isTimeout = ($jam > $timeoutTime);
                    } else {
                        $isTimeout = true; // Shift normal sudah lewat hari
                    }
                } else {
                    // PERBAIKAN: Jika timeoutTime null, anggap timeout untuk safety
                    $isTimeout = true;
                }

                // PERBAIKAN: Tambah logging untuk debug
                \Log::info("Dashboard shift malam check: nrp=$nrp, kemarin=$kemarin, isShiftMalam=$isShiftMalam, timeoutTime=$timeoutTime, jam=$jam, isTimeout=$isTimeout");

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
        $presensiHariIni = DB::table('presensi')
            ->where('nrp', $nrp)
            ->where('tgl_presensi', $activePresensiDate)
            ->first();

        // PERBAIKAN: Jika presensi hari aktif null dan ada presensi kemarin yang belum out, gunakan kemarin sebagai fallback
        if (!$presensiHariIni && $cekKemarin > 0) {
            $presensiHariIni = DB::table('presensi')
                ->where('nrp', $nrp)
                ->where('tgl_presensi', $kemarin)
                ->first();
            \Log::info("Fallback ke presensi kemarin: nrp=$nrp, presensiHariIni=" . ($presensiHariIni ? 'ada' : 'null'));
        }

        // PERBAIKAN: Tambah logging untuk konfirmasi data presensi
        \Log::info("Dashboard presensi aktif: nrp=$nrp, activePresensiDate=$activePresensiDate, presensiHariIni=" . ($presensiHariIni ? 'ada' : 'null'));

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

        return view("dashboard.dashboard", compact(
            'presensiHariIni', 'historiBulanIni', 'namaBulan', 
            'bulanIni', 'tahunIni', 'rekapPresensi', 'rekapCis'
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
