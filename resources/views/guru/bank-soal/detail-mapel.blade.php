@extends('layouts.guru')

@section('title', 'Soal Mapel - ' . $mapel->nama_mapel)

@section('content')

<div class="container-fluid">

    {{-- Header & Navigation --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-journal-text me-2"></i>Bank Soal: {{ $mapel->nama_mapel }}
            </h3>
            <p class="text-muted mb-0">
                @if($idKelas !== null)
                Daftar soal untuk mata pelajaran {{ $mapel->nama_mapel }} (Kode: {{ $mapel->kode_mapel ?? '-' }}) - Kelas
                <strong>
                    @if($idKelas === 'all')
                    Semua Kelas
                    @else
                    {{ $bankSoal->first()->kelas->nama_kelas ?? 'Kelas Terpilih' }}
                    @endif
                </strong>.
                @else
                Daftar seluruh soal ujian untuk mata pelajaran {{ $mapel->nama_mapel }} (Kode: {{ $mapel->kode_mapel ?? '-' }}).
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('guru.bank-soal.export-mapel-word', $mapel->id_mapel) }}" class="btn btn-sm btn-primary mb-2">
                <i class="bi bi-download me-1"></i> Download Bank Soal (Word)
            </a>
            <a href="{{ route('guru.bank-soal.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Bank Soal
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Search & Filter --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form action="{{ route('guru.bank-soal.mapel', $mapel->id_mapel) }}" method="GET" class="row g-2 align-items-center">
                @if($idKelas !== null)
                <input type="hidden" name="id_kelas" value="{{ $idKelas }}">
                @endif
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari isi pertanyaan..."
                        value="{{ request('keyword') }}">
                </div>
                <div class="col-md-4">
                    <select name="jenis_soal" class="form-select">
                        <option value="">-- Semua Jenis Soal --</option>
                        <option value="pilihan_ganda" {{ request('jenis_soal') == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda</option>
                        <option value="benar_salah" {{ request('jenis_soal') == 'benar_salah' ? 'selected' : '' }}>Benar / Salah</option>
                        <option value="essay" {{ request('jenis_soal') == 'essay' ? 'selected' : '' }}>Essay</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-primary w-100"><i class="bi bi-search me-1"></i> Filter</button>
                    @if(request('keyword') || request('jenis_soal'))
                    <a href="{{ route('guru.bank-soal.mapel', ['id_mapel' => $mapel->id_mapel, 'id_kelas' => $idKelas ?? null]) }}"
                        class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Card Table Soal --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Jenis Soal</th>
                            <th class="text-center" width="110">Kelas</th>
                            <th>Pertanyaan</th>
                            <th class="text-center" width="110">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bankSoal as $item)
                        <tr>
                            <td class="text-center">{{ $bankSoal->firstItem() + $loop->index }}</td>
                            <td>
                                @if($item->jenis_soal === 'pilihan_ganda')
                                <span class="badge bg-primary">Pilihan Ganda</span>
                                @elseif($item->jenis_soal === 'benar_salah')
                                <span class="badge bg-warning text-dark">Benar / Salah</span>
                                @else
                                <span class="badge bg-info text-dark">Essay</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $item->kelas->nama_kelas ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="fw-medium">
                                    {{ Str::limit(strip_tags($item->pertanyaan), 90) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center">
                                    <a href="{{ route('guru.bank-soal.show', $item->id_soal) }}{{ $idKelas !== null ? '?id_kelas=' . $idKelas : '' }}"
                                        class="btn btn-sm btn-outline-primary" title="Lihat Detail Soal">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('guru.bank-soal.edit', $item->id_soal) }}{{ $idKelas !== null ? '?id_kelas=' . $idKelas : '' }}"
                                        class="btn btn-sm btn-outline-warning ms-1" title="Edit Soal">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form id="form-hapus-{{ $item->id_soal }}"
                                        action="{{ route('guru.bank-soal.destroy', $item->id_soal) }}"
                                        method="POST" class="d-inline ms-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus Soal"
                                            data-bs-toggle="modal" data-bs-target="#modalHapusSoal"
                                            data-form-id="form-hapus-{{ $item->id_soal }}">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-journal-x fs-2 d-block mb-2 text-muted"></i>
                                <span class="text-muted">Belum ada soal untuk mata pelajaran ini. Klik <strong>Tambah Soal</strong> untuk mulai menambahkan.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bankSoal->hasPages())
        <div class="card-footer bg-white">
            {{ $bankSoal->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Modal Konfirmasi Hapus Soal --}}
<div class="modal fade" id="modalHapusSoal" tabindex="-1" aria-labelledby="modalHapusSoalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHapusSoalLabel">Hapus Soal?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Soal ini akan dihapus. Soal yang sudah dipakai di ujian dan jawaban siswa terkait soal ini juga akan ikut terhapus.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnKonfirmasiHapus">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalHapus = document.getElementById('modalHapusSoal');
    var formIdTarget = null;
    modalHapus.addEventListener('show.bs.modal', function (event) {
        formIdTarget = event.relatedTarget.getAttribute('data-form-id');
    });
    document.getElementById('btnKonfirmasiHapus').addEventListener('click', function () {
        if (formIdTarget) {
            document.getElementById(formIdTarget).submit();
        }
    });
});
</script>

@endsection