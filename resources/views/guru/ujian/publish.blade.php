@extends('layouts.guru')

@section('title', 'Publish Ujian')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-send me-2"></i>Publish Ujian</h3>
        <p class="text-muted mb-0">Aktifkan atau nonaktifkan ujian agar dapat diakses oleh siswa.</p>
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
                            <th class="text-center">Soal</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="160">Aksi Publish</th>
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
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                                <br><small class="text-muted">{{ substr($item->jam_mulai, 0, 5) }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">{{ $item->bankSoal->count() }} soal</span>
                            </td>
                            <td class="text-center">
                                @if($item->status === 'aktif')
                                    <span class="badge bg-success fs-6">
                                        <i class="bi bi-broadcast me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary fs-6">
                                        <i class="bi bi-pause-circle me-1"></i>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('guru.ujian.toggle-publish', $item->id_ujian) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @if($item->status === 'aktif')
                                    <button type="submit" class="btn btn-warning btn-sm w-100"
                                        onclick="return confirm('Nonaktifkan ujian ini?')">
                                        <i class="bi bi-pause-circle me-1"></i> Nonaktifkan
                                    </button>
                                    @else
                                    <button type="submit" class="btn btn-success btn-sm w-100"
                                        @if($item->bankSoal->count() == 0) disabled title="Tambahkan soal terlebih dahulu" @endif
                                        onclick="return confirm('Publish ujian ini? Siswa akan dapat mengaksesnya.')">
                                        <i class="bi bi-broadcast me-1"></i> Publish
                                    </button>
                                    @if($item->bankSoal->count() == 0)
                                    <small class="text-danger d-block mt-1">Belum ada soal</small>
                                    @endif
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-muted"></i>
                                <span class="text-muted">Belum ada ujian yang dapat dipublish.</span>
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
