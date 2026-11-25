@extends('layouts.admin')

@section('title', 'Hasil Siswa')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-trophy me-2"></i>Hasil Siswa</h3>
            <p class="text-muted mb-0">Lihat hasil ujian seluruh siswa dari semua guru.</p>
        </div>
    </div>

    {{-- Statistik (jika ada ujian yang dipilih) --}}
    @if($stats)
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <small class="text-white-50">Peserta</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $stats['peserta'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body">
                    <small class="text-white-50">Rata-rata Nilai</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $stats['rata'] !== null ? number_format($stats['rata'], 1) : '-' }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-warning text-dark">
                <div class="card-body">
                    <small class="text-dark-50">Nilai Tertinggi</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $stats['max'] !== null ? number_format($stats['max'], 1) : '-' }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body">
                    <small class="text-white-50">Nilai Terendah</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $stats['min'] !== null ? number_format($stats['min'], 1) : '-' }}</h2>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Card --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">
            <form action="{{ route('admin.hasil-siswa.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <select name="id_ujian" class="form-select">
                        <option value="">-- Semua Ujian --</option>
                        @foreach($ujianList as $ujian)
                        <option value="{{ $ujian->id_ujian }}" {{ (string) request('id_ujian') === (string) $ujian->id_ujian ? 'selected' : '' }}>
                            {{ $ujian->judul_ujian }} — {{ $ujian->mataPelajaran->nama_mapel ?? '-' }} ({{ $ujian->kelas->nama_kelas ?? '-' }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="keyword" class="form-control"
                        placeholder="Cari Nama / NIS Siswa..." value="{{ request('keyword') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
                @if(request('id_ujian') || request('keyword'))
                <div class="col-md-2">
                    <a href="{{ route('admin.hasil-siswa.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
                @endif
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Nama Siswa</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th>Ujian</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Status</th>
                            <th>Tanggal Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hasil as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration + ($hasil->currentPage() - 1) * $hasil->perPage() }}</td>
                            <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                            <td>{{ $item->siswa->nis ?? '-' }}</td>
                            <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $item->ujian->judul_ujian ?? '-' }}</td>
                            <td>{{ $item->ujian->mataPelajaran->nama_mapel ?? '-' }}</td>
                            <td>{{ $item->ujian->guru->nama_guru ?? '-' }}</td>
                            <td class="text-center fw-bold">{{ number_format($item->nilai, 1) }}</td>
                            <td class="text-center">
                                @if($item->status === 'selesai')
                                <span class="badge bg-success">Selesai</span>
                                @else
                                <span class="badge bg-warning text-dark">Proses</span>
                                @endif
                            </td>
                            <td>
                                {{ $item->tanggal_selesai
                                    ? \Illuminate\Support\Carbon::parse($item->tanggal_selesai)->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada hasil ujian.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($hasil->hasPages())
        <div class="card-footer bg-white">{{ $hasil->links() }}</div>
        @endif

    </div>

</div>
@endsection
