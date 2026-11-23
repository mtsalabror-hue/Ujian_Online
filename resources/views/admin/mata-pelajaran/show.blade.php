@extends('layouts.admin')

@section('title', 'Detail Mata Pelajaran')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-book me-2"></i>Detail Mata Pelajaran
            </h4>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                <tr>
                    <th width="250">Kode Mata Pelajaran</th>
                    <td><code>{{ $mataPelajaran->kode_mapel }}</code></td>
                </tr>
                <tr>
                    <th>Nama Mata Pelajaran</th>
                    <td><strong>{{ $mataPelajaran->nama_mapel }}</strong></td>
                </tr>
                <tr>
                    <th>Dibuat Pada</th>
                    <td>{{ $mataPelajaran->created_at->format('d F Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Terakhir Diubah</th>
                    <td>{{ $mataPelajaran->updated_at->format('d F Y H:i') }}</td>
                </tr>
            </table>
            </div>

        </div>

        <div class="card-footer text-end">
            <a href="{{ route('admin.mata-pelajaran.edit', $mataPelajaran) }}" class="btn btn-warning">
                <i class="bi bi-pencil-square me-1"></i> Edit
            </a>
            <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

    </div>

</div>

@endsection