@extends('layouts.auth')

@section('title', 'AL-ABROR CBT - Computer Based Test')

@section('content')
<div class="text-center">

    {{-- Logo --}}
    <img src="{{ !empty($setting->logo) ? asset('storage/' . $setting->logo) : asset('images/logoo.png') }}"
        alt="Logo sekolah"
        width="140"
        class="mb-3 img-fluid">

    {{-- Titles --}}
    <h1 class="cbt-title">
        {{ $setting->nama_sekolah ?? 'MTs Al-Abror' }}
    </h1>

    <div class="cbt-subtitle-1">
        Computer Based Test
    </div>

    {{-- Decorative Line --}}
    <div class="cbt-divider">
        <i class="bi bi-flower1 cbt-divider-icon"></i>
    </div>

    {{-- Button Masuk --}}
    <div class="mt-4">
        <a href="{{ route('pilih.login') }}"
            class="btn btn-cbt-main">
            MASUK KE SISTEM
        </a>
    </div>

</div>
@endsection