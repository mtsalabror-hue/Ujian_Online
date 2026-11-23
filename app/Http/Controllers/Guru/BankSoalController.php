<?php

namespace App\Http\Controllers\Guru;

use App\Exports\BankSoalTemplateDoc;
use App\Http\Controllers\Controller;
use App\Models\BankSoal;
use App\Models\DetailUjian;
use App\Models\JawabanSiswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Services\BankSoalDocParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankSoalController extends Controller
{
    /**
     * Menampilkan ringkasan bank soal per mata pelajaran milik guru yang login
     */
    public function index(Request $request)
    {
        $guru = Auth::user()->guru;
        $guruId = $guru?->id_guru;

        $bankSoalMapel = MataPelajaran::with(['bankSoal' => function ($q) use ($guruId) {
            // Load seluruh soal milik guru yang login beserta kelas, untuk dipisah per kelas
            $q->where('id_guru', $guruId)
                ->with('kelas')
                ->orderBy('created_at');
        }])
            // Hanya mapel yang ditugaskan admin ke guru ini
            ->whereIn('id_mapel', $this->allowedMapelIds())
            ->when($request->keyword, function ($query, $keyword) {
                $query->where('nama_mapel', 'like', "%{$keyword}%")
                    ->orWhere('kode_mapel', 'like', "%{$keyword}%");
            })
            ->paginate(10)
            ->withQueryString();

        return view('guru.bank-soal.index', compact('bankSoalMapel'));
    }

    /**
     * Menampilkan daftar soal lengkap per mata pelajaran tertentu
     */
    public function detailMapel(Request $request, $id_mapel)
    {
        $guru = Auth::user()->guru;
        $this->authorizeMapel($id_mapel);

        $mapel = MataPelajaran::findOrFail($id_mapel);

        $idKelas = $request->query('id_kelas');

        $bankSoal = BankSoal::with('kelas')
            ->where('id_guru', $guru?->id_guru)
            ->where('id_mapel', $id_mapel)
            ->when($idKelas !== null, function ($query) use ($idKelas) {
                if ($idKelas === 'all') {
                    $query->whereNull('id_kelas');
                } else {
                    $query->where('id_kelas', $idKelas);
                }
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where('pertanyaan', 'like', "%{$keyword}%");
            })
            ->when($request->jenis_soal, function ($query, $jenis) {
                $query->where('jenis_soal', $jenis);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('guru.bank-soal.detail-mapel', compact('mapel', 'bankSoal', 'idKelas'));
    }

    /**
     * Form tambah soal
     */
    public function create(Request $request)
    {
        $mataPelajaran = $this->mapelUntukGuru();
        $kelasList = Kelas::orderByRaw("CASE tingkat WHEN 'VII' THEN 1 WHEN 'VIII' THEN 2 WHEN 'IX' THEN 3 ELSE 4 END")->orderBy('nama_kelas')->get();
        $selectedMapelId = $request->query('id_mapel');
        $selectedKelasId = $request->query('id_kelas');

        return view('guru.bank-soal.create', compact('mataPelajaran', 'kelasList', 'selectedMapelId', 'selectedKelasId'));
    }

    /**
     * Simpan soal baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'id_kelas' => 'nullable|exists:kelas,id_kelas',
            'jenis_soal' => 'required|in:pilihan_ganda,benar_salah,essay',
            'pertanyaan' => 'required|string',
            'bobot' => 'required|numeric|min:0|max:100',
        ]);

        $this->authorizeMapel($request->input('id_mapel'));

        $jenisSoal = $request->jenis_soal;
        $opsiA = null;
        $opsiB = null;
        $opsiC = null;
        $opsiD = null;
        $jawabanBenar = null;

        if ($jenisSoal === 'pilihan_ganda') {
            $request->validate([
                'opsi_a' => 'required|string',
                'opsi_b' => 'required|string',
                'opsi_c' => 'required|string',
                'opsi_d' => 'required|string',
                'jawaban_benar' => 'required|in:A,B,C,D',
            ]);
            $opsiA = $request->opsi_a;
            $opsiB = $request->opsi_b;
            $opsiC = $request->opsi_c;
            $opsiD = $request->opsi_d;
            $jawabanBenar = $request->jawaban_benar;
        } elseif ($jenisSoal === 'benar_salah') {
            $request->validate([
                'jawaban_benar_bs' => 'required|in:Benar,Salah',
            ]);
            $opsiA = 'Benar';
            $opsiB = 'Salah';
            $jawabanBenar = $request->jawaban_benar_bs;
        } else { // essay
            $jawabanBenar = $request->kunci_essay;
        }

        $guru = Auth::user()->guru;

        if (! $guru) {
            return redirect()
                ->back()
                ->with('error', 'Data profil guru tidak ditemukan. Silakan lengkapi profil guru terlebih dahulu atau hubungi admin.');
        }

        BankSoal::create([
            'id_guru' => $guru->id_guru,
            'id_mapel' => $request->id_mapel,
            'id_kelas' => $request->id_kelas,
            'jenis_soal' => $jenisSoal,
            'pertanyaan' => $request->pertanyaan,
            'opsi_a' => $opsiA,
            'opsi_b' => $opsiB,
            'opsi_c' => $opsiC,
            'opsi_d' => $opsiD,
            'jawaban_benar' => $jawabanBenar,
            'bobot' => $request->bobot,
        ]);

        return redirect()
            ->route('guru.bank-soal.mapel', ['id_mapel' => $request->id_mapel, 'id_kelas' => $request->id_kelas ?: 'all'])
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    /**
     * Pastikan soal milik guru yang sedang login
     */
    private function authorizeSoal(BankSoal $bankSoal): void
    {
        $guruId = Auth::user()->guru?->id_guru;

        abort_if($guruId !== $bankSoal->id_guru, 403, 'Anda tidak berhak mengakses soal ini.');
    }

    /**
     * Daftar id_mapel yang ditugaskan admin ke guru yang sedang login.
     */
    private function allowedMapelIds(): array
    {
        $guru = Auth::user()->guru;
        if (!$guru) {
            return [];
        }

        return $guru->mataPelajaran()->pluck('mata_pelajaran.id_mapel')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Pastikan mapel termasuk penugasan guru yang sedang login.
     */
    private function authorizeMapel($idMapel): void
    {
        abort_unless(
            in_array((int) $idMapel, $this->allowedMapelIds(), true),
            403,
            'Anda tidak berhak mengakses mata pelajaran ini.'
        );
    }

    /**
     * Dropdown mapel: hanya yang ditugaskan admin ke guru ini.
     */
    private function mapelUntukGuru()
    {
        return Auth::user()->guru?->mataPelajaran()->orderBy('nama_mapel')->get() ?? collect();
    }

    /**
     * Detail soal
     */
    public function show(BankSoal $bankSoal)
    {
        $this->authorizeSoal($bankSoal);

        // Pertahankan filter kelas asal (supaya tombol Kembali balik ke daftar per kelas,
        // bukan ke daftar gabungan). Fallback ke kelas soal itu sendiri.
        $idKelas = request()->query('id_kelas');
        if ($idKelas === null) {
            $idKelas = $bankSoal->id_kelas ?? 'all';
        }

        return view('guru.bank-soal.show', compact('bankSoal', 'idKelas'));
    }

    /**
     * Form edit soal
     */
    public function edit(BankSoal $bankSoal)
    {
        $this->authorizeSoal($bankSoal);

        $mataPelajaran = $this->mapelUntukGuru();
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        // Pertahankan filter kelas asal (supaya tombol Batal balik ke daftar per kelas).
        $idKelas = request()->query('id_kelas');
        if ($idKelas === null) {
            $idKelas = $bankSoal->id_kelas ?? 'all';
        }

        return view('guru.bank-soal.edit', compact('bankSoal', 'mataPelajaran', 'kelasList', 'idKelas'));
    }

    /**
     * Update soal
     */
    public function update(Request $request, BankSoal $bankSoal)
    {
        $this->authorizeSoal($bankSoal);

        $request->validate([
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'id_kelas' => 'nullable|exists:kelas,id_kelas',
            'jenis_soal' => 'required|in:pilihan_ganda,benar_salah,essay',
            'pertanyaan' => 'required|string',
            'bobot' => 'required|numeric|min:0|max:100',
        ]);

        $this->authorizeMapel($request->input('id_mapel'));

        $jenisSoal = $request->jenis_soal;
        $opsiA = null;
        $opsiB = null;
        $opsiC = null;
        $opsiD = null;
        $jawabanBenar = null;

        if ($jenisSoal === 'pilihan_ganda') {
            $request->validate([
                'opsi_a' => 'required|string',
                'opsi_b' => 'required|string',
                'opsi_c' => 'required|string',
                'opsi_d' => 'required|string',
                'jawaban_benar' => 'required|in:A,B,C,D',
            ]);
            $opsiA = $request->opsi_a;
            $opsiB = $request->opsi_b;
            $opsiC = $request->opsi_c;
            $opsiD = $request->opsi_d;
            $jawabanBenar = $request->jawaban_benar;
        } elseif ($jenisSoal === 'benar_salah') {
            $request->validate([
                'jawaban_benar_bs' => 'required|in:Benar,Salah',
            ]);
            $opsiA = 'Benar';
            $opsiB = 'Salah';
            $jawabanBenar = $request->jawaban_benar_bs;
        } else { // essay
            $jawabanBenar = $request->kunci_essay;
        }

        $bankSoal->update([
            'id_mapel' => $request->id_mapel,
            'id_kelas' => $request->id_kelas,
            'jenis_soal' => $jenisSoal,
            'pertanyaan' => $request->pertanyaan,
            'opsi_a' => $opsiA,
            'opsi_b' => $opsiB,
            'opsi_c' => $opsiC,
            'opsi_d' => $opsiD,
            'jawaban_benar' => $jawabanBenar,
            'bobot' => $request->bobot,
        ]);

        return redirect()
            ->route('guru.bank-soal.mapel', ['id_mapel' => $request->id_mapel, 'id_kelas' => $request->id_kelas ?: 'all'])
            ->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Hapus soal
     */
    public function destroy(BankSoal $bankSoal)
    {
        $this->authorizeSoal($bankSoal);

        $idMapel = $bankSoal->id_mapel;
        // Pertahankan filter kelas asal supaya kembali ke daftar per kelas.
        $idKelas = $bankSoal->id_kelas ?? 'all';

        // Hapus dulu relasi yang memakai soal ini (hindari error foreign key)
        DetailUjian::where('id_soal', $bankSoal->id_soal)->delete();
        JawabanSiswa::where('id_soal', $bankSoal->id_soal)->delete();
        $bankSoal->delete();

        return redirect()
            ->route('guru.bank-soal.mapel', ['id_mapel' => $idMapel, 'id_kelas' => $idKelas])
            ->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Form edit seluruh soal milik mapel tertentu untuk satu kelas (satu halaman)
     */
    public function editMapel(Request $request, $id_mapel)
    {
        $guru = Auth::user()->guru;

        $this->authorizeMapel($id_mapel);

        $mapel = MataPelajaran::findOrFail($id_mapel);

        $idKelas = $request->query('id_kelas', 'all');

        $bankSoal = BankSoal::with('kelas')
            ->where('id_guru', $guru?->id_guru)
            ->where('id_mapel', $id_mapel)
            ->when($idKelas === 'all', function ($query) {
                $query->whereNull('id_kelas');
            }, function ($query) use ($idKelas) {
                $query->where('id_kelas', $idKelas);
            })
            ->orderBy('created_at')
            ->get();

        if ($bankSoal->isEmpty()) {
            return redirect()
                ->route('guru.bank-soal.index')
                ->with('error', 'Tidak ada soal untuk kelas tersebut.');
        }

        return view('guru.bank-soal.edit-mapel', compact('mapel', 'bankSoal', 'idKelas'));
    }

    /**
     * Simpan perubahan seluruh soal milik mapel tertentu
     */
    public function updateMapel(Request $request, $id_mapel)
    {
        $guru = Auth::user()->guru;
        $this->authorizeMapel($id_mapel);

        $request->validate([
            'soal' => 'required|array|min:1',
            'soal.*.pertanyaan' => 'required|string',
            'soal.*.bobot' => 'required|numeric|min:0|max:100',
        ]);

        $soals = BankSoal::where('id_guru', $guru?->id_guru)
            ->where('id_mapel', $id_mapel)
            ->get()
            ->keyBy('id_soal');

        foreach ($request->soal as $idSoal => $data) {
            $bankSoal = $soals->get($idSoal);

            if (! $bankSoal) {
                continue;
            }

            $jenisSoal = $bankSoal->jenis_soal;
            $opsiA = null;
            $opsiB = null;
            $opsiC = null;
            $opsiD = null;
            $jawabanBenar = null;

            if ($jenisSoal === 'pilihan_ganda') {
                $opsiA = $data['opsi_a'] ?? null;
                $opsiB = $data['opsi_b'] ?? null;
                $opsiC = $data['opsi_c'] ?? null;
                $opsiD = $data['opsi_d'] ?? null;
                $jawabanBenar = $data['jawaban_benar'] ?? null;
            } elseif ($jenisSoal === 'benar_salah') {
                $opsiA = 'Benar';
                $opsiB = 'Salah';
                $jawabanBenar = $data['jawaban_benar_bs'] ?? null;
            } else { // essay
                $jawabanBenar = $data['kunci_essay'] ?? null;
            }

            $bankSoal->update([
                'id_kelas' => $data['id_kelas'] ?? null,
                'pertanyaan' => $data['pertanyaan'],
                'opsi_a' => $opsiA,
                'opsi_b' => $opsiB,
                'opsi_c' => $opsiC,
                'opsi_d' => $opsiD,
                'jawaban_benar' => $jawabanBenar,
                'bobot' => $data['bobot'],
            ]);
        }

        return redirect()
            ->route('guru.bank-soal.editMapel', $id_mapel)
            ->with('success', 'Seluruh soal untuk mata pelajaran tersebut berhasil diperbarui.');
    }

    /**
     * Hapus seluruh soal milik mapel tertentu UNTUK SATU KELAS.
     * Tombol hapus di daftar bank soal ada di tiap baris (mapel + kelas),
     * jadi penghapusan WAJIB dibatasi ke kelas baris itu saja. Sebelumnya
     * fungsi ini menghapus SEMUA kelas mapel tersebut — klik hapus di baris
     * kelas 7 ikut menghapus soal kelas 8 dan 9.
     */
    public function destroyMapel(Request $request, $id_mapel)
    {
        $guru = Auth::user()->guru;
        $this->authorizeMapel($id_mapel);

        $mapel = MataPelajaran::findOrFail($id_mapel);

        // Kelas yang dipilih dari baris yang diklik ('all' = soal tanpa kelas).
        $idKelas = $request->input('id_kelas', 'all');
        $namaKelas = 'Semua Kelas';
        if ($idKelas !== 'all' && $idKelas !== null && $idKelas !== '') {
            $namaKelas = Kelas::find($idKelas)?->nama_kelas ?? 'Kelas Terpilih';
        }

        $querySoal = BankSoal::where('id_guru', $guru?->id_guru)
            ->where('id_mapel', $id_mapel);

        if ($idKelas === 'all' || $idKelas === null || $idKelas === '') {
            $querySoal->whereNull('id_kelas');
        } else {
            $querySoal->where('id_kelas', $idKelas);
        }

        // Ambil id soal yang akan dihapus, lalu lepas relasi ujian-nya dulu
        $idSoalList = $querySoal->pluck('id_soal')->toArray();

        if (! empty($idSoalList)) {
            DetailUjian::whereIn('id_soal', $idSoalList)->delete();
            JawabanSiswa::whereIn('id_soal', $idSoalList)->delete();
        }

        $querySoal->delete();

        return redirect()
            ->route('guru.bank-soal.index')
            ->with('success', "Seluruh soal {$mapel->nama_mapel} kelas {$namaKelas} berhasil dihapus. Soal kelas lain tidak ikut terhapus.");
    }

    /**
     * Download template Word (.docx) untuk import soal
     */
    public function templateDoc()
    {
        return (new BankSoalTemplateDoc)->download();
    }

    /**
     * Import soal dari dokumen Word (.docx) berformat per-tipe soal
     */
    public function importDoc(Request $request)
    {
        $guru = Auth::user()->guru;

        if (! $guru) {
            return redirect()
                ->back()
                ->with('error', 'Data profil guru tidak ditemukan. Silakan lengkapi profil guru terlebih dahulu atau hubungi admin.');
        }

        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        // .docx adalah arsip ZIP yang diawali magic bytes "PK\x03\x04".
        // Validasi berbasis finfo/guessExtension tidak andal (sering terbaca "bin"),
        // jadi periksa langsung magic bytes file-nya.
        $handle = fopen($file->getRealPath(), 'rb');
        $magic  = $handle ? fread($handle, 4) : '';
        if ($handle) {
            fclose($handle);
        }

        $isZip = $magic === "PK\x03\x04";

        if ($extension !== 'docx' && !$isZip) {
            return redirect()
                ->route('guru.bank-soal.index')
                ->with('error', 'File harus berformat .docx.');
        }
        $soalList = (new BankSoalDocParser($file->getRealPath()))->parse();

        // Validasi: mata pelajaran di dokumen harus mapel yang diajar guru ini.
        // Kalau ada mapel yang terdaftar tapi BUKAN mapel guru, import GAGAL
        // seluruhnya (tidak ada soal yang masuk) — guru hanya boleh mengimport
        // soal untuk mata pelajaran yang ditugaskan kepadanya.
        $guruMapelIds = $guru->mataPelajaran->pluck('id_mapel')->all();
        $mapelNames = array_unique(array_map(fn ($s) => trim((string) ($s['mata_pelajaran'] ?? '')), $soalList));
        foreach ($mapelNames as $namaMapel) {
            if ($namaMapel === '') {
                continue;
            }
            $mapelDoc = MataPelajaran::whereRaw('LOWER(nama_mapel) = ?', [mb_strtolower($namaMapel)])
                ->orWhereRaw('LOWER(kode_mapel) = ?', [mb_strtolower($namaMapel)])
                ->first();
            if ($mapelDoc && ! in_array($mapelDoc->id_mapel, $guruMapelIds)) {
                return redirect()
                    ->route('guru.bank-soal.index')
                    ->with('error', "Import gagal: mata pelajaran \"{$mapelDoc->nama_mapel}\" bukan mata pelajaran yang Anda ajar. Anda hanya bisa mengimport soal untuk mata pelajaran yang ditugaskan kepada Anda.");
            }
        }

        $imported = 0;
        $skipped  = 0;
        foreach ($soalList as $soal) {
            $mapel = MataPelajaran::whereRaw('LOWER(nama_mapel) = ?', [mb_strtolower(trim((string) ($soal['mata_pelajaran'] ?? '')))])
                ->orWhereRaw('LOWER(kode_mapel) = ?', [mb_strtolower(trim((string) ($soal['mata_pelajaran'] ?? '')))])
                ->first();
            if (! $mapel) {
                $skipped++;
                continue;
            }

            // Validasi isian wajib sesuai jenis soal
            $jenisSoal = $soal['jenis_soal'] ?? 'pilihan_ganda';
            $jawabanBenar = trim((string) ($soal['jawaban_benar'] ?? ''));
            $pertanyaan = trim((string) ($soal['pertanyaan'] ?? ''));

            if ($pertanyaan === '') {
                $skipped++;
                continue;
            }

            if ($jenisSoal === 'pilihan_ganda') {
                $opsiA = trim((string) ($soal['opsi_a'] ?? ''));
                $opsiB = trim((string) ($soal['opsi_b'] ?? ''));
                $opsiC = trim((string) ($soal['opsi_c'] ?? ''));
                $opsiD = trim((string) ($soal['opsi_d'] ?? ''));
                if ($opsiA === '' || $opsiB === '' || $opsiC === '' || $opsiD === '' || !in_array(strtoupper($jawabanBenar), ['A', 'B', 'C', 'D'])) {
                    $skipped++;
                    continue;
                }
            } elseif ($jenisSoal === 'benar_salah') {
                // Terima Benar/Salah ATAU True/False — keduanya valid dan
                // TIDAK dinormalisasi: format yang dipakai guru dipertahankan,
                // supaya opsi yang dilihat siswa sama dengan yang ditulis guru
                // (soal Bahasa Inggris pakai True/False, soal Indonesia pakai
                // Benar/Salah). Penilaian membandingkan jawaban siswa dengan
                // jawaban_benar ini (case-insensitive), jadi keduanya cocok.
                $lower = strtolower($jawabanBenar);
                if ($lower === 'benar') {
                    $jawabanBenar = 'Benar';
                } elseif ($lower === 'salah') {
                    $jawabanBenar = 'Salah';
                } elseif ($lower === 'true') {
                    $jawabanBenar = 'True';
                } elseif ($lower === 'false') {
                    $jawabanBenar = 'False';
                } else {
                    $skipped++;
                    continue;
                }
            } elseif ($jenisSoal === 'essay') {
                if ($jawabanBenar === '') {
                    $skipped++;
                    continue;
                }
            } else {
                $skipped++;
                continue;
            }

            $kelas = null;
            if (! empty($soal['kelas'])) {
                $namaKelas = trim((string) $soal['kelas']);
                if (strtolower($namaKelas) !== 'semua kelas' && strtolower($namaKelas) !== 'semua') {
                    $kelas = Kelas::whereRaw('LOWER(nama_kelas) = ?', [mb_strtolower($namaKelas)])->first();
                }
            }

            $bobot = max(0, min(100, floatval($soal['bobot'] ?? 1.0)));

            $opsiAData = $jenisSoal === 'pilihan_ganda' ? ($soal['opsi_a'] ?? null) : null;
            $opsiBData = $jenisSoal === 'pilihan_ganda' ? ($soal['opsi_b'] ?? null) : null;
            $opsiCData = $jenisSoal === 'pilihan_ganda' ? ($soal['opsi_c'] ?? null) : null;
            $opsiDData = $jenisSoal === 'pilihan_ganda' ? ($soal['opsi_d'] ?? null) : null;

            if ($jenisSoal === 'benar_salah') {
                // Opsi mengikuti format yang dipakai guru (diisi parser dari
                // jawaban: True/False atau Benar/Salah). Fallback ke
                // Benar/Salah untuk soal lama yang opsinya belum tersimpan.
                $opsiAData = $soal['opsi_a'] ?? 'Benar';
                $opsiBData = $soal['opsi_b'] ?? 'Salah';
            }

            BankSoal::create([
                'id_guru'       => $guru->id_guru,
                'id_mapel'      => $mapel->id_mapel,
                'id_kelas'      => $kelas?->id_kelas,
                'jenis_soal'    => $jenisSoal,
                'pertanyaan'    => $pertanyaan,
                'opsi_a'        => $opsiAData,
                'opsi_b'        => $opsiBData,
                'opsi_c'        => $opsiCData,
                'opsi_d'        => $opsiDData,
                'jawaban_benar' => $jawabanBenar,
                'bobot'         => $bobot,
            ]);
            $imported++;
        }

        if ($imported === 0) {
            return redirect()
                ->route('guru.bank-soal.index')
                ->with('error', 'Import gagal: tidak ada soal yang valid ditemukan pada dokumen. Pastikan format sesuai template, identitas KELAS & MATA PELAJARAN terisi, dan nama mapel sudah terdaftar.');
        }

        if ($skipped > 0) {
            return redirect()
                ->route('guru.bank-soal.index')
                ->with('error', "Import gagal: {$imported} soal berhasil terbaca, namun {$skipped} soal tidak valid (mata pelajaran tidak ditemukan, isian jawaban/bobot tidak sesuai format). Perbaiki dokumen lalu import ulang.");
        }

        return redirect()
            ->route('guru.bank-soal.index')
            ->with('success', "{$imported} soal berhasil diimport dari Word.");
    }

    /**
     * Upload gambar dari Summernote editor (AJAX)
     * Menerima file gambar, menyimpan ke storage/soal-images, dan mengembalikan URL
     */
    public function uploadImage(Request $request)
    {
        if (! Auth::user()->guru) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif,webp,bmp|max:4096',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename = time().'_'.bin2hex(random_bytes(8)).'.'.$extension;

        // Simpan ke storage/app/public/soal-images/
        $path = $file->storeAs('soal-images', $filename, 'public');

        return response()->json([
            'url' => asset('storage/'.$path),
        ]);
    }
}
