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
        if (!Schema::hasColumn('bank_soal', 'id_kelas')) {
            Schema::table('bank_soal', function (Blueprint $table) {
                $table->foreignId('id_kelas')
                    ->nullable()
                    ->after('id_mapel')
                    ->constrained('kelas', 'id_kelas')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bank_soal', 'id_kelas')) {
            Schema::table('bank_soal', function (Blueprint $table) {
                $table->dropForeign(['id_kelas']);
                $table->dropColumn('id_kelas');
            });
        }
    }
};
