@extends('layouts.auth')

@section('title', 'Atur Password Baru - AL-ABROR CBT')

@section('content')
<div class="container" style="max-width: 480px;">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="cbt-card">

                {{-- Header --}}
                <div class="d-flex justify-content-center">
                    <div class="cbt-card-header-badge">
                        <div class="cbt-icon-circle-sm">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h3 class="cbt-card-title">ATUR PASSWORD BARU</h3>
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <div class="cbt-divider">
                        <i class="bi bi-flower1 cbt-divider-icon"></i>
                    </div>
                </div>

                <p class="text-center text-secondary small mb-4">
                    Buat password baru untuk akun Anda.
                </p>

                @if($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3 small">
                    {{ $errors->first() }}
                </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('password.store') }}" method="POST" class="mt-3">
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
                                placeholder="Alamat Email"
                                value="{{ $email ?? old('email') }}"
                                required>
                        </div>
                        @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password Baru --}}
                    <div class="mb-3">
                        <div class="cbt-input-group">
                            <span class="input-icon-left">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Password Baru (min. 6 karakter)"
                                required>
                            <span class="toggle-password bi bi-eye"></span>
                        </div>
                        @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="mb-3">
                        <div class="cbt-input-group">
                            <span class="input-icon-left">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Ulangi Password Baru"
                                required>
                            <span class="toggle-password bi bi-eye"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-cbt-main w-100 mb-3">
                        <i class="bi bi-check2-circle me-1"></i> RESET PASSWORD
                    </button>
                </form>

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