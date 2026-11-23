@extends('layouts.admin')

@section('title', 'Edit Siswa')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Edit Data Siswa</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('admin.siswa.update', $siswa) }}" method="POST" enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>NIS</label>
                <input type="text" name="nis" class="form-control" value="{{ old('nis', $siswa->nis) }}">
            </div>

            <div class="mb-3">
                <label>NISN</label>
                <input type="text" name="nisn" class="form-control" value="{{ old('nisn', $siswa->nisn) }}" placeholder="Masukkan NISN">
            </div>

            <div class="mb-3">
                <label>Nama Siswa</label>
                <input type="text" name="nama_siswa" class="form-control" value="{{ old('nama_siswa', $siswa->nama_siswa) }}">
            </div>

            <div class="mb-3">
                <label>Kelas</label>

                <select name="id_kelas" class="form-select">

                    @foreach($kelas as $item)

                    <option value="{{ $item->id_kelas }}"
                        {{ old('id_kelas', $siswa->id_kelas) == $item->id_kelas ? 'selected' : '' }}>
                        {{ $item->nama_kelas }}
                    </option>

                    @endforeach

                </select>

            </div>

            <div class="mb-3">
                <label>Jenis Kelamin</label>

                <select name="jenis_kelamin" class="form-select">

                    <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                        Laki-laki
                    </option>

                    <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                        Perempuan
                    </option>

                </select>

            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Foto Siswa (3x4)</label>
                @if($siswa->foto)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $siswa->foto) }}" alt="Foto Siswa" class="img-thumbnail" style="width: 90px; height: 120px; object-fit: cover;">
                </div>
                @endif
                <input type="file" name="foto" class="form-control" accept="image/*">
                <div class="form-text">Biarkan kosong jika tidak ingin mengubah foto. Format: JPG, PNG, WEBP (Max 2MB).</div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-primary">
                    Update
                </button>
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </div>

        </form>

    </div>
</div>

@endsection