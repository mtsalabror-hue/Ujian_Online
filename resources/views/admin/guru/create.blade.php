@extends('layouts.admin')

@section('title', 'Tambah Guru')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                <i class="bi bi-person-plus-fill me-2"></i>
                Tambah Data Guru
            </h4>

            <a href="{{ route('admin.guru.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </a>

        </div>

        <div class="card-body">

            <form action="{{ route('admin.guru.store') }}" method="POST">

                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            NIK
                        </label>

                        <input
                            type="text"
                            name="nik"
                            class="form-control @error('nik') is-invalid @enderror"
                            value="{{ old('nik') }}"
                            placeholder="Masukkan NIK">

                        @error('nik')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Nama Guru
                        </label>

                        <input
                            type="text"
                            name="nama_guru"
                            class="form-control @error('nama_guru') is-invalid @enderror"
                            value="{{ old('nama_guru') }}"
                            placeholder="Masukkan Nama Guru">

                        @error('nama_guru')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Jenis Kelamin
                        </label>

                        <select
                            name="jenis_kelamin"
                            class="form-select @error('jenis_kelamin') is-invalid @enderror">

                            <option value="">-- Pilih --</option>

                            <option value="L"
                                {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>
                                Laki-laki
                            </option>

                            <option value="P"
                                {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>
                                Perempuan
                            </option>

                        </select>

                        @error('jenis_kelamin')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="contoh@email.com">

                        @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            No. HP
                        </label>

                        <input
                            type="text"
                            name="no_hp"
                            class="form-control @error('no_hp') is-invalid @enderror"
                            value="{{ old('no_hp') }}"
                            placeholder="08xxxxxxxxxx">

                        @error('no_hp')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Alamat
                    </label>

                    <textarea
                        name="alamat"
                        rows="4"
                        class="form-control @error('alamat') is-invalid @enderror"
                        placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>

                    @error('alamat')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        <i class="bi bi-book me-1"></i>
                        Mata Pelajaran yang Diampu
                    </label>

                    @if($mapel->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Belum ada data mata pelajaran.
                        Tambahkan dulu di menu
                        <a href="{{ route('admin.mata-pelajaran.index') }}">Mata Pelajaran</a>.
                    </div>
                    @else
                    <div class="border rounded p-3 @error('mapel') border-danger @enderror"
                        style="max-height: 220px; overflow-y: auto;">

                        <div class="row">

                            @foreach($mapel as $m)
                            <div class="col-md-6 mb-2">

                                <div class="form-check">

                                    <input
                                        type="checkbox"
                                        name="mapel[]"
                                        value="{{ $m->id_mapel }}"
                                        id="mapel_{{ $m->id_mapel }}"
                                        class="form-check-input"
                                        {{ in_array($m->id_mapel, old('mapel', [])) ? 'checked' : '' }}>

                                    <label
                                        class="form-check-label"
                                        for="mapel_{{ $m->id_mapel }}">
                                        {{ $m->nama_mapel }}
                                        <small class="text-muted">({{ $m->kode_mapel }})</small>
                                    </label>

                                </div>

                            </div>
                            @endforeach

                        </div>

                    </div>
                    @endif

                    @error('mapel')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        <i class="bi bi-building me-1"></i>
                        Kelas yang Diajar
                    </label>

                    @if($kelasList->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Belum ada data kelas.
                        Tambahkan dulu di menu
                        <a href="{{ route('admin.kelas.index') }}">Kelas</a>.
                    </div>
                    @else
                    <div class="border rounded p-3 @error('kelas') border-danger @enderror">

                        <div class="row">

                            @foreach($kelasList as $k)
                            <div class="col-md-4 mb-2">

                                <div class="form-check">

                                    <input
                                        type="checkbox"
                                        name="kelas[]"
                                        value="{{ $k->id_kelas }}"
                                        id="kelas_{{ $k->id_kelas }}"
                                        class="form-check-input"
                                        {{ in_array($k->id_kelas, old('kelas', [])) ? 'checked' : '' }}>

                                    <label
                                        class="form-check-label"
                                        for="kelas_{{ $k->id_kelas }}">
                                        {{ $k->nama_kelas }}
                                    </label>

                                </div>

                            </div>
                            @endforeach

                        </div>

                    </div>
                    @endif

                    @error('kelas')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

                <hr>

                <div class="alert alert-info">

                    <strong>Informasi Akun Login</strong>

                    <ul class="mb-0 mt-2">

                        <li>Username akan menggunakan <strong>NIK</strong>.</li>

                        <li>Password default adalah <strong>NIK</strong> guru (sama dengan username).</li>

                        <li>Role otomatis menjadi <strong>Guru</strong>.</li>

                    </ul>

                </div>

                <div class="text-end">

                    <button type="reset" class="btn btn-warning">

                        <i class="bi bi-arrow-clockwise"></i>

                        Reset

                    </button>

                    <button type="submit" class="btn btn-primary">

                        <i class="bi bi-save"></i>

                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection