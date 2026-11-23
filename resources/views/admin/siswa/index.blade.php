@extends('layouts.admin')

@section('title', 'Data Siswa')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="mb-1">
                <i class="bi bi-people-fill"></i> Data Siswa
            </h3>
            <p class="text-muted mb-0">
                Kelola data siswa yang terdaftar pada sistem.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.siswa.cetakKartu') }}" class="btn btn-warning fw-semibold">
                <i class="bi bi-card-heading me-1"></i> Cetak Kartu Ujian
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload me-1"></i> Import
            </button>
            <a href="{{ route('admin.siswa.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah Siswa
            </a>
        </div>
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
            <form action="{{ route('admin.siswa.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Cari NIS, NISN, atau Nama Siswa..."
                            value="{{ request('keyword') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="id_kelas" class="form-select">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id_kelas }}" {{ (string) request('id_kelas') === (string) $kelas->id_kelas ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    @if(request('keyword') || request('id_kelas'))
                    <div class="col-md-2">
                        <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary w-100">
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
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>JK</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th width="170" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $item)
                        <tr>
                            <td class="text-center">{{ $siswa->firstItem() + $loop->index }}</td>
                            <td>
                                <code>{{ $item->nis }}</code>
                            </td>
                            <td>{{ $item->nama_siswa }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $item->kelas->nama_kelas ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($item->jenis_kelamin === 'L')
                                <span class="badge bg-info">L</span>
                                @else
                                <span class="badge bg-danger">P</span>
                                @endif
                            </td>
                            <td>{{ $item->user?->username ?? '-' }}</td>
                            <td class="text-center">
                                @if($item->user?->status)
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.siswa.show', $item) }}"
                                    class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.siswa.edit', $item) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.siswa.destroy', $item) }}"
                                    method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus data siswa ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2 text-muted"></i>
                                Belum ada data siswa.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($siswa->hasPages())
        <div class="card-footer bg-white">
            {{ $siswa->links() }}
        </div>
        @endif

    </div>

</div>

{{-- Modal Import --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <a href="{{ route('admin.siswa.template') }}" class="btn btn-info text-white">
                        <i class="bi bi-download"></i> Download Template
                    </a>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection