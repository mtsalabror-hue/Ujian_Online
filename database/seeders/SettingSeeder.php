<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Isi identitas sekolah supaya halaman Pengaturan dan tampilan
     * (kartu ujian, kop, dsb) langsung sinkron setelah reset database.
     * Logo tidak diisi di sini — kartu memakai file default
     * public/images/logoo.png sampai logo diunggah lewat Pengaturan.
     */
    public function run(): void
    {
        Setting::firstOrCreate(
            [],
            ['nama_sekolah' => 'MTs Al-Abror']
        );
    }
}
