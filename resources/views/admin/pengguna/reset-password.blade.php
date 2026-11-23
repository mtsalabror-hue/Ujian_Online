@extends('layouts.admin')

@section('title', 'Reset Password')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-key me-2"></i>Reset Password
            </h4>
            <a href="{{ route('admin.pengguna.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card-body">

            <div class="alert alert-info py-2">
                <i class="bi bi-info-circle me-2"></i>
                Reset password untuk akun <strong>{{ $user->username }}</strong>
                ({{ ucfirst($user->role) }}).
            </div>

            <form action="{{ route('admin.pengguna.reset.store', $user->id) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Minimal 6 karakter.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation"
                            class="form-control" required>
                    </div>
                </div>

                <hr>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Password Baru
                    </button>
                </div>
            </form>

        </div>

    </div>

</div>

@endsection