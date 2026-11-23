@extends('layouts.guru')

@section('title', 'Edit Soal - ' . $mapel->nama_mapel)

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-pencil-square me-2"></i>Edit Seluruh Soal: {{ $mapel->nama_mapel }}
            </h3>
            <p class="text-muted mb-0">
                Ubah semua soal untuk mata pelajaran {{ $mapel->nama_mapel }} (Kode: {{ $mapel->kode_mapel ?? '-' }}) - Kelas
                <strong>
                    @if($bankSoal->first()->id_kelas)
                    {{ $bankSoal->first()->kelas->nama_kelas ?? 'Kelas Terpilih' }}
                    @else
                    Semua Kelas
                    @endif
                </strong>.
                Total <strong>{{ $bankSoal->count() }}</strong> soal.
            </p>
        </div>
        <div class="d-flex gap-2">
          
            <a href="{{ route('guru.bank-soal.create', ['id_mapel' => $mapel->id_mapel]) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah Soal
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>{{ session('success') }}
        <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($bankSoal->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-journal-x fs-1 d-block mb-2 text-muted"></i>
            <p class="text-muted mb-0">Belum ada soal untuk mata pelajaran ini.</p>
        </div>
    </div>
    @else

    <form action="{{ route('guru.bank-soal.updateMapel', $mapel->id_mapel) }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($bankSoal as $index => $item)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-circle">#{{ $loop->iteration }}</span>
                    <strong>Soal {{ $loop->iteration }}</strong>
                    @if($item->jenis_soal === 'pilihan_ganda')
                    <span class="badge bg-primary">Pilihan Ganda</span>
                    @elseif($item->jenis_soal === 'benar_salah')
                    <span class="badge bg-warning text-dark">Benar / Salah</span>
                    @else
                    <span class="badge bg-info text-dark">Essay</span>
                    @endif
                </div>
            </div>
            <div class="card-body">

                <input type="hidden" name="soal[{{ $item->id_soal }}][id]" value="{{ $item->id_soal }}">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Kelas Target</label>
                        <input type="text" class="form-control"
                            value="{{ $item->id_kelas ? ($item->kelas->nama_kelas ?? '-') : 'Semua Kelas' }}" disabled>
                        <input type="hidden" name="soal[{{ $item->id_soal }}][id_kelas]" value="{{ $item->id_kelas }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Bobot Nilai <span class="text-danger">*</span></label>
                        <input type="number" name="soal[{{ $item->id_soal }}][bobot]" step="0.01" min="0" max="100"
                            class="form-control" value="{{ old("soal.{$item->id_soal}.bobot", $item->bobot) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Jenis Soal</label>
                        <input type="text" class="form-control" value="{{ ucwords(str_replace('_', ' ', $item->jenis_soal)) }}" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Pertanyaan <span class="text-danger">*</span></label>
                    <textarea name="soal[{{ $item->id_soal }}][pertanyaan]" rows="3"
                        class="form-control">{{ old("soal.{$item->id_soal}.pertanyaan", $item->pertanyaan) }}</textarea>
                </div>

                @if($item->jenis_soal === 'pilihan_ganda')
                <div class="card bg-light border-0 mb-3 p-3">
                    <label class="form-label fw-semibold mb-3"><i class="bi bi-list-check me-1"></i> Pilihan Jawaban</label>
                    @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label)
                    <div class="mb-2 input-group">
                        <span class="input-group-text fw-bold bg-white">{{ $label }}</span>
                        <input type="text" name="soal[{{ $item->id_soal }}][opsi_{{ $key }}]"
                            class="form-control" value="{{ old("soal.{$item->id_soal}.opsi_{$key}", $item->{'opsi_'.$key}) }}">
                    </div>
                    @endforeach
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Jawaban Benar</label>
                        <select name="soal[{{ $item->id_soal }}][jawaban_benar]" class="form-select">
                            @foreach(['A','B','C','D'] as $opt)
                            <option value="{{ $opt }}"
                                {{ old("soal.{$item->id_soal}.jawaban_benar", $item->jawaban_benar) == $opt ? 'selected' : '' }}>
                                Opsi {{ $opt }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @elseif($item->jenis_soal === 'benar_salah')
                <div class="card bg-light border-0 mb-3 p-3">
                    <label class="form-label fw-semibold mb-2"><i class="bi bi-check2-circle me-1"></i> Kunci Jawaban Benar / Salah</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="soal[{{ $item->id_soal }}][jawaban_benar_bs]"
                                id="bsBenar{{ $item->id_soal }}" value="Benar"
                                {{ old("soal.{$item->id_soal}.jawaban_benar_bs", $item->jawaban_benar) == 'Benar' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-success" for="bsBenar{{ $item->id_soal }}">BENAR</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="soal[{{ $item->id_soal }}][jawaban_benar_bs]"
                                id="bsSalah{{ $item->id_soal }}" value="Salah"
                                {{ old("soal.{$item->id_soal}.jawaban_benar_bs", $item->jawaban_benar) == 'Salah' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-danger" for="bsSalah{{ $item->id_soal }}">SALAH</label>
                        </div>
                    </div>
                </div>
                @else
                <div class="card bg-light border-0 mb-3 p-3">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-file-text me-1"></i> Kunci Jawaban / Pedoman Penskoran Essay <small class="text-muted">(Opsional)</small>
                    </label>
                    <textarea name="soal[{{ $item->id_soal }}][kunci_essay]" rows="2" class="form-control"
                        placeholder="Tulis kunci jawaban atau pedoman penskoran essay...">{{ old("soal.{$item->id_soal}.kunci_essay", $item->jawaban_benar) }}</textarea>
                </div>
                @endif

            </div>
        </div>
        @endforeach

        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
            <a href="{{ route('guru.bank-soal.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save me-1"></i> Simpan Semua Perubahan
            </button>
        </div>

    </form>

    @endif

</div>

@endsection
