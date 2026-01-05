<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
 
    public function index()
    {  
        $hariIni = date("Y-m-d");
        $bulanIni = date("m") * 1; //1 atau Januari
        $tahunIni = date('Y'); //2025
        $jam = date("H:i:s"); // 16:20:25
        $nrp = Auth::guard('karyawan')->user()->nrp;
        
        // PERUBAHAN: Ambil presensi hari ini terlebih dahulu
        $presensiHariIni = DB::table('presensi')->where('nrp',$nrp)->where('tgl_presensi',$hariIni)->first();
        
        // PERUBAHAN: Jika belum ada record hari ini, cek shift malam (record hari kemarin dengan jam_out null)
        if (!$presensiHariIni) {
            $kemarin = date("Y-m-d", strtotime("-1 day", strtotime($hariIni)));
            $presensiKemarin = DB::table('presensi')->where('nrp', $nrp)->where('tgl_presensi', $kemarin)->whereNull('jam_out')->first();
            if ($presensiKemarin) {
                $presensiHariIni = $presensiKemarin; // Gunakan record kemarin sebagai "hari ini" untuk shift malam
            }
        }
        
        $historiBulanIni = DB::table('presensi')
            ->select('presensi.*','keterangan','jam_kerja.*','doc_cis','nama_cuti')
            ->leftjoin ('jam_kerja','presensi.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->leftJoin('cis','presensi.kode_izin','=','cis.kode_izin')
            ->leftJoin('master_cuti','cis.kode_izin','=','master_cuti.kode_cuti')
            ->where('presensi.nrp', $nrp)
            ->whereRaw('MONTH(tgl_presensi)="' . $bulanIni . '"')
            ->whereRaw('YEAR(tgl_presensi)="' . $tahunIni . '"')
            ->orderBy('tgl_presensi')
            ->get();
        
        $rekapPresensi = DB::table('presensi')
            ->selectRaw('COUNT(nrp) as totHadir, SUM(IF(jam_in > jam_masuk ,1,0)) as totLate' )
            ->leftjoin('jam_kerja','presensi.kode_jam_kerja','=','jam_kerja.kode_jam_kerja')
            ->where('nrp', $nrp)
            ->whereRaw('MONTH(tgl_presensi)="' . $bulanIni . '"')
            ->whereRaw('YEAR(tgl_presensi)="' . $tahunIni . '"')
            ->first();

        $namaBulan = ["","Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November","Desember"];
        
        $rekapCis = DB::table('cis')
            ->selectRaw('SUM(IF(status="i",1,0)) as jmlIzin,SUM(IF(status="s",1,0)) as jmlSakit')
            ->where('nrp',$nrp)
            ->whereRaw('MONTH(tgl_izin_dari)="' . $bulanIni . '"')
            ->whereRaw('YEAR(tgl_izin_dari)="' . $tahunIni . '"')
            ->where('status_approved',1)
            ->first();
        return view("dashboard.dashboard", compact('presensiHariIni' , 'historiBulanIni', 'namaBulan', 'bulanIni', 'tahunIni', 'rekapPresensi','rekapCis'));
    }

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
