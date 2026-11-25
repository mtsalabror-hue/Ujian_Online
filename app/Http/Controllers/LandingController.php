<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing.index');
    }

    /**
     * Halaman pilih jenis pengguna sudah tidak dipakai lagi —
     * semua pengguna login lewat satu form di /login.
     * Rute lama dialihkan ke sana supaya tautan lama tidak rusak.
     */
    public function pilihLogin()
    {
        return redirect()->route('login');
    }
}
