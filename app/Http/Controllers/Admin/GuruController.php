<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    /**
     * Menampilkan daftar guru.
     */
    public function index(Request $request)
    {
        $guru = Guru::with(['user', 'mataPelajaran', 'kelas'])
            ->when($request->keyword, function ($query, $keyword) {
                $query->where('nama_guru', 'like', "%{$keyword}%")
                    ->orWhere('nik', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.guru.index', compact('guru'));
    }

    /**
     * Form tambah guru.
     */
    public function create()
    {
        $mapel = MataPelajaran::orderBy('nama_mapel')->get();
        $kelasList = Kelas::orderByRaw("CASE tingkat WHEN 'VII' THEN 1 WHEN 'VIII' THEN 2 WHEN 'IX' THEN 3 ELSE 4 END")->orderBy('nama_kelas')->get();

        return view('admin.guru.create', compact('mapel', 'kelasList'));
    }

    /**
     * Simpan data guru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik'             => 'required|string|max:30|unique:guru,nik',
            'nama_guru'       => 'required|string|max:100',
            'jenis_kelamin'   => 'required|in:L,P',
            'email'           => 'nullable|email|unique:guru,email',
            'no_hp'           => 'nullable|string|max:20',
            'alamat'          => 'nullable|string',
            'mapel'           => 'nullable|array',
            'mapel.*'         => 'exists:mata_pelajaran,id_mapel',
            'kelas'           => 'nullable|array',
            'kelas.*'         => 'exists:kelas,id_kelas',
        ]);

        DB::transaction(function () use ($request) {

            $user = User::create([
                'username' => $request->nik,
                'email'    => $request->email,
                'password' => Hash::make($request->nik),
                'role'     => 'guru',
                'status'   => true,
            ]);

            $guru = Guru::create([
                'user_id'         => $user->id,
                'nik'             => $request->nik,
                'nama_guru'       => $request->nama_guru,
                'jenis_kelamin'   => $request->jenis_kelamin,
                'email'           => $request->email,
                'no_hp'           => $request->no_hp,
                'alamat'          => $request->alamat,
            ]);

            // Simpan mata pelajaran yang diampu guru
            $guru->mataPelajaran()->sync($request->input('mapel', []));

            // Simpan kelas yang diajar guru
            $guru->kelas()->sync($request->input('kelas', []));
        });

        return redirect()
            ->route('admin.guru.index')
            ->with('success', 'Data guru berhasil ditambahkan.');
    }

    /**
     * Detail guru.
     */
    public function show(Guru $guru)
    {
        $guru->load('mataPelajaran', 'kelas');

        return view('admin.guru.show', compact('guru'));
    }

    /**
     * Form edit guru.
     */
    public function edit(Guru $guru)
    {
        $guru->load('mataPelajaran', 'kelas');
        $mapel = MataPelajaran::orderBy('nama_mapel')->get();
        $kelasList = Kelas::orderByRaw("CASE tingkat WHEN 'VII' THEN 1 WHEN 'VIII' THEN 2 WHEN 'IX' THEN 3 ELSE 4 END")->orderBy('nama_kelas')->get();

        return view('admin.guru.edit', compact('guru', 'mapel', 'kelasList'));
    }

    /**
     * Update guru.
     */
    public function update(Request $request, Guru $guru)
    {
        $request->validate([
            'nik' => [
                'required',
                Rule::unique('guru', 'nik')->ignore($guru->id_guru, 'id_guru'),
            ],

            'nama_guru' => 'required|max:100',

            'jenis_kelamin' => 'required|in:L,P',

            'email' => [
                'nullable',
                'email',
                Rule::unique('guru', 'email')->ignore($guru->id_guru, 'id_guru'),
            ],

            'no_hp' => 'nullable|max:20',

            'alamat' => 'nullable',

            'mapel' => 'nullable|array',

            'mapel.*' => 'exists:mata_pelajaran,id_mapel',

            'kelas' => 'nullable|array',

            'kelas.*' => 'exists:kelas,id_kelas',
        ]);

        DB::transaction(function () use ($request, $guru) {

            $guru->update([
                'nik'             => $request->nik,
                'nama_guru'       => $request->nama_guru,
                'jenis_kelamin'   => $request->jenis_kelamin,
                'email'           => $request->email,
                'no_hp'           => $request->no_hp,
                'alamat'          => $request->alamat,
            ]);

            // Perbarui mata pelajaran yang diampu guru
            $guru->mataPelajaran()->sync($request->input('mapel', []));

            // Perbarui kelas yang diajar guru
            $guru->kelas()->sync($request->input('kelas', []));

            // Username = NIK, password = NIK yang didaftarkan,
            // supaya login guru selalu sinkron dengan data guru.
            $guru->user->update([
                'username' => $request->nik,
                'email'    => $request->email,
                'password' => Hash::make($request->nik),
            ]);
        });

        return redirect()
            ->route('admin.guru.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Hapus guru.
     */
    public function destroy(Guru $guru)
    {
        DB::transaction(function () use ($guru) {

            $user = $guru->user;

            $guru->delete();

            if ($user) {
                $user->delete();
            }
        });

        return redirect()
            ->route('admin.guru.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }
}
