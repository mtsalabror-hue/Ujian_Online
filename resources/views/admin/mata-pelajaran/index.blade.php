@extends('layouts.admin')

@section('title', 'Data Mata Pelajaran')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">
                <i class="bi bi-book"></i> Data Mata Pelajaran
            </h3>
            <p class="text-muted mb-0">
                Kelola mata pelajaran yang diajarkan pada sistem.
            </p>
        </div>

        <a href="{{ route('admin.mata-pelajaran.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Tambah Mata Pelajaran
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Card --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <form action="{{ route('admin.mata-pelajaran.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Cari kode atau nama mata pelajaran..."
                            value="{{ request('keyword') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    @if(request('keyword'))
                    <div class="col-md-2">
                        <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="60">No</th>
                            <th>Kode Mapel</th>
                            <th>Nama Mata Pelajaran</th>
                            <th width="170" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mataPelajaran as $item)
                        <tr>
                            <td class="text-center">{{ $mataPelajaran->firstItem() + $loop->index }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $item->kode_mapel }}</span>
                            </td>
                            <td>{{ $item->nama_mapel }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.mata-pelajaran.show', $item) }}"
                                    class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.mata-pelajaran.edit', $item) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.mata-pelajaran.destroy', $item) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus mata pelajaran ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2 text-muted"></i>
                                Belum ada data mata pelajaran.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($mataPelajaran->hasPages())
        <div class="card-footer bg-white">
            {{ $mataPelajaran->links() }}
        </div>
        @endif

    </div>

</div>
@endsection