<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            return view('guru.dashboard', [
                'totalBankSoal' => 0,
                'totalSoal'     => 0,
                'totalUjian'    => 0,
                'ujianAktif'    => 0,
                'ujianTerbaru'  => collect(),
            ]);
        }

        return view('guru.dashboard', [
            // "Bank Soal" dihitung per mata pelajaran yang punya soal
            'totalBankSoal' => $guru->bankSoal()->distinct()->count('id_mapel'),
            'totalSoal'     => $guru->bankSoal()->count(),
            'totalUjian'    => $guru->ujian()->count(),
            'ujianAktif'    => $guru->ujian()->where('status', 'aktif')->count(),
            'ujianTerbaru'  => $guru->ujian()
                ->with('kelas')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}