<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    /**
     * Seed daftar kelas saja (tanpa siswa contoh).
     * Idempotent — aman dijalankan berulang.
     *
     * Siswa contoh (60 siswa) sudah dihapus dan tidak dibuat lagi.
     * Siswa asli diinput lewat panel admin.
     */
    public function run(): void
    {
        // ── Daftar kelas yang akan dibuat / dipakai ──────────────────────
        $kelasSeed = [
            ['nama_kelas' => 'VII', 'tingkat' => 'VII'],
            ['nama_kelas' => 'VIII', 'tingkat' => 'VIII'],
            ['nama_kelas' => 'IX', 'tingkat' => 'IX'],
        ];

        foreach ($kelasSeed as $k) {
            Kelas::firstOrCreate(
                ['nama_kelas' => $k['nama_kelas']],
                ['tingkat' => $k['tingkat']]
            );
        }

        
    }
}
