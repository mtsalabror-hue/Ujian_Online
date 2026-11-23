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
        Schema::create('settings', function (Blueprint $table) {

            $table->id();

            $table->string('nama_sekolah');

            $table->string('npsn')->nullable();

            $table->text('alamat')->nullable();

            $table->string('telepon')->nullable();

            $table->string('email')->nullable();

            $table->string('website')->nullable();

            $table->string('kepala_sekolah')->nullable();

            $table->string('logo')->nullable();

            $table->integer('durasi_default')->default(60);

            $table->boolean('acak_soal')->default(true);

            $table->boolean('acak_jawaban')->default(true);

            $table->boolean('tampilkan_nilai')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
