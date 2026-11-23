<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\BankSoal;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\Siswa;
use App\Models\Ujian;
use App\Services\CosineSimilarityGrader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjianController extends Controller
{
    private function getSiswa()
    {
        return Auth::user()->siswa;
    }

    /**
     * Periksa apakah siswa berhak mengakses ujian tertentu (kelas & status).
     * Mengembalikan true jika boleh, false jika tidak.
     */
    private function canAccessUjian(?Siswa $siswa, Ujian $ujian): bool
    {
        return $siswa
            && $siswa->id_kelas == $ujian->id_kelas
            && $ujian->status === 'aktif'
            && $ujian->sudahMulai();
    }

    /**
     * Periksa apakah waktu ujian sudah habis.
     * Batas akhir mengikuti jadwal ujian (jam_mulai + durasi), bukan waktu mulai siswa,
     * sehingga siswa yang terlambat mendapat sisa waktu yang tersisa.
     */
    private function isTimeUp(Ujian $ujian, HasilUjian $hasil): bool
    {
        return now()->greaterThanOrEqualTo($ujian->waktuSelesai());
    }

    /**
     * Menampilkan daftar ujian yang aktif untuk kelas siswa.
     */
    public function index()
    {
        $siswa = Auth::user()->siswa;

        if (!$siswa) {
            Auth::logout();
            return redirect()->route('login.siswa')
                ->withErrors(['username' => 'Data siswa tidak ditemukan. Hubungi administrator.']);
        }

        $ujian = Ujian::with(['mataPelajaran', 'kelas', 'bankSoal'])
            ->where('id_kelas', $siswa->id_kelas)
            ->where('status', 'aktif')
            ->latest()
            ->get();

        // Finalisasi otomatis ujian yang masih 'proses' tapi waktunya sudah berakhir,
        // supaya siswa tidak melihat tombol "Lanjutkan" pada ujian yang sudah selesai waktunya.
        foreach ($ujian as $item) {
            $hasil = HasilUjian::where('id_ujian', $item->id_ujian)
                ->where('id_siswa', $siswa->id_siswa)
                ->where('status', 'proses')
                ->first();

            if ($hasil && now()->greaterThanOrEqualTo($item->waktuSelesai())) {
                $this->finalisasiUjian($item, $siswa);
            }
        }

        $hasilUjian = HasilUjian::where('id_siswa', $siswa->id_siswa)
            ->pluck('status', 'id_ujian')
            ->toArray();

        $nilaiUjian = HasilUjian::where('id_siswa', $siswa->id_siswa)
            ->pluck('nilai', 'id_ujian')
            ->toArray();

        return view('siswa.ujian.index', compact('ujian', 'siswa', 'hasilUjian', 'nilaiUjian'));
    }

    /**
     * Halaman detail / konfirmasi sebelum mengikuti ujian.
     */
    public function show(Ujian $ujian)
    {
        $siswa = $this->getSiswa();

        if (!$siswa || $ujian->id_kelas != $siswa->id_kelas) {
            return redirect()->route('siswa.ujian')
                ->with('error', 'Ujian tidak tersedia untuk Anda.');
        }

        $hasil = HasilUjian::where('id_ujian', $ujian->id_ujian)
            ->where('id_siswa', $siswa->id_siswa)
            ->first();

        // Siswa yang sudah menyelesaikan ujian boleh melihat detail hasil kapan pun.
        if ($hasil && $hasil->status === 'selesai') {
            return view('siswa.ujian.show', compact('ujian', 'siswa', 'hasil'));
        }

        // Belum pernah menyelesaikan: hanya boleh diakses jika ujian aktif & waktunya sudah tiba.
        if ($ujian->status !== 'aktif' || !$ujian->sudahMulai()) {
            return redirect()->route('siswa.ujian')
                ->with('error', 'Ujian tidak tersedia untuk Anda.');
        }

        return view('siswa.ujian.show', compact('ujian', 'siswa', 'hasil'));
    }

    /**
     * Konfirmasi mulai ujian.
     */
    public function mulai(Ujian $ujian)
    {
        return $this->show($ujian);
    }

    /**
     * Proses start ujian.
     */
    public function start(Request $request, Ujian $ujian)
    {
        $siswa = $this->getSiswa();

        if (!$siswa || $ujian->id_kelas != $siswa->id_kelas || $ujian->status !== 'aktif' || !$ujian->sudahMulai()) {
            return redirect()->route('siswa.ujian')
                ->with('error', 'Ujian tidak dapat diakses.');
        }

        // Jangan izinkan memulai ujian yang waktunya sudah berakhir
        if (now()->greaterThanOrEqualTo($ujian->waktuSelesai())) {
            return redirect()->route('siswa.ujian')
                ->with('error', 'Waktu ujian sudah berakhir. Anda tidak dapat mengikuti ujian ini.');
        }

        $hasil = HasilUjian::firstOrCreate([
            'id_ujian' => $ujian->id_ujian,
            'id_siswa' => $siswa->id_siswa,
        ], [
            'nilai'   => 0,
            'status'  => 'proses',
        ]);

        return redirect()->route('siswa.ujian.kerjakan', $ujian->id_ujian);
    }

    /**
     * Lembar kerja siswa saat mengikuti ujian.
     */
    public function kerjakan(Ujian $ujian)
    {
        $siswa = $this->getSiswa();

        if (!$siswa || $ujian->id_kelas != $siswa->id_kelas || $ujian->status !== 'aktif' || !$ujian->sudahMulai()) {
            return redirect()->route('siswa.ujian')
                ->with('error', 'Akses ujian tidak valid.');
        }

        $hasil = HasilUjian::where('id_ujian', $ujian->id_ujian)
            ->where('id_siswa', $siswa->id_siswa)
            ->first();

        if ($hasil && $hasil->status === 'selesai') {
            return redirect()->route('siswa.ujian.show', $ujian->id_ujian)
                ->with('info', 'Anda telah menyelesaikan ujian ini.');
        }

        // Hitung sisa waktu dalam detik berdasarkan jadwal ujian (jam_mulai + durasi).
        // Siswa yang telat akan mendapat sisa waktu sampai batas akhir ujian.
        $waktuSelesai = $ujian->waktuSelesai();
        $sisaDetik    = max(0, (int) now()->diffInSeconds($waktuSelesai, false));

        // Waktu sudah habis: hanya auto-finalisasi jika siswa memang sudah memulai ujian.
        // Siswa yang baru masuk ke ujian yang sudah berakhir TIDAK ditandai selesai.
        if ($sisaDetik <= 0) {
            if ($hasil && $hasil->status === 'proses') {
                return $this->selesai(new Request(), $ujian);
            }

            return redirect()->route('siswa.ujian.show', $ujian->id_ujian)
                ->with('error', 'Waktu ujian sudah berakhir. Anda tidak dapat mengikuti ujian ini.');
        }

        // Pastikan status 'proses' tersimpan saat siswa benar-benar mengerjakan
        if (!$hasil) {
            $hasil = HasilUjian::create([
                'id_ujian' => $ujian->id_ujian,
                'id_siswa' => $siswa->id_siswa,
                'nilai'    => 0,
                'status'   => 'proses',
            ]);
        }

        $soal = $ujian->bankSoal;

        // Acak urutan soal per siswa — diundi sekali saat pertama
        // mengerjakan, lalu disimpan di hasil_ujian.urutan_soal supaya
        // urutannya tetap sama walau halaman di-refresh. Tiap siswa
        // mendapat urutan yang berbeda. Jawaban tetap tersimpan per
        // id_soal, jadi pengacakan tidak mengganggu jawaban yang
        // sudah diisi.
        $urutan = $hasil->urutan_soal;
        if (empty($urutan) || !is_array($urutan)) {
            $urutan = $soal->pluck('id_soal')->shuffle()->values()->all();
            $hasil->update(['urutan_soal' => $urutan]);
        } else {
            // Soal yang baru ditambahkan guru setelah urutan dibuat:
            // taruh di akhir. Soal yang sudah dihapus dari ujian:
            // buang dari urutan.
            $semuaId = $soal->pluck('id_soal')->all();
            $urutan = array_values(array_intersect($urutan, $semuaId));
            $baru = array_values(array_diff($semuaId, $urutan));
            if (!empty($baru)) {
                $urutan = array_merge($urutan, $baru);
                $hasil->update(['urutan_soal' => $urutan]);
            }
        }

        $soal = $soal->sortBy(function ($s) use ($urutan) {
            $pos = array_search($s->id_soal, $urutan);
            return $pos === false ? 9999 : $pos;
        })->values();

        $jawaban = JawabanSiswa::where('id_ujian', $ujian->id_ujian)
            ->where('id_siswa', $siswa->id_siswa)
            ->pluck('jawaban', 'id_soal')
            ->toArray();

        return view('siswa.ujian.kerjakan', compact('ujian', 'soal', 'jawaban', 'sisaDetik', 'siswa'));
    }

    /**
     * Simpan jawaban per soal (via Form / AJAX).
     */
    public function simpanJawaban(Request $request, Ujian $ujian)
    {
        $siswa = $this->getSiswa();

        if (!$this->canAccessUjian($siswa, $ujian)) {
            return redirect()->route('siswa.ujian')
                ->with('error', 'Akses ujian tidak valid.');
        }

        $request->validate([
            'id_soal' => 'required|exists:bank_soal,id_soal',
            'jawaban' => 'nullable|string',
        ]);

        $soal = BankSoal::findOrFail($request->id_soal);

        // Pastikan soal memang bagian dari ujian ini, bukan asal tebak id_soal
        if (!$ujian->bankSoal()->where('bank_soal.id_soal', $soal->id_soal)->exists()) {
            abort(403, 'Soal tidak termasuk dalam ujian ini.');
        }

        $hasil = HasilUjian::where('id_ujian', $ujian->id_ujian)
            ->where('id_siswa', $siswa->id_siswa)
            ->first();

        // Pelindung: tolak simpan jawaban jika ujian sudah selesai / belum dimulai
        if (!$hasil || $hasil->status === 'selesai') {
            return redirect()->route('siswa.ujian.show', $ujian->id_ujian)
                ->with('error', 'Ujian tidak aktif atau telah diselesaikan.');
        }

        // Pelindung: waktu ujian sudah habis -> finalisasi otomatis & tolak simpan
        if ($this->isTimeUp($ujian, $hasil)) {
            $this->selesai($request, $ujian);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Waktu ujian telah habis.',
                ], 422);
            }

            return redirect()->route('siswa.ujian.show', $ujian->id_ujian)
                ->with('error', 'Waktu ujian telah habis. Ujian otomatis diselesaikan.');
        }

        $jawaban = $request->jawaban;

        // Sanitasi jawaban: buang semua tag HTML kecuali format bawaan Quill.
        // Tebal/miring/garis bawah/list/header tetap tampil di halaman penilaian guru,
        // sedangkan tag seperti <script> terbuang sehingga tidak bisa jalan di browser guru.
        if (is_string($jawaban) && $jawaban !== '') {
            $jawaban = strip_tags($jawaban, '<p><br><strong><em><u><s><ol><ul><li><h1><h2><h3><blockquote>');
        }

        $isBenar = false;

        if ($jawaban !== null && $jawaban !== '') {
            if ($soal->jenis_soal === 'pilihan_ganda') {
                $isBenar = (strtoupper(trim($jawaban)) === strtoupper(trim($soal->jawaban_benar)));
            } elseif ($soal->jenis_soal === 'benar_salah') {
                $isBenar = (strtolower(trim($jawaban)) === strtolower(trim($soal->jawaban_benar)));
            }
        }

        JawabanSiswa::updateOrCreate([
            'id_ujian' => $ujian->id_ujian,
            'id_siswa' => $siswa->id_siswa,
            'id_soal'  => $soal->id_soal,
        ], [
            'jawaban'      => $jawaban,
            'status_benar' => $isBenar,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Jawaban tersimpan']);
        }

        return redirect()->back();
    }

    /**
     * Hitung & finalisasi ujian (status 'selesai') untuk siswa tertentu.
     */
    private function finalisasiUjian(Ujian $ujian, Siswa $siswa): HasilUjian
    {
        $soalList = $ujian->bankSoal;
        $totalBobot = $soalList->sum('bobot') ?: 1;
        $bobotDidapat = 0;

        $jawabanSiswa = JawabanSiswa::where('id_ujian', $ujian->id_ujian)
            ->where('id_siswa', $siswa->id_siswa)
            ->get()
            ->keyBy('id_soal');

        $grader = app(CosineSimilarityGrader::class);

        foreach ($soalList as $soal) {
            $jwb = $jawabanSiswa->get($soal->id_soal);
            if (!$jwb) {
                continue;
            }

            // Essay dinilai otomatis via cosine similarity saat siswa submit,
            // seperti PG. Hanya bila kunci jawaban terisi; guru tetap bisa
            // mengoreksi lewat halaman tinjau jawaban.
            if ($soal->jenis_soal === 'essay' && $jwb->skor === null
                && trim(strip_tags((string) $soal->jawaban_benar)) !== '') {
                $auto = $grader->saranNilai(
                    $soal->jawaban_benar,
                    $jwb->jawaban ?? '',
                    (float) $soal->bobot
                );
                $jwb->update(['skor' => $auto['skor']]);
                $jwb->skor = $auto['skor'];
            }

            if ($jwb->status_benar) {
                $bobotDidapat += $soal->bobot;
            } elseif ($soal->jenis_soal === 'essay' && $jwb->skor !== null) {
                $bobotDidapat += min((float) $jwb->skor, (float) $soal->bobot);
            }
        }

        $nilaiFinal = round(($bobotDidapat / $totalBobot) * 100, 2);

        return HasilUjian::updateOrCreate([
            'id_ujian' => $ujian->id_ujian,
            'id_siswa' => $siswa->id_siswa,
        ], [
            'nilai'           => $nilaiFinal,
            'tanggal_selesai' => now(),
            'status'          => 'selesai',
        ]);
    }

    /**
     * Selesaikan ujian dan hitung nilai.
     */
    public function selesai(Request $request, Ujian $ujian)
    {
        $siswa = $this->getSiswa();

        if (!$this->canAccessUjian($siswa, $ujian)) {
            return redirect()->route('siswa.ujian')
                ->with('error', 'Akses ujian tidak valid.');
        }

        $hasil = $this->finalisasiUjian($ujian, $siswa);

        return view('siswa.ujian.selesai', compact('ujian', 'hasil', 'siswa'));
    }

    /**
     * Mencatat pelanggaran anti-kecurangan (keluar dari halaman ujian).
     * Langsung dikunci setelah 1x pelanggaran keluar dari halaman ujian.
     */
    public function pelanggaran(Request $request, Ujian $ujian)
    {
        $siswa = $this->getSiswa();

        if (!$this->canAccessUjian($siswa, $ujian)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ujian tidak valid.',
            ], 403);
        }

        $hasil = HasilUjian::where('id_ujian', $ujian->id_ujian)
            ->where('id_siswa', $siswa->id_siswa)
            ->first();

        if (!$hasil || $hasil->status === 'selesai') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ujian sudah selesai.',
            ], 422);
        }

        // Langsung kunci setelah 1x pelanggaran keluar dari halaman
        $hasil->increment('pelanggaran');

        $kunci = now()->addMinutes(5);

        $user = Auth::user();
        if ($user) {
            $user->update(['exam_locked_until' => $kunci]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status'       => 'locked',
            'message'      => 'Anda keluar dari halaman ujian. Akun dikunci selama 5 menit.',
            'locked_until' => $kunci->toIso8601String(),
        ]);
    }
}
