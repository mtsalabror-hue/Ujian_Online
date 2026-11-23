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
    // Sisa waktu dihitung server dari jadwal (jam_mulai + durasi) dan dikirim
    // sebagai sisaDetik saat halaman dimuat. Countdown di sini TIDAK mengurangi
    // penghitung tiap detik (setInterval bisa meleset / dijeda browser saat tab
    // disembunyikan), melainkan dihitung dari waktu nyata yang berlalu
    // (Date.now()) — jadi tampilan selalu sinkron dengan batas akhir jadwal.
    const sisaAwalDetik = UJIAN_DATA.sisaDetik;
    const mulaiHitungMs = Date.now();
    function sisaDetikSekarang() {
        return sisaAwalDetik - Math.floor((Date.now() - mulaiHitungMs) / 1000);
    }

    let currentNum = 1;

    // Penanda submit sah. Dipakai beforeunload: dialog peringatan HANYA
    // muncul bila siswa keluar di tengah ujian, bukan saat ujian selesai
    // dengan benar (klik "Selesai" / waktu habis).
    let examSubmitting = false;

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
                    if (formSelesai) {
                        examSubmitting = true;
                        formSelesai.submit();
                    }
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
        const remaining = sisaDetikSekarang();

        if (remaining <= 0) {
            if (timerDisplay) timerDisplay.innerText = "00:00:00";
            if (formSelesai) {
                examSubmitting = true;
                formSelesai.submit();
            }
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
    }

    setInterval(updateTimer, 1000);
    updateTimer();

    // ===== Anti-Kecurangan: Deteksi Keluar Halaman (LANGSUNG KELUAR) =====
    const pelanggaranUrl = UJIAN_DATA.pelanggaranUrl;
    const loginUrl = UJIAN_DATA.loginUrl;
    let lastViolation = 0;
    let sudahKeluar = false;

    // ===== Cegah layar HP mati sendiri selama ujian (Wake Lock) =====
    // Layar HP yang mati (kunci otomatis) membuat halaman "tersembunyi"
    // sehingga dulu langsung dihitung pelanggaran. Wake Lock meminta
    // browser menjaga layar tetap menyala selama ujian berjalan.
    // Tidak semua browser mendukung — bila gagal, abaikan saja, karena
    // masa tenggang di visibilitychange di bawah jadi pengaman kedua.
    let wakeLock = null;
    async function requestWakeLock() {
        try {
            if ('wakeLock' in navigator) {
                wakeLock = await navigator.wakeLock.request('screen');
            }
        } catch (e) { /* tidak didukung / ditolak — lanjut tanpa itu */ }
    }
    // Minta saat ujian dimulai
    requestWakeLock();

    // Keluarkan siswa LANGSUNG tanpa modal/peringatan "Kembali ke Halaman
    // Login": layar jadi hitam polos (soal langsung tertutup), sirene 10 detik
    // + getar HP berbunyi, lalu dialihkan otomatis ke halaman login
    // setelah sirene selesai. Dijaga sekali jalan (flag sudahKeluar).
    function keluarLangsung() {
        if (sudahKeluar) return;
        sudahKeluar = true;
        const overlay = document.getElementById('lockOverlay');
        if (overlay) overlay.style.display = 'block';
        setTimeout(function() {
            window.location.href = loginUrl;
        }, 10000);
    }

    // Laporkan pelanggaran ke server dan langsung keluar.
    // Pakai sendBeacon dulu: beacon dijamin terkirim walau halaman sudah
    // di-background-kan (mis. siswa pencet tombol Home HP) atau ditutup —
    // fetch biasa bisa keburu dibatalkan browser sebelum sampai ke server.
    // CSRF dikirim sebagai field _token (Laravel menerimanya).
    function reportViolation() {
        let beaconQueued = false;
        try {
            if (navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('_token', csrfToken);
                beaconQueued = navigator.sendBeacon(pelanggaranUrl, formData);
            }
        } catch (e) { beaconQueued = false; }

        if (beaconQueued) {
            keluarLangsung();
            return;
        }

        fetch(pelanggaranUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(() => {
                keluarLangsung();
            })
            .catch(() => {
                // Walau laporan gagal, tetap keluarkan siswa.
                keluarLangsung();
            });
    }

    // ===== Bunyi bel (5 detik) saat terdeteksi keluar halaman =====
    // Memakai Web Audio API (tanpa file audio). Bel TIDAK bunyi bila HP
    // dalam mode senyap/getar atau volume dimatikan — itu batasan HP-nya,
    // bukan aplikasi. AudioContext harus dibuka dulu oleh interaksi siswa
    // (syarat browser); siswa sudah berinteraksi saat mengerjakan soal.
    let audioCtx = null;

    function ensureAudio() {
        try {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            if (!audioCtx) audioCtx = new AC();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        } catch (e) { /* abaikan bila audio tidak didukung */ }
    }

    // Buka kunci audio saat siswa pertama kali menyentuh layar / klik
    document.addEventListener('pointerdown', ensureAudio, { once: true });

    function playBell() {
        try {
            ensureAudio();
            if (!audioCtx) return;
            // Sirene alarm (seperti video TikTok "Allarme della Sirena"):
            // nada naik-turun 700 -> 1400 Hz tiap 1 detik, berbunyi
            // selama 10 detik.
            const DURASI = 10; // detik
            const RENDAH = 700, TINGGI = 1400, SIKLUS = 1; // detik per naik-turun
            const t0 = audioCtx.currentTime + 0.02;
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(RENDAH, t0);
            for (let t = 0; t < DURASI; t += SIKLUS) {
                osc.frequency.linearRampToValueAtTime(TINGGI, t0 + t + SIKLUS / 2);
                osc.frequency.linearRampToValueAtTime(RENDAH, t0 + t + SIKLUS);
            }
            gain.gain.setValueAtTime(0.0001, t0);
            gain.gain.exponentialRampToValueAtTime(0.5, t0 + 0.05);
            gain.gain.setValueAtTime(0.5, t0 + DURASI - 0.1);
            gain.gain.exponentialRampToValueAtTime(0.0001, t0 + DURASI);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start(t0);
            osc.stop(t0 + DURASI + 0.1);
        } catch (e) { /* abaikan bila audio tidak didukung */ }
    }

    function handleLeave() {
        // Abaikan kejadian berulang dalam 2 detik
        const now = Date.now();
        if (now - lastViolation < 2000) return;
        lastViolation = now;

        // Bunyikan sirene (10 detik) sebagai peringatan
        playBell();

        // Getar HP (Android) sebagai peringatan tambahan — berfungsi walau
        // halaman sudah di-background-kan sehingga bel audio tidak terdengar.
        if (navigator.vibrate) {
            try { navigator.vibrate(200); } catch (e) {}
        }

        Promise.resolve(flushPendingEssay()).finally(function() {
            reportViolation();
        });
    }

    // Deteksi pindah tab / membuka jendela lain (hanya visibilitychange, bukan blur)
    // blur event dihapus karena menyebabkan false positive (klik taskbar, notifikasi, dll)
    //
    // MASA TENGGANG 10 DETIK — layar HP terkunci (otomatis karena waktu
    // kunci layar, atau tombol power ditekan) juga membuat halaman
    // "tersembunyi". Dulu langsung dihitung pelanggaran begitu layar mati.
    // Sekarang: saat halaman tersembunyi, tunggu 10 detik dulu. Kalau
    // siswa membuka lagi layarnya dalam 10 detik itu, batalkan — bukan
    // pelanggaran. Kalau setelah 10 detik halaman masih tersembunyi,
    // baru dihitung pelanggaran (siswa benar-benar meninggalkan ujian).
    let hiddenTimer = null;
    const GRACE_MS = 10000; // 10 detik
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (hiddenTimer) clearTimeout(hiddenTimer);
            hiddenTimer = setTimeout(function() {
                hiddenTimer = null;
                handleLeave();
            }, GRACE_MS);
        } else {
            if (hiddenTimer) {
                clearTimeout(hiddenTimer);
                hiddenTimer = null;
            }
            // Wake lock dilepas otomatis saat halaman disembunyikan —
            // minta lagi begitu halaman terlihat kembali.
            requestWakeLock();
        }
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

    // Tandai submit sah (klik "Ya, Selesaikan Ujian") agar dialog
    // beforeunload tidak muncul saat ujian selesai dengan benar.
    const formSelesaiEl = document.getElementById('formSelesai');
    if (formSelesaiEl) {
        formSelesaiEl.addEventListener('submit', function() {
            examSubmitting = true;
        });
    }

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
            // Kalau halaman sedang tersembunyi (layar HP terkunci),
            // jangan hitung keluar fullscreen sebagai pelanggaran —
            // layar terkunci otomatis keluar dari fullscreen. Serahkan
            // ke logika masa tenggang di visibilitychange di atas.
            // Hanya bila halaman terlihat (siswa sengaja keluar
            // fullscreen) langsung dihitung pelanggaran.
            if (!document.hidden) handleLeave();
        } else {
            gate.style.display = 'none';
        }
    });

    if (btnFS && fsDidukung) {
        btnFS.addEventListener('click', function() {
            document.documentElement.requestFullscreen().catch(() => {});
            // Minta lagi setelah interaksi siswa (lebih andal di sebagian browser)
            requestWakeLock();
        });
    }

    // Tampilkan gate saat halaman dimuat bila fullscreen didukung tapi
    // siswa belum masuk layar penuh — ujian hanya jalan dalam fullscreen.
    // Bila fullscreen tidak didukung (mis. iPhone Safari), gate disembunyikan
    // dan ujian tetap bisa dikerjakan (batas platform, bukan aplikasi).
    if (perluFullscreen()) {
        gate.style.display = 'flex';
    } else {
        gate.style.display = 'none';
    }
});
