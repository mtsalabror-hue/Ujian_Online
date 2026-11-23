@extends('layouts.guru')

@section('title', 'Bank Soal')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-journal-text me-2 text-primary"></i>Bank Soal</h3>
            <p class="text-muted mb-0 small">Kelola daftar bank soal ujian per Mata Pelajaran.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importSoalModal">
                <i class="bi bi-upload me-1"></i> Import Soal
            </button>
            <a href="{{ route('guru.bank-soal.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah Soal
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>{{ session('success') }}
        <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>{{ session('error') }}
        <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Search Bar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form action="{{ route('guru.bank-soal.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="keyword" class="form-control border-start-0 ps-0"
                            placeholder="Cari Nama atau Kode Mata Pelajaran..."
                            value="{{ request('keyword') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-primary w-100"><i class="bi bi-search me-1"></i> Cari</button>
                    @if(request('keyword'))
                    <a href="{{ route('guru.bank-soal.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom-mockup align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Mata Pelajaran</th>
                            <th width="160" class="text-center">Kelas</th>
                            <th width="200" class="text-center">Jumlah Soal</th>
                            <th width="170" class="text-center">Dibuat Pada</th>
                            <th width="90" class="text-center">Bobot</th>
                            <th width="170" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $nomorBaris = $bankSoalMapel->firstItem(); @endphp
                        @forelse($bankSoalMapel as $mapel)
                        @php
                            $kelompokSoal = $mapel->bankSoal->groupBy(function ($soal) {
                                return $soal->id_kelas ?: 'all';
                            });
                        @endphp

                        @foreach($kelompokSoal as $kunci => $soalKelas)
                            @php
                                $soalPertama = $soalKelas->first();
                                $namaKelas = $soalPertama->id_kelas
                                    ? ($soalPertama->kelas?->nama_kelas ?? '-')
                                    : 'Semua Kelas';
                                $jumlah_pg = $soalKelas->where('jenis_soal', 'pilihan_ganda')->count();
                                $jumlah_bs = $soalKelas->where('jenis_soal', 'benar_salah')->count();
                                $jumlah_essay = $soalKelas->where('jenis_soal', 'essay')->count();
                                $total = $soalKelas->count();
                                $bobot = $soalKelas->sum('bobot');
                                $tanggal = $soalKelas->max('created_at');
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ $nomorBaris++ }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $mapel->nama_mapel }}</div>
                                    @if($mapel->kode_mapel ?? false)
                                    <small class="text-muted">{{ $mapel->kode_mapel }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill" style="background:#0dcaf0;color:#000;font-size:.78rem;">{{ $namaKelas }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold fs-5 text-dark">{{ $total }}</span>
                                    <div class="d-flex justify-content-center gap-1 mt-1 flex-wrap">
                                        @if($jumlah_pg > 0)
                                        <span class="badge bg-primary" title="Pilihan Ganda" style="font-size:.72rem;">PG {{ $jumlah_pg }}</span>
                                        @endif
                                        @if($jumlah_bs > 0)
                                        <span class="badge bg-warning text-dark" title="Benar/Salah" style="font-size:.72rem;">B/S {{ $jumlah_bs }}</span>
                                        @endif
                                        @if($jumlah_essay > 0)
                                        <span class="badge bg-info text-dark" title="Essay" style="font-size:.72rem;">Ess {{ $jumlah_essay }}</span>
                                        @endif
                                        @if($total == 0)
                                        <span class="text-muted small fst-italic">Belum ada soal</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($tanggal)
                                    <div class="fw-semibold text-dark small">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</div>
                                    <div class="text-muted" style="font-size:.78rem;">{{ \Carbon\Carbon::parse($tanggal)->format('H:i') }} WIB</div>
                                    @else
                                    <span class="text-muted small fst-italic">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold">{{ number_format($bobot, 0) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <a href="{{ route('guru.bank-soal.mapel', [
                                                'id_mapel' => $mapel->id_mapel,
                                                'id_kelas' => $soalPertama->id_kelas ?? 'all',
                                            ]) }}"
                                            class="btn btn-sm btn-outline-primary" title="Lihat Daftar Soal">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('guru.bank-soal.editMapel', ['id_mapel' => $mapel->id_mapel, 'id_kelas' => $soalPertama->id_kelas ?? 'all']) }}"
                                            class="btn btn-sm btn-outline-warning ms-1" title="Edit Soal Kelas Ini">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('guru.bank-soal.destroyMapel', $mapel->id_mapel) }}"
                                            method="POST" class="d-inline ms-1">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="id_kelas" value="{{ $soalPertama->id_kelas ?? 'all' }}">
                                            <button type="submit"
                                                onclick="return confirm('Yakin ingin menghapus SELURUH soal {{ $mapel->nama_mapel }} kelas {{ $namaKelas }}? Soal kelas lain tidak ikut terhapus.')"
                                                class="btn btn-sm btn-outline-danger" title="Hapus Soal Kelas {{ $namaKelas }}">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 text-muted opacity-50"></i>
                                <p class="text-muted mb-1 fw-semibold">Belum ada bank soal.</p>
                                <small class="text-muted">Klik <strong>Tambah Soal</strong> atau <strong>Import Soal</strong> untuk mulai.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($bankSoalMapel->hasPages())
    <div class="card-footer bg-white">
        {{ $bankSoalMapel->links() }}
    </div>
    @endif

</div>


<!-- Modal Import Soal -->
<div class="modal fade" id="importSoalModal" tabindex="-1" aria-labelledby="importSoalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="importSoalForm" action="{{ route('guru.bank-soal.import.doc') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importSoalModalLabel"><i class="bi bi-upload me-2"></i>Import Bank Soal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File (Format Word .docx)</label>
                        <input type="file" name="file" id="importFile" class="form-control" accept=".docx" required>
                        <div class="form-text mt-2" id="importHint">
                            Isi identitas (KELAS & MATA PELAJARAN) lalu lengkapi setiap bagian sesuai jenis soal (PG, Benar/Salah, Essay).
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <a href="{{ route('guru.bank-soal.template.doc') }}" class="btn btn-outline-success btn-sm" id="downloadWordTemplate">
                            <i class="bi bi-download me-1"></i> Template Word
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i> Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection