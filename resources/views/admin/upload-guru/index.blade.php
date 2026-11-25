@extends('layouts.admin')

@section('title', 'Status Upload Guru')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-cloud-arrow-up me-2"></i>Status Upload Guru</h3>
            <p class="text-muted mb-0">Pantau bank soal dan ujian yang sudah diupload oleh setiap guru.</p>
        </div>
    </div>

    {{-- Card --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">
            <form action="{{ route('admin.upload-guru.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control"
                        placeholder="Cari NIK atau Nama Guru..." value="{{ request('keyword') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
                @if(request('keyword'))
                <div class="col-md-2">
                    <a href="{{ route('admin.upload-guru.index') }}" class="btn btn-secondary w-100">Reset</a>
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
                            <th>Nama Guru</th>
                            <th>NIK</th>
                            <th>Mapel Diampu</th>
                            <th class="text-center">Mapel Ada Soal</th>
                            <th class="text-center">Total Soal</th>
                            <th class="text-center">Total Ujian</th>
                            <th>Soal Terakhir</th>
                            <th class="text-center">Status Upload</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guru as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration + ($guru->currentPage() - 1) * $guru->perPage() }}</td>
                            <td>{{ $item->nama_guru }}</td>
                            <td>{{ $item->nik ?? '-' }}</td>
                            <td>{{ $item->mataPelajaran->pluck('nama_mapel')->join(', ') ?: '-' }}</td>
                            <td class="text-center">{{ $mapelAdaSoal[$item->id_guru] ?? 0 }} dari {{ $item->mataPelajaran->count() }}</td>
                            <td class="text-center">{{ $item->bank_soal_count }}</td>
                            <td class="text-center">{{ $item->ujian_count }}</td>
                            <td>
                                {{ $item->soal_terakhir
                                    ? \Illuminate\Support\Carbon::parse($item->soal_terakhir)->translatedFormat('d M Y H:i')
                                    : '-' }}
                            </td>
                            <td class="text-center">
                                @if($item->bank_soal_count > 0)
                                <span class="badge bg-success">Sudah Upload</span>
                                @else
                                <span class="badge bg-secondary">Belum Upload</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada data guru.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($guru->hasPages())
        <div class="card-footer bg-white">{{ $guru->links() }}</div>
        @endif

    </div>

</div>
@endsection
