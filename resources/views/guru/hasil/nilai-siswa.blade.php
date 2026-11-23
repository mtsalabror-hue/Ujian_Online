@extends('layouts.guru')

@section('title', 'Tinjau Jawaban Siswa')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2"></i>Tinjau Jawaban Siswa</h3>
        <p class="text-muted mb-0">Periksa jawaban siswa dan beri nilai pada soal essay.</p>
    </div>

    {{-- Info Ujian & Siswa --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h5 class="fw-bold mb-1">{{ $ujian->judul_ujian }}</h5>
                <small class="text-muted">
                    {{ $ujian->mataPelajaran->nama_mapel ?? '-' }} |
                    Kelas {{ $ujian->kelas->nama_kelas ?? '-' }} |
                    {{ \Carbon\Carbon::parse($ujian->tanggal)->format('d M Y') }}
                </small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div>
                    <div class="fw-bold">{{ $siswa->nama_siswa ?? '-' }}</div>
                    <small class="text-muted">NIS: {{ $siswa->nis ?? '-' }} | {{ $siswa->kelas->nama_kelas ?? '-' }}</small>
                </div>
                <div class="text-center bg-primary bg-opacity-10 border border-primary rounded px-4 py-2">
                    <small class="text-muted d-block">Nilai Akhir</small>
                    <span class="fs-4 fw-bold text-primary">{{ number_format($hasil->nilai, 1) }}</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('guru.hasil.simpan', [$ujian->id_ujian, $siswa->id_siswa]) }}" method="POST">
        @csrf

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-1"></i>Daftar Jawaban ({{ $ujian->bankSoal->count() }} Soal)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th>Pertanyaan</th>
                                <th style="min-width: 220px;">Jawaban Siswa</th>
                                <th style="min-width: 200px;">Kunci Jawaban</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="min-width: 120px;">Skor Essay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ujian->bankSoal as $index => $item)
                            @php
                            $jwb = $jawaban[$item->id_soal] ?? null;
                            @endphp
                            <tr>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold mb-1">{!! $item->pertanyaan !!}</div>
                                    @if($item->jenis_soal === 'pilihan_ganda')
                                    <span class="badge bg-primary">Pilihan Ganda</span>
                                    <small class="text-muted d-block mt-1">
                                        A. {!! $item->opsi_a !!} | B. {!! $item->opsi_b !!} |
                                        C. {!! $item->opsi_c !!} | D. {!! $item->opsi_d !!}
                                    </small>
                                    <small class="text-success d-block">Kunci: {{ $item->jawaban_benar }}</small>
                                    @elseif($item->jenis_soal === 'benar_salah')
                                    <span class="badge bg-warning text-dark">Benar / Salah</span>
                                    <small class="text-success d-block mt-1">Kunci: {{ $item->jawaban_benar }}</small>
                                    @else
                                    <span class="badge bg-info text-dark">Essay (Bobot: {{ $item->bobot }})</span>
                                    @endif
                                    <small class="text-muted d-block mt-1">Bobot: {{ $item->bobot }}</small>
                                </td>
                                <td>
                                    @if($jwb)
                                    @if($item->jenis_soal === 'essay')
                                    <div class="bg-light border rounded p-2" style="white-space: pre-wrap;">
                                        {!! $jwb->jawaban ?? 'Tidak dijawab' !!}
                                    </div>
                                    @else
                                    <span class="badge bg-dark">{{ $jwb->jawaban ?: '-' }}</span>
                                    @endif
                                    @else
                                    <em class="text-muted">Tidak dijawab</em>
                                    @endif
                                </td>
                                <td>
                                    @if($item->jenis_soal === 'essay')
                                        @if(trim(strip_tags($item->jawaban_benar ?? '')) !== '')
                                        <div class="border rounded p-2 bg-success-subtle" style="white-space: pre-wrap;">{{ $item->jawaban_benar }}</div>
                                        @else
                                        <span class="badge bg-warning text-dark">Belum diisi</span>
                                        @endif
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($jwb && $jwb->status_benar)
                                    <span class="badge bg-success">Benar</span>
                                    @elseif($jwb && $item->jenis_soal === 'essay' && $jwb->skor !== null)
                                    <span class="badge bg-info">Essay: {{ $jwb->skor }}</span>
                                    @elseif($jwb)
                                    <span class="badge bg-danger">Salah</span>
                                    @else
                                    <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->jenis_soal === 'essay')
                                    @php $auto = $saran[$item->id_soal] ?? null; @endphp
                                    <input type="number" step="0.01" min="0" max="{{ $item->bobot }}"
                                        name="skor[{{ $item->id_soal }}]"
                                        class="form-control form-control-sm text-center"
                                        value="{{ $jwb && $jwb->skor !== null ? $jwb->skor : ($auto['skor'] ?? '') }}"
                                        placeholder="0-{{ $item->bobot }}">
                                    @if($auto)
                                    <small class="d-block text-muted mt-1">
                                        <i class="bi bi-stars"></i> Otomatis: {{ $auto['skor'] }}
                                        (kemiripan {{ $auto['kemiripan'] }}%, {{ $auto['kata_cocok'] }} kata cocok).
                                        Periksa dulu sebelum simpan.
                                    </small>
                                    @endif
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Ujian ini tidak memiliki soal.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <a href="{{ route('guru.hasil.peserta', ['id_ujian' => $ujian->id_ujian]) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="bi bi-save me-1"></i> Simpan Penilaian
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
