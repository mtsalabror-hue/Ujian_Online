@extends('layouts.guru')

@section('title', 'Detail Soal')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="card shadow-sm border-0">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-eye me-2"></i>Detail Soal
                    </h4>
                </div>

                <div class="card-body">

                    {{-- Badges Header --}}
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <span class="badge bg-info text-dark fs-6">
                                <i class="bi bi-book me-1"></i>
                                {{ $bankSoal->mataPelajaran->nama_mapel ?? '-' }}
                            </span>
                            @if($bankSoal->jenis_soal === 'pilihan_ganda')
                            <span class="badge bg-primary fs-6">Pilihan Ganda</span>
                            @elseif($bankSoal->jenis_soal === 'benar_salah')
                            <span class="badge bg-warning text-dark fs-6">Benar / Salah</span>
                            @else
                            <span class="badge bg-info text-dark fs-6">Essay</span>
                            @endif
                        </div>
                        <div>
                            <span class="badge bg-secondary fs-6">
                                Bobot: {{ $bankSoal->bobot }}
                            </span>
                        </div>
                    </div>

                    {{-- Pertanyaan --}}
                    <div class="mb-4">
                        <label class="form-label text-muted small">PERTANYAAN</label>
                        <div class="p-3 bg-light rounded fs-5">
                            {!! $bankSoal->pertanyaan !!}
                        </div>
                    </div>

                    {{-- Pilihan Ganda --}}
                    @if($bankSoal->jenis_soal === 'pilihan_ganda')
                    <label class="form-label text-muted small">PILIHAN JAWABAN</label>
                    <div class="d-flex flex-column gap-2 mb-4">
                        @foreach(['A' => $bankSoal->opsi_a, 'B' => $bankSoal->opsi_b, 'C' => $bankSoal->opsi_c, 'D' => $bankSoal->opsi_d] as $opt => $text)
                        @php $isCorrect = strtoupper($bankSoal->jawaban_benar) === $opt; @endphp
                        <div class="d-flex align-items-start p-3 rounded border
                                {{ $isCorrect ? 'border-success bg-success bg-opacity-10' : 'border-light bg-white' }}">
                            <span class="badge me-3 mt-1 {{ $isCorrect ? 'bg-success' : 'bg-secondary' }} fs-6">
                                {{ $opt }}
                            </span>
                            <span class="{{ $isCorrect ? 'fw-bold text-success' : '' }}">
                                {!! $text !!}
                            </span>
                            @if($isCorrect)
                            <span class="ms-auto badge bg-success">
                                <i class="bi bi-check-lg"></i> Jawaban Benar
                            </span>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    {{-- Benar / Salah --}}
                    @elseif($bankSoal->jenis_soal === 'benar_salah')
                    <label class="form-label text-muted small">KUNCI JAWABAN BENAR / SALAH</label>
                    <div class="d-flex gap-3 mb-4">
                        <div class="p-3 rounded border w-50 text-center
                                {{ $bankSoal->jawaban_benar === 'Benar' ? 'border-success bg-success bg-opacity-10 text-success fw-bold' : 'border-light bg-white text-muted' }}">
                            <i class="bi bi-check-circle fs-3 d-block mb-1"></i>
                            BENAR
                            @if($bankSoal->jawaban_benar === 'Benar')
                            <span class="badge bg-success ms-2">Jawaban Benar</span>
                            @endif
                        </div>
                        <div class="p-3 rounded border w-50 text-center
                                {{ $bankSoal->jawaban_benar === 'Salah' ? 'border-danger bg-danger bg-opacity-10 text-danger fw-bold' : 'border-light bg-white text-muted' }}">
                            <i class="bi bi-x-circle fs-3 d-block mb-1"></i>
                            SALAH
                            @if($bankSoal->jawaban_benar === 'Salah')
                            <span class="badge bg-danger ms-2">Jawaban Benar</span>
                            @endif
                        </div>
                    </div>

                    {{-- Essay --}}
                    @else
                    <label class="form-label text-muted small">KUNCI JAWABAN / PEDOMAN PENSKORAN ESSAY</label>
                    <div class="p-3 bg-light rounded mb-4 border border-info">
                        @if($bankSoal->jawaban_benar)
                        {{ $bankSoal->jawaban_benar }}
                        @else
                        <span class="text-muted italic">Tidak ada kunci jawaban spesifik yang ditulis.</span>
                        @endif
                    </div>
                    @endif

                    {{-- Footer Info --}}
                    <div class="text-muted small border-top pt-3">
                        <i class="bi bi-clock me-1"></i>
                        Ditambahkan: {{ $bankSoal->created_at->format('d M Y, H:i') }}
                        &nbsp;|&nbsp;
                        Diperbarui: {{ $bankSoal->updated_at->format('d M Y, H:i') }}
                    </div>

                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                     <a href="{{ route('guru.bank-soal.mapel', ['id_mapel' => $bankSoal->id_mapel, 'id_kelas' => $idKelas]) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                     <a href="{{ route('guru.bank-soal.edit', $bankSoal->id_soal) }}" class="btn btn-warning">
                            <i class="bi bi-pencil-fill me-1"></i> Edit Soal
                        </a>
                     <form id="form-hapus-soal" action="{{ route('guru.bank-soal.destroy', $bankSoal->id_soal) }}"
                        method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapusSoal">
                            <i class="bi bi-trash-fill me-1"></i> Hapus Soal
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>

</div>

{{-- Modal Konfirmasi Hapus Soal --}}
<div class="modal fade" id="modalHapusSoal" tabindex="-1" aria-labelledby="modalHapusSoalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHapusSoalLabel">Hapus Soal?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Soal ini akan dihapus. Soal yang sudah dipakai di ujian dan jawaban siswa terkait soal ini juga akan ikut terhapus.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnKonfirmasiHapus">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btnKonfirmasiHapus').addEventListener('click', function () {
        document.getElementById('form-hapus-soal').submit();
    });
});
</script>

@endsection