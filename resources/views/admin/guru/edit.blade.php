@extends('layouts.admin')

@section('title', 'Edit Data Guru')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                <i class="bi bi-pencil-square me-2"></i>
                Edit Data Guru
            </h4>
        </div>

        <div class="card-body">

            <form action="{{ route('admin.guru.update', $guru) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="row">

                    {{-- NIK --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            NIK
                        </label>

                        <input
                            type="text"
                            name="nik"
                            class="form-control @error('nik') is-invalid @enderror"
                            value="{{ old('nik', $guru->nik) }}">

                        @error('nik')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    {{-- Nama Guru --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Nama Guru
                        </label>

                        <input
                            type="text"
                            name="nama_guru"
                            class="form-control @error('nama_guru') is-invalid @enderror"
                            value="{{ old('nama_guru', $guru->nama_guru) }}">

                        @error('nama_guru')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Jenis Kelamin
                        </label>

                        <select
                            name="jenis_kelamin"
                            class="form-select @error('jenis_kelamin') is-invalid @enderror">

                            <option value="">-- Pilih --</option>

                            <option value="L"
                                {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                Laki-laki
                            </option>

                            <option value="P"
                                {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                Perempuan
                            </option>

                        </select>

                        @error('jenis_kelamin')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    {{-- Email --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $guru->email) }}">

                        @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    {{-- No HP --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            No. HP
                        </label>

                        <input
                            type="text"
                            name="no_hp"
                            class="form-control @error('no_hp') is-invalid @enderror"
                            value="{{ old('no_hp', $guru->no_hp) }}">

                        @error('no_hp')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    {{-- Username --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $guru->user->username }}"
                            readonly>

                        <small class="text-muted">
                            Username mengikuti NIK.
                        </small>

                    </div>

                    {{-- Alamat --}}
                    <div class="col-md-12 mb-3">

                        <label class="form-label">
                            Alamat
                        </label>

                        <textarea
                            name="alamat"
                            rows="4"
                            class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $guru->alamat) }}</textarea>

                        @error('alamat')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    {{-- Mata Pelajaran yang Diampu --}}
                    <div class="col-md-12 mb-3">

                        <label class="form-label fw-semibold">
                            <i class="bi bi-book me-1"></i>
                            Mata Pelajaran yang Diampu
                        </label>

                        @php
                        $mapelTerpilih = old('mapel', $guru->mataPelajaran->pluck('id_mapel')->toArray());
                        @endphp

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
                                            {{ in_array($m->id_mapel, $mapelTerpilih) ? 'checked' : '' }}>

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

                    {{-- Kelas yang Diajar --}}
                    <div class="col-md-12 mb-3">

                        <label class="form-label fw-semibold">
                            <i class="bi bi-building me-1"></i>
                            Kelas yang Diajar
                        </label>

                        @php
                        $kelasTerpilih = old('kelas', $guru->kelas->pluck('id_kelas')->toArray());
                        @endphp

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
                                            {{ in_array($k->id_kelas, $kelasTerpilih) ? 'checked' : '' }}>

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

                </div>

                <hr>

                <div class="text-end">

                    <button type="reset" class="btn btn-warning">

                        <i class="bi bi-arrow-clockwise"></i>

                        Reset

                    </button>

                    <button type="submit" class="btn btn-primary">

                        <i class="bi bi-save"></i>

                        Update Data

                    </button>
                    <a href="{{ route('admin.guru.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Batal
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
