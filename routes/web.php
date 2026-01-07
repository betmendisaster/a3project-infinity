<?php
//testing git commit
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\IzinCisController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\SakitCisController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\IzinCutiController;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;

// USER KARYAWAN
Route::middleware(['guest:karyawan'])->group(function () {
    // Ubah ke method di controller dengan header no-cache
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/proseslogin', [AuthController::class, 'proseslogin']);
});

// USER ADMIN
Route::middleware(['guest:user'])->group(function () {
    // Ubah ke method di controller dengan header no-cache
    Route::get('/panel', [AuthController::class, 'loginadmin'])->name('loginadmin');
    Route::post('/prosesloginadmin', [AuthController::class, 'prosesloginadmin']);
});

Route::middleware(['auth:karyawan'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/proseslogout', [AuthController::class, 'proseslogout']);

    //Presensi
    Route::get('/presensi/create', [PresensiController::class,'create']);
    
    // takeabsen
    Route::post('/presensi/store', [PresensiController::class, 'store']);

    //edit profile
    Route::get('/editProfile', [PresensiController::class,'editProfile']);
    Route::post('/presensi/{nrp}/updateProfile', [PresensiController::class, 'updateProfile']);
    
    // Histori Absen    
    Route::get('/presensi/histori', [PresensiController::class, 'histori']);
    Route::post('/getHistori', [PresensiController::class, 'getHistori']);

    // MAINTENANCE
    Route::get('/presensi/maintenance',[PresensiController::class,'maintenance']);
    
    // CIS (CUTI IZIN SAKIT)
    // IZIN
    Route::get('/presensi/cis/izin',[PresensiController::class, 'izin']);
    Route::get('/presensi/cis/buatIzin',[PresensiController::class,'buatIzin']);
    Route::post('/presensi/cis/storeIzin',[IzinCisController::class,'storeIzin']);
    Route::get('/presensi/izinCis/{kode_izin}/edit',[IzinCisController::class,'edit']);
    Route::post('/presensi/cis/{kode_izin}/update',[IzinCisController::class,'update']);
    // Sakit
    Route::get('/presensi/cis/izinSakit',[SakitCisController::class,'create']);
    Route::post('/presensi/cis/storeSakit',[SakitCisController::class,'storeSakit']);
    Route::get('/presensi/izinSakit/{kode_izin}/edit',[SakitCisController::class,'edit']);
    Route::post('/presensi/cis/{kode_izin}/update',[SakitCisController::class,'update']);
    // CUTI
    Route::get('/presensi/cis/cuti',[IzinCutiController::class,'create']);
    Route::post('/presensi/cis/storeCuti',[IzinCutiController::class,'storeCuti']);
    Route::get('/presensi/izinCuti/{kode_izin}/edit',[IzinCutiController::class,'edit']);
    Route::post('/presensi/cis/{kode_izin}/update',[IzinCutiController::class,'update']);
    

    Route::get('/presensi/cis/{kode_izin}/showact',[PresensiController::class,'showact']);
    Route::get('/presensi/cis/{kode_izin}/delete',[PresensiController::class,'deleteIzin']);

    Route::get('/presensi/bugar-selamat', [PresensiController::class, 'bugarSelamat']);
    Route::post('/presensi/store-bugar-selamat', [PresensiController::class, 'storeBugarSelamat']);

    // Route::get('/presensi/ganti-shift', [PresensiController::class, 'gantiShift']);
    Route::post('/presensi/update-shift-ajax', [PresensiController::class, 'updateShiftAjax']);

});

Route::middleware(['auth:user'])->group(function() {
    Route::post('/proseslogoutadmin', [AuthController::class, 'proseslogoutadmin']);
    Route::get('/panel/dashboardadmin',[DashboardController::class,'dashboardAdmin']);

    // Data master Karyawan
    // Route::get('/panel/karyawan',[KaryawanController::class,'karyawanInfo']);
    Route::get('/panel/karyawan',[KaryawanController::class,'index']);
    Route::post('/panel/karyawan/store', [KaryawanController::class, 'store']);
    Route::post('/panel/karyawan/edit', [KaryawanController::class, 'edit']);
    Route::post('/panel/karyawan/{nrp}/update', [KaryawanController::class, 'update']);
    Route::post('/panel/karyawan/{nrp}/delete', [KaryawanController::class, 'delete']);
    
    // Data master Department
    Route::get('/panel/department', [DepartmentController::class,'index']);
    Route::post('/panel/department/store', [DepartmentController::class,'store']);
    Route::post('/panel/department/edit', [DepartmentController::class,'edit']);
    Route::post('/panel/department/{kode_dept}/update', [DepartmentController::class,'update']);
    Route::post('/panel/department/{kode_dept}/delete', [DepartmentController::class,'delete']);

    // Presensi
    Route::get('/panel/presensi/monitoring', [PresensiController::class,'monitoring']);
    Route::post('/getpresensi', [PresensiController::class,'getPresensi']);
    Route::post('/showlocation', [PresensiController::class,'showLocation']);
    Route::get('/panel/report', [PresensiController::class,'report']);
    Route::post('/presensi/cetakReport', [PresensiController::class,'cetakReport']);
    // Mounthly Recap Report
    Route::get('/panel/rekapReport', [PresensiController::class,'rekapReport']);
    Route::post('/presensi/cetakRekap', [PresensiController::class,'cetakRekap']);
    // Daily Recap Report
    Route::get('/panel/dailyReport',[PresensiController::class,'dailyReport']);
    Route::post('/presensi/cetakDailyReport',[PresensiController::class,'cetakDailyReport'])->name('cetakDailyReport');

    // Cabang Area Presensi
    Route::get('/settings/cabang',[CabangController::class, 'index']);
    Route::post('/settings/cabang/store',[CabangController::class, 'store']);
    Route::post('/settings/cabang/edit',[CabangController::class,'edit']);
    Route::post('/settings/cabang/update',[CabangController::class,'update']);
    Route::post('/settings/cabang/{kode_cabang}/delete',[CabangController::class,'delete']);
    // Settings
    // Route::get('/settings/lokasi/radius',[SettingsController::class,'radius']);
    // Route::post('/settings/lokasi/radius/update',[SettingsController::class,'updateRadius']);
    Route::get('/settings/jamKerja',[SettingsController::class,'jamKerja']);
    Route::post('/settings/storeJamKerja',[SettingsController::class,'storeJamKerja']);
    Route::post('/settings/editJamKerja',[SettingsController::class,'editJamKerja']);
    Route::post('/settings/updateJamKerja',[SettingsController::class,'updateJamKerja']);
    Route::post('/settings/jamKerja/{kode_jam_kerja}/delete',[SettingsController::class,'deleteJamKerja']);
    Route::get('/settings/{nrp}/setJamKerja',[SettingsController::class,'setJamKerja']);
    Route::post('/settings/storesetJamKerja',[SettingsController::class,'storesetJamKerja']);
    Route::post('/settings/updatesetJamKerja',[SettingsController::class,'updatesetJamKerja']);
    
    
    Route::get('/settings/jamKerjaDept',[SettingsController::class,'setJamKerjaDept']);
    Route::get('/settings/jamKerjaDept/create',[SettingsController::class,'createJamKerjaDept']);
    Route::post('/settings/jamKerjaDept/store',[SettingsController::class,'storeJamKerjaDept']);
    Route::get('/settings/jamKerjaDept/{kode_jk_dept}/edit',[SettingsController::class,'editJamKerjaDept']);
    Route::post('/settings/jamKerjaDept/{kode_jk_dept}/update',[SettingsController::class,'updateJamKerjaDept']);
    Route::get('/settings/jamKerjaDept/{kode_jk_dept}/delete',[SettingsController::class,'deleteJamKerjaDept']);

    // CIS Session

    Route::get('/panel/presensi/cis',[PresensiController::class,'monitoringCis']);
    Route::post('/panel/presensi/cis/approveCis', [PresensiController::class,'approveCis']);
    Route::get('/panel/presensi/cis/{kode_izin}/cancelCis',[PresensiController::class,'cancelCis']);

    // Cuti
    Route::get('/panel/cuti',[CutiController::class,'index']);
    Route::post('/panel/cuti/store',[CutiController::class,'store']);
    Route::post('/panel/cuti/edit',[CutiController::class,'edit']);
    Route::post('/panel/cuti/{kode_cuti}/update',[CutiController::class,'update']);
        Route::post('/panel/cuti/{kode_cuti}/delete',[CutiController::class,'delete']);
});

Route::get('/hash', function () {
    return Hash::make('123');
});
