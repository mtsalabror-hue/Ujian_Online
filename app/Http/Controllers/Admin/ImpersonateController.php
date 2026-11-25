<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Admin masuk sebagai guru tanpa logout dari akun admin.
     * ID admin disimpan di session supaya bisa kembali lagi.
     */
    public function masukSebagaiGuru(Request $request, Guru $guru)
    {
        $target = $guru->user;

        if (!$target || $target->role !== 'guru') {
            return back()->with('error', 'Akun guru tidak ditemukan atau belum memiliki akun login.');
        }

        if (!$target->status) {
            return back()->with('error', 'Akun guru ini tidak aktif.');
        }

        $request->session()->put('impersonate_admin_id', Auth::id());

        Auth::login($target);
        $request->session()->regenerate();

        return redirect()->route('guru.dashboard')
            ->with('success', 'Anda masuk sebagai guru ' . $guru->nama_guru . '.');
    }

    /**
     * Kembali ke akun admin setelah sebelumnya masuk sebagai guru.
     */
    public function kembaliKeAdmin(Request $request)
    {
        $adminId = $request->session()->get('impersonate_admin_id');

        $admin = $adminId
            ? User::where('id', $adminId)->where('role', 'admin')->first()
            : null;

        if (!$admin) {
            return redirect()->route('login');
        }

        $request->session()->forget('impersonate_admin_id');

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Kembali ke akun admin.');
    }
}
