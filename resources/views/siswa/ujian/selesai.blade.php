@extends('layouts.siswa')

@section('title', 'Ujian Selesai')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center py-3">
        <div class="col-lg-6">

            <div class="card shadow-sm border-0 text-center">

                <div class="card-header bg-success text-white py-3">
                    <i class="bi bi-check-circle-fill fs-3 d-block mb-1"></i>
                    <h4 class="fw-bold mb-0">Ujian Berhasil Diselesaikan!</h4>
                </div>

                <div class="card-body p-4">

                    <h5 class="fw-bold text-dark mb-1">{{ $ujian->judul_ujian }}</h5>
                    <p class="text-muted mb-3">Mata Pelajaran: <strong>{{ $ujian->mataPelajaran->nama_mapel ?? '-' }}</strong></p>

                    <div class="card border-0 bg-light p-3 mb-3">
                        <small class="text-muted fw-bold d-block mb-1">NILAI UJIAN ANDA</small>
                        <div class="display-6 fw-bold text-success">
                            {{ number_format($hasil->nilai, 1) }}
                        </div>
                        <small class="text-muted mt-2">
                            Diselesaikan pada: {{ $hasil->tanggal_selesai ? $hasil->tanggal_selesai->format('d M Y, H:i') : now()->format('d M Y, H:i') }} WIB
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Terima kasih telah mengerjakan ujian dengan jujur dan tepat waktu.
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('siswa.ujian') }}" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-house-door me-2"></i> Kembali ke Daftar Ujian
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </div>

</div>

@endsection
