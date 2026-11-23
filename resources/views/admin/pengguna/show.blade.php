@extends('layouts.admin')

@section('title', 'Detail Pengguna')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-person-circle me-2"></i>Detail Pengguna
            </h4>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                <tr>
                    <th width="220">Username</th>
                    <td><strong>{{ $user->username }}</strong></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td>
                        @if($user->role == 'admin')
                        <span class="badge bg-danger">Admin</span>
                        @elseif($user->role == 'guru')
                        <span class="badge bg-primary">Guru</span>
                        @else
                        <span class="badge bg-success">Siswa</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if($user->status)
                        <span class="badge bg-success">Aktif</span>
                        @else
                        <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Dibuat Pada</th>
                    <td>{{ $user->created_at->format('d F Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Terakhir Diubah</th>
                    <td>{{ $user->updated_at->format('d F Y H:i') }}</td>
                </tr>
            </table>
            </div>

        </div>

        <div class="card-footer text-end">
            <a href="{{ route('admin.pengguna.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

    </div>

</div>

@endsection