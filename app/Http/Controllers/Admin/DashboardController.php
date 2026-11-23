<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Ujian;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalGuru'  => Guru::count(),
            'totalSiswa' => Siswa::count(),
            'totalKelas' => Kelas::count(),
            'totalUjian' => Ujian::count(),
        ]);
    }
}
