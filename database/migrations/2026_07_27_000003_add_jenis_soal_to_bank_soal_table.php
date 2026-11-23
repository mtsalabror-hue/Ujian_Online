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
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->string('jenis_soal', 20)->default('pilihan_ganda')->after('id_mapel');
            $table->text('opsi_a')->nullable()->change();
            $table->text('opsi_b')->nullable()->change();
            $table->text('opsi_c')->nullable()->change();
            $table->text('opsi_d')->nullable()->change();
            $table->string('jawaban_benar', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->dropColumn('jenis_soal');
            $table->text('opsi_a')->change();
            $table->text('opsi_b')->change();
            $table->text('opsi_c')->change();
            $table->text('opsi_d')->change();
            $table->char('jawaban_benar', 1)->change();
        });
    }
};
