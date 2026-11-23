@extends('layouts.guru')

@section('title', 'Atur Peserta Ujian')

@section('content')
<div class="container-fluid">

    {{-- Pilih Ujian --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('guru.ujian.peserta') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Pilih Jadwal Ujian</label>
                    <select name="id_ujian" class="form-select">
                        <option value="">-- Pilih Ujian --</option>
                        @foreach($ujianList as $u)
                        <option value="{{ $u->id_ujian }}" {{ request('id_ujian') == $u->id_ujian ? 'selected' : '' }}>
                            {{ $u->judul_ujian }} — Kelas {{ $u->kelas->nama_kelas ?? '-' }}
                            ({{ \Carbon\Carbon::parse($u->tanggal)->format('d M Y') }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-search me-1"></i> Tampilkan Daftar Peserta
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($ujian)
    @php
    $totalSiswa = $peserta->count();
    $sudahCount = $peserta->filter(function($s) use ($ujian) {
    return $ujian->hasilUjian->where('id_siswa', $s->id_siswa)->isNotEmpty();
    })->count();
    $belumCount = $totalSiswa - $sudahCount;
    @endphp

    {{-- Quick Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-white-50 text-uppercase fw-semibold" style="font-size: 11px;">Total Peserta</small>
                        <h3 class="mb-0 fw-bold">{{ $totalSiswa }}</h3>
                        <small style="font-size: 11px;">Siswa Kelas {{ $ujian->kelas->nama_kelas ?? '-' }}</small>
                    </div>
                    <div class="fs-1 text-white-50"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-white-50 text-uppercase fw-semibold" style="font-size: 11px;">Sudah Mengerjakan</small>
                        <h3 class="mb-0 fw-bold">{{ $sudahCount }}</h3>
                        <small style="font-size: 11px;">Siswa Selesai</small>
                    </div>
                    <div class="fs-1 text-white-50"><i class="bi bi-check-circle-fill"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-dark-50 text-uppercase fw-semibold" style="font-size: 11px;">Belum Mengerjakan</small>
                        <h3 class="mb-0 fw-bold">{{ $belumCount }}</h3>
                        <small style="font-size: 11px;">Siswa Menunggu</small>
                    </div>
                    <div class="fs-1 text-dark-50"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Ujian Aktif --}}
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-calendar-event fs-5 me-1"></i>
            <div>
                <strong>{{ $ujian->judul_ujian }}</strong> |
                Mata Pelajaran: <strong>{{ $ujian->mataPelajaran->nama_mapel ?? '-' }}</strong> |
                Kelas: <strong>{{ $ujian->kelas->nama_kelas ?? '-' }}</strong> |
                Tanggal: <strong>{{ \Carbon\Carbon::parse($ujian->tanggal)->format('d M Y') }}</strong> ({{ substr($ujian->jam_mulai, 0, 5) }} WIB) |
                Durasi: <strong>{{ $ujian->durasi }} Menit</strong>
            </div>
        </div>
    </div>

    {{-- Tabel Peserta --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-people-fill me-2 text-primary"></i>Daftar Siswa Peserta Ujian
            </h6>
            <span class="badge bg-light text-dark border">Total {{ $peserta->count() }} Siswa</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>NIS / Username</th>
                            <th>Nama Lengkap Siswa</th>
                            <th class="text-center">Jenis Kelamin</th>
                            <th class="text-center">Status Pengerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peserta as $siswa)
                        @php
                        $sudahMengerjakan = $ujian->hasilUjian->where('id_siswa', $siswa->id_siswa)->isNotEmpty();
                        @endphp
                        <tr>
                            <td class="text-center fw-semibold text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <code>{{ $siswa->nis }}</code>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $siswa->nama_siswa }}</strong>
                            </td>
                            <td class="text-center">
                                @if($siswa->jenis_kelamin === 'L')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Laki-laki</span>
                                @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Perempuan</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($sudahMengerjakan)
                                <span class="badge bg-success py-2 px-3"><i class="bi bi-check-circle-fill me-1"></i>Sudah Mengerjakan</span>
                                @else
                                <span class="badge bg-warning text-dark py-2 px-3"><i class="bi bi-clock-history me-1"></i>Belum Mengerjakan</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                Tidak ada siswa terdaftar di kelas {{ $ujian->kelas->nama_kelas ?? '-' }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection