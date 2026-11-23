@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">

    <div class="mb-3">
        <h3 class="fw-bold">Dashboard Admin</h3>
        <p class="text-muted">
            Selamat datang,
            <strong>{{ auth()->user()->username }}</strong>. Berikut ringkasan data sistem.
        </p>
    </div>

    <div class="row">

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <small class="text-white-50">Total Guru</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $totalGuru }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body">
                    <small class="text-white-50">Total Siswa</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $totalSiswa }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-warning text-dark">
                <div class="card-body">
                    <small class="text-dark-50">Total Kelas</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $totalKelas }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body">
                    <small class="text-white-50">Total Ujian</small>
                    <h2 class="fw-bold mt-2 mb-0">{{ $totalUjian }}</h2>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold">Ringkasan Sistem</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">
                Kelola data guru, siswa, kelas, dan ujian melalui menu di samping kiri.
            </p>
        </div>
    </div>

</div>

@endsection