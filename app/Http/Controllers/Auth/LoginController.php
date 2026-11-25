<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login gabungan untuk semua pengguna
     * (admin, guru, dan siswa memakai satu form yang sama).
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses login gabungan.
     *
     * Identitas yang diterima:
     * - Admin  : username
     * - Guru   : username, NIK, nama guru, atau email
     * - Siswa  : username, NIS, NISN, atau nama siswa
     *
     * Setelah password cocok, pengguna diarahkan sesuai rolenya.
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $input = trim($credentials['username']);

        // 1. Cari user secara langsung via username di tabel users.
        $user = User::where('username', $input)->first();

        // 2. Jika tidak ditemukan, cari via tabel guru
        //    (berdasarkan NIK, nama guru, atau email).
        if (!$user) {
            $guru = Guru::where('nik', $input)
                ->orWhere('nama_guru', $input)
                ->orWhere('email', $input)
                ->first();

            if ($guru && $guru->user) {
                $user = $guru->user;
            }
        }

        // 3. Jika belum ditemukan, cari via tabel siswa
        //    (berdasarkan NIS, NISN, atau nama siswa).
        if (!$user) {
            $siswa = Siswa::where('nis', $input)
                ->orWhere('nisn', $input)
                ->orWhere('nama_siswa', $input)
                ->first();

            if ($siswa && $siswa->user) {
                $user = $siswa->user;
            }
        }

        // 4. Verifikasi password jika user ditemukan.
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'username' => 'Username / NIS / NIK atau password salah.',
            ])->onlyInput('username');
        }

        // Cek status akun
        if (!$user->status) {
            return back()->withErrors([
                'username' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ])->onlyInput('username');
        }

        // Cek kunci sementara siswa karena kecurangan (3x keluar dari ujian).
        if ($user->role === 'siswa'
            && $user->exam_locked_until
            && $user->exam_locked_until->isFuture()
        ) {
            $menit = max(1, (int) ceil(now()->diffInMinutes($user->exam_locked_until)));

            return back()->withErrors([
                'username' => "Akun Anda dikunci karena kecurangan. Silakan coba lagi dalam ± {$menit} menit.",
            ])->onlyInput('username');
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Redirect berdasarkan role
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru'  => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.ujian'),
            default => $this->logout($request),
        };
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
