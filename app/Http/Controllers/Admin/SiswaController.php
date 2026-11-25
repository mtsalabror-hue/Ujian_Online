<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $idKelas = $request->id_kelas;

        $siswa = Siswa::with(['kelas', 'user'])
            ->when($keyword, function ($query) use ($keyword) {
                // Kelompokkan pencarian nama/NIS/NISN dalam satu kurung supaya
                // filter kelas di bawah tetap berlaku untuk ketiganya.
                $query->where(function ($q) use ($keyword) {
                    $q->where('nama_siswa', 'like', "%{$keyword}%")
                        ->orWhere('nis', 'like', "%{$keyword}%")
                        ->orWhere('nisn', 'like', "%{$keyword}%");
                });
            })
            ->when($idKelas, function ($query) use ($idKelas) {
                $query->where('id_kelas', $idKelas);
            })
            // Urut abjad A–Z berdasarkan nama siswa (tetap berlaku saat difilter).
            ->orderBy('nama_siswa')
            ->paginate(10)
            // Pertahankan filter + pencarian saat pindah halaman.
            ->withQueryString();

        $kelasList = Kelas::orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")->orderBy('nama_kelas')->get();

        return view('admin.siswa.index', compact('siswa', 'kelasList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelas = Kelas::orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")->orderBy('nama_kelas')->get();

        return view('admin.siswa.create', compact('kelas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nis'            => 'required|unique:siswa,nis',
            'nisn'           => 'nullable|string|max:30',
            'nama_siswa'     => 'required|max:100',
            'id_kelas'       => 'required|exists:kelas,id_kelas',
            'jenis_kelamin'  => 'required|in:L,P',
            'foto'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('siswa-foto', 'public');
        }

        DB::transaction(function () use ($request, $fotoPath) {

            // Password default siswa = NISN (atau NIS kalau NISN kosong).
            $password = !empty($request->nisn) ? $request->nisn : $request->nis;

            $user = User::create([
                'username' => $request->nis,
                'password' => Hash::make($password),
                'role'     => 'siswa',
                'status'   => true,
            ]);

            Siswa::create([
                'user_id'       => $user->id,
                'id_kelas'      => $request->id_kelas,
                'nis'           => $request->nis,
                'nisn'          => $request->nisn ?? null,
                'nama_siswa'    => mb_convert_case($request->nama_siswa, MB_CASE_TITLE, 'UTF-8'),
                'jenis_kelamin' => $request->jenis_kelamin,
                'foto'          => $fotoPath,
            ]);
        });

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
        $siswa->load(['kelas', 'user']);

        return view('admin.siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")->orderBy('nama_kelas')->get();

        return view('admin.siswa.edit', compact('siswa', 'kelas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nis'           => 'required|unique:siswa,nis,' . $siswa->id_siswa . ',id_siswa',
            'nisn'          => 'nullable|string|max:30',
            'nama_siswa'    => 'required|max:100',
            'id_kelas'      => 'required|exists:kelas,id_kelas',
            'jenis_kelamin' => 'required|in:L,P',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $fotoPath = $siswa->foto;
        if ($request->hasFile('foto')) {
            if ($siswa->foto && Storage::disk('public')->exists($siswa->foto)) {
                Storage::disk('public')->delete($siswa->foto);
            }
            $fotoPath = $request->file('foto')->store('siswa-foto', 'public');
        }

        DB::transaction(function () use ($request, $siswa, $fotoPath) {

            // Username = NIS, password = NISN (atau NIS kalau NISN kosong),
            // supaya kartu ujian selalu sinkron dengan akun login siswa.
            $siswa->user->update([
                'username' => $request->nis,
                'password' => Hash::make(!empty($request->nisn) ? $request->nisn : $request->nis),
            ]);

            $siswa->update([
                'nis'           => $request->nis,
                'nisn'          => $request->nisn,
                'nama_siswa'    => mb_convert_case($request->nama_siswa, MB_CASE_TITLE, 'UTF-8'),
                'id_kelas'      => $request->id_kelas,
                'jenis_kelamin' => $request->jenis_kelamin,
                'foto'          => $fotoPath,
            ]);
        });

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        DB::transaction(function () use ($siswa) {

            // Simpan user sebelum siswa dihapus
            $user = $siswa->user;

            // Hapus dulu data terkait ujian siswa (hindari error foreign key)
            JawabanSiswa::where('id_siswa', $siswa->id_siswa)->delete();
            HasilUjian::where('id_siswa', $siswa->id_siswa)->delete();

            // Hapus data siswa
            $siswa->delete();

            // Hapus akun login
            if ($user) {
                $user->delete();
            }
        });

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $import = new SiswaImport;
        Excel::import($import, $request->file('file'));

        // Gagal jika TIDAK ADA data valid ATAU ada baris yang dilewati
        if ($import->imported === 0) {
            return redirect()
                ->route('admin.siswa.index')
                ->with('error', 'Import gagal: tidak ada data siswa yang valid. Pastikan file sesuai template (kolom: nis, nisn, nama_siswa, kelas, jenis_kelamin) dan nama kelas sudah terdaftar.');
        }

        if ($import->skipped > 0) {
            return redirect()
                ->route('admin.siswa.index')
                ->with('error', "Import gagal: {$import->imported} data siswa berhasil dibaca, namun {$import->skipped} baris tidak valid (kelas tidak ditemukan, NIS duplikat, atau format tidak sesuai template). Perbaiki file lalu import ulang.");
        }

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', "{$import->imported} data siswa berhasil diimport.");
    }

    public function exportExcel()
    {
        return Excel::download(new \App\Exports\SiswaExport, 'data-siswa.xlsx');
    }

    public function exportPdf()
    {
        return Excel::download(new \App\Exports\SiswaExport, 'data-siswa.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    }

    public function template()
    {
        return Excel::download(new \App\Exports\SiswaTemplateExport, 'template-siswa.xlsx');
    }

    public function cetakKartu(Request $request)
    {
        $idKelas = $request->id_kelas;
        $namaUjian = $request->nama_ujian ?? 'UJIAN TENGAH SEMESTER';
        $tahunAjaran = $request->tahun_ajaran ?? '2026/2027';

        $setting = \App\Models\Setting::firstOrCreate(
            [],
            ['nama_sekolah' => 'Nama Sekolah']
        );

        $jabatanPenandatangan = $request->jabatan_penandatangan ?? 'Panitia Pelaksana';
        $namaPenandatangan = $request->nama_penandatangan ?? ($setting->kepala_sekolah ?? '');
        $nipPenandatangan = $request->nip_penandatangan ?? '';

        $kelasList = Kelas::orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")->orderBy('nama_kelas')->get();

        $siswa = Siswa::with(['kelas', 'user'])
            ->when($idKelas, function ($query) use ($idKelas) {
                $query->where('id_kelas', $idKelas);
            })
            ->orderBy('nama_siswa')
            ->get();

        return view('admin.siswa.cetak-kartu', compact(
            'siswa', 'kelasList', 'idKelas', 'namaUjian', 'tahunAjaran', 'setting',
            'jabatanPenandatangan', 'namaPenandatangan', 'nipPenandatangan'
        ));
    }

    public function downloadKartuPdf(Request $request)
    {
        // PDF kartu memuat banyak gambar base64 sekaligus (logo di tiap kartu
        // + foto siswa) — butuh memori lebih besar dari default 128M.
        ini_set('memory_limit', '512M');

        $idKelas = $request->id_kelas;
        $namaUjian = $request->nama_ujian ?? 'UJIAN TENGAH SEMESTER';
        $tahunAjaran = $request->tahun_ajaran ?? '2026/2027';

        $setting = \App\Models\Setting::firstOrCreate(
            [],
            ['nama_sekolah' => 'Nama Sekolah']
        );

        $jabatanPenandatangan = $request->jabatan_penandatangan ?? 'Panitia Pelaksana';
        $namaPenandatangan = $request->nama_penandatangan ?? ($setting->kepala_sekolah ?? '');
        $nipPenandatangan = $request->nip_penandatangan ?? '';

        $siswa = Siswa::with(['kelas', 'user'])
            ->when($idKelas, function ($query) use ($idKelas) {
                $query->where('id_kelas', $idKelas);
            })
            ->orderBy('nama_siswa')
            ->get();

        // Logo image base64 conversion for reliable Dompdf rendering
        $logoPath = null;
        if (!empty($setting->logo) && file_exists(public_path('storage/' . $setting->logo))) {
            $logoPath = public_path('storage/' . $setting->logo);
        } elseif (file_exists(public_path('images/logoo.png'))) {
            $logoPath = public_path('images/logoo.png');
        }

        $logoBase64 = null;
        if ($logoPath && file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($logoData);
        }

        // TTD base64 conversion
        $ttdBase64 = null;
        if (!empty($setting->ttd_image) && file_exists(public_path('storage/' . $setting->ttd_image))) {
            $ttdPath = public_path('storage/' . $setting->ttd_image);
            $ttdData = file_get_contents($ttdPath);
            $type = pathinfo($ttdPath, PATHINFO_EXTENSION);
            $ttdBase64 = 'data:image/' . $type . ';base64,' . base64_encode($ttdData);
        }

        // Stempel base64 conversion
        $stempelBase64 = null;
        if (!empty($setting->stempel_image) && file_exists(public_path('storage/' . $setting->stempel_image))) {
            $stempelPath = public_path('storage/' . $setting->stempel_image);
            $stempelData = file_get_contents($stempelPath);
            $type = pathinfo($stempelPath, PATHINFO_EXTENSION);
            $stempelBase64 = 'data:image/' . $type . ';base64,' . base64_encode($stempelData);
        }

        // Siswa photos base64 conversion
        foreach ($siswa as $s) {
            $s->foto_base64 = null;
            if (!empty($s->foto) && file_exists(public_path('storage/' . $s->foto))) {
                $fotoPath = public_path('storage/' . $s->foto);
                $fotoData = file_get_contents($fotoPath);
                $type = pathinfo($fotoPath, PATHINFO_EXTENSION);
                $s->foto_base64 = 'data:image/' . $type . ';base64,' . base64_encode($fotoData);
            }
        }

        $cssContent = file_exists(public_path('css/pdf-kartu.css')) ? file_get_contents(public_path('css/pdf-kartu.css')) : '';

        $html = view('admin.siswa.pdf-kartu', compact(
            'siswa', 'idKelas', 'namaUjian', 'tahunAjaran', 'setting', 'logoBase64',
            'ttdBase64', 'stempelBase64', 'jabatanPenandatangan', 'namaPenandatangan', 'nipPenandatangan',
            'cssContent'
        ))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Kartu_Ujian_Siswa_' . date('Ymd_His') . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
