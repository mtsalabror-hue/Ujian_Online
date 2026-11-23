@extends('layouts.guru')

@section('title', 'Buat Ujian Baru')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Buat Ujian Baru</h4>

        </div>
        <div class="card-body">

            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form action="{{ route('guru.ujian.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-semibold">Judul Ujian <span class="text-danger">*</span></label>
                        <input type="text" name="judul_ujian"
                            class="form-control @error('judul_ujian') is-invalid @enderror"
                            value="{{ old('judul_ujian') }}"
                            placeholder="Contoh: Ujian Tengah Semester Matematika Kelas 9A">
                        @error('judul_ujian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="id_mapel" class="form-select @error('id_mapel') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            @foreach($mataPelajaran as $m)
                            <option value="{{ $m->id_mapel }}" {{ old('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                                {{ $m->nama_mapel }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_mapel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select name="id_kelas" class="form-select @error('id_kelas') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            @foreach($kelas as $k)
                            <option value="{{ $k->id_kelas }}" {{ old('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select name="id_tahun" class="form-select @error('id_tahun') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            @foreach($tahunAjaran as $t)
                            <option value="{{ $t->id_tahun }}" {{ old('id_tahun') == $t->id_tahun ? 'selected' : '' }}>
                                {{ $t->tahun_ajaran }} ({{ $t->semester }})
                            </option>
                            @endforeach
                        </select>
                        @error('id_tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Tanggal Ujian <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal"
                            class="form-control @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal') }}">
                        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="jam_mulai"
                            class="form-control @error('jam_mulai') is-invalid @enderror"
                            value="{{ old('jam_mulai') }}">
                        @error('jam_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Durasi (menit) <span class="text-danger">*</span></label>
                        <input type="number" name="durasi" min="1"
                            class="form-control @error('durasi') is-invalid @enderror"
                            value="{{ old('durasi', 60) }}"
                            placeholder="60">
                        @error('durasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Info Pengambilan Soal Per Mapel --}}
                <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis d-flex align-items-center mb-4">
                    <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                    <div>
                        <strong>Pengambilan Soal Otomatis:</strong> Seluruh soal di Bank Soal milik Mata Pelajaran yang Anda pilih (mencakup Pilihan Ganda, Benar/Salah, & Essay) akan otomatis disertakan ke dalam Ujian ini.
                    </div>
                </div>

                <hr>
                <div class="text-end">
                    <a href="{{ route('guru.ujian.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Ujian
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection