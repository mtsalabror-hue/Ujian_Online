@extends('layouts.auth')

@section('title', 'Login Guru - AL-ABROR CBT')

@section('content')
<div class="container" style="max-width: 950px;">
    <div class="row align-items-center justify-content-center g-4">

        {{-- Branding Info Column (Left) --}}
        <div class="col-lg-6 col-md-6 text-center d-none d-md-block">
            <img src="{{ !empty($setting->logo) ? asset('storage/' . $setting->logo) : asset('images/logoo.png') }}"
                alt="Logo sekolah"
                width="140"
                class="mb-3 img-fluid">

            <h1 class="cbt-title" style="font-size: 2.2rem;">
                {{ $setting->nama_sekolah ?? 'SMK IT AL-ABROR' }}
            </h1>

            <div class="cbt-subtitle-1" style="font-size: 1.15rem;">
                Computer Based Test
            </div>

            <div class="cbt-divider my-3">
                <i class="bi bi-flower1 cbt-divider-icon"></i>
            </div>
        </div>

        {{-- Form Column (Right) --}}
        <div class="col-lg-5 col-md-6">
            <div class="cbt-card">

                {{-- Header Badge inside Card --}}
                <div class="d-flex justify-content-center">
                    <div class="cbt-card-header-badge">
                        <div class="cbt-icon-circle-sm">
                            <i class="bi bi-easel"></i>
                        </div>
                        <h3 class="cbt-card-title">GURU</h3>
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <div class="cbt-divider">
                        <i class="bi bi-flower1 cbt-divider-icon"></i>
                    </div>
                </div>

                @if(session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3 small">
                    {{ session('error') }}
                </div>
                @endif

                @if($errors->has('username'))
                <div class="alert alert-danger py-2 px-3 mb-3 small">
                    {{ $errors->first('username') }}
                </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('login.guru.process') }}" method="POST" class="mt-3">
                    @csrf

                    {{-- Username --}}
                    <div class="mb-3">
                        <div class="cbt-input-group">
                            <span class="input-icon-left">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text"
                                name="username"
                                class="form-control"
                                placeholder="NIK / Nama Guru / Username"
                                value="{{ old('username') }}"
                                required
                                autofocus>
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <div class="cbt-input-group">
                            <span class="input-icon-left">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password"
                                name="password"
                                class="form-control"
                                placeholder="Password"
                                required>
                            <span class="toggle-password bi bi-eye"></span>
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="rememberGuru">
                        <label class="form-check-label small text-secondary" for="rememberGuru">
                            Ingat Saya
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn btn-cbt-main w-100 mb-3">
                        MASUK KE SISTEM
                    </button>
                </form>

                {{-- Link --}}
                <div class="text-center">
                    <span class="small text-secondary">
                        Lupa Password? <a href="{{ route('password.request') }}" class="text-success text-decoration-none fw-semibold">Klik di sini</a>
                    </span>
                </div>

                {{-- Back Navigation --}}
                <div class="d-flex justify-content-center mt-3 pt-2 border-top">
                    <a href="{{ route('pilih.login') }}" class="text-decoration-none text-secondary small me-3">
                        <i class="bi bi-arrow-left me-1"></i> Pilih Role Login
                    </a>

                </div>

            </div>
        </div>

    </div>
</div>
@endsection