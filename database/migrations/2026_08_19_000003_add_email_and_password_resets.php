<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 150)->nullable()->after('username');
            $table->index('email');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 150)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Isi email pada users dari profil guru yang sudah terdaftar
        DB::statement("
            UPDATE users
            SET email = (
                SELECT g.email FROM guru g
                WHERE g.user_id = users.id AND g.email IS NOT NULL
                ORDER BY g.id_guru LIMIT 1
            )
            WHERE users.email IS NULL
              AND EXISTS (
                SELECT 1 FROM guru g
                WHERE g.user_id = users.id AND g.email IS NOT NULL
              )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropColumn('email');
        });
    }
};