@extends('layouts.auth')

@section('title', 'Pilih Jenis Pengguna - AL-ABROR CBT')

@section('content')
<div class="text-center w-100" style="max-width: 900px;">

    {{-- Logo & Header --}}
    <img src="{{ !empty($setting->logo) ? asset('storage/' . $setting->logo) : asset('images/logoo.png') }}"
        alt="Logo sekolah"
        width="110"
        class="mb-2 img-fluid">

    <h2 class="cbt-title" style="font-size: 1.9rem;">
        {{ $setting->nama_sekolah ?? 'SMK IT AL-ABROR' }}
    </h2>

    <div class="cbt-subtitle-1" style="font-size: 1.05rem;">
        Computer Based Test
    </div>

    {{-- Decorative Line --}}
    <div class="cbt-divider">
        <i class="bi bi-flower1 cbt-divider-icon"></i>
    </div>

    <p class="text-dark fw-medium mb-4" style="font-size: 0.95rem;">
        Silahkan pilih jenis pengguna
    </p>

    {{-- Choice Cards --}}
    <div class="row g-4 justify-content-center mb-4">

        {{-- SISWA --}}
        <div class="col-md-5 col-sm-6">
            <a href="{{ route('login.siswa') }}" class="cbt-choice-card">
                <div class="cbt-icon-circle">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <h3 class="cbt-card-title">SISWA</h3>

                <div class="cbt-divider my-2" style="width: 140px;">
                    <i class="bi bi-flower1 cbt-divider-icon" style="font-size: 14px;"></i>
                </div>

                <p class="text-secondary mb-0 small fw-medium">
                    Mengikuti ujian online
                </p>
            </a>
        </div>

        {{-- GURU --}}
        <div class="col-md-5 col-sm-6">
            <a href="{{ route('login.guru') }}" class="cbt-choice-card">
                <div class="cbt-icon-circle">
                    <i class="bi bi-easel"></i>
                </div>
                <h3 class="cbt-card-title">GURU</h3>

                <div class="cbt-divider my-2" style="width: 140px;">
                    <i class="bi bi-flower1 cbt-divider-icon" style="font-size: 14px;"></i>
                </div>

                <p class="text-secondary mb-0 small fw-medium">
                    Kelola Soal Ujian
                </p>
            </a>
        </div>

    </div>

    {{-- Admin Button --}}
    <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap align-items-center">
        <a href="{{ route('login.admin') }}" class="btn-cbt-admin">
            <i class="bi bi-gear-fill text-success fs-5"></i>
            <span>Login Sebagai Admin</span>
        </a>
    </div>

    <div class="mt-3">
        <a href="{{ route('landing') }}" class="text-decoration-none text-secondary small fw-medium">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Utama
        </a>
    </div>

</div>
@endsection