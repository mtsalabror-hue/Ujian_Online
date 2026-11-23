<?php

namespace App\Exports;

use App\Models\Ujian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HasilPerMapelExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected Ujian $ujian;
    protected Collection $hasil;
    protected int $no = 0;

    public function __construct(Ujian $ujian, Collection $hasil)
    {
        $this->ujian = $ujian;
        $this->hasil = $hasil;
    }

    public function collection(): Collection
    {
        return $this->hasil;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'Nilai',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            $row->siswa->nama_siswa ?? '-',
            number_format($row->nilai, 1),
        ];
    }

    public function title(): string
    {
        $mapel = $this->ujian->mataPelajaran->nama_mapel ?? 'Mapel';
        $kelas = $this->ujian->kelas->nama_kelas ?? 'Kelas';

        return mb_substr($mapel . ' ' . $kelas, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = $sheet->getHighestDataColumn();
        $lastRow = $sheet->getHighestDataRow();
        $range   = 'A1:' . $lastCol . $lastRow;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            $range => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFB4B4B4'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
