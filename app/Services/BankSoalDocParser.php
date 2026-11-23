<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\BreakElement;
use PhpOffice\PhpWord\Element\Image as WordImage;
use PhpOffice\PhpWord\Element\Link;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text as WordText;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;

/**
 * Membaca bank soal dari dokumen Word (.docx).
 *
 * Format yang dikenali (teks, TABEL, dan GAMBAR):
 *  - Tabel di dalam soal diubah jadi HTML <table> dan ikut terimport
 *    sebagai bagian pertanyaan (tampil di halaman siswa).
 *  - Gambar di dalam paragraf soal ikut terimport (inline). Gambar yang
 *    berdiri di paragraf sendiri (tanpa teks) ditempelkan ke soal yang
 *    sedang diproses, bukan dibuang.
 *  TEMPLATE BANK SOAL
 *  KELAS: VII A
 *  MATA PELAJARAN: Bahasa Indonesia
 *  A. PILIHAN GANDA
 *  <pertanyaan>
 *  A. opsi a
 *  B. opsi b
 *  C. opsi c
 *  D. opsi d
 *  Jawaban: B
 *  B. BENAR / SALAH
 *  <pertanyaan>
 *  Jawaban: Benar | Bobot: 1
 *  C. ESSAY
 *  <pertanyaan>
 *  Jawaban: <kunci>
 *  Bobot: 5
 *
 * Gambar di dalam paragraf diekstrak ke storage/public/soal-images.
 */
class BankSoalDocParser
{
    protected string $docxPath;

    public function __construct(string $docxPath)
    {
        $this->docxPath = $docxPath;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(): array
    {
        $phpWord = IOFactory::load($this->docxPath);

        $lines = [];
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof TextRun) {
                    $lines[] = $this->cellHtml($element);
                } elseif ($element instanceof WordText) {
                    $lines[] = e($element->getText() ?? '');
                } elseif ($element instanceof ListItem) {
                    $lines[] = e($element->getTextObject()->getText() ?? '');
                } elseif ($element instanceof Table) {
                    // Tabel di dalam soal: ubah jadi HTML <table> supaya
                    // ikut terimport sebagai bagian pertanyaan.
                    $lines[] = $this->tableHtml($element);
                }
            }
        }

        return $this->parseLines($lines);
    }

    /**
     * Proses baris-baris teks menjadi daftar soal.
     */
    protected function parseLines(array $lines): array
    {
        $soalList = [];
        $defaults = [
            'kelas'          => null,
            'mata_pelajaran' => null,
        ];
        $currentType = null;
        $pending     = null;

        $newPending = function () {
            return [
                'pertanyaan' => [],
                'opsi'       => [],
                'jawaban'    => null,
                'bobot'      => null,
            ];
        };

        $flush = function () use (&$pending, &$soalList, &$defaults, &$currentType) {
            if ($pending === null) {
                return;
            }
            $q = trim(preg_replace('/<br\s*\/?>/i', ' ', $this->plainText($pending['pertanyaan'] ? implode(' ', $pending['pertanyaan']) : '')));
            $jawaban = $pending['jawaban'];

            // Lewati baris contoh dari template (pertanyaan diawali "CONTOH:") —
            // contoh tidak ikut terimport ke bank soal.
            if (preg_match('/^contoh\s*:/i', $q)) {
                $pending = null;

                return;
            }

            if ($q !== '' && $jawaban !== null && $currentType !== null) {
                $soalList[] = [
                    'kelas'          => $defaults['kelas'],
                    'mata_pelajaran' => $defaults['mata_pelajaran'],
                    'jenis_soal'     => $currentType,
                    'pertanyaan'     => $pending['pertanyaan'] ? implode('<br>', $pending['pertanyaan']) : $q,
                    'opsi_a'         => $pending['opsi']['A'] ?? null,
                    'opsi_b'         => $pending['opsi']['B'] ?? null,
                    'opsi_c'         => $pending['opsi']['C'] ?? null,
                    'opsi_d'         => $pending['opsi']['D'] ?? null,
                    'opsi_e'         => $pending['opsi']['E'] ?? null,
                    'jawaban_benar'  => $jawaban,
                    'bobot'          => $pending['bobot'],
                ];
            }
            $pending = null;
        };

        foreach ($lines as $raw) {
            $plain = trim($this->plainText($raw));
            if ($plain === '') {
                // Baris tanpa teks tapi berisi GAMBAR (paragraf gambar
                // sendiri, mis. gambar soal yang diletakkan di bawah
                // teks pertanyaan) — tempelkan gambarnya ke soal yang
                // sedang diproses, jangan dibuang. Kalau belum ada soal
                // yang diproses, simpan dulu sebagai awal soal berikutnya.
                if (str_contains($raw, '<img')) {
                    if ($pending === null) {
                        $pending = $newPending();
                    }
                    $pending['pertanyaan'][] = $raw;
                }
                continue;
            }

            // TABEL (HTML <table>): tempelkan ke soal yang sedang
            // diproses sebagai bagian pertanyaan. Cek di awal supaya isi
            // sel tabel tidak salah dikenali sebagai opsi atau sub-judul.
            if (str_contains($raw, '<table')) {
                if ($pending === null) {
                    $pending = $newPending();
                }
                $pending['pertanyaan'][] = $raw;
                continue;
            }

            // Judul / instruksi yang tidak relevan
            if (preg_match('/^template\s*bank\s*soal/i', $plain)) {
                continue;
            }
            if (preg_match('/^isi\s+identitas/i', $plain) || preg_match('/^hapus\s+baris\s+contoh/i', $plain)) {
                continue;
            }
            if (preg_match('/^catatan\s*:/i', $plain)) {
                continue;
            }

            // Identitas
            if (preg_match('/^kelas\s*:\s*(.+)$/i', $plain, $m)) {
                $defaults['kelas'] = trim($m[1]);
                continue;
            }
            if (preg_match('/^mata\s*pelajaran\s*:\s*(.+)$/i', $plain, $m)) {
                $defaults['mata_pelajaran'] = trim($m[1]);
                continue;
            }

            // Opsi Benar/Salah: baris "(Benar /Salah)" atau "Benar" / "Salah"
            // saja di bagian B/S adalah label pilihan, bukan teks soal —
            // lewati agar tidak masuk ke pertanyaan. (Opsi B/S diisi
            // otomatis saat import.) Berlaku juga untuk padanannya dalam
            // Bahasa Inggris: "(True /False)", "True", "False".
            // Cek INI DULU sebelum cek sub-judul:
            // setelah dinormalisasi, "(Benar /Salah)" menjadi "benar salah"
            // dan akan salah dikenali sebagai header "B. BENAR / SALAH",
            // sehingga soal B/S terbuang sebelum baris Jawaban terbaca.
            if ($currentType === 'benar_salah' && preg_match('/^(?:\(?\s*(?:benar\s*\/\s*salah|true\s*\/\s*false)\s*\)?|benar|salah|true|false)$/i', $plain)) {
                continue;
            }

            // Sub-judul jenis soal: "A. PILIHAN GANDA", "B. BENAR / SALAH", "C. ESSAY"
            if ($this->isSectionHeader($plain)) {
                $flush();
                $currentType = $this->detectTypeText($plain);
                continue;
            }

            if ($currentType === null) {
                continue;
            }

            // Jawaban: ... (titik dua boleh ada atau tidak: "Jawaban: D" / "Jawaban D")
            if (preg_match('/^jawaban\s*:?\s*(.+)$/i', $plain, $m)) {
                $answer = trim($m[1]);
                $bobot  = $pending['bobot'] ?? null;
                // B/S: "Benar | Bobot: 1" (titik dua pada Bobot juga opsional)
                if (preg_match('/^(.+?)\s*[|]\s*bobot\s*:?\s*([\d.,]+)$/i', $answer, $bm)) {
                    $answer = trim($bm[1]);
                    $bobot  = $bm[2];
                }
                if ($pending === null) {
                    $pending = $newPending();
                }
                $pending['jawaban'] = $answer;
                $pending['bobot']   = $bobot;

                // B/S: opsi mengikuti format yang dipakai guru — True/False
                // ATAU Benar/Salah (keduanya diterima, TIDAK dinormalisasi).
                // Opsi ini yang nanti ditampilkan ke siswa, dan jawaban_benar
                // disimpan sesuai formatnya supaya penilaian cocok.
                if ($currentType === 'benar_salah') {
                    $lowerAns = strtolower($answer);
                    if ($lowerAns === 'true' || $lowerAns === 'false') {
                        $pending['opsi']['A'] = 'True';
                        $pending['opsi']['B'] = 'False';
                        $pending['jawaban']   = $lowerAns === 'true' ? 'True' : 'False';
                    } elseif ($lowerAns === 'benar' || $lowerAns === 'salah') {
                        $pending['opsi']['A'] = 'Benar';
                        $pending['opsi']['B'] = 'Salah';
                        $pending['jawaban']   = $lowerAns === 'benar' ? 'Benar' : 'Salah';
                    }
                }

                // PG & B/S langsung selesai; Essay menunggu baris "Bobot:"
                if ($currentType !== 'essay') {
                    $flush();
                }
                continue;
            }

            // Bobot: N (Essay) — titik dua opsional: "Bobot: 5" / "Bobot 5"
            if (preg_match('/^bobot\s*:?\s*([\d.,]+)$/i', $plain, $m)) {
                if ($pending === null) {
                    $pending = $newPending();
                }
                $pending['bobot'] = $m[1];
                if ($pending['jawaban'] !== null) {
                    $flush();
                }
                continue;
            }

            // Opsi dua kolom: "A. teks1    C. teks2" dalam satu baris —
            // pecah jadi 2 opsi (pemisah: tab atau 2+ spasi).
            if ($currentType === 'pilihan_ganda' && preg_match('/^([A-Ea-e])[.)]\s*(.+?)(?:\t+|[ ]{2,})([A-Ea-e])[.)]\s*(.+)$/', $plain, $m)) {
                if ($pending === null) {
                    $pending = $newPending();
                }
                $pending['opsi'][strtoupper($m[1])] = trim($m[2]);
                $pending['opsi'][strtoupper($m[3])] = trim($m[4]);
                continue;
            }

            // Opsi pilihan ganda: "A. teks", "B) teks"
            if ($currentType === 'pilihan_ganda' && preg_match('/^([A-Ea-e])[.)]\s*(.+)$/', $plain, $m)) {
                if ($pending === null) {
                    $pending = $newPending();
                }
                $letter = strtoupper($m[1]);
                $value  = trim(preg_replace('/<br\s*\/?>/i', ' ', $this->plainText(substr($raw, strpos($raw, $m[2]) !== false ? strpos($raw, $m[2]) : 0))));
                $pending['opsi'][$letter] = $value !== '' ? $value : trim($m[2]);
                continue;
            }

            // Baris teks soal
            if ($pending !== null && $pending['jawaban'] !== null) {
                $flush();
            }
            if ($pending === null) {
                $pending = $newPending();
            }
            $pending['pertanyaan'][] = $raw;
        }
        $flush();

        return array_values($soalList);
    }

    protected function isSectionHeader(string $plain): bool
    {
        // Baris label "(Benar /Salah)" di bawah tiap soal B/S BUKAN sub-judul —
        // setelah dinormalisasi ia menjadi "benar salah" dan akan salah
        // dikenali sebagai header, sehingga soal B/S terbuang.
        // Sama untuk padanannya "(True /False)" -> "true false".
        if (preg_match('/^\(?\s*benar\s*\/\s*salah\s*\)?$/i', $plain)) {
            return false;
        }
        if (preg_match('/^\(?\s*true\s*\/\s*false\s*\)?$/i', $plain)) {
            return false;
        }

        $norm = preg_replace('/[^a-z]/', ' ', strtolower($plain));
        $norm = trim(preg_replace('/\s+/', ' ', $norm));

        return preg_match(
            '/^(?:[a-d]\s+)?(pilihan ganda|benar salah|true false|essay|essai|uraian|isian)$/',
            $norm
        ) === 1;
    }

    /**
     * Tentukan jenis soal dari teks sub-judul (mis. "A. PILIHAN GANDA").
     */
    protected function detectTypeText(string $text): ?string
    {
        $norm = preg_replace('/[^a-z]/', ' ', strtolower($text));
        $norm = trim(preg_replace('/\s+/', ' ', $norm));

        if (preg_match('/(pilihan\s*ganda|\bpg\b)/', $norm)) {
            return 'pilihan_ganda';
        }
        if (preg_match('/(benar\s*salah|true\s*false|\bbs\b)/', $norm)) {
            return 'benar_salah';
        }
        if (preg_match('/(essay|essai|uraian|isian)/', $norm)) {
            return 'essay';
        }

        return null;
    }

    /**
     * Ubah tabel Word menjadi HTML <table> (ikut terimport sebagai
     * bagian pertanyaan; tampil di halaman siswa). Isi tiap sel
     * dikonversi seperti isi paragraf (teks + gambar).
     */
    protected function tableHtml(Table $table): string
    {
        $html = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;margin:8px 0;">';
        foreach ($table->getRows() as $row) {
            $html .= '<tr>';
            foreach ($row->getCells() as $cell) {
                $html .= '<td>' . $this->cellHtml($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }

    protected function cellHtml($cell): string
    {
        $html = '';
        foreach ($this->iterateElements($cell) as $el) {
            if ($el instanceof TextRun) {
                foreach ($this->iterateElements($el) as $run) {
                    if ($run instanceof WordText) {
                        $html .= e($run->getText() ?? '');
                    } elseif ($run instanceof Link) {
                        $html .= '<a href="'.e($run->getSource()).'" target="_blank">'.e($run->getText() ?? '').'</a>';
                    } elseif ($run instanceof WordImage) {
                        $html .= $this->imageHtml($run);
                    } else {
                        $html .= e($this->extractAnyText($run));
                    }
                }
                $html .= '<br>';
            } elseif ($el instanceof WordText) {
                $html .= e($el->getText() ?? '');
            } elseif ($el instanceof WordImage) {
                $html .= $this->imageHtml($el);
            } elseif ($el instanceof Table) {
                // Tabel bersarang di dalam sel/paragraf.
                $html .= $this->tableHtml($el);
            } elseif ($el instanceof ListItem) {
                $html .= '<p>'.e($el->getTextObject()->getText() ?? '').'</p>';
            } elseif ($el instanceof BreakElement) {
                $html .= '<br>';
            } else {
                $html .= e($this->extractAnyText($el));
            }
        }

        while (preg_match('/<br>$/', $html)) {
            $html = substr($html, 0, -4);
        }

        return trim($html);
    }

    protected function iterateElements($object)
    {
        if (is_array($object)) {
            foreach ($object as $el) {
                yield $el;
            }
        } elseif ($object instanceof \IteratorAggregate) {
            foreach ($object->getIterator() as $el) {
                yield $el;
            }
        } elseif (method_exists($object, 'getElements')) {
            foreach ($object->getElements() as $el) {
                yield $el;
            }
        }
    }

    protected function imageHtml(WordImage $image): string
    {
        $source = $image->getSource();
        if (preg_match('/#(.+)$/', $source, $m)) {
            $relative = urldecode($m[1]);
            $contents = $this->extractFromZip($relative);
            if ($contents !== null) {
                $ext = $image->getImageExtension() ?: 'png';
                $filename = 'soal-images/docx_'.time().'_'.bin2hex(random_bytes(8)).'.'.$ext;
                Storage::disk('public')->put($filename, $contents);

                return '<img src="'.e(asset('storage/'.$filename)).'" style="max-width:100%;height:auto;" alt="Gambar">';
            }
        }

        return '';
    }

    protected function extractFromZip(string $relativePath): ?string
    {
        $zip = new \ZipArchive;
        if ($zip->open($this->docxPath) !== true) {
            return null;
        }

        $contents = null;
        if ($zip->locateName($relativePath) !== false) {
            $contents = $zip->getFromName($relativePath);
        }
        $zip->close();

        return $contents ?: null;
    }

    protected function plainText(?string $html = ''): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
    }

    private function extractAnyText($el): string
    {
        if (method_exists($el, 'getText')) {
            return (string) $el->getText();
        }

        return '';
    }
}
