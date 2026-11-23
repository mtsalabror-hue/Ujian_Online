<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SiswaExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Siswa::with('kelas', 'user')->get();
    }

    public function headings(): array
    {
        return [
            'NIS',
            'NISN',
            'Nama Siswa',
            'Kelas',
            'Jenis Kelamin',
            'Username'
        ];
    }

    public function map($siswa): array
    {
        return [
            $siswa->nis,
            $siswa->nisn ?? '-',
            $siswa->nama_siswa,
            $siswa->kelas->nama_kelas ?? '-',
            $siswa->jenis_kelamin,
            $siswa->user->username ?? '-',
        ];
    }
}
