@extends('layouts.guru')

@section('title', 'Jadwal Ujian')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-calendar-event me-2"></i>Jadwal Ujian</h3>
            <p class="text-muted mb-0">Jadwal ujian yang telah dibuat beserta status dan waktu pelaksanaan.</p>
        </div>
        <a href="{{ route('guru.ujian.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Buat Ujian Baru
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
                            <th>Tahun Ajaran</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Jam Mulai</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-center">Status</th>
                            <th width="90" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ujian as $item)
                        @php
                            $tgl = \Carbon\Carbon::parse($item->tanggal);
                            $isToday   = $tgl->isToday();
                            $isPast    = $tgl->isPast() && !$isToday;
                            $isFuture  = $tgl->isFuture();
                        @endphp
                        <tr class="{{ $isToday ? 'table-warning' : '' }}">
                            <td class="text-center">{{ $ujian->firstItem() + $loop->index }}</td>
                            <td>
                                <strong>{{ $item->judul_ujian }}</strong>
                                @if($isToday)
                                    <span class="badge bg-warning text-dark ms-1">Hari Ini</span>
                                @endif
                            </td>
                            <td>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</td>
                            <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                            <td>
                                {{ $item->tahunAjaran->tahun_ajaran ?? '-' }}
                                <br><small class="text-muted">{{ $item->tahunAjaran->semester ?? '' }}</small>
                            </td>
                            <td class="text-center">{{ $tgl->format('d M Y') }}</td>
                            <td class="text-center">{{ substr($item->jam_mulai, 0, 5) }}</td>
                            <td class="text-center">{{ $item->durasi }} menit</td>
                            <td class="text-center">
                                @if($item->status === 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('guru.ujian.edit', $item->id_ujian) }}"
                                    class="btn btn-sm btn-warning" title="Edit Jadwal Ujian">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('guru.ujian.destroy', $item->id_ujian) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus ujian {{ addslashes($item->judul_ujian) }}? Semua data terkait (hasil jawaban siswa) juga akan terhapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus Ujian">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2 text-muted"></i>
                                <span class="text-muted">Belum ada jadwal ujian.</span>
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
