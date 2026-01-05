<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class KaryawanController extends Controller
{
    // public function karyawanInfo(){
    //     $totalUsers = DB::table('karyawan')->count();
    //     return view('karyawan.index', compact( 'totalUsers'));
    // }
    
    public function index(Request $request){
        
        $query = Karyawan::query();
        $query->select('karyawan.*','nama_dept');
        $query->join('department','karyawan.kode_dept','=','department.kode_dept');
        $query->orderBy('nama');
        if (!empty($request->nama_karyawan)){
            $query->where('nama','like','%' . $request->nama_karyawan .'%');
        }

        // ADA 2 KODE_DEPT, DEFINISIKAN DARI TABLE MANA 'karyawan.kode_dept'
        if (!empty($request->kode_dept)){
            $query->where('karyawan.kode_dept',  $request->kode_dept);
        }
        $karyawan = $query->paginate(10);

        $department = DB::table('department')->get();
        $cabang = DB::table('cabang')->orderBy('kode_cabang')->get();
        return view('karyawan.index', compact('karyawan','department', 'cabang'));
    }

    // ADD DATA KARYAWAN
    public function store(Request $request){
        // Tambahkan validasi untuk keamanan
        $request->validate([
            'nrp' => 'required|unique:karyawan,nrp|string|max:20',
            'nama' => 'required|string|max:255',
            'kode_dept' => 'required',
            'jabatan' => 'required|string|max:255',
            'telp' => 'nullable|string|max:20',
            'kode_cabang' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validasi file foto
        ]);

        $nrp = $request->nrp;
        $nama = $request->nama;
        $kode_dept = $request->kode_dept;
        $jabatan = $request->jabatan;
        $telp = $request->telp;
        $password = Hash::make($nrp); // PERUBAHAN: Password default sekarang berdasarkan NRP (bukan '123')
        $kode_cabang = $request->kode_cabang;
        
        if ($request->hasFile('foto')) {
            $foto = $nrp . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = null;
        }

        try {
            $data = [
                'nrp' => $nrp,
                'nama' => $nama,
                'kode_dept' => $kode_dept,
                'jabatan' => $jabatan,
                'telp' => $telp,
                'foto' => $foto,
                'password' => $password,
                'kode_cabang' => $kode_cabang
            ];
            $simpan = DB::table('karyawan')->insert($data);
            if($simpan){
                if($request->hasFile('foto')){
                    $folderPath = "uploads/karyawan/fotoProfile/";
                    $request->file('foto')->storeAs($folderPath, $foto);
                }
                return Redirect::back()->with(['success' =>'Data Berhasil di Simpan']);
            }
        } catch (\Exception $e) {
            // PERBAIKAN: Tambahkan pesan error untuk debugging
            return Redirect::back()->with(['warning' =>'Data Gagal di Simpan: ' . $e->getMessage()]);
        }
    }  

    public function edit(Request $request)
    {
        $nrp = $request->nrp;
        $department = DB::table('department')->get();
        $cabang = DB::table('cabang')->orderBy('kode_cabang')->get();
        $karyawan = DB::table('karyawan')->where('nrp',$nrp)->first();
        return view('karyawan.edit',compact('department','karyawan', 'cabang'));
    }

    public function update ($nrp, Request $request){
        // Tambahkan validasi untuk keamanan
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode_dept' => 'required',
            'jabatan' => 'required|string|max:255',
            'telp' => 'nullable|string|max:20',
            'kode_cabang' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validasi file foto
        ]);

        $nrp = $request->nrp;
        $nama = $request->nama;
        $kode_dept = $request->kode_dept;
        $kode_cabang = $request->kode_cabang;
        $jabatan = $request->jabatan;
        $telp = $request->telp;
        $old_foto = $request->old_foto;
        
        if ($request->hasFile('foto')) {
            $foto = $nrp . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = $old_foto;
        }

        try {
            $data = [
                'nama' => $nama,
                'kode_dept' => $kode_dept,
                'jabatan' => $jabatan,
                'telp' => $telp,
                'foto' => $foto,
                'kode_cabang' => $kode_cabang
            ];
            $update = DB::table('karyawan')->where('nrp', $nrp)->update($data);
            if($update){
                if($request->hasFile('foto')){
                    $folderPath = "uploads/karyawan/fotoProfile/";
                    $folderPathOld = "uploads/karyawan/fotoProfile/" . $old_foto;
                    Storage::delete($folderPathOld); // PERBAIKAN: Pastikan file lama dihapus dengan aman
                }
                return Redirect::back()->with(['success' =>'Data Berhasil di Update']);
            }
        } catch (\Exception $e) {
            // PERBAIKAN: Tambahkan pesan error untuk debugging
            return Redirect::back()->with(['warning' =>'Data Gagal di Update: ' . $e->getMessage()]);
        }
    }
    
    public function delete($nrp){
        try {
            // Cek apakah karyawan ada
            $karyawan = DB::table('karyawan')->where('nrp', $nrp)->first();
            if (!$karyawan) {
                return Redirect::back()->with(['warning' => 'Karyawan tidak ditemukan']);
            }

            // Hapus record terkait di tabel bugar_selamat terlebih dahulu (untuk menghindari foreign key constraint)
            DB::table('bugar_selamat')->where('nrp', $nrp)->delete();

            // Hapus foto dari storage jika ada
            if ($karyawan->foto) {
                Storage::delete("uploads/karyawan/fotoProfile/" . $karyawan->foto);
            }

            // Hapus karyawan
            $delete = DB::table('karyawan')->where('nrp', $nrp)->delete();
            if ($delete) {
                return Redirect::back()->with(['success' => 'Data Berhasil di Delete']);
            } else {
                return Redirect::back()->with(['warning' => 'Data Gagal di Delete']);
            }
        } catch (\Exception $e) {
            // Tangani error dan tampilkan pesan
            return Redirect::back()->with(['warning' => 'Data Gagal di Delete: ' . $e->getMessage()]);
        }
    }
}