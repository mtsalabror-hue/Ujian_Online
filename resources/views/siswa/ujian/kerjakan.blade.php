@extends('layouts.siswa-ujian')

@section('title', 'Mengikuti Ujian - ' . $ujian->judul_ujian)

@section('content')

{{-- Gerbang mode terkunci: ujian hanya berjalan dalam layar penuh --}}
<div id="fullscreenGate" class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center bg-dark bg-opacity-75" style="z-index: 2000; display: none;">
    <div class="card shadow-lg border-0" style="max-width: 420px; width: 90%;">
        <div class="card-body text-center p-4">
            <i class="bi bi-arrows-fullscreen fs-1 text-primary d-block mb-3"></i>
            <h5 class="fw-bold">Mode Ujian Terkunci</h5>
            <p class="text-muted mb-4">Ujian harus dikerjakan dalam <strong>layar penuh</strong>.<br>Keluar dari layar penuh tercatat sebagai pelanggaran.</p>
            <button type="button" id="btnFullscreen" class="btn btn-primary fw-bold w-100">
                <i class="bi bi-play-fill me-1"></i> Masuk Layar Penuh & Mulai
            </button>
        </div>
    </div>
</div>

{{-- Layar hitam polos saat pelanggaran: siswa langsung keluar dari soal
     tanpa modal/peringatan — bel 5 detik + getar HP jadi peringatannya,
     lalu dialihkan otomatis ke halaman login --}}
<div id="lockOverlay" class="position-fixed top-0 start-0 w-100 h-100 bg-black" style="z-index: 3000; display: none;"></div>

<div class="container-fluid pb-5">

    {{-- Top Bar --}}
    <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 10px; z-index: 1020;">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2 bg-success text-white rounded">
            <div class="flex-grow-1 text-truncate">
                <h6 class="fw-bold mb-0 text-white text-truncate">{{ $ujian->judul_ujian }}</h6>
                <small class="text-white-50 d-none d-sm-block">{{ $ujian->mataPelajaran->nama_mapel ?? '-' }} | {{ $siswa->nama_siswa ?? 'Siswa' }}</small>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <div class="bg-white text-dark px-2 px-sm-3 py-1 rounded fw-bold shadow-sm" style="font-size: .85rem;">
                    <i class="bi bi-stopwatch text-danger me-1"></i>
                    <span style="font-family: monospace;" id="timerDisplay">00:00:00</span>
                </div>
                <button type="button" class="btn btn-warning fw-bold text-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalSelesai">
                    <i class="bi bi-check-circle-fill me-1"></i> <span class="d-none d-sm-inline">Selesai Ujian</span><span class="d-sm-none">Selesai</span>
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Soal Workspace --}}
        <div class="col-lg-8">
            @foreach($soal as $index => $item)
            @php
            $num = $index + 1;
            $savedAns = $jawaban[$item->id_soal] ?? '';
            @endphp
            <div class="card shadow-sm border-0 soal-card {{ $index === 0 ? '' : 'd-none' }}" id="soalCard_{{ $num }}">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="badge bg-success fs-6">Soal No. {{ $num }} dari {{ $soal->count() }}</span>
                    @if($item->jenis_soal === 'pilihan_ganda')
                    <span class="badge bg-primary">Pilihan Ganda</span>
                    @elseif($item->jenis_soal === 'benar_salah')
                    <span class="badge bg-warning text-dark">Benar / Salah</span>
                    @else
                    <span class="badge bg-info text-dark">Essay</span>
                    @endif
                </div>

                <div class="card-body p-4">
                    {{-- Pertanyaan --}}
                    <div class="fs-5 fw-semibold mb-4 text-dark p-3 bg-light rounded">
                        {!! $item->pertanyaan !!}
                    </div>

                    {{-- Form Jawaban --}}
                    <form class="form-jawaban" data-soal-id="{{ $item->id_soal }}" data-num="{{ $num }}">

                        {{-- Pilihan Ganda --}}
                        @if($item->jenis_soal === 'pilihan_ganda')
                        <div class="d-flex flex-column gap-2 mb-3">
                            @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $k => $lbl)
                            @php $optText = $item->{'opsi_'.$k}; @endphp
                            @if(!empty($optText))
                            <label class="pilihan-label d-flex align-items-center p-3 rounded border cursor-pointer {{ strtoupper($savedAns) === $lbl ? 'border-success bg-success bg-opacity-10' : '' }}"
                                id="label_{{ $item->id_soal }}_{{ $lbl }}">
                                <input type="radio" name="jawaban_{{ $item->id_soal }}" value="{{ $lbl }}"
                                    class="form-check-input me-3 radio-jawaban" data-soal-id="{{ $item->id_soal }}" data-num="{{ $num }}"
                                    {{ strtoupper($savedAns) === $lbl ? 'checked' : '' }}>
                                <span class="fw-bold me-2">{{ $lbl }}.</span>
                                <span>{!! $optText !!}</span>
                            </label>
                            @endif
                            @endforeach
                        </div>

                        {{-- Benar / Salah (opsi mengikuti format soal: True/False atau Benar/Salah) --}}
                        @elseif($item->jenis_soal === 'benar_salah')
                        @php
                            $bsOpsiA = $item->opsi_a ?? 'Benar';
                            $bsOpsiB = $item->opsi_b ?? 'Salah';
                        @endphp
                        <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                            <label class="pilihan-label flex-fill p-3 rounded border text-center cursor-pointer {{ strtolower($savedAns) === strtolower($bsOpsiA) ? 'border-success bg-success bg-opacity-10 text-success fw-bold' : '' }}"
                                id="label_{{ $item->id_soal }}_A">
                                <input type="radio" name="jawaban_{{ $item->id_soal }}" value="{{ $bsOpsiA }}"
                                    class="form-check-input me-2 radio-jawaban" data-soal-id="{{ $item->id_soal }}" data-num="{{ $num }}"
                                    {{ strtolower($savedAns) === strtolower($bsOpsiA) ? 'checked' : '' }}>
                                <i class="bi bi-check-circle me-1"></i> {{ strtoupper($bsOpsiA) }}
                            </label>
                            <label class="pilihan-label flex-fill p-3 rounded border text-center cursor-pointer {{ strtolower($savedAns) === strtolower($bsOpsiB) ? 'border-danger bg-danger bg-opacity-10 text-danger fw-bold' : '' }}"
                                id="label_{{ $item->id_soal }}_B">
                                <input type="radio" name="jawaban_{{ $item->id_soal }}" value="{{ $bsOpsiB }}"
                                    class="form-check-input me-2 radio-jawaban" data-soal-id="{{ $item->id_soal }}" data-num="{{ $num }}"
                                    {{ strtolower($savedAns) === strtolower($bsOpsiB) ? 'checked' : '' }}>
                                <i class="bi bi-x-circle me-1"></i> {{ strtoupper($bsOpsiB) }}
                            </label>
                        </div>

                        {{-- Essay (dengan Quill Rich Text Editor) --}}
                        @else
                        <div class="mb-3">
                            <div class="quill-wrapper border rounded">
                                <div class="quill-editor" id="quill_{{ $item->id_soal }}"
                                    data-soal-id="{{ $item->id_soal }}" data-num="{{ $num }}"></div>
                            </div>
                            {{-- Disembunyikan: penampung HTML dari Quill, tetap dibaca alur simpan otomatis --}}
                            <textarea name="jawaban_{{ $item->id_soal }}" rows="5"
                                class="form-control input-essay d-none" data-soal-id="{{ $item->id_soal }}" data-num="{{ $num }}"
                                placeholder="Tulis jawaban essay Anda di sini...">{{ $savedAns }}</textarea>
                        </div>
                        @endif

                    </form>
                </div>

                <div class="card-footer bg-white d-flex justify-content-between p-3">
                    <button type="button" class="btn btn-secondary btn-prev" data-num="{{ $num }}" {{ $num === 1 ? 'disabled' : '' }}>
                        <i class="bi bi-arrow-left me-1"></i> Sebelumnya
                    </button>

                    @if($num < $soal->count())
                        <button type="button" class="btn btn-primary btn-next" data-num="{{ $num }}">
                            Berikutnya <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                        @else
                        <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalSelesai">
                            <i class="bi bi-check-circle me-1"></i> Selesai
                        </button>
                        @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Right Sidebar: Navigasi Soal --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-grid-3x3-gap me-2"></i>Navigasi Soal</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($soal as $index => $item)
                        @php
                        $num = $index + 1;
                        $hasAns = !empty($jawaban[$item->id_soal]);
                        @endphp
                        <button type="button"
                            class="btn btn-nav-soal {{ $index === 0 ? 'border-2 border-dark' : '' }} {{ $hasAns ? 'btn-success' : 'btn-outline-secondary' }}"
                            id="navSoal_{{ $num }}" data-num="{{ $num }}" style="width: 48px; height: 48px; font-weight: bold;">
                            {{ $num }}
                        </button>
                        @endforeach
                    </div>

                    <div class="d-flex gap-3 small border-top pt-3">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-success" style="width: 14px; height: 14px; display: inline-block;"></span>
                            <span>Sudah Dijawab</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-outline-secondary border" style="width: 14px; height: 14px; display: inline-block;"></span>
                            <span>Belum Dijawab</span>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white">
                    <button type="button" class="btn btn-success w-100 fw-bold py-2" data-bs-toggle="modal" data-bs-target="#modalSelesai">
                        <i class="bi bi-send-check me-1"></i> Selesai & Kirim Ujian
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal Konfirmasi Selesai -->
<div class="modal fade" id="modalSelesai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-question-circle me-2"></i>Konfirmasi Selesai Ujian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-clipboard-check fs-1 text-success d-block mb-3"></i>
                <h5 class="fw-bold">Apakah Anda yakin ingin menyelesaikan ujian ini?</h5>
                <p class="text-muted mb-0">Pastikan semua soal telah Anda periksa dengan seksama.</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali Kerjakan</button>
                <form id="formSelesai" action="{{ route('siswa.ujian.selesai', $ujian->id_ujian) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Ya, Selesaikan Ujian
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Peringatan Anti-Kecurangan -->
<div class="modal fade" id="modalPeringatan" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Peringatan!</h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-shield-exclamation fs-1 text-danger d-block mb-3"></i>
                <h5 class="fw-bold" id="peringatanTitle">Anda keluar dari halaman ujian.</h5>
                <p class="text-muted mb-0" id="peringatanBody"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger fw-bold" id="btnKembaliUjian">
                    <i class="bi bi-arrow-return-left me-1"></i> Kembali Mengerjakan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDikunci" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-lock-fill me-2"></i>Anda Dikunci</h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-ban fs-1 text-danger d-block mb-3"></i>
                <h5 class="fw-bold" id="dikunciJudul">Terlalu banyak keluar dari ujian</h5>
                <p class="text-muted mb-1">Anda dianggap melakukan kecurangan.</p>
                <p class="text-danger fw-bold mb-0" id="dikunciInfo">Akun dikunci selama 5 menit.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('login') }}" class="btn btn-secondary">
                    <i class="bi bi-box-arrow-right me-1"></i> Ke Halaman Login
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    const UJIAN_DATA = {
        totalSoal: parseInt("{{ $soal->count() }}", 10),
        saveUrl: "{{ route('siswa.ujian.jawaban', $ujian->id_ujian) }}",
        pelanggaranUrl: "{{ route('siswa.ujian.pelanggaran', $ujian->id_ujian) }}",
        loginUrl: "{{ route('login') }}",
        csrfToken: "{{ csrf_token() }}",
        sisaDetik: parseInt("{{ $sisaDetik }}", 10)
    };
</script>
<script src="{{ asset('js/ujian.js') }}?v={{ filemtime(public_path('js/ujian.js')) }}"></script>

{{-- Quill Rich Text Editor untuk jawaban essay (CDN, sama seperti Summernote di bank soal) --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<style>
    .quill-wrapper .ql-toolbar { border: none; border-bottom: 1px solid #dee2e6; border-top-left-radius: .375rem; border-top-right-radius: .375rem; background: #f8f9fa; }
    .quill-wrapper .ql-container { border: none; border-bottom-left-radius: .375rem; border-bottom-right-radius: .375rem; font-size: 1rem; }
    .quill-wrapper .ql-editor { min-height: 140px; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Quill === 'undefined') {
        // CDN gagal dimuat -> tampilkan kembali textarea biasa agar siswa tetap bisa menjawab
        document.querySelectorAll('textarea.input-essay.d-none').forEach(function(t) {
            t.classList.remove('d-none');
        });
        return;
    }

    document.querySelectorAll('.quill-editor').forEach(function(el) {
        const soalId = el.getAttribute('data-soal-id');
        const textarea = document.querySelector('textarea.input-essay[data-soal-id="' + soalId + '"]');
        if (!textarea) return;

        const quill = new Quill(el, {
            theme: 'snow',
            placeholder: 'Tulis jawaban essay Anda di sini...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['clean']
                ]
            }
        });

        // Muat jawaban yang sudah tersimpan (teks polos lama maupun HTML baru)
        if (textarea.value) {
            quill.root.innerHTML = textarea.value;
        }

        const syncKeTextarea = function() {
            const kosong = quill.getText().trim() === '';
            const nilaiBaru = kosong ? '' : quill.root.innerHTML;
            if (textarea.value !== nilaiBaru) {
                textarea.value = nilaiBaru;
                // Picu alur simpan-otomatis yang sudah ada (debounce 600ms di ujian.js)
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        quill.on('text-change', syncKeTextarea);
        quill.on('selection-change', function(range) {
            if (range === null) {
                // Editor kehilangan fokus -> simpan langsung seperti 'blur' textarea
                textarea.dispatchEvent(new Event('blur', { bubbles: true }));
            }
        });
    });
});
</script>

@endsection