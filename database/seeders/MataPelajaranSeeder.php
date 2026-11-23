<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Seed mata pelajaran sesuai daftar resmi MTs Al-Abror (17 mapel,
     * dari dokumen sekolah 10 Nov 2026).
     * Idempotent — aman dijalankan berulang (updateOrCreate by kode_mapel).
     *
     * Catatan:
     * - Nama mengikuti daftar sekolah ("IPA", "IPS", "PJOK", dst), bukan
     *   nama panjang lama ("Ilmu Pengetahuan Alam", dst).
     * - "Fikih" dicari berdasarkan NAMA (bukan kode) supaya tidak
     *   menduplikasi record Fikih yang sudah ada (id_mapel 14) — ejaan
     *   sistem memakai "Fikih" (KBBI), dokumen sekolah menulis "Fiqih".
     * - "PAI" dan "Prakarya" tidak ada di daftar sekolah (PAI sudah
     *   terpecah jadi Qur'an Hadits, Akidah Akhlak, Fikih, SKI, Bahasa
     *   Arab), jadi tidak lagi di-seed. Record lama di DB tidak dihapus
     *   otomatis oleh seeder ini.
     */
    public function run(): void
    {
        $mapel = [
            ['kode_mapel' => 'BIN', 'nama_mapel' => 'Bahasa Indonesia'],
            ['kode_mapel' => 'QHD', 'nama_mapel' => "Al-Qur'an Hadits"],
            ['kode_mapel' => 'PKN', 'nama_mapel' => 'Pendidikan Pancasila'],
            ['kode_mapel' => 'IPA', 'nama_mapel' => 'IPA'],
            ['kode_mapel' => 'BAR', 'nama_mapel' => 'Bahasa Arab'],
            ['kode_mapel' => 'SKI', 'nama_mapel' => 'SKI'],
            ['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika'],
            ['kode_mapel' => 'AAK', 'nama_mapel' => 'Akidah Akhlak'],
            ['kode_mapel' => 'BTQ', 'nama_mapel' => 'BTQ'],
            ['kode_mapel' => 'TIK', 'nama_mapel' => 'Informatika'],
            ['kode_mapel' => 'BIG', 'nama_mapel' => 'Bahasa Inggris'],
            ['kode_mapel' => 'QK',  'nama_mapel' => 'QK'],
            ['kode_mapel' => 'IPS', 'nama_mapel' => 'IPS'],
            ['kode_mapel' => 'SBK', 'nama_mapel' => 'Seni Budaya'],
            ['kode_mapel' => 'BDA', 'nama_mapel' => 'Bahasa Sunda'],
            ['kode_mapel' => 'PJOK', 'nama_mapel' => 'PJOK'],
        ];

        foreach ($mapel as $row) {
            MataPelajaran::updateOrCreate(
                ['kode_mapel' => $row['kode_mapel']],
                ['nama_mapel' => $row['nama_mapel']]
            );
        }

        // Fikih: cocokkan berdasarkan nama supaya record yang sudah ada
        // (dipakai import soal Fikih) tidak terduplikasi.
        MataPelajaran::updateOrCreate(
            ['nama_mapel' => 'Fikih'],
            ['kode_mapel' => 'FQH']
        );
    }
}
