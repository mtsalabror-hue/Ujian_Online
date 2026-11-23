<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Ujian;
use App\Models\BankSoal;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;

        $kelas = Kelas::withCount('siswa')
            ->when($keyword, function ($query) use ($keyword) {

                $query->where('nama_kelas', 'like', "%{$keyword}%")
                    ->orWhere('tingkat', 'like', "%{$keyword}%");
            })
            ->orderByRaw("CASE tingkat WHEN 'VII' THEN 1 WHEN 'VIII' THEN 2 WHEN 'IX' THEN 3 ELSE 4 END")
            ->orderBy('nama_kelas')
            ->paginate(10);

        return view('admin.kelas.index', compact('kelas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.kelas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tingkat' => 'required|in:VII,VIII,IX',
            'nama_kelas' => 'required|max:20|unique:kelas,nama_kelas',
        ]);

        Kelas::create([
            'tingkat' => $request->tingkat,
            'nama_kelas' => $request->nama_kelas,
        ]);

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Data kelas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kelas $kelas)
    {
        $kelas->load('siswa');

        return view('admin.kelas.show', compact('kelas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(kelas $kelas)
    {
        return view('admin.kelas.edit', [
            'kelas' => $kelas
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kelas $kelas)
    {
        $request->validate([
            'tingkat' => 'required|in:VII,VIII,IX',
            'nama_kelas' => 'required|max:20|unique:kelas,nama_kelas,' . $kelas->id_kelas . ',id_kelas',
        ]);

        $kelas->update([
            'tingkat' => $request->tingkat,
            'nama_kelas' => $request->nama_kelas,
        ]);

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kelas)
    {
        $errors = [];

        if ($kelas->siswa()->count() > 0) {
            $errors[] = 'Kelas tidak dapat dihapus karena masih memiliki siswa.';
        }

        if ($kelas->ujian()->count() > 0) {
            $errors[] = 'Kelas tidak dapat dihapus karena masih memiliki ujian.';
        }

        if ($kelas->bankSoal()->count() > 0) {
            $errors[] = 'Kelas tidak dapat dihapus karena masih memiliki bank soal.';
        }

        if (! empty($errors)) {
            return redirect()
                ->route('admin.kelas.index')
                ->with('error', implode(' ', $errors));
        }

        $kelas->delete();

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Data kelas berhasil dihapus.');
    }
}
