@extends('layouts.admin')

@section('title', 'Detail Guru')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-person-vcard me-2"></i>Detail Data Guru
            </h4>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                <tr>
                    <th width="220">NIK</th>
                    <td><code>{{ $guru->nik }}</code></td>
                </tr>
                <tr>
                    <th>Nama Guru</th>
                    <td><strong>{{ $guru->nama_guru }}</strong></td>
                </tr>
                <tr>
                    <th>Jenis Kelamin</th>
                    <td>{{ $guru->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $guru->email ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Nomor HP</th>
                    <td>{{ $guru->no_hp ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td>{{ $guru->alamat ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Mata Pelajaran yang Diampu</th>
                    <td>
                        @forelse($guru->mataPelajaran as $m)
                        <span class="badge bg-primary me-1 mb-1">{{ $m->nama_mapel }}</span>
                        @empty
                        <span class="text-muted">Belum ada mata pelajaran yang diampu.</span>
                        @endforelse
                    </td>
                </tr>
                <tr>
                    <th>Kelas yang Diajar</th>
                    <td>
                        @forelse($guru->kelas as $k)
                        <span class="badge bg-success me-1 mb-1">{{ $k->nama_kelas }}</span>
                        @empty
                        <span class="text-muted">Belum ada kelas yang diajar.</span>
                        @endforelse
                    </td>
                </tr>
                <tr>
                    <th>Username</th>
                    <td>{{ $guru->user?->username ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status Akun</th>
                    <td>
                        @if($guru->user?->status)
                        <span class="badge bg-success">Aktif</span>
                        @else
                        <span class="badge bg-danger">Nonaktif</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Dibuat</th>
                    <td>{{ $guru->created_at->format('d F Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Terakhir Diubah</th>
                    <td>{{ $guru->updated_at->format('d F Y H:i') }}</td>
                </tr>
            </table>
            </div>

        </div>

        <div class="card-footer text-end">
            <a href="{{ route('admin.guru.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

    </div>

</div>

@endsection