@extends('layouts.admin')

@section('title', 'Detail Siswa')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                <i class="bi bi-person-vcard"></i>
                Detail Data Siswa
            </h4>
        </div>

        <div class="card-body">

            <div class="row">



                <div class="col-md-9">

                    <div class="table-responsive">
                        <table class="table table-bordered">

                        <tr>
                            <th width="220">NIS</th>
                            <td>{{ $siswa->nis }}</td>
                        </tr>

                        <tr>
                            <th>NISN</th>
                            <td>{{ $siswa->nisn ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Nama Siswa</th>
                            <td>{{ $siswa->nama_siswa }}</td>
                        </tr>

                        <tr>
                            <th>Kelas</th>
                            <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Jenis Kelamin</th>
                            <td>{{ $siswa->jenis_kelamin }}</td>
                        </tr>

                        <tr>
                            <th>Email</th>
                            <td>{{ $siswa->email ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>No. HP</th>
                            <td>{{ $siswa->no_hp ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Alamat</th>
                            <td>{{ $siswa->alamat }}</td>
                        </tr>

                        <tr>
                            <th>Username</th>
                            <td>{{ $siswa->user?->username ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Status Akun</th>
                            <td>

                                @if($siswa->user?->status)

                                <span class="badge bg-success">
                                    Aktif
                                </span>

                                @else

                                <span class="badge bg-danger">
                                    Nonaktif
                                </span>

                                @endif

                            </td>
                        </tr>

                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $siswa->created_at->format('d F Y H:i') }}</td>
                        </tr>

                        <tr>
                            <th>Terakhir Diubah</th>
                            <td>{{ $siswa->updated_at->format('d F Y H:i') }}</td>
                        </tr>

</table>
                    </div>

                </div>

            </div>

        </div>

        <div class="card-footer text-end">
            <a href="{{ route('admin.siswa.index') }}"
                class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>
                Kembali

            </a>

        </div>

    </div>

</div>

@endsection