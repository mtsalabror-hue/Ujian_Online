<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login admin/guru.
     */
    public function showLoginForm()
    {
        return view('auth.login-admin');
    }

    public function showSiswaLoginForm()
    {
        return view('auth.login-siswa');
    }

    /**
     * Proses login admin/guru.
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Proses autentikasi
        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();

            $user = Auth::user();

            // Cek status akun
            if (!$user->status) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'username' => 'Akun Anda tidak aktif.',
                ])->onlyInput('username');
            }

            // Redirect berdasarkan role
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'guru'  => redirect()->route('guru.dashboard'),
                'siswa' => redirect()->route('siswa.ujian'),
                default => $this->logout($request),
            };
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        $role = Auth::user()?->role;

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return match ($role) {
            'admin' => redirect()->route('login.admin'),
            'guru'  => redirect()->route('login.guru'),
            'siswa' => redirect()->route('login.siswa'),
            default => redirect()->route('landing'),
        };
    }
}
