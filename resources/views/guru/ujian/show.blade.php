@extends('layouts.guru')

@section('title', 'Detail Ujian - ' . $ujian->judul_ujian)

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-eye me-2"></i>Detail Ujian</h3>
            <p class="text-muted mb-0">Informasi lengkap ujian beserta daftar soal yang disertakan.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('guru.ujian.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <a href="{{ route('guru.ujian.edit', $ujian->id_ujian) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-clipboard-data me-2"></i>Informasi Ujian</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <th width="200" class="text-muted fw-normal">Judul Ujian</th>
                                <td><strong class="fs-5">{{ $ujian->judul_ujian }}</strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Mata Pelajaran</th>
                                <td>{{ $ujian->mataPelajaran->nama_mapel ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Kelas</th>
                                <td>{{ $ujian->kelas->nama_kelas ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Tahun Ajaran</th>
                                <td>
                                    {{ $ujian->tahunAjaran->tahun_ajaran ?? '-' }}
                                    @if($ujian->tahunAjaran)
                                    <span class="badge bg-light text-dark border ms-1">{{ $ujian->tahunAjaran->semester ?? '' }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Jadwal</th>
                                <td>
                                    {{ \Carbon\Carbon::parse($ujian->tanggal)->format('d M Y') }}
                                    | {{ substr($ujian->jam_mulai, 0, 5) }} WIB
                                    | {{ $ujian->durasi }} menit
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Status</th>
                                <td>
                                    @if($ujian->status === 'aktif')
                                        <span class="badge bg-success fs-6"><i class="bi bi-broadcast me-1"></i>Aktif</span>
                                    @else
                                        <span class="badge bg-secondary fs-6"><i class="bi bi-pause-circle me-1"></i>Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2"></i>Ringkasan</h5>
                </div>
                <div class="card-body">
                    @php
                        $jumlahSoal = $ujian->bankSoal->count();
                        $peserta = $ujian->hasilUjian->count();
                        $selesai = $ujian->hasilUjian->where('status', 'selesai')->count();
                        $proses  = $ujian->hasilUjian->where('status', 'proses')->count();
                    @endphp
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="fs-3 fw-bold text-primary">{{ $jumlahSoal }}</div>
                                <small class="text-muted">Jumlah Soal</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="fs-3 fw-bold text-info">{{ $peserta }}</div>
                                <small class="text-muted">Total Peserta</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="fs-3 fw-bold text-success">{{ $selesai }}</div>
                                <small class="text-muted">Selesai</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="fs-3 fw-bold text-warning">{{ $proses }}</div>
                                <small class="text-muted">Sedang Berjalan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2"></i>Daftar Soal</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Jenis Soal</th>
                            <th>Tingkat</th>
                            <th>Pertanyaan</th>
                            <th class="text-center">Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ujian->bankSoal as $soal)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                @if($soal->jenis_soal === 'pilihan_ganda')
                                <span class="badge bg-primary">Pilihan Ganda</span>
                                @elseif($soal->jenis_soal === 'benar_salah')
                                <span class="badge bg-warning text-dark">Benar / Salah</span>
                                @else
                                <span class="badge bg-info text-dark">Essay</span>
                                @endif
                            </td>
                            <td>
                                @if($soal->id_kelas)
                                <span class="badge bg-light text-dark border">{{ $soal->kelas->nama_kelas ?? '-' }}</span>
                                @else
                                <span class="badge bg-secondary">Semua Kelas</span>
                                @endif
                            </td>
                            <td>{{ Str::limit(strip_tags($soal->pertanyaan), 90) }}</td>
                            <td class="text-center">{{ $soal->bobot }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                Belum ada soal pada ujian ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection