@extends('layouts.admin')

@section('title', 'Detail Kelas')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                <i class="bi bi-house-door-fill me-2"></i>
                Detail Kelas
            </h4>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered">

                <tr>
                    <th width="250">ID Kelas</th>
                    <td>{{ $kelas->id_kelas }}</td>
                </tr>

                <tr>
                    <th>Tingkat</th>
                    <td>{{ $kelas->tingkat }}</td>
                </tr>

                <tr>
                    <th>Nama Kelas</th>
                    <td>{{ $kelas->nama_kelas }}</td>
                </tr>

                <tr>
                    <th>Jumlah Siswa</th>
                    <td>
                        <span class="badge bg-primary">
                            {{ $kelas->siswa->count() }} Siswa
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Dibuat Pada</th>
                    <td>{{ $kelas->created_at?->format('d F Y H:i') }}</td>
                </tr>

                <tr>
                    <th>Terakhir Diubah</th>
                    <td>{{ $kelas->updated_at?->format('d F Y H:i') }}</td>
                </tr>

            </table>
            </div>

        </div>

        <div class="card-footer text-end">

           

            <a href="{{ route('admin.kelas.index') }}"
                class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>
                Kembali

            </a>

        </div>

    </div>

</div>

@endsection