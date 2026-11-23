@extends('layouts.guru')

@section('title', 'Lihat Hasil Ujian')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-bar-chart-line me-2"></i>Lihat Hasil Ujian</h3>
        <p class="text-muted mb-0">Lihat rekap hasil ujian dari setiap ujian yang telah dibuat.</p>
    </div>

    {{-- Pilih Ujian --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('guru.hasil.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Pilih Ujian</label>
                    <select name="id_ujian" class="form-select">
                        <option value="">-- Pilih Ujian --</option>
                        @foreach($ujianList as $u)
                        <option value="{{ $u->id_ujian }}" {{ request('id_ujian') == $u->id_ujian ? 'selected' : '' }}>
                            {{ $u->judul_ujian }} — {{ $u->kelas->nama_kelas ?? '-' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Tampilkan Hasil
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($ujian)

    {{-- Summary Cards --}}
    @php
        $total     = $hasil->count();
        $avgNilai  = $total ? $hasil->avg('nilai') : 0;
        $maxNilai  = $total ? $hasil->max('nilai') : 0;
        $minNilai  = $total ? $hasil->min('nilai') : 0;
        $lulus     = $hasil->where('nilai', '>=', 70)->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-2 fw-bold text-primary">{{ $total }}</div>
                <div class="text-muted small">Total Peserta</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-2 fw-bold text-success">{{ number_format($avgNilai, 1) }}</div>
                <div class="text-muted small">Rata-rata Nilai</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-2 fw-bold text-info">{{ number_format($maxNilai, 1) }}</div>
                <div class="text-muted small">Nilai Tertinggi</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-2 fw-bold {{ $lulus == $total ? 'text-success' : 'text-warning' }}">
                    {{ $lulus }}/{{ $total }}
                </div>
                <div class="text-muted small">Lulus (≥70)</div>
            </div>
        </div>
    </div>

    {{-- Tabel Hasil --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-bold"><i class="bi bi-table me-1"></i>Detail Hasil</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Nama Siswa</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Keterangan</th>
                            <th class="text-center">Selesai Pada</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hasil as $h)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td><strong>{{ $h->siswa->nama_siswa ?? '-' }}</strong></td>
                            <td>{{ $h->siswa->nis ?? '-' }}</td>
                            <td>{{ $h->siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge fs-6 {{ $h->nilai >= 70 ? 'bg-success' : 'bg-danger' }}">
                                    {{ number_format($h->nilai, 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($h->nilai >= 70)
                                    <span class="badge bg-success-subtle text-success border border-success">Lulus</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger">Tidak Lulus</span>
                                @endif
                            </td>
                            <td class="text-center text-muted small">
                                {{ $h->tanggal_selesai ? $h->tanggal_selesai->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('guru.hasil.nilai', [$ujian->id_ujian, $h->id_siswa]) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square me-1"></i> Nilai Jawaban
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada siswa yang mengerjakan ujian ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @endif

</div>
@endsection
