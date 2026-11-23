<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jawaban_siswa', function (Blueprint $table) {
            $table->id('id_jawaban');

            $table->foreignId('id_ujian')
                ->constrained('ujian', 'id_ujian')
                ->cascadeOnDelete();

            $table->foreignId('id_siswa')
                ->constrained('siswa', 'id_siswa')
                ->cascadeOnDelete();

            $table->foreignId('id_soal')
                ->constrained('bank_soal', 'id_soal')
                ->restrictOnDelete();

            $table->char('jawaban', 1)->nullable();
            $table->boolean('status_benar')->default(false);

            $table->timestamps();
            $table->unique(['id_ujian', 'id_siswa', 'id_soal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswa');
    }
};
