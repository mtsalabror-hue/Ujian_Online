@extends('layouts.siswa')

@section('title', 'Daftar Ujian Siswa')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-journal-check me-2"></i>Daftar Ujian</h3>
        <p class="text-muted mb-0">
            Selamat datang, <strong>{{ $siswa->nama_siswa ?? auth()->user()->username }}</strong>
            (Kelas: <span class="badge bg-success">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>)
        </p>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show">
        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Daftar Ujian Cards --}}
    <div class="row g-4">

        @forelse($ujian as $item)
        @php
            $statusSiswa = $hasilUjian[$item->id_ujian] ?? null;
            $nilaiSiswa  = $nilaiUjian[$item->id_ujian] ?? null;
            $isSelesai   = $statusSiswa === 'selesai';
            $isProses    = $statusSiswa === 'proses';
            $isBelumMulai = !$item->sudahMulai();
            $isBerakhir  = !$isSelesai && !$isProses && !$isBelumMulai && now()->greaterThanOrEqualTo($item->waktuSelesai());
        @endphp

        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100 {{ $isSelesai ? 'border-success border-2' : '' }}">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-1 border-0 pt-3 px-3">
                    <span class="badge bg-primary text-wrap">
                        <i class="bi bi-book me-1"></i>{{ $item->mataPelajaran->nama_mapel ?? '-' }}
                    </span>
                    @if($isSelesai)
                        <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>Sudah Selesai</span>
                    @elseif($isProses)
                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Sedang Berlangsung</span>
                    @elseif($isBelumMulai)
                        <span class="badge bg-secondary"><i class="bi bi-lock me-1"></i>Belum Dibuka</span>
                    @elseif($isBerakhir)
                        <span class="badge bg-dark"><i class="bi bi-clock-history me-1"></i>Waktu Berakhir</span>
                    @else
                        <span class="badge bg-info text-dark"><i class="bi bi-clock me-1"></i>Tersedia</span>
                    @endif
                </div>

                <div class="card-body px-3 py-2">
                    <h5 class="fw-bold text-dark mb-3">{{ $item->judul_ujian }}</h5>

                    <div class="p-2 bg-light rounded text-secondary small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><i class="bi bi-calendar3 me-1"></i> Tanggal:</span>
                            <strong>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span><i class="bi bi-clock-history me-1"></i> Jam Mulai:</span>
                            <strong>{{ substr($item->jam_mulai, 0, 5) }} WIB</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span><i class="bi bi-stopwatch me-1"></i> Durasi:</span>
                            <strong>{{ $item->durasi }} Menit</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span><i class="bi bi-hourglass-split me-1"></i> Jam Selesai:</span>
                            <strong>{{ \Carbon\Carbon::parse($item->waktuSelesai())->format('H:i') }} WIB</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-file-earmark-text me-1"></i> Total Soal:</span>
                            <strong>{{ $item->bankSoal->count() }} Soal</strong>
                        </div>
                    </div>

                    @if($isSelesai)
                    <div class="alert alert-success py-2 text-center mb-0">
                        <small class="d-block text-muted">Nilai Anda</small>
                        <strong class="fs-4">{{ number_format($nilaiSiswa, 1) }}</strong>
                    </div>
                    @endif
                </div>

                <div class="card-footer bg-white border-0 pb-3 px-3">
                    @if($isSelesai)
                        <a href="{{ route('siswa.ujian.show', $item->id_ujian) }}" class="btn btn-outline-success w-100">
                            <i class="bi bi-eye me-1"></i> Detail Hasil
                        </a>
                    @elseif($isProses)
                        <a href="{{ route('siswa.ujian.kerjakan', $item->id_ujian) }}" class="btn btn-warning w-100 text-dark fw-bold">
                            <i class="bi bi-play-circle me-1"></i> Lanjutkan Ujian
                        </a>
                    @elseif($isBelumMulai)
                        <button type="button" class="btn btn-secondary w-100 fw-semibold" disabled>
                            <i class="bi bi-lock me-1"></i> Dibuka {{ \Carbon\Carbon::parse($item->waktuMulai())->format('d M Y H:i') }} WIB
                        </button>
                    @elseif($isBerakhir)
                        <button type="button" class="btn btn-outline-secondary w-100 fw-semibold" disabled>
                            <i class="bi bi-clock-history me-1"></i> Waktu Ujian Berakhir
                        </button>
                    @else
                        <a href="{{ route('siswa.ujian.show', $item->id_ujian) }}" class="btn btn-primary w-100 fw-semibold">
                            <i class="bi bi-pencil-square me-1"></i> Ikuti Ujian
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-journal-x fs-1 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold text-secondary">Belum Ada Ujian</h5>
                    <p class="text-muted mb-0">Saat ini belum ada ujian aktif untuk kelas Anda ({{ $siswa->kelas->nama_kelas ?? '-' }}).</p>
                </div>
            </div>
        </div>
        @endforelse

    </div>

</div>

@endsection