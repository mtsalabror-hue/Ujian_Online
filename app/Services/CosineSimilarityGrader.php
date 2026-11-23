<?php

namespace App\Services;

/**
 * Penilai essay berbasis kemiripan kosinus (cosine similarity).
 *
 * Cara kerja: kunci jawaban dan jawaban siswa diubah menjadi vektor
 * frekuensi kata (TF) setelah pra-pemrosesan teks Bahasa Indonesia
 * (buang HTML, huruf kecil, buang tanda baca, buang stopword), lalu
 * dihitung kosinus sudut di antara kedua vektor. Saran nilai =
 * kemiripan × bobot soal.
 *
 * Batasan: metode ini hanya membandingkan kata, bukan makna. Jawaban
 * benar dengan redaksi berbeda bisa dinilai rendah, dan kalimat
 * berlawanan arti ("tidak merusak" vs "merusak") masih bisa dinilai
 * cukup tinggi pada kalimat panjang — kata negasi ikut dihitung agar
 * tidak dinilai identik 100%, tapi guru tetap bisa mengoreksi lewat
 * halaman tinjau jawaban.
 */
class CosineSimilarityGrader
{
    /**
     * Kata umum Bahasa Indonesia yang diabaikan saat perbandingan.
     */
    protected array $stopwords = [
        'ada', 'adalah', 'adanya', 'agar', 'akan', 'akibat', 'antara',
        'atau', 'bagi', 'bahwa', 'banyak', 'berapa', 'begitu',
        'berupa', 'bisa', 'dalam', 'dan', 'dapat', 'dari', 'dengan',
        'di', 'dia', 'dimana', 'dll', 'guna', 'hal', 'hanya', 'harus',
        'ialah', 'ini', 'itu', 'jadi', 'jika', 'juga',
        'kala', 'kalau', 'karena', 'ke', 'kembali', 'kepada', 'ketika',
        'khusus', 'kita', 'lain', 'lalu', 'lebih', 'melalui', 'mereka',
        'merupakan', 'misal', 'misalnya', 'mungkin', 'namun', 'oleh',
        'pada', 'para', 'perlu', 'saja', 'sama', 'sampai', 'sangat',
        'saya', 'sebagai', 'sebelum', 'secara', 'sedangkan', 'sejak',
        'selain', 'selama', 'semua', 'seperti', 'serta', 'sudah',
        'supaya', 'telah', 'tentang', 'terhadap', 'tersebut', 'tetapi',
        'tiap', 'untuk', 'yang', 'yaitu', 'yakni',
    ];

    /**
     * Catatan: kata negasi ('tidak', 'jangan', 'belum') SENGAJA tidak
     * masuk stopword. Kalau dibuang, jawaban 'tidak merusak' dan kunci
     * 'merusak' menjadi token yang identik dan dinilai 100% — padahal
     * maknanya berlawanan.
     */

    /**
     * Hitung saran nilai essay.
     *
     * @return array{skor: float, kemiripan: float, kata_cocok: int}
     */
    public function saranNilai(?string $kunciJawaban, ?string $jawabanSiswa, float $bobot): array
    {
        $tokensKunci = $this->tokenisasi($kunciJawaban ?? '');
        $tokensJawaban = $this->tokenisasi($jawabanSiswa ?? '');

        if ($tokensKunci === [] || $tokensJawaban === []) {
            return ['skor' => 0.0, 'kemiripan' => 0.0, 'kata_cocok' => 0];
        }

        $vektorKunci = $this->vektorTf($tokensKunci);
        $vektorJawaban = $this->vektorTf($tokensJawaban);

        $kemiripan = $this->kosinus($vektorKunci, $vektorJawaban);
        $kataCocok = count(array_intersect(array_keys($vektorKunci), array_keys($vektorJawaban)));

        return [
            'skor' => round($kemiripan * $bobot, 2),
            'kemiripan' => round($kemiripan * 100, 1),
            'kata_cocok' => $kataCocok,
        ];
    }

    /**
     * Pra-pemrosesan teks: buang HTML, decode entity HTML (&nbsp; dll),
     * huruf kecil, buang tanda baca, pecah per kata, buang stopword
     * dan kata 1 huruf.
     */
    protected function tokenisasi(string $teks): array
    {
        $teks = strip_tags($teks);
        // Entity seperti &nbsp; harus di-decode dulu; kalau tidak, sisa
        // teks "nbsp"/"amp"/"quot" ikut dihitung sebagai kata dan
        // menurunkan skor secara tidak adil.
        $teks = html_entity_decode($teks, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $teks = mb_strtolower($teks, 'UTF-8');
        // Hanya huruf dan angka yang dipertahankan; sisanya jadi spasi.
        $teks = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $teks);

        $tokens = preg_split('/\s+/', trim((string) $teks)) ?: [];

        return array_values(array_filter($tokens, function (string $kata): bool {
            return mb_strlen($kata, 'UTF-8') > 1 && ! in_array($kata, $this->stopwords, true);
        }));
    }

    /**
     * Vektor frekuensi term (TF): kata => jumlah kemunculan.
     */
    protected function vektorTf(array $tokens): array
    {
        return array_count_values($tokens);
    }

    /**
     * Kemiripan kosinus dua vektor TF, rentang 0..1.
     */
    protected function kosinus(array $a, array $b): float
    {
        $dot = 0.0;
        foreach ($a as $kata => $frekuensi) {
            if (isset($b[$kata])) {
                $dot += $frekuensi * $b[$kata];
            }
        }

        $normA = sqrt(array_sum(array_map(fn ($x) => $x * $x, $a)));
        $normB = sqrt(array_sum(array_map(fn ($x) => $x * $x, $b)));

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return max(0.0, min(1.0, $dot / ($normA * $normB)));
    }
}
