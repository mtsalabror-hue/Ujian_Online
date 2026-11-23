@extends('layouts.siswa')

@section('title', 'Konfirmasi Ujian')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-file-earmark-text me-2"></i>Konfirmasi Ujian
                    </h4>
                </div>

                <div class="card-body p-4">

                    <h3 class="fw-bold text-dark mb-1">{{ $ujian->judul_ujian }}</h3>
                    <p class="text-muted mb-4">Mata Pelajaran: <strong>{{ $ujian->mataPelajaran->nama_mapel ?? '-' }}</strong></p>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block mb-1"><i class="bi bi-people me-1"></i>Kelas</small>
                                <strong class="fs-6 text-dark">{{ $ujian->kelas->nama_kelas ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block mb-1"><i class="bi bi-stopwatch me-1"></i>Durasi</small>
                                <strong class="fs-6 text-dark">{{ $ujian->durasi }} Menit</strong>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block mb-1"><i class="bi bi-question-circle me-1"></i>Total Soal</small>
                                <strong class="fs-6 text-dark">{{ $ujian->bankSoal->count() }} Soal</strong>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block mb-1"><i class="bi bi-calendar3 me-1"></i>Tanggal</small>
                                <strong class="fs-6 text-dark">{{ \Carbon\Carbon::parse($ujian->tanggal)->format('d M Y') }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Petunjuk Pengerjaan --}}
                    <div class="alert alert-warning border-warning">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Petunjuk Penting Sebelum Mengikuti Ujian:</h6>
                        <ol class="mb-0 ps-3 small">
                            <li>Pastikan koneksi internet Anda stabil selama ujian berlangsung.</li>
                            <li>Waktu ujian akan mulai dihitung begitu Anda mengklik tombol <strong>Mulai Ujian Now</strong>.</li>
                            <li>Jawaban Anda tersimpan secara otomatis setiap kali memilih pilihan jawaban.</li>
                            <li>Jangan menutup atau merefresh browser saat ujian sedang berlangsung.</li>
                            <li>Pastikan menekan tombol <strong>Selesai Ujian</strong> jika seluruh soal sudah dikerjakan.</li>
                        </ol>
                    </div>

                    @if($hasil && $hasil->status === 'selesai')
                    <div class="card border-success bg-success bg-opacity-10 text-center p-4 mb-4">
                        <i class="bi bi-check-circle-fill fs-1 text-success mb-2"></i>
                        <h4 class="fw-bold text-success">Ujian Telah Selesai</h4>
                        <p class="text-muted mb-2">Anda telah menyelesaikan ujian ini pada <strong>{{ $hasil->tanggal_selesai ? $hasil->tanggal_selesai->format('d M Y H:i') : '-' }}</strong>.</p>
                        <div class="fs-2 fw-bold text-dark">Nilai: {{ number_format($hasil->nilai, 1) }}</div>
                    </div>
                    @endif

                </div>

                <div class="card-footer bg-white p-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3">
                    <a href="{{ route('siswa.ujian') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Ujian
                    </a>

                    @if(!$hasil || $hasil->status !== 'selesai')
                        <form action="{{ route('siswa.ujian.start', $ujian->id_ujian) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg px-4 fw-bold">
                                <i class="bi bi-play-fill me-1"></i>
                                {{ $hasil && $hasil->status === 'proses' ? 'Lanjutkan Ujian' : 'Mulai Ujian Sekarang' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>

</div>

@endsection
