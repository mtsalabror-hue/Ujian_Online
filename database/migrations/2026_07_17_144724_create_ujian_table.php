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
        Schema::create('ujian', function (Blueprint $table) {
            $table->id('id_ujian');

            $table->foreignId('id_guru')
                ->constrained('guru', 'id_guru')
                ->cascadeOnDelete();

            $table->foreignId('id_mapel')
                ->constrained('mata_pelajaran', 'id_mapel')
                ->restrictOnDelete();

            $table->foreignId('id_kelas')
                ->constrained('kelas', 'id_kelas')
                ->restrictOnDelete();

            $table->foreignId('id_tahun')
                ->constrained('tahun_ajaran', 'id_tahun')
                ->restrictOnDelete();

            $table->string('judul_ujian', 150);
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->integer('durasi'); // dalam menit
            $table->string('status', 20)->default('nonaktif');

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian');
    }
};
