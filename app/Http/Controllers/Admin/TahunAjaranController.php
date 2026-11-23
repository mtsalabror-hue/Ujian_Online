<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TahunAjaran;

class TahunAjaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, TahunAjaran $tahunAjaran)
    {
        $keyword = $request->keyword;

        $tahunAjaran = $tahunAjaran->when($keyword, function ($query) use ($keyword) {
            $query->where('tahun_ajaran', 'like', "%{$keyword}%")
                ->orWhere('semester', 'like', "%{$keyword}%");
        })
            ->orderByDesc('status')
            ->orderByDesc('tahun_ajaran')
            ->paginate(10);

        return view('admin.tahun-ajaran.index', compact('tahunAjaran'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(TahunAjaran $tahunAjaran)
    {
        return view('admin.tahun-ajaran.create', compact('tahunAjaran'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, TahunAjaran $tahunAjaran)
    {
        $request->validate([
            'tahun_ajaran' => [
                'required',
                \Illuminate\Validation\Rule::unique('tahun_ajaran')
                    ->where(function ($query) use ($request) {
                        return $query->where('semester', $request->semester);
                    }),
            ],
            'semester' => 'required',
            'status'   => 'required',
        ], [
            'tahun_ajaran.unique' => 'Tahun Pelajaran dan Semester ini sudah ada.',
        ]);

        if ($request->status) {

            TahunAjaran::query()->update([
                'status' => false
            ]);
        }

        TahunAjaran::create([
            'tahun_ajaran' => $request->tahun_ajaran,
            'semester' => $request->semester,
            'status' => $request->status
        ]);

        return redirect()
            ->route('admin.tahun-ajaran.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TahunAjaran $tahunAjaran)
    {
        return view('admin.tahun-ajaran.show', compact('tahunAjaran'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TahunAjaran $tahunAjaran)
    {
        return view('admin.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $request->validate([
            'tahun_ajaran' => [
                'required',
                \Illuminate\Validation\Rule::unique('tahun_ajaran')
                    ->where(function ($query) use ($request) {
                        return $query->where('semester', $request->semester);
                    })
                    ->ignore($tahunAjaran->id_tahun, 'id_tahun'),
            ],
            'semester' => 'required',
            'status'   => 'required',
        ], [
            'tahun_ajaran.unique' => 'Tahun Pelajaran dan Semester ini sudah ada.',
        ]);

        if ($request->status) {

            TahunAjaran::query()->update([
                'status' => false
            ]);
        }

        $tahunAjaran->update([
            'tahun_ajaran' => $request->tahun_ajaran,
            'semester' => $request->semester,
            'status' => $request->status
        ]);

        return redirect()
            ->route('admin.tahun-ajaran.index')
            ->with('success', 'Data berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->delete();
        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Data berhasil dihapus.');
    }
}
