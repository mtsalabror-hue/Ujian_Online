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
     *
     * updateOrCreate dipakai supaya nama sekolah di database lama
     * ikut berubah saat seeder dijalankan ulang (mis. saat deploy).
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            [],
            ['nama_sekolah' => 'SMK IT AL-ABROR']
        );
    }
}
