<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\Ujian;
use App\Services\CosineSimilarityGrader;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class HasilUjianController extends Controller
{
    private function getGuruId(): int|null
    {
        return Auth::user()->guru?->id_guru;
    }

    /**
     * Ambil ujian hanya jika milik guru yang login; 404 jika bukan.
     */
    private function getOwnedUjian(int $ujianId): Ujian
    {
        return Ujian::with(['mataPelajaran', 'kelas'])
            ->where('id_guru', $this->getGuruId())
            ->findOrFail($ujianId);
    }

    /**
     * Lihat semua hasil ujian dari ujian milik guru.
     */
    public function index(Request $request)
    {
        $ujianList = Ujian::with(['mataPelajaran', 'kelas'])
            ->where('id_guru', $this->getGuruId())
            ->latest()
            ->get();

        $ujianId = $request->id_ujian;
        $ujian   = null;
        $hasil   = collect();

        if ($ujianId) {
            $ujian = $this->getOwnedUjian($ujianId);
            $hasil = HasilUjian::with('siswa.kelas')
                ->where('id_ujian', $ujianId)
                ->orderByDesc('nilai')
                ->get();
        }

        return view('guru.hasil.index', compact('ujianList', 'ujian', 'hasil'));
    }

    /**
     * Hasil ujian per peserta.
     */
    public function peserta(Request $request)
    {
        $ujianList = Ujian::with(['mataPelajaran', 'kelas'])
            ->where('id_guru', $this->getGuruId())
            ->latest()
            ->get();

        $ujianId = $request->id_ujian;
        $ujian   = null;
        $peserta = collect();

        if ($ujianId) {
            $ujian   = $this->getOwnedUjian($ujianId);
            $peserta = HasilUjian::with(['siswa.kelas'])
                ->where('id_ujian', $ujianId)
                ->orderByDesc('nilai')
                ->get();
        }

        return view('guru.hasil.peserta', compact('ujianList', 'ujian', 'peserta'));
    }

    /**
     * Daftar siswa yang pernah mengerjakan ujian milik guru.
     */
    private function getSiswaList(): Collection
    {
        $ujianIds = Ujian::where('id_guru', $this->getGuruId())->pluck('id_ujian');
        $siswaIds = HasilUjian::whereIn('id_ujian', $ujianIds)->distinct()->pluck('id_siswa');

        return Siswa::with('kelas')
            ->whereIn('id_siswa', $siswaIds)
            ->orderBy('nama_siswa')
            ->get();
    }

    /**
     * Baris nilai per mata pelajaran untuk satu siswa.
     */
    private function buildSiswaRows(Siswa $siswa): Collection
    {
        return HasilUjian::with(['ujian.mataPelajaran'])
            ->where('id_siswa', $siswa->id_siswa)
            ->get()
            ->groupBy(fn ($h) => $h->ujian->id_mapel)
            ->map(function ($group) {
                $latest = $group->sortByDesc('tanggal_selesai')->first();

                return [
                    'mapel' => $latest->ujian->mataPelajaran->nama_mapel ?? '-',
                    'nilai' => $latest->nilai,
                ];
            })
            ->values();
    }

    /**
     * Halaman cetak / export nilai (dua jenis dalam satu halaman):
     * per siswa (satu siswa, semua mapel) dan per mata pelajaran
     * (satu ujian: satu mapel + satu kelas — daftar nilai seluruh siswa).
     */
    public function export(Request $request)
    {
        $siswaList = $this->getSiswaList();
        $ujianList = $this->getUjianList();

        $siswaId = $request->id_siswa;
        $siswa   = null;
        $rows    = collect();

        if ($siswaId) {
            $siswa = Siswa::with('kelas')->findOrFail($siswaId);
            $rows  = $this->buildSiswaRows($siswa);
        }

        $ujianId = $request->id_ujian;
        $ujian   = null;
        $hasil   = collect();
        $stats   = ['jumlah' => 0, 'rata' => 0, 'max' => 0, 'min' => 0];

        if ($ujianId) {
            $ujian = $this->getOwnedUjian($ujianId);
            $hasil = $this->buildPerMapelRows($ujian);
            $stats = $this->buildPerMapelStats($hasil);
        }

        return view('guru.hasil.export', compact('siswaList', 'siswa', 'rows', 'ujianList', 'ujian', 'hasil', 'stats'));
    }

    /**
     * Export nilai per siswa ke Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $siswaId = $request->id_siswa;

        if (!$siswaId) {
            return redirect()->route('guru.hasil.export')
                ->with('error', 'Pilih siswa terlebih dahulu.');
        }

        $siswa = Siswa::with('kelas')->findOrFail($siswaId);
        $rows  = $this->buildSiswaRows($siswa);

        $filename = 'nilai_siswa_' . str_replace([' ', '/'], '_', $siswa->nama_siswa) . '.xlsx';

        return Excel::download(
            new \App\Exports\HasilUjianExport($siswa, $rows),
            $filename
        );
    }

    /**
     * Export nilai per siswa ke Word (.docx).
     */
    public function exportWord(Request $request)
    {
        $siswaId = $request->id_siswa;

        if (!$siswaId) {
            return redirect()->route('guru.hasil.export')
                ->with('error', 'Pilih siswa terlebih dahulu.');
        }

        $siswa = Siswa::with('kelas')->findOrFail($siswaId);
        $rows  = $this->buildSiswaRows($siswa);

        $setting = Setting::firstOrCreate(
            [],
            ['nama_sekolah' => 'SMK IT AL-ABROR']
        );

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection();

        // Kop sekolah + judul
        $section->addText(
            $setting->nama_sekolah ?? 'SMK IT AL-ABROR',
            ['bold' => true, 'size' => 16],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'Daftar Nilai Siswa',
            ['size' => 13],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak();

        // Info siswa
        $info = [
            'Nama Siswa' => $siswa->nama_siswa,
            'Kelas'      => $siswa->kelas->nama_kelas ?? '-',
            'NIS'        => $siswa->nis ?? '-',
        ];
        foreach ($info as $label => $value) {
            $run = $section->addTextRun();
            $run->addText($label . ' : ', ['bold' => true]);
            $run->addText($value);
        }
        $section->addTextBreak();

        // Tabel nilai
        $table = $section->addTable([
            'borderSize'  => 6,
            'borderColor' => '999999',
            'cellMargin'  => 80,
        ]);

        $table->addRow();
        $table->addCell(600, ['bgColor' => 'EEEEEE'])
            ->addText('No', ['bold' => true], ['alignment' => Jc::CENTER]);
        $table->addCell(7000, ['bgColor' => 'EEEEEE'])
            ->addText('Mata Pelajaran', ['bold' => true]);
        $table->addCell(1500, ['bgColor' => 'EEEEEE'])
            ->addText('Nilai', ['bold' => true], ['alignment' => Jc::CENTER]);

        foreach ($rows as $i => $row) {
            $table->addRow();
            $table->addCell(600)
                ->addText((string) ($i + 1), [], ['alignment' => Jc::CENTER]);
            $table->addCell(7000)->addText($row['mapel']);
            $table->addCell(1500)
                ->addText(number_format($row['nilai'], 1), ['bold' => true], ['alignment' => Jc::CENTER]);
        }

        $section->addTextBreak();
        $section->addText('Dicetak pada ' . now()->translatedFormat('d F Y'));

        // Tanda tangan guru
        $section->addTextBreak(2);
        $section->addText('Guru Mata Pelajaran', [], ['alignment' => Jc::END]);
        $section->addTextBreak(3);
        $section->addText('( ______________________ )', [], ['alignment' => Jc::END]);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');

        $filename = 'nilai_siswa_' . str_replace([' ', '/'], '_', $siswa->nama_siswa) . '.docx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Daftar ujian milik guru (untuk pemilih export per mata pelajaran).
     */
    private function getUjianList(): Collection
    {
        return Ujian::with(['mataPelajaran', 'kelas'])
            ->where('id_guru', $this->getGuruId())
            ->latest()
            ->get();
    }

    /**
     * Hasil satu ujian, diurutkan abjad nama siswa (daftar kelas).
     */
    private function buildPerMapelRows(Ujian $ujian): Collection
    {
        return HasilUjian::with('siswa.kelas')
            ->where('id_ujian', $ujian->id_ujian)
            ->get()
            ->sortBy(fn ($h) => $h->siswa->nama_siswa ?? '')
            ->values();
    }

    /**
     * Statistik ringkas satu ujian (untuk preview & dokumen).
     */
    private function buildPerMapelStats(Collection $hasil): array
    {
        $jumlah = $hasil->count();

        return [
            'jumlah' => $jumlah,
            'rata'   => $jumlah ? round((float) $hasil->avg('nilai'), 1) : 0,
            'max'    => $jumlah ? (float) $hasil->max('nilai') : 0,
            'min'    => $jumlah ? (float) $hasil->min('nilai') : 0,
        ];
    }

    /**
     * Export nilai per mata pelajaran ke Excel (.xlsx).
     */
    public function exportPerMapelExcel(Request $request)
    {
        $ujianId = $request->id_ujian;

        if (!$ujianId) {
            return redirect()->route('guru.hasil.export')
                ->with('error', 'Pilih ujian terlebih dahulu.');
        }

        $ujian = $this->getOwnedUjian($ujianId);
        $hasil = $this->buildPerMapelRows($ujian);

        $filename = 'nilai_' . str_replace([' ', '/'], '_', ($ujian->mataPelajaran->nama_mapel ?? 'mapel'))
            . '_' . str_replace([' ', '/'], '_', ($ujian->kelas->nama_kelas ?? 'kelas')) . '.xlsx';

        return Excel::download(
            new \App\Exports\HasilPerMapelExport($ujian, $hasil),
            $filename
        );
    }

    /**
     * Export nilai per mata pelajaran ke Word (.docx).
     */
    public function exportPerMapelWord(Request $request)
    {
        $ujianId = $request->id_ujian;

        if (!$ujianId) {
            return redirect()->route('guru.hasil.export')
                ->with('error', 'Pilih ujian terlebih dahulu.');
        }

        $ujian = $this->getOwnedUjian($ujianId);
        $hasil = $this->buildPerMapelRows($ujian);
        $stats = $this->buildPerMapelStats($hasil);

        $setting = Setting::firstOrCreate(
            [],
            ['nama_sekolah' => 'SMK IT AL-ABROR']
        );

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection();

        // Kop sekolah + judul
        $section->addText(
            $setting->nama_sekolah ?? 'SMK IT AL-ABROR',
            ['bold' => true, 'size' => 16],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'Daftar Nilai Mata Pelajaran',
            ['size' => 13],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak();

        // Info ujian
        $info = [
            'Mata Pelajaran' => $ujian->mataPelajaran->nama_mapel ?? '-',
            'Kelas'          => $ujian->kelas->nama_kelas ?? '-',
            'Ujian'          => $ujian->judul_ujian ?? '-',
            'Tanggal'        => $ujian->tanggal
                ? \Illuminate\Support\Carbon::parse($ujian->tanggal)->translatedFormat('d F Y')
                : '-',
        ];
        foreach ($info as $label => $value) {
            $run = $section->addTextRun();
            $run->addText($label . ' : ', ['bold' => true]);
            $run->addText($value);
        }
        $section->addTextBreak();

        // Tabel nilai
        $table = $section->addTable([
            'borderSize'  => 6,
            'borderColor' => '999999',
            'cellMargin'  => 80,
        ]);

        $table->addRow();
        $table->addCell(600, ['bgColor' => 'EEEEEE'])
            ->addText('No', ['bold' => true], ['alignment' => Jc::CENTER]);
        $table->addCell(7000, ['bgColor' => 'EEEEEE'])
            ->addText('Nama Siswa', ['bold' => true]);
        $table->addCell(1500, ['bgColor' => 'EEEEEE'])
            ->addText('Nilai', ['bold' => true], ['alignment' => Jc::CENTER]);

        foreach ($hasil as $i => $h) {
            $table->addRow();
            $table->addCell(600)
                ->addText((string) ($i + 1), [], ['alignment' => Jc::CENTER]);
            $table->addCell(7000)->addText($h->siswa->nama_siswa ?? '-');
            $table->addCell(1500)
                ->addText(number_format($h->nilai, 1), ['bold' => true], ['alignment' => Jc::CENTER]);
        }

        $section->addTextBreak();

        // Statistik ringkas
        $statLines = [
            'Jumlah Siswa'   => (string) $stats['jumlah'] . ' siswa',
            'Rata-rata'      => number_format($stats['rata'], 1),
            'Nilai Tertinggi' => number_format($stats['max'], 1),
            'Nilai Terendah'  => number_format($stats['min'], 1),
        ];
        foreach ($statLines as $label => $value) {
            $run = $section->addTextRun();
            $run->addText($label . ' : ', ['bold' => true]);
            $run->addText($value);
        }

        $section->addTextBreak();
        $section->addText('Dicetak pada ' . now()->translatedFormat('d F Y'));

        // Tanda tangan guru
        $section->addTextBreak(2);
        $section->addText('Guru Mata Pelajaran', [], ['alignment' => Jc::END]);
        $section->addTextBreak(3);
        $section->addText('( ______________________ )', [], ['alignment' => Jc::END]);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');

        $filename = 'nilai_' . str_replace([' ', '/'], '_', ($ujian->mataPelajaran->nama_mapel ?? 'mapel'))
            . '_' . str_replace([' ', '/'], '_', ($ujian->kelas->nama_kelas ?? 'kelas')) . '.docx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Hitung ulang nilai akhir siswa untuk ujian tertentu.
     * PG/Benar-Salah: bobot penuh bila benar; Essay: sesuai skor yang dinilai guru.
     */
    private function recalculateNilai(Ujian $ujian, Siswa $siswa): float
    {
        $soalList = $ujian->bankSoal;
        $totalBobot = $soalList->sum('bobot') ?: 1;
        $bobotDidapat = 0;

        $jawabanSiswa = JawabanSiswa::where('id_ujian', $ujian->id_ujian)
            ->where('id_siswa', $siswa->id_siswa)
            ->get()
            ->keyBy('id_soal');

        foreach ($soalList as $soal) {
            $jwb = $jawabanSiswa->get($soal->id_soal);
            if (!$jwb) {
                continue;
            }

            if ($jwb->status_benar) {
                $bobotDidapat += $soal->bobot;
            } elseif ($soal->jenis_soal === 'essay' && $jwb->skor !== null) {
                $bobotDidapat += min((float) $jwb->skor, (float) $soal->bobot);
            }
        }

        return round(($bobotDidapat / $totalBobot) * 100, 2);
    }

    /**
     * Tinjau jawaban siswa & nilai essay secara manual.
     */
    public function nilaiSiswa(Request $request, Ujian $ujian, Siswa $siswa)
    {
        $this->authorizeUjian($ujian);

        $ujian->load(['mataPelajaran', 'kelas', 'bankSoal']);

        $jawaban = JawabanSiswa::where('id_ujian', $ujian->id_ujian)
            ->where('id_siswa', $siswa->id_siswa)
            ->get()
            ->keyBy('id_soal');

        $hasil = HasilUjian::where('id_ujian', $ujian->id_ujian)
            ->where('id_siswa', $siswa->id_siswa)
            ->first();

        if (!$hasil) {
            return redirect()->route('guru.hasil.peserta')
                ->with('error', 'Siswa belum mengerjakan ujian ini.');
        }

        // Saran nilai essay otomatis via cosine similarity. Hanya untuk soal
        // yang belum dinilai guru dan memiliki kunci jawaban; hanya mengisi
        // form, tidak menyimpan ke database sebelum guru menekan Simpan.
        $saran = [];
        $grader = app(CosineSimilarityGrader::class);
        foreach ($ujian->bankSoal->where('jenis_soal', 'essay') as $soal) {
            $jwb = $jawaban[$soal->id_soal] ?? null;
            if (!$jwb || $jwb->skor !== null) {
                continue; // sudah dinilai guru: jangan timpa
            }
            if (trim(strip_tags((string) $soal->jawaban_benar)) === '') {
                continue; // kunci kosong: tidak ada pembanding
            }
            if (trim(strip_tags((string) ($jwb->jawaban ?? ''))) === '') {
                continue; // siswa tidak menjawab
            }
            $saran[$soal->id_soal] = $grader->saranNilai(
                $soal->jawaban_benar,
                $jwb->jawaban ?? '',
                (float) $soal->bobot
            );
        }

        return view('guru.hasil.nilai-siswa', compact('ujian', 'siswa', 'jawaban', 'hasil', 'saran'));
    }

    /**
     * Simpan skor essay & perbarui nilai akhir siswa.
     */
    public function simpanNilai(Request $request, Ujian $ujian, Siswa $siswa)
    {
        $this->authorizeUjian($ujian);

        $request->validate([
            'skor' => 'required|array',
            'skor.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $soalEssay = $ujian->bankSoal->where('jenis_soal', 'essay');

        foreach ($soalEssay as $soal) {
            $skor = $request->input("skor.{$soal->id_soal}");

            if ($skor === null || $skor === '') {
                continue;
            }

            JawabanSiswa::where('id_ujian', $ujian->id_ujian)
                ->where('id_siswa', $siswa->id_siswa)
                ->where('id_soal', $soal->id_soal)
                ->update([
                    'skor' => min((float) $skor, (float) $soal->bobot),
                ]);
        }

        $nilai = $this->recalculateNilai($ujian, $siswa);

        HasilUjian::updateOrCreate(
            [
                'id_ujian' => $ujian->id_ujian,
                'id_siswa' => $siswa->id_siswa,
            ],
            [
                'nilai'           => $nilai,
                'tanggal_selesai' => now(),
                'status'          => 'selesai',
            ]
        );

        return redirect()
            ->route('guru.hasil.nilai', [$ujian->id_ujian, $siswa->id_siswa])
            ->with('success', "Nilai akhir siswa berhasil diperbarui: {$nilai}.");
    }

    /**
     * Pastikan ujian milik guru yang sedang login.
     */
    private function authorizeUjian(Ujian $ujian): void
    {
        abort_if($ujian->id_guru !== $this->getGuruId(), 403, 'Anda tidak berhak mengakses ujian ini.');
    }
}
