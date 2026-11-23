@extends('layouts.admin')

@section('title', 'Tambah Siswa')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Tambah Data Siswa</h4>
    </div>

    <div class="card-body">

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <form action="{{ route('admin.siswa.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            <div class="mb-3">
                <label>NIS</label>
                <input type="text" name="nis" class="form-control" value="{{ old('nis') }}" required>
            </div>

            <div class="mb-3">
                <label>NISN</label>
                <input type="text" name="nisn" class="form-control" value="{{ old('nisn') }}" placeholder="Masukkan NISN (Opsional)">
            </div>

            <div class="mb-3">
                <label>Nama Siswa</label>
                <input type="text" name="nama_siswa" class="form-control" value="{{ old('nama_siswa') }}" required>
            </div>

            <div class="mb-3">
                <label>Kelas</label>

                <select name="id_kelas" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>

                    @foreach($kelas as $item)
                    <option value="{{ $item->id_kelas }}" {{ old('id_kelas') == $item->id_kelas ? 'selected' : '' }}>
                        {{ $item->nama_kelas }}
                    </option>
                    @endforeach

                </select>
            </div>

            <div class="mb-3">
                <label>Jenis Kelamin</label>

                <select name="jenis_kelamin" class="form-select">
                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>

            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Foto Siswa (3x4)</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
                <div class="form-text">Format: JPG, PNG, WEBP (Max 2MB). Foto ini akan dicetak di kartu ujian.</div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-primary">
                    Simpan
                </button>
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </div>

        </form>

    </div>
</div>

@endsection