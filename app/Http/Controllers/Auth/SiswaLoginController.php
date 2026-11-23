<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SiswaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login-siswa');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'], // Bisa berupa Username, NIS, NISN, atau Nama Siswa
            'password' => ['required'],
        ]);

        $input = trim($credentials['username']);

        // 1. Cari user secara langsung via username di tabel users
        $user = User::where('username', $input)
            ->where('role', 'siswa')
            ->first();

        // 2. Jika tidak ditemukan, cari via tabel siswa (berdasarkan NIS, NISN, atau Nama Siswa)
        if (!$user) {
            $siswa = Siswa::where('nis', $input)
                ->orWhere('nisn', $input)
                ->orWhere('nama_siswa', $input)
                ->first();

            if ($siswa && $siswa->user && $siswa->user->role === 'siswa') {
                $user = $siswa->user;
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

            // Cek kunci sementara karena kecurangan (3x keluar dari ujian)
            if ($user->exam_locked_until && $user->exam_locked_until->isFuture()) {
                $menit = max(1, (int) ceil(now()->diffInMinutes($user->exam_locked_until)));

                return back()->withInput()->withErrors([
                    'username' => "Akun Anda dikunci karena kecurangan. Silakan coba lagi dalam ± {$menit} menit."
                ]);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->route('siswa.ujian');
        }

        return back()
            ->withInput()
            ->withErrors([
                'username' => 'Username / NIS / Nama atau Password salah.'
            ]);
    }
}
