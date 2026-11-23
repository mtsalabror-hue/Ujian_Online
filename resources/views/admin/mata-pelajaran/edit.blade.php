@extends('layouts.admin')

@section('title', 'Edit Mata Pelajaran')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-pencil-square me-2"></i>Edit Mata Pelajaran
            </h4>
            <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.mata-pelajaran.update', $mataPelajaran) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Kode Mapel <span class="text-danger">*</span></label>
                        <input type="text" name="kode_mapel"
                            class="form-control @error('kode_mapel') is-invalid @enderror"
                            value="{{ old('kode_mapel', $mataPelajaran->kode_mapel) }}" required maxlength="10">
                        @error('kode_mapel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">Nama Mapel <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mapel"
                            class="form-control @error('nama_mapel') is-invalid @enderror"
                            value="{{ old('nama_mapel', $mataPelajaran->nama_mapel) }}" required maxlength="50">
                        @error('nama_mapel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr>

                <div class="text-end">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection