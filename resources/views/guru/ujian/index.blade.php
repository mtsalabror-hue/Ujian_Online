@extends('layouts.guru')

@section('title', 'Kelola Ujian')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2"></i>Kelola Ujian</h3>
            <p class="text-muted mb-0">Daftar semua ujian yang telah dibuat.</p>
        </div>
        <a href="{{ route('guru.ujian.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Buat Ujian
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Judul Ujian</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="160">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ujian as $item)
                        <tr>
                            <td class="text-center">{{ $ujian->firstItem() + $loop->index }}</td>
                            <td><strong>{{ $item->judul_ujian }}</strong></td>
                            <td>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</td>
                            <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}<br>
                                <small class="text-muted">{{ $item->jam_mulai }} ({{ $item->durasi }} menit)</small>
                            </td>
                            <td class="text-center">
                                @if($item->status === 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('guru.ujian.show', $item->id_ujian) }}" class="btn btn-info btn-sm" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('guru.ujian.edit', $item->id_ujian) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('guru.ujian.destroy', $item->id_ujian) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Hapus"
                                        onclick="return confirm('Yakin ingin menghapus ujian ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-muted"></i>
                                <span class="text-muted">Belum ada ujian. Klik <strong>Buat Ujian</strong> untuk mulai.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($ujian->hasPages())
        <div class="card-footer bg-white">{{ $ujian->links() }}</div>
        @endif
    </div>

</div>
@endsection
