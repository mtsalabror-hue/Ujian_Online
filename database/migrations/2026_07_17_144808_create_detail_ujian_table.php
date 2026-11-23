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
        Schema::create('detail_ujian', function (Blueprint $table) {
            $table->id('id_detail');

            $table->foreignId('id_ujian')
                ->constrained('ujian', 'id_ujian')
                ->cascadeOnDelete();

            $table->foreignId('id_soal')
                ->constrained('bank_soal', 'id_soal')
                ->restrictOnDelete();

            $table->timestamps();
            $table->unique(['id_ujian', 'id_soal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_ujian');
    }
};
