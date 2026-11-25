@extends('layouts.admin')

@section('title', 'Data Guru')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-person-workspace me-2"></i>Data Guru</h3>
            <p class="text-muted mb-0">Kelola data guru yang terdaftar pada sistem.</p>
        </div>
        <a href="{{ route('admin.guru.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Guru
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Card --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white py-3">
            <form action="{{ route('admin.guru.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari NIK atau Nama Guru..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
                @if(request('search'))
                <div class="col-md-2">
                    <a href="{{ route('admin.guru.index') }}" class="btn btn-secondary w-100">Reset</a>
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
                            <th>NIK</th>
                            <th>Nama Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th class="text-center">Status</th>
                            <th width="160" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guru as $item)
                        <tr>
                            <td class="text-center">{{ $guru->firstItem() + $loop->index }}</td>
                            <td><code>{{ $item->nik }}</code></td>
                            <td><strong>{{ $item->nama_guru }}</strong></td>
                            <td>
                                @forelse($item->mataPelajaran as $m)
                                <span class="badge bg-primary me-1 mb-1">{{ $m->nama_mapel }}</span>
                                @empty
                                <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td>
                                @forelse($item->kelas as $k)
                                <span class="badge bg-success me-1 mb-1">{{ $k->nama_kelas }}</span>
                                @empty
                                <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td class="text-center">
                                @if($item->user && $item->user->status)
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.guru.show', $item) }}" class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.guru.edit', $item) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                @if($item->user)
                                <form action="{{ route('admin.guru.impersonate', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" title="Masuk sebagai guru ini">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.guru.destroy', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus data guru ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
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