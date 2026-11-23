@extends('layouts.auth')

@section('title', 'Lupa Password - AL-ABROR CBT')

@section('content')
<div class="container" style="max-width: 480px;">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="cbt-card">

                {{-- Header --}}
                <div class="d-flex justify-content-center">
                    <div class="cbt-card-header-badge">
                        <div class="cbt-icon-circle-sm">
                            <i class="bi bi-key"></i>
                        </div>
                        <h3 class="cbt-card-title">LUPA PASSWORD</h3>
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <div class="cbt-divider">
                        <i class="bi bi-flower1 cbt-divider-icon"></i>
                    </div>
                </div>

                <p class="text-center text-secondary small mb-4">
                    Masukkan alamat email yang terdaftar pada akun Anda. Kami akan mengirimkan tautan untuk
                    mereset password.
                </p>

                @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 small">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3 small">
                    {{ $errors->first() }}
                </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('password.email') }}" method="POST" class="mt-3">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-3">
                        <div class="cbt-input-group">
                            <span class="input-icon-left">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="Alamat Email Terdaftar"
                                value="{{ old('email') }}"
                                required
                                autofocus>
                        </div>
                        @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-cbt-main w-100 mb-3">
                        <i class="bi bi-send me-1"></i> KIRIM TAUTAN RESET
                    </button>
                </form>

                {{-- Back Navigation --}}
                <div class="d-flex justify-content-center mt-3 pt-2 border-top">
                    <a href="{{ route('pilih.login') }}" class="text-decoration-none text-secondary small">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Pilih Role Login
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection