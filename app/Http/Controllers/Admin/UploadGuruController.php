<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankSoal;
use App\Models\Guru;
use Illuminate\Http\Request;

class UploadGuruController extends Controller
{
    /**
     * Pantau status upload bank soal dan ujian dari semua guru.
     */
    public function index(Request $request)
    {
        $guru = Guru::with(['mataPelajaran', 'user'])
            ->withCount(['bankSoal', 'ujian'])
            ->withMax('bankSoal as soal_terakhir', 'created_at')
            ->when($request->keyword, function ($query, $keyword) {
                $query->where('nama_guru', 'like', "%{$keyword}%")
                    ->orWhere('nik', 'like', "%{$keyword}%");
            })
            ->orderBy('nama_guru')
            ->paginate(15)
            ->withQueryString();

        // Jumlah mata pelajaran yang sudah punya soal, per guru.
        $mapelAdaSoal = BankSoal::selectRaw('id_guru, COUNT(DISTINCT id_mapel) as jumlah')
            ->groupBy('id_guru')
            ->pluck('jumlah', 'id_guru');

        return view('admin.upload-guru.index', compact('guru', 'mapelAdaSoal'));
    }
}
