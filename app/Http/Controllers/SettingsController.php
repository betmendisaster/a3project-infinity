<?php

namespace App\Http\Controllers;

use App\Models\setJamKerja;
use App\Models\setJamKerjaDept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\CssSelector\XPath\Extension\FunctionExtension;

class SettingsController extends Controller
{
    // public function radius(){

    //     $lok_site = DB::table('lokasi_presensi')->where('id',1)->first();
        
    //     return view('settings.lokasi.radius',compact('lok_site'));
    // }

    // public function updateRadius(Request $request) {
    //     $lokasi_presensi = $request->lokasi_presensi;
    //     $radius = $request->radius;
    //     $nama_lokasi = $request->nama_lokasi;

    //     $update = DB::table('lokasi_presensi')->where('id',1)->update([
    //         'lokasi_presensi' => $lokasi_presensi,
    //         'radius'=>$radius,
    //         'nama_lokasi'=>$nama_lokasi
    //     ]);

    //     if($update){
    //         return Redirect::back()->with(['success'=>'Data Berhasil di Update']);
    //     }else {
    //         return Redirect::back()->with(['success'=>'Data Gagal di Update']);
    //     }
    // }

    public function jamKerja(){
        $jam_kerja = DB::table('jam_kerja')->orderBy('kode_jam_kerja')->get();
        return view('settings.jamkerja.jamKerja', compact('jam_kerja'));
    }

    public function storeJamKerja(Request $request) {
        $kode_jam_kerja = $request->kode_jam_kerja;
        $nama_jam_kerja = $request->nama_jam_kerja;
        $awal_jam_masuk = $request->awal_jam_masuk;
        $jam_masuk = $request->jam_masuk;
        $akhir_jam_masuk = $request->akhir_jam_masuk;
        $awal_jam_pulang = $request->awal_jam_pulang; // BARU
        $jam_pulang = $request->jam_pulang;
        $akhir_jam_pulang = $request->akhir_jam_pulang; // BARU

        $data = [
            'kode_jam_kerja' => $kode_jam_kerja,
            'nama_jam_kerja' => $nama_jam_kerja,
            'awal_jam_masuk' => $awal_jam_masuk,
            'jam_masuk' => $jam_masuk,
            'akhir_jam_masuk' => $akhir_jam_masuk,
            'awal_jam_pulang' => $awal_jam_pulang, // BARU
            'jam_pulang' => $jam_pulang,
            'akhir_jam_pulang' => $akhir_jam_pulang, // BARU
        ];
        try {
            DB::table('jam_kerja')->insert($data);
            return Redirect::back()->with(['success' => 'Data Berhasil di Simpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => 'Data Gagal di Simpan']);
        }
    }

    public function editJamKerja(Request $request){
        $kode_jam_kerja = $request->kode_jam_kerja;
        $jamkerja = DB::table('jam_kerja')->where('kode_jam_kerja', $kode_jam_kerja)->first();
        return view('settings.jamkerja.edit', compact('jamkerja'));
    }

    public function updateJamKerja(Request $request) {
        $kode_jam_kerja = $request->kode_jam_kerja;
        $nama_jam_kerja = $request->nama_jam_kerja;
        $awal_jam_masuk = $request->awal_jam_masuk;
        $jam_masuk = $request->jam_masuk;
        $akhir_jam_masuk = $request->akhir_jam_masuk;
        $awal_jam_pulang = $request->awal_jam_pulang; // BARU
        $jam_pulang = $request->jam_pulang;
        $akhir_jam_pulang = $request->akhir_jam_pulang; // BARU

        $data = [
            'nama_jam_kerja' => $nama_jam_kerja,
            'awal_jam_masuk' => $awal_jam_masuk,
            'jam_masuk' => $jam_masuk,
            'akhir_jam_masuk' => $akhir_jam_masuk,
            'awal_jam_pulang' => $awal_jam_pulang, // BARU
            'jam_pulang' => $jam_pulang,
            'akhir_jam_pulang' => $akhir_jam_pulang, // BARU
        ];
        try {
            DB::table('jam_kerja')->where('kode_jam_kerja', $kode_jam_kerja)->update($data);
            return Redirect::back()->with(['success' => 'Data Berhasil di Simpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => 'Data Gagal di Simpan']);
        }
    }

    public function deleteJamKerja($kode_jam_kerja){
        $delete = DB::table('jam_kerja')->where('kode_jam_kerja', $kode_jam_kerja)->delete();
        if($delete){
            return Redirect::back()->with(['success'=>'Data Berhasil di Delete']);
        } else {
            return Redirect::back()->with(['warning'=>'Data Gagal di Delete']);
        }
    }

    public function setJamKerja($nrp){
        $karyawan = DB::table('karyawan')->where('nrp',$nrp)->first();
        $jamKerja = DB::table('jam_kerja')->orderBy('nama_jam_kerja')->get();
        $cekJamKerja = DB::table('settings_jam_kerja')->where('nrp',$nrp)->count();
        if ($cekJamKerja > 0){
            $setJamKerja = DB::table('settings_jam_kerja')->where('nrp', $nrp)->get();
            return view('settings.jamkerja.editSetJamKerja', compact('karyawan','jamKerja', 'setJamKerja'));
        }else{
            return view('settings.jamkerja.setJamKerja', compact('karyawan','jamKerja'));
        }
    }

    public function storesetJamKerja (Request $request) {
        $nrp = $request->nrp;
        $hari = $request->hari;
        $kode_jam_kerja = $request->kode_jam_kerja;

        for($i=0; $i<count($hari); $i++){
            $data[] = [
                'nrp' => $nrp,
                'hari' => $hari[$i],
                'kode_jam_kerja' => $kode_jam_kerja[$i]
            ];
        }

        try {
            setJamKerja::insert($data);
            return redirect ('/panel/karyawan')->with(['success' => 'Shift Jam Kerja Berhasil di Setting']);
        } catch (\Exception $e ) {
            return redirect ('/panel/karyawan')->with(['warning' => 'Shift Jam Kerja Gagal di Setting']);
        }
    }

    public function updatesetJamKerja (Request $request) {
        $nrp = $request->nrp;
        $hari = $request->hari;
        $kode_jam_kerja = $request->kode_jam_kerja;

        for($i=0; $i<count($hari); $i++){
            $data[] = [
                'nrp' => $nrp,
                'hari' => $hari[$i],
                'kode_jam_kerja' => $kode_jam_kerja[$i]
            ];
        }

        DB::beginTransaction();
        try {
            DB::table('settings_jam_kerja')->where('nrp',$nrp)->delete();
            setJamKerja::insert($data);
            DB::commit();
            return redirect ('/panel/karyawan')->with(['success' => 'Shift Jam Kerja Berhasil di Setting']);
        } catch (\Exception $e ) {
            DB::rollBack();
            return redirect ('/panel/karyawan')->with(['warning' => 'Shift Jam Kerja Gagal di Setting']);
        }
    }

    public function setJamKerjaDept(){
        $jamKerjaDept = DB::table('settings_jk_dept')
        ->join('department','settings_jk_dept.kode_dept','=','department.kode_dept')
        ->get();
        return view('settings.jamkerja.jamKerjaDept',compact('jamKerjaDept'));
    }

    public function createJamKerjaDept(){

        $jamKerja = DB::table('jam_kerja')->orderBy('nama_jam_kerja')->get();
        // $cabang = DB::table('cabang')->get();
        $department = DB::table('department')->get();
        return view('settings.jamkerja.createJamKerjaDept',compact('jamKerja','department'));
    }
    public function storeJamKerjaDept (Request $request) {
        // $kode_cabang = $request->kode_cabang;
        $kode_dept = $request->kode_dept;
        $hari = $request->hari;
        $kode_jam_kerja = $request->kode_jam_kerja;
        $kode_jk_dept = "S" . $kode_dept;

        DB::beginTransaction();
        try {
            //menyimpan data ke table settings_jk_dept;
            DB::table('settings_jk_dept')->insert([
                'kode_jk_dept' => $kode_jk_dept,
                'kode_dept' => $kode_dept,
            ]);

            for ($i = 0; $i < count($hari); $i++){
                $data[] = [
                    'kode_jk_dept' => $kode_jk_dept,
                    'hari' => $hari[$i],
                    'kode_jam_kerja' => $kode_jam_kerja[$i]
                ];
            }
            setJamKerjaDept::insert($data);
            DB::commit();
            return redirect('/settings/jamKerjaDept')->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e ) {
            // 
            DB::rollBack();
            return redirect('/settings/jamKerjaDept')->with(['warning' => 'Data Gagal Disimpan']);
        }
    }

    public function editJamKerjaDept($kode_jk_dept){
        $jamKerja = DB::table('jam_kerja')->orderBy('nama_jam_kerja')->get();
        // $cabang = DB::table('cabang')->get();
        $department = DB::table('department')->get();
        $jamKerjaDept = DB::table('settings_jk_dept')->where('kode_jk_dept',$kode_jk_dept)->first();
        $jamKerjaDeptDetail = DB::table('settings_jk_dept_detail')->where('kode_jk_dept',$kode_jk_dept)->get();
        return view('settings.jamkerja.editJamKerjaDept',compact('jamKerja','department','jamKerjaDept','jamKerjaDeptDetail'));
    }

    public function updateJamKerjaDept ($kode_jk_dept, Request $request) {
        // $kode_cabang = $request->kode_cabang;
        $hari = $request->hari;
        $kode_jam_kerja = $request->kode_jam_kerja;

        DB::beginTransaction();
        try {
            // Hapus data jamkerja sebelumnya
            DB::table('settings_jk_dept_detail')->where('kode_jk_dept',$kode_jk_dept)->delete();
            
            for ($i = 0; $i < count($hari); $i++){
                $data[] = [
                    'kode_jk_dept' => $kode_jk_dept,
                    'hari' => $hari[$i],
                    'kode_jam_kerja' => $kode_jam_kerja[$i]
                ];
            }
            setJamKerjaDept::insert($data);
            DB::commit();
            return redirect('/settings/jamKerjaDept')->with(['success' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e ) {
            // 
            DB::rollBack();
            return redirect('/settings/jamKerjaDept')->with(['warning' => 'Data Gagal Disimpan']);
        }
    }

    public function deleteJamKerjaDept($kode_jk_dept){
        try {
            //code...
            DB::table('settings_jk_dept')->where('kode_jk_dept',$kode_jk_dept)->delete();
            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
        } catch (\Exception $e) {
            //throw $th;
             return Redirect::back()->with(['warning' => 'Data Gagal Dihapus']);
        }
    }
    
}
