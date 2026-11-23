document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('jenisSoalSelect');
    const secPG  = document.getElementById('sectionPilihanGanda');
    const secBS  = document.getElementById('sectionBenarSalah');
    const secEssay = document.getElementById('sectionEssay');

    if (select && secPG && secBS && secEssay) {
        function toggleSections() {
            const val = select.value;
            secPG.style.display   = (val === 'pilihan_ganda') ? 'block' : 'none';
            secBS.style.display   = (val === 'benar_salah')   ? 'block' : 'none';
            secEssay.style.display = (val === 'essay')         ? 'block' : 'none';
        }
        select.addEventListener('change', toggleSections);
        toggleSections();
    }

    // Inisialisasi Summernote dengan AJAX image upload (gambar disimpan sebagai file, bukan base64)
    if (typeof $ !== 'undefined' && $.fn.summernote) {
        function initSummernote(selector, height, placeholder) {
            $(selector).summernote({
                placeholder: placeholder,
                tabsize: 2,
                height: height,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        var editor = $(this);
                        for (var i = 0; i < files.length; i++) {
                            var formData = new FormData();
                            formData.append('file', files[i]);
                            formData.append('_token', typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
                            $.ajax({
                                url: typeof UPLOAD_URL !== 'undefined' ? UPLOAD_URL : '',
                                method: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                success: function(data) {
                                    editor.summernote('insertImage', data.url);
                                },
                                error: function(xhr) {
                                    var msg = xhr.responseJSON?.message || 'Gagal upload gambar. Pastikan ukuran < 4MB dan format JPG/PNG/GIF.';
                                    alert(msg);
                                }
                            });
                        }
                    }
                }
            });
        }

        initSummernote('.summernote', 240, 'Tulis isi di sini. Bisa masukkan gambar, tabel, atau format teks...');
        initSummernote('.summernote-opsi', 110, 'Isi pilihan ini... (bisa gambar, tabel, atau teks)');
    }
});