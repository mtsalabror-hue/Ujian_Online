<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Simpan urutan soal acak per siswa per ujian, supaya tiap siswa
     * mendapat urutan soal yang berbeda dan urutan itu tetap sama walau
     * halaman di-refresh. Jawaban tetap tersimpan per id_soal, jadi
     * pengacakan tidak mengganggu jawaban yang sudah diisi.
     */
    public function up(): void
    {
        Schema::table('hasil_ujian', function (Blueprint $table) {
            $table->json('urutan_soal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_ujian', function (Blueprint $table) {
            $table->dropColumn('urutan_soal');
        });
    }
};
