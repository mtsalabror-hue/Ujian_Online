<?php

namespace App\Console\Commands;

use App\Models\BankSoal;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Console\Command;

/**
 * Beberesih selektif pengganti `migrate:fresh` — DIPAKAI karena data guru
 * dan soal yang sudah di-upload TIDAK BOLEH hilang.
 *
 * Yang dihapus HANYA:
 *  1. Siswa contoh NIS 10002–10061 (beserta akun user, jawaban, dan hasil
 *     ujiannya) — dibuat seeder lama 9 Nov 2026.
 *  2. Semua soal Fikih (mapel dicari berdasarkan nama, bukan id hardcode)
 *     — soal parsial/duplikat dari import yang gagal, supaya bisa
 *     di-import ulang bersih (45 soal).
 *
 * Yang TIDAK disentuh: data guru, siswa asli, soal mapel lain, kelas,
 * mapel, pengaturan, dan ujian.
 *
 * Jalankan: php artisan cbt:bersihkan-data-contoh [--force]
 * Tanpa --force, perintah menampilkan jumlah dulu dan minta konfirmasi.
 */
class BersihkanDataContoh extends Command
{
    protected $signature = 'cbt:bersihkan-data-contoh {--force : Hapus langsung tanpa konfirmasi}';
    protected $description = 'Hapus siswa contoh (NIS 10002-10061) dan soal Fikih parsial/duplikat. Data guru dan soal mapel lain tidak dihapus.';

    public function handle(): int
    {
        $siswaContoh = Siswa::whereBetween('nis', ['10002', '10061'])->get();

        $mapelFikih = MataPelajaran::whereRaw('LOWER(nama_mapel) = ?', ['fikih'])->first();
        $jumlahSoalFikih = $mapelFikih
            ? BankSoal::where('id_mapel', $mapelFikih->id_mapel)->count()
            : 0;

        $this->info('Yang akan dihapus:');
        $this->info("  - Siswa contoh (NIS 10002-10061): {$siswaContoh->count()} siswa");
        $this->info("  - Soal Fikih: {$jumlahSoalFikih} soal");

        if ($siswaContoh->isEmpty() && $jumlahSoalFikih === 0) {
            $this->info('Tidak ada yang perlu dihapus.');
            return 0;
        }

        if (! $this->option('force') && ! $this->confirm('Yakin hapus data di atas? Data guru dan soal mapel lain TIDAK ikut terhapus.', false)) {
            $this->info('Dibatalkan, tidak ada yang dihapus.');
            return 0;
        }

        $nSiswa = 0;
        foreach ($siswaContoh as $s) {
            $s->jawabanSiswa()->delete();
            $s->hasilUjian()->delete();
            $u = $s->user;
            $s->delete();
            if ($u) {
                $u->delete();
            }
            $nSiswa++;
        }
        $this->info("Siswa contoh dihapus: {$nSiswa}");

        $nSoal = 0;
        if ($mapelFikih) {
            $nSoal = BankSoal::where('id_mapel', $mapelFikih->id_mapel)->delete();
        }
        $this->info("Soal Fikih dihapus: {$nSoal}");

        $this->info('Selesai. Data guru dan soal mapel lain tidak disentuh.');
        return 0;
    }
}
