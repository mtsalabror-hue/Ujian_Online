<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GuruLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login-guru');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'], // Bisa berupa Username, NIK, NIP, atau Nama Guru
            'password' => ['required'],
        ]);

        $input = trim($credentials['username']);

        // 1. Cari user secara langsung via username di tabel users (role guru)
        $user = User::where('username', $input)
            ->where('role', 'guru')
            ->first();

        // 2. Jika tidak ditemukan, cari via tabel guru (berdasarkan NIK, NIP, Nama Guru, atau Email)
        if (!$user) {
            $guru = Guru::where('nik', $input)
                ->orWhere('nama_guru', $input)
                ->orWhere('email', $input)
                ->first();

            if ($guru && $guru->user && $guru->user->role === 'guru') {
                $user = $guru->user;
            }
        }

        // 3. Verifikasi password jika user ditemukan
        if ($user && Hash::check($credentials['password'], $user->password)) {

            // Cek status aktif
            if (!$user->status) {
                return back()->withInput()->withErrors([
                    'username' => 'Akun Anda tidak aktif. Hubungi administrator.'
                ]);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->route('guru.dashboard');
        }

        return back()
            ->withInput()
            ->withErrors([
                'username' => 'NIK / Nama Guru / Username atau Password salah.'
            ]);
    }
}
