@extends('layouts.admin')

@section('title', 'Detail Tahun Ajaran')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                <i class="bi bi-calendar-event"></i>
                Detail Tahun Ajaran
            </h4>

        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered">

                <tr>
                    <th width="250">ID</th>
                    <td>{{ $tahunAjaran->id_tahun }}</td>
                </tr>

                <tr>
                    <th>Tahun Ajaran</th>
                    <td>{{ $tahunAjaran->tahun_ajaran }}</td>
                </tr>

                <tr>
                    <th>Semester</th>
                    <td>{{ $tahunAjaran->semester }}</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        @if($tahunAjaran->status)
                        <span class="badge bg-success">
                            Aktif
                        </span>
                        @else
                        <span class="badge bg-secondary">
                            Tidak Aktif
                        </span>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Dibuat Pada</th>
                    <td>{{ $tahunAjaran->created_at->format('d F Y H:i') }}</td>
                </tr>

                <tr>
                    <th>Terakhir Diubah</th>
                    <td>{{ $tahunAjaran->updated_at->format('d F Y H:i') }}</td>
                </tr>

            </table>
            </div>

        </div>

        <div class="card-footer text-end">

            <a href="{{ route('admin.tahun-ajaran.edit', $tahunAjaran) }}"
                class="btn btn-warning">

                <i class="bi bi-pencil-square"></i>
                Edit

            </a>

            <a href="{{ route('admin.tahun-ajaran.index') }}"
                class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>
                Kembali

            </a>

        </div>

    </div>

</div>

@endsection