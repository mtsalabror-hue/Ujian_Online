@extends('layouts.admin')

@section('title', 'Tambah Kelas')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-plus-circle me-2"></i>Tambah Data Kelas
            </h4>
            <a href="{{ route('admin.kelas.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card-body">

            <form action="{{ route('admin.kelas.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tingkat <span class="text-danger">*</span></label>
                        <select name="tingkat" class="form-select @error('tingkat') is-invalid @enderror">
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="VII" {{ old('tingkat') == 'VII' ? 'selected' : '' }}>VII</option>
                            <option value="VIII" {{ old('tingkat') == 'VIII' ? 'selected' : '' }}>VIII</option>
                            <option value="IX" {{ old('tingkat') == 'IX' ? 'selected' : '' }}>IX</option>
                        </select>
                        @error('tingkat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kelas"
                            class="form-control @error('nama_kelas') is-invalid @enderror"
                            placeholder="Contoh: VII A" value="{{ old('nama_kelas') }}">
                        @error('nama_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>

        </div>

    </div>

</div>

@endsection