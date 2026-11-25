@extends('layouts.admin')

@section('body-class', 'cetak-kartu-page')

@section('title', 'Cetak Kartu Ujian Siswa')

@push('styles')
<style>
    /* Sidebar tetap diam, hanya area kartu yang scroll */
    .cetak-kartu-page .sidebar {
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
    }
    .cetak-kartu-page .main {
        height: 100vh;
        overflow-y: auto;
    }
    @media (max-width: 767.98px) {
        .cetak-kartu-page .main {
            height: auto;
            overflow-y: visible;
        }
        /* Header pengaturan: tombol-tombol membungkus supaya tidak meluber */
        .cetak-kartu-page .card-header {
            flex-wrap: wrap;
            gap: 8px;
        }
        .cetak-kartu-page .card-header > div {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
    }
</style>
@endpush

@section('content')

<link rel="stylesheet" href="{{ asset('css/cetak-kartu.css') }}">

<div class="container-fluid">

    {{-- Card Pengaturan & Kelengkapan Kartu Ujian (Hidden saat Print) --}}
    <div class="card shadow-sm border-0 mb-4 no-print">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h5 class="mb-0 fw-bold fs-6"><i class="bi bi-card-heading me-2"></i>Pengaturan & Kelengkapan Cetak Kartu Ujian</h5>
            <div>
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-sm btn-light me-1">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
                <a href="{{ route('admin.siswa.downloadKartuPdf', request()->query()) }}" class="btn btn-sm btn-danger fw-bold me-1" target="_blank">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
                </a>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-warning fw-bold">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Direct (Print)
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- Form Filter & Pengaturan Teks --}}
            <form action="{{ route('admin.siswa.cetakKartu') }}" method="GET" class="mb-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small mb-1">Pilih Kelas</label>
                        <select name="id_kelas" class="form-select form-select-sm">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($kelasList as $k)
                            <option value="{{ $k->id_kelas }}" {{ $idKelas == $k->id_kelas ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small mb-1">Header Nama Ujian</label>
                        <input type="text" name="nama_ujian" class="form-control form-control-sm"
                            placeholder="Contoh: UJIAN TENGAH SEMESTER GANJIL" value="{{ $namaUjian }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="form-control form-control-sm" value="{{ $tahunAjaran }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">Jabatan Penandatangan</label>
                        <input type="text" name="jabatan_penandatangan" class="form-control form-control-sm"
                            placeholder="Panitia / Kepala Sekolah" value="{{ $jabatanPenandatangan }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">Nama Penandatangan</label>
                        <input type="text" name="nama_penandatangan" class="form-control form-control-sm"
                            placeholder="Nama Lengkap" value="{{ $namaPenandatangan }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">NIP Penandatangan</label>
                        <input type="text" name="nip_penandatangan" class="form-control form-control-sm"
                            placeholder="NIP (opsional)" value="{{ $nipPenandatangan }}">
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                            <i class="bi bi-funnel me-1"></i> Terapkan Pengaturan & Filter Kartu
                        </button>
                    </div>
                </div>
            </form>

            <hr class="my-3 text-muted" style="opacity: 0.15;">

            {{-- Section Upload TTD & Stempel --}}
            <div class="row g-3">
                {{-- Upload TTD --}}
                <div class="col-md-6 border-end">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 text-center">
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="width: 105px; height: 62px; overflow: hidden;">
                                @if(!empty($setting->ttd_image) && file_exists(public_path('storage/' . $setting->ttd_image)))
                                <img src="{{ asset('storage/' . $setting->ttd_image) }}" alt="TTD" style="max-width: 95px; max-height: 52px; object-fit: contain;">
                                @else
                                <div class="text-muted text-center" style="font-size: 10px;">
                                    <i class="bi bi-pen fs-5 d-block mb-1"></i>Belum Ada TTD
                                </div>
                                @endif
                            </div>
                            @if(!empty($setting->ttd_image) && file_exists(public_path('storage/' . $setting->ttd_image)))
                            <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 9px;">
                                <i class="bi bi-check-circle me-1"></i>Tersimpan
                            </span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <form action="{{ route('admin.pengaturan.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <label class="form-label fw-semibold small mb-1"><i class="bi bi-pen me-1"></i>Upload Tanda Tangan (TTD)</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="ttd_image" class="form-control" accept="image/*">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-upload me-1"></i>Upload
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 10px;">Format PNG/JPG transparan. Maks 2MB.</small>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Upload Stempel --}}
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 text-center">
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="width: 105px; height: 62px; overflow: hidden;">
                                @if(!empty($setting->stempel_image) && file_exists(public_path('storage/' . $setting->stempel_image)))
                                <img src="{{ asset('storage/' . $setting->stempel_image) }}" alt="Stempel" style="max-width: 55px; max-height: 55px; object-fit: contain;">
                                @else
                                <div class="text-muted text-center" style="font-size: 10px;">
                                    <i class="bi bi-shield-check fs-5 d-block mb-1"></i>Belum Ada Stempel
                                </div>
                                @endif
                            </div>
                            @if(!empty($setting->stempel_image) && file_exists(public_path('storage/' . $setting->stempel_image)))
                            <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 9px;">
                                <i class="bi bi-check-circle me-1"></i>Tersimpan
                            </span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <form action="{{ route('admin.pengaturan.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <label class="form-label fw-semibold small mb-1"><i class="bi bi-shield-check me-1"></i>Upload Stempel Sekolah</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="stempel_image" class="form-control" accept="image/*">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-upload me-1"></i>Upload
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 10px;">Format PNG bulat transparan disarankan. Maks 2MB.</small>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Banner Ringkasan Hasil (Hidden saat Print) --}}
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h6 class="fw-bold mb-0 text-secondary">
            Menampilkan <span class="text-primary">{{ $siswa->count() }}</span> Kartu Ujian Siswa
        </h6>
        <span class="badge bg-info text-dark">
            Header: <strong>{{ strtoupper($namaUjian) }}</strong> (TA {{ $tahunAjaran }})
        </span>
    </div>

    {{-- Grid Kartu Ujian --}}
    <div class="card-ujian-container">
        @forelse($siswa as $s)
        <div class="card-ujian">
            {{-- Header Kop --}}
            <div class="card-ujian-header">
                @if(!empty($setting->logo) && file_exists(public_path('storage/' . $setting->logo)))
                <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo Sekolah" class="card-ujian-logo" style="width: 48px; height: 48px; max-width: 48px; max-height: 48px; object-fit: contain;">
                @else
                <img src="{{ asset('images/logoo.png') }}" alt="Logo Sekolah" class="card-ujian-logo" style="width: 48px; height: 48px; max-width: 48px; max-height: 48px; object-fit: contain;"
                    onerror="this.src='https://via.placeholder.com/60?text=LOGO'">
                @endif
                <div class="card-ujian-title">
                    <h5>{{ $setting->nama_sekolah ?? 'SMK IT AL-ABROR' }}</h5>
                    <h6>KARTU PESERTA {{ strtoupper($namaUjian) }}</h6>
                    <p>Tahun Ajaran {{ $tahunAjaran }}</p>
                </div>
            </div>

            {{-- Body Biodata --}}
            <div class="card-ujian-body">
                <div class="card-ujian-foto">
                    @if($s->foto && file_exists(public_path('storage/' . $s->foto)))
                    <img src="{{ asset('storage/' . $s->foto) }}" alt="Foto Siswa" style="width: 100%; height: 100%; object-fit: cover; border-radius: 3px;">
                    @else
                    <span>FOTO<br>3x4</span>
                    @endif
                </div>
                <table class="card-ujian-table">
                    <tr>
                        <td class="label">Nama Peserta</td>
                        <td>: <strong>{{ $s->nama_siswa }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">NIS</td>
                        <td>: {{ $s->nis ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">NISN</td>
                        <td>: {{ $s->nisn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kelas</td>
                        <td>: {{ $s->kelas->nama_kelas ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Username</td>
                        <td>: <code class="fw-bold text-dark fs-6">{{ $s->nama_siswa }}</code></td>
                    </tr>
                    <tr>
                        <td class="label">Password</td>
                        <td>: <code class="fw-bold text-dark fs-6">{{ !empty($s->nisn) ? $s->nisn : $s->nis }}</code></td>
                    </tr>
                </table>
            </div>

            {{-- Footer Tanda Tangan --}}
            <div class="card-ujian-footer">
                <div>
                    <span class="text-muted">*Harap simpan kartu ini selama ujian berlangsung.</span>
                </div>
                <div class="card-ujian-sig">
                    <div>{{ $jabatanPenandatangan }},</div>
                    <div style="position: relative; height: 32px; width: 100%;">
                        {{-- Stempel (ditumpuk di sebelah kiri TTD seperti stempel basah asli) --}}
                        @if(!empty($setting->stempel_image) && file_exists(public_path('storage/' . $setting->stempel_image)))
                        <img src="{{ asset('storage/' . $setting->stempel_image) }}" alt="Stempel"
                            style="position: absolute; width: 36px; height: 36px; object-fit: contain; top: -2px; left: 4px; opacity: 0.82; z-index: 1;">
                        @endif
                        {{-- TTD --}}
                        @if(!empty($setting->ttd_image) && file_exists(public_path('storage/' . $setting->ttd_image)))
                        <img src="{{ asset('storage/' . $setting->ttd_image) }}" alt="TTD"
                            style="position: absolute; max-height: 30px; max-width: 95px; object-fit: contain; bottom: 1px; left: 50%; transform: translateX(-50%); z-index: 2;">
                        @endif
                        @if((empty($setting->ttd_image) || !file_exists(public_path('storage/' . $setting->ttd_image))) &&
                        (empty($setting->stempel_image) || !file_exists(public_path('storage/' . $setting->stempel_image))))
                        <div class="card-ujian-sig-space"></div>
                        @endif
                    </div>
                    <div style="position: relative; z-index: 3;">
                        <strong>{{ !empty($namaPenandatangan) ? $namaPenandatangan : '( ................................ )' }}</strong>
                        @if(!empty($nipPenandatangan))
                        <br><small class="text-muted" style="font-size: 8px;">NIP. {{ $nipPenandatangan }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 no-print" style="grid-column: 1 / -1;">
            <i class="bi bi-card-heading fs-1 text-muted d-block mb-2"></i>
            <h5 class="text-muted">Tidak ada siswa ditemukan untuk dicetak kartunya.</h5>
            <p class="text-muted small">Silakan pilih kelas lain pada form filter di atas.</p>
        </div>
        @endforelse
    </div>

</div>

@endsection