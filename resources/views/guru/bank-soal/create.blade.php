@extends('layouts.guru')

@section('title', 'Tambah Soal')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-plus-circle me-2"></i>Tambah Soal Baru
            </h4>
        </div>

        <div class="card-body">

            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('guru.bank-soal.store') }}" method="POST">
                @csrf

                <div class="row">
                    {{-- Mata Pelajaran --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="id_mapel" class="form-select @error('id_mapel') is-invalid @enderror">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mataPelajaran as $mapel)
                            <option value="{{ $mapel->id_mapel }}"
                                {{ old('id_mapel', $selectedMapelId ?? '') == $mapel->id_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_mapel')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kelas --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Kelas Target</label>
                        <select name="id_kelas" class="form-select @error('id_kelas') is-invalid @enderror">
                            <option value="">-- Semua / Pilih Kelas --</option>
                            @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id_kelas }}"
                                {{ old('id_kelas', $selectedKelasId ?? '') == $kelas->id_kelas ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jenis Soal --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Jenis Soal <span class="text-danger">*</span></label>
                        <select name="jenis_soal" id="jenisSoalSelect" class="form-select @error('jenis_soal') is-invalid @enderror">
                            <option value="pilihan_ganda" {{ old('jenis_soal', 'pilihan_ganda') == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda</option>
                            <option value="benar_salah" {{ old('jenis_soal') == 'benar_salah' ? 'selected' : '' }}>Benar / Salah</option>
                            <option value="essay" {{ old('jenis_soal') == 'essay' ? 'selected' : '' }}>Essay</option>
                        </select>
                        @error('jenis_soal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Pertanyaan (Quill Rich Text Editor) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pertanyaan <span class="text-danger">*</span></label>
                    <div class="quill-wrapper border rounded @error('pertanyaan') border-danger @enderror">
                        <div id="quillPertanyaan"></div>
                    </div>
                    <textarea name="pertanyaan" id="pertanyaanInput" rows="5"
                        class="form-control d-none @error('pertanyaan') is-invalid @enderror"
                        placeholder="Tulis pertanyaan di sini...">{{ old('pertanyaan') }}</textarea>
                    @error('pertanyaan')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Section Pilihan Ganda --}}
                <div id="sectionPilihanGanda">
                    <div class="card bg-light border-0 mb-3 p-3">
                        <label class="form-label fw-semibold mb-3">
                            <i class="bi bi-list-check me-1"></i> Pilihan Jawaban <span class="text-danger">*</span>
                        </label>
                        @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label)
                        <div class="mb-3">
                            <span class="fw-bold d-inline-block mb-1">Opsi {{ $label }}</span>
                            <textarea name="opsi_{{ $key }}" class="form-control summernote-opsi summernote"
                                placeholder="Pilihan {{ $label }} (bisa berisi gambar, tabel, atau teks)">{{ old('opsi_'.$key) }}</textarea>
                            @error('opsi_'.$key)
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @endforeach

                        <div class="mt-3">
                            <label class="form-label fw-semibold">Jawaban Benar <span class="text-danger">*</span></label>
                            <select name="jawaban_benar" class="form-select @error('jawaban_benar') is-invalid @enderror">
                                <option value="">-- Pilih Jawaban Benar --</option>
                                @foreach(['A','B','C','D'] as $opt)
                                <option value="{{ $opt }}" {{ old('jawaban_benar') == $opt ? 'selected' : '' }}>
                                    Opsi {{ $opt }}
                                </option>
                                @endforeach
                            </select>
                            @error('jawaban_benar')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section Benar / Salah --}}
                <div id="sectionBenarSalah" style="display: none;">
                    <div class="card bg-light border-0 mb-3 p-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="bi bi-check2-circle me-1"></i> Kunci Jawaban Benar / Salah <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jawaban_benar_bs" id="bsBenar" value="Benar"
                                    {{ old('jawaban_benar_bs', 'Benar') == 'Benar' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-success" for="bsBenar">
                                    <i class="bi bi-check-circle me-1"></i> BENAR
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jawaban_benar_bs" id="bsSalah" value="Salah"
                                    {{ old('jawaban_benar_bs') == 'Salah' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-danger" for="bsSalah">
                                    <i class="bi bi-x-circle me-1"></i> SALAH
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Essay --}}
                <div id="sectionEssay" style="display: none;">
                    <div class="card bg-light border-0 mb-3 p-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="bi bi-file-text me-1"></i> Kunci Jawaban / Pedoman Penskoran Essay <small class="text-muted">(Opsional)</small>
                        </label>
                        <textarea name="kunci_essay" rows="3" class="form-control"
                            placeholder="Tulis kunci jawaban atau pedoman penskoran essay...">{{ old('kunci_essay') }}</textarea>
                    </div>
                </div>

                {{-- Bobot --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Bobot Nilai <span class="text-danger">*</span></label>
                    <input type="number" name="bobot" step="0.01" min="0" max="100"
                        class="form-control @error('bobot') is-invalid @enderror"
                        value="{{ old('bobot', 1) }}"
                        placeholder="Contoh: 1.0">
                    @error('bobot')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <div class="text-end">

                    <a href="{{ route('guru.bank-soal.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Soal
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

{{-- Summernote Rich Text Editor CDN --}}
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<link rel="stylesheet" href="{{ asset('css/bank-soal.css') }}">
<script>
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const UPLOAD_URL = '{{ route("guru.bank-soal.uploadImage") }}';
</script>
<script src="{{ asset('js/bank-soal.js') }}"></script>

{{-- Quill Rich Text Editor untuk field Pertanyaan (CDN, pola sama seperti jawaban essay siswa) --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<style>
    .quill-wrapper .ql-toolbar { border: none; border-bottom: 1px solid #dee2e6; border-top-left-radius: .375rem; border-top-right-radius: .375rem; background: #f8f9fa; }
    .quill-wrapper .ql-container { border: none; border-bottom-left-radius: .375rem; border-bottom-right-radius: .375rem; font-size: 1rem; }
    .quill-wrapper .ql-editor { min-height: 160px; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var textarea = document.getElementById('pertanyaanInput');
    var editorEl = document.getElementById('quillPertanyaan');
    if (!textarea || !editorEl) return;

    if (typeof Quill === 'undefined') {
        // CDN gagal dimuat -> tampilkan kembali textarea biasa
        textarea.classList.remove('d-none');
        editorEl.closest('.quill-wrapper').style.display = 'none';
        return;
    }

    var quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: 'Tulis pertanyaan di sini...',
        modules: {
            toolbar: {
                container: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ],
                handlers: {
                    image: function() {
                        var input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/*');
                        input.click();
                        input.onchange = function() {
                            var file = input.files[0];
                            if (!file) return;
                            var formData = new FormData();
                            formData.append('file', file);
                            formData.append('_token', typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
                            fetch(typeof UPLOAD_URL !== 'undefined' ? UPLOAD_URL : '', {
                                method: 'POST',
                                body: formData,
                                headers: { 'Accept': 'application/json' }
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                var range = quill.getSelection(true);
                                quill.insertEmbed(range.index, 'image', data.url);
                            })
                            .catch(function() {
                                alert('Gagal upload gambar. Pastikan ukuran < 4MB dan format JPG/PNG/GIF.');
                            });
                        };
                    }
                }
            }
        }
    });

    // Muat isi lama (old input / data soal saat edit)
    if (textarea.value) {
        quill.root.innerHTML = textarea.value;
    }

    var syncKeTextarea = function() {
        textarea.value = quill.getText().trim() === '' ? '' : quill.root.innerHTML;
    };
    quill.on('text-change', syncKeTextarea);
    var form = textarea.closest('form');
    if (form) form.addEventListener('submit', syncKeTextarea);
});
</script>

@endsection