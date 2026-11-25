@extends('layouts.auth')

@section('title', 'SMK IT AL-ABROR CBT - Computer Based Test')

@section('content')
<div class="cbt-hero-card text-center">

    {{-- Logo --}}
    <img src="{{ !empty($setting->logo) ? asset('storage/' . $setting->logo) : asset('images/logoo.png') }}"
        alt="Logo sekolah"
        width="120"
        class="mb-3 img-fluid">

    {{-- Titles --}}
    <h1 class="cbt-title">
        {{ $setting->nama_sekolah ?? 'SMK IT AL-ABROR' }}
    </h1>

    <div class="cbt-subtitle-1">
        Computer Based Test
    </div>

    {{-- Decorative Line --}}
    <div class="cbt-divider">
        <i class="bi bi-mortarboard-fill cbt-divider-icon"></i>
    </div>

    <p class="cbt-hero-desc">
        Ujian online yang aman dan terpantau untuk siswa, guru, dan admin —
        kerjakan soal dari mana saja, nilai langsung tercatat.
    </p>

    {{-- Button Masuk --}}
    <div class="mt-4">
        <a href="{{ route('login') }}"
            class="btn btn-cbt-main">
            MASUK KE SISTEM
        </a>
    </div>

    {{-- Pengguna --}}
    <div class="cbt-hero-meta">
        <span><i class="bi bi-mortarboard"></i> Siswa</span>
        <span><i class="bi bi-easel"></i> Guru</span>
        <span><i class="bi bi-gear"></i> Admin</span>
    </div>

</div>
@endsection
