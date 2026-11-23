@extends('layouts.guru')

@section('title', 'Hasil Ujian Peserta')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-person-check me-2"></i>Hasil Ujian Peserta</h3>
        <p class="text-muted mb-0">Lihat detail nilai per peserta pada ujian yang dipilih.</p>
    </div>

    {{-- Pilih Ujian --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('guru.hasil.peserta') }}" method="GET" class="row g-3 align-items-end">
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
                        <i class="bi bi-search me-1"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($ujian)
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 fw-bold">{{ $ujian->judul_ujian }}</h6>
                <small class="text-muted">
                    {{ $ujian->mataPelajaran->nama_mapel ?? '-' }} |
                    Kelas {{ $ujian->kelas->nama_kelas ?? '-' }} |
                    {{ \Carbon\Carbon::parse($ujian->tanggal)->format('d M Y') }}
                </small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center">Rank</th>
                            <th>Nama Siswa</th>
                            <th>NIS</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Keterangan</th>
                            <th class="text-center">Selesai Pada</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peserta as $h)
                        <tr>
                            <td class="text-center fw-bold">
                                @if($loop->iteration <= 3)
                                    <span class="badge {{ $loop->iteration == 1 ? 'bg-warning text-dark' : ($loop->iteration == 2 ? 'bg-secondary' : 'bg-warning-subtle text-dark') }}">
                                        #{{ $loop->iteration }}
                                    </span>
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                        style="width:32px;height:32px;font-size:13px;flex-shrink:0;">
                                        {{ strtoupper(substr($h->siswa->nama_siswa ?? 'S', 0, 1)) }}
                                    </div>
                                    <strong>{{ $h->siswa->nama_siswa ?? '-' }}</strong>
                                </div>
                            </td>
                            <td>{{ $h->siswa->nis ?? '-' }}</td>
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
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                                Belum ada peserta yang mengerjakan ujian ini.
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
