<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hapus data awal kelas dan mata pelajaran yang dulu di-seed
     * otomatis, supaya keduanya mulai dari kosong dan diinput
     * sendiri oleh pengguna lewat panel admin.
     *
     * Dijalankan sekali (tercatat di tabel migrations). Tabel anak
     * (siswa, ujian, bank soal) tidak disentuh — kalau ada data di
     * sana, migrasi ini gagal dengan aman daripada menghapus data
     * pengguna.
     */
    public function up(): void
    {
        // Hapus dulu baris pivot yang merujuk ke mapel/kelas.
        DB::table('guru_mata_pelajaran')->delete();
        DB::table('guru_kelas')->delete();

        // Baru hapus data awalnya.
        DB::table('mata_pelajaran')->delete();
        DB::table('kelas')->delete();
    }

    public function down(): void
    {
        // Data yang dihapus tidak bisa dikembalikan otomatis.
    }
};
