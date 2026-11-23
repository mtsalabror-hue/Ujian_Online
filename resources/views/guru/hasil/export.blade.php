@extends('layouts.guru')

@section('title', 'Cetak / Export Nilai')

@push('styles')
<style>
    /* Cetak: yang tercetak adalah dokumen yang sama dengan file Word yang di-download
       (kop, info, tabel nilai, tanggal, tanda tangan) — bukan halaman dashboard. */
    .print-only { display: none; }
    @media print {
        @page { size: A4 portrait; margin: 12mm 14mm; }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Sembunyikan semua elemen UI web */
        .no-print, .sidebar, nav, footer { display: none !important; }

        .wrapper, .main, .content {
            display: block !important;
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .print-only { display: block !important; }
        .print-only table { width: 100%; border-collapse: collapse; }
        .print-only th, .print-only td { border: 1px solid #333; padding: 6px 8px; }
        .print-only thead th { background: #eee !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="mb-4 no-print">
        <h3 class="fw-bold mb-1"><i class="bi bi-printer me-2"></i>Cetak / Export Nilai</h3>
        <p class="text-muted mb-0">Export nilai per siswa atau per mata pelajaran ke Excel (.xlsx) atau Word (.docx).</p>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show no-print">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Tab: Per Siswa | Per Mata Pelajaran --}}
    <ul class="nav nav-tabs mb-4 no-print" id="exportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('id_ujian') ? '' : 'active' }}" id="tab-siswa" data-bs-toggle="tab" data-bs-target="#pane-siswa" type="button" role="tab" aria-controls="pane-siswa" aria-selected="{{ request('id_ujian') ? 'false' : 'true' }}">
                <i class="bi bi-person me-1"></i> Per Siswa
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('id_ujian') ? 'active' : '' }}" id="tab-mapel" data-bs-toggle="tab" data-bs-target="#pane-mapel" type="button" role="tab" aria-controls="pane-mapel" aria-selected="{{ request('id_ujian') ? 'true' : 'false' }}">
                <i class="bi bi-journal-text me-1"></i> Per Mata Pelajaran
            </button>
        </li>
    </ul>

    <div class="tab-content" id="exportTabsContent">

        {{-- ==================== TAB PER SISWA ==================== --}}
        <div class="tab-pane fade {{ request('id_ujian') ? '' : 'show active' }}" id="pane-siswa" role="tabpanel" aria-labelledby="tab-siswa">

            {{-- Pilih Siswa --}}
            <div class="card shadow-sm border-0 mb-4 no-print">
                <div class="card-body">
                    <form action="{{ route('guru.hasil.export') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Pilih Siswa</label>
                            <select name="id_siswa" class="form-select">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($siswaList as $s)
                                <option value="{{ $s->id_siswa }}" {{ request('id_siswa') == $s->id_siswa ? 'selected' : '' }}>
                                    {{ $s->nama_siswa }} — {{ $s->kelas->nama_kelas ?? '-' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-eye me-1"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($siswa)

            {{-- Preview Data --}}
            <div class="card shadow-sm border-0 mb-4 no-print">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-table me-1"></i>
                        Preview: {{ $siswa->nama_siswa }}
                        <span class="text-muted fw-normal">
                            ({{ $siswa->kelas->nama_kelas ?? '-' }})
                        </span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Mata Pelajaran</th>
                                    <th class="text-center">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $row['mapel'] }}</td>
                                    <td class="text-center fw-bold {{ $row['nilai'] >= 70 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($row['nilai'], 1) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Belum ada data.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Export Button --}}
            <div class="card shadow-sm border-0 no-print">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-0 bg-success bg-opacity-10 text-center p-4 h-100">
                                <i class="bi bi-file-earmark-excel fs-1 text-success mb-3"></i>
                                <h5 class="fw-bold text-success mb-1">Excel</h5>
                                <p class="text-muted small mb-3">Format spreadsheet (.xlsx) untuk analisis data lebih lanjut.</p>
                                <a href="{{ route('guru.hasil.export.excel') }}?id_siswa={{ request('id_siswa') }}"
                                    class="btn btn-success mt-auto">
                                    <i class="bi bi-download me-1"></i> Download Excel
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-primary bg-opacity-10 text-center p-4 h-100">
                                <i class="bi bi-file-earmark-word fs-1 text-primary mb-3"></i>
                                <h5 class="fw-bold text-primary mb-1">Word</h5>
                                <p class="text-muted small mb-3">Format dokumen (.docx) siap cetak dan masih bisa diedit.</p>
                                <a href="{{ route('guru.hasil.export.word') }}?id_siswa={{ request('id_siswa') }}"
                                    class="btn btn-primary mt-auto">
                                    <i class="bi bi-download me-1"></i> Download Word
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-secondary bg-opacity-10 text-center p-4 h-100">
                                <i class="bi bi-printer fs-1 text-secondary mb-3"></i>
                                <h5 class="fw-bold text-secondary mb-1">Cetak</h5>
                                <p class="text-muted small mb-3">Cetak langsung dokumen yang sama seperti file Word.</p>
                                <button type="button" onclick="window.print()" class="btn btn-secondary mt-auto">
                                    <i class="bi bi-printer me-1"></i> Cetak Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokumen cetak: isinya sama persis dengan file Word yang di-download --}}
            <div class="print-only">
                <div class="text-center mb-3">
                    <h4 class="fw-bold mb-0">{{ $setting->nama_sekolah ?? 'MTs Al-Abror' }}</h4>
                    <p class="mb-0">Daftar Nilai Siswa</p>
                </div>

                <div class="mb-3">
                    <p class="mb-0"><strong>Nama Siswa</strong> : {{ $siswa->nama_siswa }}</p>
                    <p class="mb-0"><strong>Kelas</strong> : {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
                    <p class="mb-0"><strong>NIS</strong> : {{ $siswa->nis ?? '-' }}</p>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No</th>
                            <th>Mata Pelajaran</th>
                            <th style="width: 100px; text-align: center;">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $row['mapel'] }}</td>
                            <td class="text-center fw-bold">{{ number_format($row['nilai'], 1) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <p class="mt-3">Dicetak pada {{ now()->translatedFormat('d F Y') }}</p>

                <div class="text-end mt-4">
                    <p class="mb-0">Guru Mata Pelajaran</p>
                    <br><br><br>
                    <p class="mb-0">( ______________________ )</p>
                </div>
            </div>

            @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-arrow-up-circle fs-2 d-block mb-2"></i>
                Pilih siswa di atas untuk menampilkan preview dan opsi export.
            </div>
            @endif

        </div>

        {{-- ==================== TAB PER MATA PELAJARAN ==================== --}}
        <div class="tab-pane fade {{ request('id_ujian') ? 'show active' : '' }}" id="pane-mapel" role="tabpanel" aria-labelledby="tab-mapel">

            {{-- Pilih Ujian --}}
            <div class="card shadow-sm border-0 mb-4 no-print">
                <div class="card-body">
                    <form action="{{ route('guru.hasil.export') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Pilih Ujian (Mata Pelajaran — Kelas)</label>
                            <select name="id_ujian" class="form-select">
                                <option value="">-- Pilih Ujian --</option>
                                @foreach($ujianList as $u)
                                <option value="{{ $u->id_ujian }}" {{ request('id_ujian') == $u->id_ujian ? 'selected' : '' }}>
                                    {{ $u->judul_ujian }} — {{ $u->mataPelajaran->nama_mapel ?? '-' }} — {{ $u->kelas->nama_kelas ?? '-' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-eye me-1"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($ujian)

            {{-- Statistik ringkas --}}
            <div class="row g-3 mb-4 no-print">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 text-center p-3 h-100">
                        <div class="text-muted small">Jumlah Siswa</div>
                        <div class="fs-4 fw-bold">{{ $stats['jumlah'] }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 text-center p-3 h-100">
                        <div class="text-muted small">Rata-rata</div>
                        <div class="fs-4 fw-bold">{{ number_format($stats['rata'], 1) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 text-center p-3 h-100">
                        <div class="text-muted small">Nilai Tertinggi</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($stats['max'], 1) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 text-center p-3 h-100">
                        <div class="text-muted small">Nilai Terendah</div>
                        <div class="fs-4 fw-bold text-danger">{{ number_format($stats['min'], 1) }}</div>
                    </div>
                </div>
            </div>

            {{-- Preview Data --}}
            <div class="card shadow-sm border-0 mb-4 no-print">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-table me-1"></i>
                        Preview: {{ $ujian->mataPelajaran->nama_mapel ?? '-' }}
                        <span class="text-muted fw-normal">
                            ({{ $ujian->kelas->nama_kelas ?? '-' }} — {{ $ujian->judul_ujian }})
                        </span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Nama Siswa</th>
                                    <th class="text-center">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hasil as $h)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $h->siswa->nama_siswa ?? '-' }}</td>
                                    <td class="text-center fw-bold {{ $h->nilai >= 70 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($h->nilai, 1) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Belum ada siswa yang mengerjakan ujian ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Export Button --}}
            <div class="card shadow-sm border-0 no-print">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-0 bg-success bg-opacity-10 text-center p-4 h-100">
                                <i class="bi bi-file-earmark-excel fs-1 text-success mb-3"></i>
                                <h5 class="fw-bold text-success mb-1">Excel</h5>
                                <p class="text-muted small mb-3">Format spreadsheet (.xlsx) untuk analisis data lebih lanjut.</p>
                                <a href="{{ route('guru.hasil.export.mapel.excel') }}?id_ujian={{ request('id_ujian') }}"
                                    class="btn btn-success mt-auto">
                                    <i class="bi bi-download me-1"></i> Download Excel
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-primary bg-opacity-10 text-center p-4 h-100">
                                <i class="bi bi-file-earmark-word fs-1 text-primary mb-3"></i>
                                <h5 class="fw-bold text-primary mb-1">Word</h5>
                                <p class="text-muted small mb-3">Format dokumen (.docx) siap cetak dan masih bisa diedit.</p>
                                <a href="{{ route('guru.hasil.export.mapel.word') }}?id_ujian={{ request('id_ujian') }}"
                                    class="btn btn-primary mt-auto">
                                    <i class="bi bi-download me-1"></i> Download Word
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-secondary bg-opacity-10 text-center p-4 h-100">
                                <i class="bi bi-printer fs-1 text-secondary mb-3"></i>
                                <h5 class="fw-bold text-secondary mb-1">Cetak</h5>
                                <p class="text-muted small mb-3">Cetak langsung dokumen yang sama seperti file Word.</p>
                                <button type="button" onclick="window.print()" class="btn btn-secondary mt-auto">
                                    <i class="bi bi-printer me-1"></i> Cetak Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokumen cetak: isinya sama persis dengan file Word yang di-download --}}
            <div class="print-only">
                <div class="text-center mb-3">
                    <h4 class="fw-bold mb-0">{{ $setting->nama_sekolah ?? 'MTs Al-Abror' }}</h4>
                    <p class="mb-0">Daftar Nilai Mata Pelajaran</p>
                </div>

                <div class="mb-3">
                    <p class="mb-0"><strong>Mata Pelajaran</strong> : {{ $ujian->mataPelajaran->nama_mapel ?? '-' }}</p>
                    <p class="mb-0"><strong>Kelas</strong> : {{ $ujian->kelas->nama_kelas ?? '-' }}</p>
                    <p class="mb-0"><strong>Ujian</strong> : {{ $ujian->judul_ujian }}</p>
                    <p class="mb-0"><strong>Tanggal</strong> : {{ $ujian->tanggal ? \Illuminate\Support\Carbon::parse($ujian->tanggal)->translatedFormat('d F Y') : '-' }}</p>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No</th>
                            <th>Nama Siswa</th>
                            <th style="width: 100px; text-align: center;">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hasil as $h)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $h->siswa->nama_siswa ?? '-' }}</td>
                            <td class="text-center fw-bold">{{ number_format($h->nilai, 1) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    <p class="mb-0"><strong>Jumlah Siswa</strong> : {{ $stats['jumlah'] }} siswa</p>
                    <p class="mb-0"><strong>Rata-rata</strong> : {{ number_format($stats['rata'], 1) }}</p>
                    <p class="mb-0"><strong>Nilai Tertinggi</strong> : {{ number_format($stats['max'], 1) }}</p>
                    <p class="mb-0"><strong>Nilai Terendah</strong> : {{ number_format($stats['min'], 1) }}</p>
                </div>

                <p class="mt-3">Dicetak pada {{ now()->translatedFormat('d F Y') }}</p>

                <div class="text-end mt-4">
                    <p class="mb-0">Guru Mata Pelajaran</p>
                    <br><br><br>
                    <p class="mb-0">( ______________________ )</p>
                </div>
            </div>

            @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-arrow-up-circle fs-2 d-block mb-2"></i>
                Pilih ujian di atas untuk menampilkan preview dan opsi export.
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
