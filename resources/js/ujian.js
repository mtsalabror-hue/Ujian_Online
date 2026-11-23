document.addEventListener('DOMContentLoaded', function() {
    if (typeof UJIAN_DATA === 'undefined') return;

    // Kunci navigasi "kembali" browser: siswa tidak bisa keluar dari halaman ujian
    history.pushState(null, null, location.href);
    window.addEventListener('popstate', function() {
        history.pushState(null, null, location.href);
    });

    const totalSoal = UJIAN_DATA.totalSoal;
    const saveUrl = UJIAN_DATA.saveUrl;
    const csrfToken = UJIAN_DATA.csrfToken;
    let remaining = UJIAN_DATA.sisaDetik;

    let currentNum = 1;

    // Switch Soal
    function showSoal(num) {
        document.querySelectorAll('.soal-card').forEach(card => card.classList.add('d-none'));
        const target = document.getElementById('soalCard_' + num);
        if (target) target.classList.remove('d-none');

        document.querySelectorAll('.btn-nav-soal').forEach(btn => btn.classList.remove('border-2', 'border-dark'));
        const navBtn = document.getElementById('navSoal_' + num);
        if (navBtn) navBtn.classList.add('border-2', 'border-dark');

        currentNum = num;
    }

    document.querySelectorAll('.btn-nav-soal').forEach(btn => {
        btn.addEventListener('click', function() {
            showSoal(parseInt(this.getAttribute('data-num'), 10));
        });
    });

    document.querySelectorAll('.btn-next').forEach(btn => {
        btn.addEventListener('click', function() {
            let next = parseInt(this.getAttribute('data-num'), 10) + 1;
            if (next <= totalSoal) showSoal(next);
        });
    });

    document.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', function() {
            let prev = parseInt(this.getAttribute('data-num'), 10) - 1;
            if (prev >= 1) showSoal(prev);
        });
    });

    // Save Answer Function
    function saveAnswer(soalId, jawaban, num) {
        return fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id_soal: soalId,
                    jawaban: jawaban
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.status === 'error') {
                    const formSelesai = document.getElementById('formSelesai');
                    if (formSelesai) formSelesai.submit();
                    return;
                }

                const navBtn = document.getElementById('navSoal_' + num);
                if (navBtn) {
                    if (jawaban && jawaban.trim() !== '') {
                        navBtn.classList.remove('btn-outline-secondary');
                        navBtn.classList.add('btn-success');
                    } else {
                        navBtn.classList.remove('btn-success');
                        navBtn.classList.add('btn-outline-secondary');
                    }
                }
            })
            .catch(err => console.error('Error saving answer:', err));
    }

    // Radio change (PG & Benar/Salah)
    document.querySelectorAll('.radio-jawaban').forEach(radio => {
        radio.addEventListener('change', function() {
            const soalId = this.getAttribute('data-soal-id');
            const num = this.getAttribute('data-num');
            const val = this.value;

            // highlight UI
            document.querySelectorAll(`input[name="jawaban_${soalId}"]`).forEach(r => {
                const label = r.closest('label');
                if (label) label.classList.remove('border-success', 'bg-success', 'bg-opacity-10', 'text-success', 'fw-bold');
            });
            const selectedLabel = this.closest('label');
            if (selectedLabel) selectedLabel.classList.add('border-success', 'bg-opacity-10');

            saveAnswer(soalId, val, num);
        });
    });

    // Essay textarea debounce + blur
    let essayTimeout;
    let pendingEssay = null;
    const saveEssayAnswer = function(element) {
        const soalId = element.getAttribute('data-soal-id');
        const num = element.getAttribute('data-num');
        const val = element.value;
        return saveAnswer(soalId, val, num);
    };

    document.querySelectorAll('.input-essay').forEach(area => {
        area.addEventListener('input', function() {
            pendingEssay = this;
            clearTimeout(essayTimeout);
            essayTimeout = setTimeout(() => {
                pendingEssay = null;
                saveEssayAnswer(this);
            }, 600);
        });

        area.addEventListener('blur', function() {
            if (pendingEssay === this) pendingEssay = null;
            clearTimeout(essayTimeout);
            saveEssayAnswer(this);
        });
    });

    // Simpan jawaban essay yang masih "pending" (belum sempat tersimpan)
    // sebelum pelanggaran dilaporkan, supaya jawaban tidak hilang saat dikunci.
    function flushPendingEssay() {
        if (!pendingEssay) return Promise.resolve();
        clearTimeout(essayTimeout);
        const element = pendingEssay;
        pendingEssay = null;
        return saveEssayAnswer(element).catch(() => {});
    }

    // Timer Countdown
    function updateTimer() {
        const timerDisplay = document.getElementById('timerDisplay');
        const formSelesai = document.getElementById('formSelesai');

        if (remaining <= 0) {
            if (timerDisplay) timerDisplay.innerText = "00:00:00";
            if (formSelesai) formSelesai.submit();
            return;
        }

        let hours = Math.floor(remaining / 3600);
        let minutes = Math.floor((remaining % 3600) / 60);
        let seconds = remaining % 60;

        if (timerDisplay) {
            timerDisplay.innerText =
                (hours < 10 ? '0' + hours : hours) + ':' +
                (minutes < 10 ? '0' + minutes : minutes) + ':' +
                (seconds < 10 ? '0' + seconds : seconds);
        }

        remaining--;
    }

    setInterval(updateTimer, 1000);
    updateTimer();

    // ===== Anti-Kecurangan: Deteksi Keluar Halaman (LANGSUNG KUNCI) =====
    const pelanggaranUrl = UJIAN_DATA.pelanggaranUrl;
    let lastViolation = 0;

    const lockedModal = document.getElementById('modalDikunci');
    const dikunciInfo = document.getElementById('dikunciInfo');

    // Laporkan pelanggaran ke server dan langsung kunci
    function reportViolation() {
        fetch(pelanggaranUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(res => res.json())
            .then(data => {
                if (dikunciInfo) {
                    dikunciInfo.textContent = data.message || 'Akun Anda dikunci karena keluar dari halaman ujian.';
                }
                if (lockedModal) {
                    const bsLock = bootstrap.Modal.getOrCreateInstance(lockedModal);
                    bsLock.show();
                }
            })
            .catch(() => {});
    }

    function handleLeave() {
        // Abaikan kejadian berulang dalam 2 detik
        const now = Date.now();
        if (now - lastViolation < 2000) return;
        lastViolation = now;

        Promise.resolve(flushPendingEssay()).finally(function() {
            reportViolation();
        });
    }

    // Deteksi pindah tab / membuka jendela lain (hanya visibilitychange, bukan blur)
    // blur event dihapus karena menyebabkan false positive (klik taskbar, notifikasi, dll)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) handleLeave();
    });

    // Blokir klik kanan
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // Blokir shortcut DevTools
    document.addEventListener('keydown', function(e) {
        const k = e.key.toUpperCase();
        if (e.key === 'F12' ||
            (e.ctrlKey && e.shiftKey && ['I', 'J', 'C', 'K'].includes(k)) ||
            (e.ctrlKey && k === 'U')) {
            e.preventDefault();
        }
    });

    // Blokir penutupan tab / navigasi keluar
    window.addEventListener('beforeunload', function(e) {
        if (!examSubmitting) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Fullscreen gate
    const gate = document.getElementById('fullscreenGate');
    const btnFS = document.getElementById('btnFullscreen');
    const fsDidukung = !!document.fullscreenEnabled;

    function perluFullscreen() {
        return fsDidukung && !document.fullscreenElement;
    }

    document.addEventListener('fullscreenchange', function() {
        if (perluFullscreen()) {
            gate.style.display = 'flex';
            handleLeave();
        } else {
            gate.style.display = 'none';
        }
    });

    if (btnFS && fsDidukung) {
        btnFS.addEventListener('click', function() {
            document.documentElement.requestFullscreen().catch(() => {});
        });
    }

    // Sembunyikan gate jika sudah fullscreen
    if (document.fullscreenElement) {
        gate.style.display = 'none';
    }
});
