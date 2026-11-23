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
        Schema::table('hasil_ujian', function (Blueprint $table) {
            $table->unsignedTinyInteger('pelanggaran')->default(0)->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('exam_locked_until')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('exam_locked_until');
        });

        Schema::table('hasil_ujian', function (Blueprint $table) {
            $table->dropColumn('pelanggaran');
        });
    }
};