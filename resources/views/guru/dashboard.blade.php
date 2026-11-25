@extends('layouts.guru')

@section('title', 'Dashboard Guru')

@section('content')

<div class="container-fluid">

    <div class="mb-3">
        <h3 class="fw-bold">Dashboard Guru</h3>
        <p class="text-muted">
            Selamat datang,
            <strong>{{ optional(auth()->user()->guru)->nama_guru ?? auth()->user()->username }}</strong>.
        </p>
    </div>

    <div class="row">

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <small class="text-white-50">Total Bank Soal</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $totalBankSoal }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body">
                    <small class="text-white-50">Total Soal</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $totalSoal }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-warning text-dark">
                <div class="card-body">
                    <small class="text-dark-50">Total Ujian</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $totalUjian }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body">
                    <small class="text-white-50">Ujian Aktif</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $ujianAktif }}</h2>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0 fw-bold">
                Ujian Terbaru
            </h5>

        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">

                <thead>

                    <tr>

                        <th>No</th>
                        <th>Nama Ujian</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($ujianTerbaru as $ujian)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $ujian->judul_ujian }}</td>

                        <td>{{ $ujian->kelas->nama_kelas }}</td>

                        <td>{{ $ujian->tanggal }}</td>

                        <td>

                            <span class="badge bg-success">
                                Aktif
                            </span>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="5" class="text-center">

                            Belum ada data ujian.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

                </table>
            </div>
        </div>

    </div>

    {{-- Download Bank Soal per Mata Pelajaran --}}
    <div class="card shadow-sm border-0 mt-4">

        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-download me-2"></i>Download Bank Soal per Mata Pelajaran
            </h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Mata Pelajaran</th>
                            <th>Jumlah Soal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($mapelDownload as $mapel)

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $mapel->nama_mapel }}</td>
                            <td>{{ $mapel->bank_soal_count }} soal</td>
                            <td>
                                <a href="{{ route('guru.bank-soal.export-mapel-word', $mapel->id_mapel) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="bi bi-file-earmark-word me-1"></i> Download Word
                                </a>
                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="4" class="text-center">
                                Belum ada bank soal. Tambah soal dulu lewat menu Bank Soal.
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