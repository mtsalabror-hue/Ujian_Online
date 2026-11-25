<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilUjian;
use App\Models\Ujian;
use Illuminate\Http\Request;

class HasilSiswaController extends Controller
{
    /**
     * Lihat hasil ujian seluruh siswa (semua guru), dengan filter
     * per ujian dan pencarian nama / NIS siswa.
     */
    public function index(Request $request)
    {
        $ujianList = Ujian::with(['mataPelajaran', 'kelas'])
            ->latest()
            ->get();

        $hasil = HasilUjian::with(['siswa.kelas', 'ujian.mataPelajaran', 'ujian.kelas', 'ujian.guru'])
            ->when($request->id_ujian, function ($query, $ujianId) {
                $query->where('id_ujian', $ujianId);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->whereHas('siswa', function ($siswa) use ($keyword) {
                    $siswa->where('nama_siswa', 'like', "%{$keyword}%")
                        ->orWhere('nis', 'like', "%{$keyword}%");
                });
            })
            ->orderByDesc('tanggal_selesai')
            ->paginate(20)
            ->withQueryString();

        // Statistik ringkas untuk ujian yang sedang dipilih.
        $stats = null;

        if ($request->id_ujian) {
            $query = HasilUjian::where('id_ujian', $request->id_ujian);

            $stats = [
                'peserta' => (clone $query)->count(),
                'rata'    => (clone $query)->avg('nilai'),
                'max'     => (clone $query)->max('nilai'),
                'min'     => (clone $query)->min('nilai'),
            ];
        }

        return view('admin.hasil-siswa.index', compact('ujianList', 'hasil', 'stats'));
    }
}
