<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\BankSoal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Ujian;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UjianController extends Controller
{
    private function getGuruId(): int|null
    {
        return Auth::user()->guru?->id_guru;
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
     * Pastikan ujian milik guru yang sedang login.
     */
    private function authorizeUjian(Ujian $ujian): void
    {
        abort_if($ujian->id_guru !== $this->getGuruId(), 403, 'Anda tidak berhak mengakses ujian ini.');
    }

    /**
     * Cek apakah jadwal ujian bentrok dengan ujian lain
     * di kelas dan tanggal yang sama.
     *
     * Mengembalikan ujian yang bentrok, atau null jika tidak ada.
     */
    private function jadwalBentrok($id_kelas, $tanggal, $jam_mulai, $durasi, $kecualiId = null): ?Ujian
    {
        $mulaiBaru   = Carbon::parse($tanggal . ' ' . $jam_mulai);
        $selesaiBaru = $mulaiBaru->copy()->addMinutes((int) $durasi);

        $query = Ujian::where('id_kelas', $id_kelas)
            ->whereDate('tanggal', $tanggal);

        if ($kecualiId) {
            $query->where('id_ujian', '!=', $kecualiId);
        }

        foreach ($query->get() as $ujian) {
            $mulaiAda   = Carbon::parse($ujian->tanggal . ' ' . $ujian->jam_mulai);
            $selesaiAda = $mulaiAda->copy()->addMinutes((int) $ujian->durasi);

            // Bentrok jika rentang waktunya saling tumpang tindih
            if ($mulaiBaru->lt($selesaiAda) && $selesaiBaru->gt($mulaiAda)) {
                return $ujian;
            }
        }

        return null;
    }

    /**
     * Susun pesan error untuk jadwal yang bentrok.
     */
    private function pesanBentrok(Ujian $bentrok): string
    {
        $mulai   = Carbon::parse($bentrok->tanggal . ' ' . $bentrok->jam_mulai);
        $selesai = $mulai->copy()->addMinutes((int) $bentrok->durasi);

        return 'Jadwal bentrok: kelas ini sudah memiliki ujian "' . $bentrok->judul_ujian . '" pada '
            . $mulai->format('d-m-Y') . ' pukul '
            . $mulai->format('H:i') . '-' . $selesai->format('H:i') . '.';
    }

    /**
     * Daftar semua ujian milik guru.
     */
    public function index()
    {
        $ujian = Ujian::with(['mataPelajaran', 'kelas', 'tahunAjaran'])
            ->where('id_guru', $this->getGuruId())
            ->latest()
            ->paginate(10);

        return view('guru.ujian.index', compact('ujian'));
    }

    /**
     * Form buat ujian baru.
     */
    public function create()
    {
        $mataPelajaran = $this->mapelUntukGuru();
        $kelas         = Kelas::orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")->orderBy('nama_kelas')->get();
        $tahunAjaran   = TahunAjaran::all();
        $bankSoal      = BankSoal::with('mataPelajaran')
            ->where('id_guru', $this->getGuruId())
            ->whereIn('id_mapel', $this->allowedMapelIds())
            ->get();

        return view('guru.ujian.create', compact('mataPelajaran', 'kelas', 'tahunAjaran', 'bankSoal'));
    }

    /**
     * Simpan ujian baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul_ujian' => 'required|string|max:150',
            'id_mapel'    => 'required|exists:mata_pelajaran,id_mapel',
            'id_kelas'    => 'required|exists:kelas,id_kelas',
            'id_tahun'    => 'required|exists:tahun_ajaran,id_tahun',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required',
            'durasi'      => 'required|integer|min:1',
        ]);

        $this->authorizeMapel($request->input('id_mapel'));

        // Tolak jika jadwal bentrok dengan ujian lain di kelas & tanggal yang sama
        if ($bentrok = $this->jadwalBentrok($request->id_kelas, $request->tanggal, $request->jam_mulai, $request->durasi)) {
            return back()->withInput()->withErrors(['jam_mulai' => $this->pesanBentrok($bentrok)]);
        }

        $ujian = Ujian::create([
            'id_guru'     => $this->getGuruId(),
            'id_mapel'    => $request->id_mapel,
            'id_kelas'    => $request->id_kelas,
            'id_tahun'    => $request->id_tahun,
            'judul_ujian' => $request->judul_ujian,
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $request->jam_mulai,
            'durasi'      => $request->durasi,
            'status'      => 'nonaktif',
        ]);

        // Otomatis sinkronkan soal dari Mata Pelajaran ini KHUSUS untuk
        // kelas ujian ini saja (PG, Benar/Salah, & Essay) — tanpa filter
        // id_kelas, ujian tiap kelas akan menarik SEMUA soal mapel itu
        // (mis. ASAS Inggris kelas 7 ikut menarik soal kelas 8 & 9).
        $allSoalMapel = BankSoal::where('id_mapel', $request->id_mapel)
            ->where('id_kelas', $request->id_kelas)
            ->where('id_guru', $this->getGuruId())
            ->pluck('id_soal')
            ->toArray();
        $ujian->bankSoal()->sync($allSoalMapel);

        return redirect()->route('guru.ujian.index')
            ->with('success', 'Ujian berhasil dibuat dengan seluruh soal mata pelajaran tersebut.');
    }

    /**
     * Detail ujian.
     */
    public function show(Ujian $ujian)
    {
        $this->authorizeUjian($ujian);

        $ujian->load(['mataPelajaran', 'kelas', 'tahunAjaran', 'bankSoal', 'hasilUjian.siswa']);
        return view('guru.ujian.show', compact('ujian'));
    }

    /**
     * Form edit ujian.
     */
    public function edit(Ujian $ujian)
    {
        $this->authorizeUjian($ujian);

        $mataPelajaran = $this->mapelUntukGuru();
        $kelas         = Kelas::orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")->orderBy('nama_kelas')->get();
        $tahunAjaran   = TahunAjaran::all();

        return view('guru.ujian.edit', compact('ujian', 'mataPelajaran', 'kelas', 'tahunAjaran'));
    }

    /**
     * Update ujian.
     */
    public function update(Request $request, Ujian $ujian)
    {
        $this->authorizeUjian($ujian);

        $request->validate([
            'judul_ujian' => 'required|string|max:150',
            'id_mapel'    => 'required|exists:mata_pelajaran,id_mapel',
            'id_kelas'    => 'required|exists:kelas,id_kelas',
            'id_tahun'    => 'required|exists:tahun_ajaran,id_tahun',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required',
            'durasi'      => 'required|integer|min:1',
        ]);

        $this->authorizeMapel($request->input('id_mapel'));

        // Tolak jika jadwal bentrok dengan ujian lain di kelas & tanggal yang sama
        if ($bentrok = $this->jadwalBentrok($request->id_kelas, $request->tanggal, $request->jam_mulai, $request->durasi, $ujian->id_ujian)) {
            return back()->withInput()->withErrors(['jam_mulai' => $this->pesanBentrok($bentrok)]);
        }

        $ujian->update([
            'id_mapel'    => $request->id_mapel,
            'id_kelas'    => $request->id_kelas,
            'id_tahun'    => $request->id_tahun,
            'judul_ujian' => $request->judul_ujian,
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $request->jam_mulai,
            'durasi'      => $request->durasi,
        ]);

        // Otomatis sinkronkan soal dari Mata Pelajaran ini KHUSUS untuk
        // kelas ujian ini saja — tanpa filter id_kelas, ujian tiap kelas
        // akan menarik SEMUA soal mapel itu. Sinkron ulang ini juga yang
        // menarik soal hasil import terbaru ke dalam ujian.
        $allSoalMapel = BankSoal::where('id_mapel', $request->id_mapel)
            ->where('id_kelas', $request->id_kelas)
            ->where('id_guru', $this->getGuruId())
            ->pluck('id_soal')
            ->toArray();
        $ujian->bankSoal()->sync($allSoalMapel);

        return redirect()->route('guru.ujian.index')
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    /**
     * Hapus ujian.
     */
    public function destroy(Ujian $ujian)
    {
        $this->authorizeUjian($ujian);

        $ujian->bankSoal()->detach();
        $ujian->delete();

        return redirect()->route('guru.ujian.index')
            ->with('success', 'Ujian berhasil dihapus.');
    }

    /**
     * Halaman Buat Jadwal Ujian.
     */
    public function jadwal()
    {
        $ujian = Ujian::with(['mataPelajaran', 'kelas', 'tahunAjaran'])
            ->where('id_guru', $this->getGuruId())
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return view('guru.ujian.jadwal', compact('ujian'));
    }

    /**
     * Halaman Atur Peserta Ujian.
     */
    public function peserta(Request $request)
    {
        $ujianList = Ujian::with(['mataPelajaran', 'kelas'])
            ->where('id_guru', $this->getGuruId())
            ->latest()
            ->get();

        $ujianId  = $request->id_ujian;
        $ujian    = null;
        $peserta  = collect();

        if ($ujianId) {
            $ujian   = Ujian::with(['kelas.siswa.user', 'hasilUjian.siswa'])
                ->where('id_guru', $this->getGuruId())
                ->findOrFail($ujianId);
            $peserta = $ujian->kelas->siswa ?? collect();
        }

        return view('guru.ujian.peserta', compact('ujianList', 'ujian', 'peserta'));
    }

    /**
     * Halaman Publish Ujian.
     */
    public function publish(Request $request)
    {
        $ujian = Ujian::with(['mataPelajaran', 'kelas', 'tahunAjaran'])
            ->where('id_guru', $this->getGuruId())
            ->latest()
            ->paginate(10);

        return view('guru.ujian.publish', compact('ujian'));
    }

    /**
     * Toggle status publish ujian.
     */
    public function togglePublish(Request $request, Ujian $ujian)
    {
        $this->authorizeUjian($ujian);

        $newStatus = $ujian->status === 'aktif' ? 'nonaktif' : 'aktif';
        $ujian->update(['status' => $newStatus]);

        $msg = $newStatus === 'aktif' ? 'Ujian berhasil dipublish.' : 'Ujian berhasil dinonaktifkan.';
        return redirect()->back()->with('success', $msg);
    }
}
