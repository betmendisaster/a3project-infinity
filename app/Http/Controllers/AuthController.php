<?php

namespace App\Http\Controllers;

// use Auth;
// use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    // Method baru untuk halaman login karyawan dengan header no-cache
    public function login()
    {
        return response()->view('auth.login')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    // Method baru untuk halaman login admin dengan header no-cache
    public function loginadmin()
    {
        return response()->view('auth.loginadmin')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function proseslogin(Request $request)
    {
        if (Auth::guard('karyawan')->attempt(['nrp' => $request->nrp, 'password' => $request->password])) {
            return redirect('/dashboard');
        } else {
            return redirect('/')->with(['warning' => 'NRP atau Password salah']);
        }
    }

    // Tambahkan Request $request, invalidate sesi, dan regenerate token
    public function proseslogout(Request $request)
    {
        if (Auth::guard("karyawan")->check()) {
            Auth::guard('karyawan')->logout();
            $request->session()->invalidate();  // Hapus semua data sesi
            $request->session()->regenerateToken();  // Regenerate CSRF token
            return redirect('/')->with(['warning' => 'Anda telah logout'])
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
    }

    // Tambahkan Request $request, invalidate sesi, dan regenerate token
    public function proseslogoutadmin(Request $request)
    {
        if (Auth::guard("user")->check()) {
            Auth::guard('user')->logout();
            $request->session()->invalidate();  // Hapus semua data sesi
            $request->session()->regenerateToken();  // Regenerate CSRF token
            return redirect('/panel')->with(['warning' => 'Anda telah logout'])
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
    }

    public function prosesloginadmin(Request $request)
    {
        if (Auth::guard('user')->attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect('/panel/dashboardadmin');
        } else {
            return redirect('/panel')->with(['warning' => 'Email atau Password salah']);
        }
    }
}