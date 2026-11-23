<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\Image as WordImage;
use PhpOffice\PhpWord\Element\Table as WordTable;
use PhpOffice\PhpWord\Element\Text as WordText;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;

/**
 * Membaca bank soal dari dokumen Word (.docx).
 *
 * Struktur yang dikenali:
 *  - Satu tabel formal dengan baris pertama sebagai header kolom.
 *  - Header dicocokkan secara fleksibel (abaikan spasi/garis bawah/akhiran):
 *      NOMOR, KELAS, MATA PELAJARAN/MAPEL, JENIS SOAL,
 *      PERTANYAAN, OPSI A-D / PILIHAN A-D, JAWABAN BENAR / KUNCI, BOBOT.
 *  - Gambar di dalam cell diekstrak ke storage/public/soal-images
 *    dan direferensikan sebagai <img src="storage/...">.
 */
class BankSoalWordParser
{
    protected string $docxPath;

    /** Map nama kolom normalisasi (huruf+angka saja) -> list alias. */
    protected array $aliases = [
        'nomor'          => ['nomor', 'no', 'nomer'],
        'kelas'          => ['kelas'],
        'mata_pelajaran' => ['matapelajaran', 'mapel', 'mata', 'pelajaran', 'matapel'],
        'jenis_soal'     => ['jenissoal', 'jenis', 'tipesoal', 'tipe'],
        'pertanyaan'     => ['pertanyaan', 'soalpertanyaan', 'soal', 'isi', 'textpertanyaan'],
        'opsi_a'         => ['opsia', 'opsia_', 'pilihana', 'optiona'],
        'opsi_b'         => ['opsib', 'opsib_', 'pilihanb', 'optionb'],
        'opsi_c'         => ['opsic', 'opsic_', 'pilihanc', 'optionc'],
        'opsi_d'         => ['opsid', 'opsid_', 'pilihand', 'optiond'],
        'opsi_e'         => ['opsie', 'opsie_', 'pilihane', 'optione'],
        'jawaban_benar'  => ['jawabanbenar', 'jawaban', 'kunci', 'kuncijawaban', 'answer', 'benarsalah'],
        'bobot'          => ['bobot', 'poin', 'skor', 'weight'],
    ];

    public function __construct(string $docxPath)
    {
        $this->docxPath = $docxPath;
    }

    /**
     * Baca seluruh data soal dari docx.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(): array
    {
        $phpWord = IOFactory::load($this->docxPath);
        $soalList = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof WordTable) {
                    $soalList = array_merge($soalList, $this->parseTable($element));
                }
            }
        }

        return array_values($soalList);
    }

    protected function parseTable(WordTable $table): array
    {
        $rows = $table->getRows();
        if (count($rows) < 2) {
            return [];
        }

        $headerMap = $this->parseHeaderRow($rows[0]);
        if (empty($headerMap['pertanyaan']) && empty($headerMap['soal'])) {
            // Header tidak dikenali — fallback urutan kolom baku (seperti template Excel).
            $headerMap = $this->mapDefaultHeader(count($rows[0]->getCells()));
        }

        $soalList = [];
        $count = count($rows);
        for ($i = 1; $i < $count; $i++) {
            $cells = $rows[$i]->getCells();

            $get = function (string $key) use ($headerMap, $cells) {
                if (!isset($headerMap[$key])) {
                    return null;
                }

                $idx = $headerMap[$key];

                return isset($cells[$idx]) ? $this->cellHtml($cells[$idx]) : null;
            };

            $pertanyaan = trim($this->plainText($get('pertanyaan')));
            if ($pertanyaan === '') {
                continue; // baris kosong — lewati
            }

            $soal = [
                'nomor'          => $this->plainText($get('nomor')),
                'kelas'          => $this->plainText($get('kelas')),
                'mata_pelajaran' => $this->plainText($get('mata_pelajaran')),
                'jenis_soal'     => $this->plainText($get('jenis_soal')) ?: 'pilihan_ganda',
                'pertanyaan'     => $get('pertanyaan'),
                'opsi_a'         => $get('opsi_a'),
                'opsi_b'         => $get('opsi_b'),
                'opsi_c'         => $get('opsi_c'),
                'opsi_d'         => $get('opsi_d'),
                'opsi_e'         => $get('opsi_e'),
                'jawaban_benar'  => $this->plainText($get('jawaban_benar')),
                'bobot'          => $this->plainText($get('bobot')),
            ];

            // Normalisasi jenis soal
            $mapJenis = [
                'pilihan_ganda' => 'pilihan_ganda',
                'pilihan ganda' => 'pilihan_ganda',
                'pg'            => 'pilihan_ganda',
                'benar_salah'   => 'benar_salah',
                'benar salah'   => 'benar_salah',
                'benar/salah'   => 'benar_salah',
                'bs'            => 'benar_salah',
                'essai'         => 'essay',
                'essay'         => 'essay',
                'uraian'        => 'essay',
                'isian'         => 'essay',
            ];
            $jenis = strtolower(trim($soal['jenis_soal']));
            $soal['jenis_soal'] = $mapJenis[$jenis]
                ?? (in_array($jenis, ['pilihan_ganda', 'benar_salah', 'essay']) ? $jenis : 'pilihan_ganda');

            $soalList[] = $soal;
        }

        return $soalList;
    }

    protected function parseHeaderRow(object $row): array
    {
        $map = [];
        if (!method_exists($row, 'getCells')) {
            return $map;
        }

        foreach ($row->getCells() as $i => $cell) {
            $norm = preg_replace('/[^a-z0-9]/', '', strtolower($this->plainText($this->cellHtml($cell))));

            // Pertanyaan punya banyak alias, periksa khusus dulu
            if ($this->isAlias($norm, 'pertanyaan')) {
                $map['pertanyaan'] = $i;
            } elseif ($this->isAlias($norm, 'nomor')) {
                $map['nomor'] = $i;
            } elseif ($this->isAlias($norm, 'kelas')) {
                $map['kelas'] = $i;
            } elseif ($this->isAlias($norm, 'mata_pelajaran')) {
                $map['mata_pelajaran'] = $i;
            } elseif ($this->isAlias($norm, 'jenis_soal')) {
                $map['jenis_soal'] = $i;
            } elseif ($this->isAlias($norm, 'opsi_a')) {
                $map['opsi_a'] = $i;
            } elseif ($this->isAlias($norm, 'opsi_b')) {
                $map['opsi_b'] = $i;
            } elseif ($this->isAlias($norm, 'opsi_c')) {
                $map['opsi_c'] = $i;
            } elseif ($this->isAlias($norm, 'opsi_d')) {
                $map['opsi_d'] = $i;
            } elseif ($this->isAlias($norm, 'opsi_e')) {
                $map['opsi_e'] = $i;
            } elseif ($this->isAlias($norm, 'jawaban_benar')) {
                $map['jawaban_benar'] = $i;
            } elseif ($this->isAlias($norm, 'bobot')) {
                $map['bobot'] = $i;
            }
        }

        return $map;
    }

    protected function isAlias(string $norm, string $key): bool
    {
        return in_array($norm, $this->aliases[$key] ?? [], true);
    }

    /**
     * Fallback urutan kolom baku (sama seperti template Excel):
     * kelas, mata_pelajaran, jenis_soal, pertanyaan, opsi_a..d, jawaban_benar, bobot
     */
    protected function mapDefaultHeader(int $totalCols): array
    {
        $default = [
            'kelas'          => 0,
            'mata_pelajaran' => 1,
            'jenis_soal'     => 2,
            'pertanyaan'     => 3,
            'opsi_a'         => 4,
            'opsi_b'         => 5,
            'opsi_c'         => 6,
            'opsi_d'         => 7,
            'jawaban_benar'  => 8,
            'bobot'          => 9,
        ];

        // Potong kolom yang melebihi jumlah kolom dokumen
        return array_filter($default, fn ($idx) => $idx < $totalCols);
    }

    /**
     * Baca 1 cell menjadi string HTML (dukungan <br>, <ul>, <a>, dan gambar).
     */
    protected function cellHtml($cell): string
    {
        $html = '';
        foreach ($cell->getElements() as $el) {
            if ($el instanceof TextRun) {
                foreach ($el->getElements() as $run) {
                    if ($run instanceof WordText) {
                        $html .= e($run->getText() ?? '');
                    } elseif ($run instanceof \PhpOffice\PhpWord\Element\Link) {
                        $html .= '<a href="' . e($run->getSource()) . '" target="_blank">' . e($run->getText() ?? '') . '</a>';
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
            } elseif ($el instanceof \PhpOffice\PhpWord\Element\ListItem) {
                $html .= '<p>' . e($el->getTextObject()->getText() ?? '') . '</p>';
            } elseif ($el instanceof \PhpOffice\PhpWord\Element\BreakElement) {
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

    /**
     * Ekstrak gambar dari docx, simpan ke storage, dan kembalikan tag <img>.
     */
    protected function imageHtml(WordImage $image): string
    {
        $source = $image->getSource();
        if (preg_match('/#(.+)$/', $source, $m)) {
            $relative = urldecode($m[1]);
            $contents = $this->extractFromZip($relative);
            if ($contents !== null) {
                $ext = $image->getImageExtension() ?: 'png';
                $filename = 'soal-images/docx_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                Storage::disk('public')->put($filename, $contents);

                return '<img src="' . e(asset('storage/' . $filename)) . '" style="max-width:100%;height:auto;" alt="Gambar">';
            }
        }

        return '';
    }

    /**
     * Baca file mentah dari dalam ZIP docx.
     */
    protected function extractFromZip(string $relativePath): ?string
    {
        $zip = new \ZipArchive();
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