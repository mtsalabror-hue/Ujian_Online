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
        Schema::create('hasil_ujian', function (Blueprint $table) {
            $table->id('id_hasil');

            $table->foreignId('id_ujian')
                ->constrained('ujian', 'id_ujian')
                ->cascadeOnDelete();

            $table->foreignId('id_siswa')
                ->constrained('siswa', 'id_siswa')
                ->cascadeOnDelete();

            $table->decimal('nilai', 5, 2)->default(0);
            $table->timestamp('tanggal_selesai')->nullable();
            $table->string('status', 20)->default('selesai');

            $table->timestamps();
            $table->unique(['id_ujian', 'id_siswa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_ujian');
    }
};
