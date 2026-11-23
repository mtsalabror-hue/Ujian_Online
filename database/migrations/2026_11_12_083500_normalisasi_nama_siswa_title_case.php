<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Normalisasi nama siswa: hanya huruf awal tiap kata yang kapital
     * (mis. "SINTA NUR FAUZIAH" -> "Sinta Nur Fauziah"). Nama yang
     * di-import dari template Excel tersimpan dalam huruf kapital semua.
     */
    public function up(): void
    {
        foreach (DB::table('siswa')->get(['id_siswa', 'nama_siswa']) as $row) {
            $baru = mb_convert_case($row->nama_siswa, MB_CASE_TITLE, 'UTF-8');
            if ($baru !== $row->nama_siswa) {
                DB::table('siswa')
                    ->where('id_siswa', $row->id_siswa)
                    ->update(['nama_siswa' => $baru]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * Kapitalisasi asal tidak dapat dikembalikan secara andal, jadi
     * rollback sengaja tidak mengubah data.
     */
    public function down(): void
    {
        //
    }
};
