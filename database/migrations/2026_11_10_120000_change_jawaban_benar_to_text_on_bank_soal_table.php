<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kunci jawaban essay bisa sangat panjang (mis. daftar rukun sholat),
     * melebihi batas varchar(255) sehingga import gagal dengan error
     * "value too long for type character varying(255)". Ubah ke TEXT
     * agar kunci jawaban essay sepanjang apa pun bisa disimpan.
     */
    public function up(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->text('jawaban_benar')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->string('jawaban_benar', 255)->nullable()->change();
        });
    }
};
