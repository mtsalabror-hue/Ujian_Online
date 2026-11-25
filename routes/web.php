<?php

use App\Http\Controllers\Admin\DashboardController;
/*
|--------------------------------------------------------------------------
| Landing & Authentication
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\HasilSiswaController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\UploadGuruController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Guru\BankSoalController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\HasilUjianController;
use App\Http\Controllers\Guru\UjianController as GuruUjianController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Siswa\UjianController as SiswaUjianController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/pilih-login', [LandingController::class, 'pilihLogin'])
    ->name('pilih.login');

/*
|--------------------------------------------------------------------------
| Login Gabungan — satu form untuk admin, guru, dan siswa.
| Rute login lama per peran dialihkan ke sini supaya tautan lama
| (mis. /login/guru, /login/siswa, /login/admin) tidak rusak.
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::get('/login/admin', function () {
    return redirect()->route('login');
})->name('login.admin');

Route::get('/login/guru', function () {
    return redirect()->route('login');
})->name('login.guru');

Route::get('/login/siswa', function () {
    return redirect()->route('login');
})->name('login.siswa');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.process');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Kembali ke akun admin setelah masuk sebagai guru.
| Di luar grup admin karena saat dipakai pengguna sedang login
| sebagai guru (role admin tidak lagi berlaku).
|--------------------------------------------------------------------------
*/

Route::post('/kembali-ke-admin', [ImpersonateController::class, 'kembaliKeAdmin'])
    ->middleware('auth')
    ->name('impersonate.kembali');

/*
|--------------------------------------------------------------------------
| Lupa Password
|--------------------------------------------------------------------------
*/

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->name('password.email');

Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->name('password.store');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('guru', GuruController::class);

        // Admin masuk sebagai guru (tanpa logout dari admin)
        Route::post('guru/{guru}/masuk-sebagai-guru', [ImpersonateController::class, 'masukSebagaiGuru'])
            ->name('guru.impersonate');

        // Pantau status upload guru & hasil siswa
        Route::get('upload-guru', [UploadGuruController::class, 'index'])
            ->name('upload-guru.index');

        Route::get('hasil-siswa', [HasilSiswaController::class, 'index'])
            ->name('hasil-siswa.index');

        // Sub-routes untuk siswa harus DISEBELUM resource('siswa')
        Route::get('siswa/export/excel', [SiswaController::class, 'exportExcel'])
            ->name('siswa.export.excel');

        Route::get('siswa/export/pdf', [SiswaController::class, 'exportPdf'])
            ->name('siswa.export.pdf');

        Route::get('siswa/template', [SiswaController::class, 'template'])
            ->name('siswa.template');

        Route::post('siswa/import', [SiswaController::class, 'import'])
            ->name('siswa.import');

        Route::get('siswa/download-kartu-pdf', [SiswaController::class, 'downloadKartuPdf'])
            ->name('siswa.downloadKartuPdf');

        Route::get('siswa/cetak-kartu', [SiswaController::class, 'cetakKartu'])
            ->name('siswa.cetakKartu');

        Route::resource('siswa', SiswaController::class);

        Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas']);

        Route::resource('mata-pelajaran', MataPelajaranController::class);

        Route::resource('tahun-ajaran', TahunAjaranController::class);

        Route::resource('pengguna', PenggunaController::class);

        Route::get('pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPassword'])
            ->name('pengguna.reset');

        Route::post('pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPasswordStore'])
            ->name('pengguna.reset.store');

        Route::prefix('pengaturan')->name('pengaturan.')->group(function () {

            Route::get('/', [SettingController::class, 'index'])
                ->name('index');

            Route::put('/', [SettingController::class, 'update'])
                ->name('update');

            Route::put('/password', [SettingController::class, 'changePassword'])
                ->name('password');
        });
    });

Route::middleware(['auth', 'role:guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {

        Route::get('/dashboard', [GuruDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Bank Soal
        |--------------------------------------------------------------------------
        */

        Route::get(
            'bank-soal/template/doc',
            [BankSoalController::class, 'templateDoc']
        )
            ->name('bank-soal.template.doc');

        Route::get(
            'bank-soal/mapel/{id_mapel}/download-word',
            [BankSoalController::class, 'exportMapelWord']
        )
            ->name('bank-soal.export-mapel-word');

        Route::post(
            'bank-soal/import/doc',
            [BankSoalController::class, 'importDoc']
        )
            ->name('bank-soal.import.doc');

        Route::get(
            'bank-soal/mapel/{id_mapel}',
            [BankSoalController::class, 'detailMapel']
        )->name('bank-soal.mapel');

        Route::get(
            'bank-soal/mapel/{id_mapel}/edit',
            [BankSoalController::class, 'editMapel']
        )->name('bank-soal.editMapel');

        Route::put(
            'bank-soal/mapel/{id_mapel}',
            [BankSoalController::class, 'updateMapel']
        )->name('bank-soal.updateMapel');

        Route::delete(
            'bank-soal/mapel/{id_mapel}',
            [BankSoalController::class, 'destroyMapel']
        )->name('bank-soal.destroyMapel');

        // Upload gambar dari Summernote editor
        Route::post(
            'bank-soal/upload-image',
            [BankSoalController::class, 'uploadImage']
        )->name('bank-soal.uploadImage');

        Route::resource('bank-soal', BankSoalController::class);

        /*
        |--------------------------------------------------------------------------
        | Kelola Ujian
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/ujian/jadwal',
            [GuruUjianController::class, 'jadwal']
        )
            ->name('ujian.jadwal');

        Route::get(
            '/ujian/peserta',
            [GuruUjianController::class, 'peserta']
        )
            ->name('ujian.peserta');

        Route::get(
            '/ujian/publish',
            [GuruUjianController::class, 'publish']
        )
            ->name('ujian.publish');

        Route::patch(
            '/ujian/{ujian}/toggle-publish',
            [GuruUjianController::class, 'togglePublish']
        )
            ->name('ujian.toggle-publish');

        Route::resource('ujian', GuruUjianController::class);

        /*
        |--------------------------------------------------------------------------
        | Hasil Ujian
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/hasil',
            [HasilUjianController::class, 'index']
        )
            ->name('hasil.index');

        Route::get(
            '/hasil/peserta',
            [HasilUjianController::class, 'peserta']
        )
            ->name('hasil.peserta');

        Route::get(
            '/hasil/export',
            [HasilUjianController::class, 'export']
        )
            ->name('hasil.export');

        Route::get(
            '/hasil/export/excel',
            [HasilUjianController::class, 'exportExcel']
        )
            ->name('hasil.export.excel');

        Route::get(
            '/hasil/export/word',
            [HasilUjianController::class, 'exportWord']
        )
            ->name('hasil.export.word');

        Route::get(
            '/hasil/export-per-mapel/excel',
            [HasilUjianController::class, 'exportPerMapelExcel']
        )
            ->name('hasil.export.mapel.excel');

        Route::get(
            '/hasil/export-per-mapel/word',
            [HasilUjianController::class, 'exportPerMapelWord']
        )
            ->name('hasil.export.mapel.word');

        Route::get(
            '/hasil/ujian/{ujian}/siswa/{siswa}',
            [HasilUjianController::class, 'nilaiSiswa']
        )
            ->name('hasil.nilai');

        Route::post(
            '/hasil/ujian/{ujian}/siswa/{siswa}',
            [HasilUjianController::class, 'simpanNilai']
        )
            ->name('hasil.simpan');

    });

Route::middleware(['auth', 'role:siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return redirect()->route('siswa.ujian');
        })->name('dashboard');

        Route::get(
            '/ujian',
            [SiswaUjianController::class, 'index']
        )
            ->name('ujian');

        Route::get(
            '/ujian/{ujian}',
            [SiswaUjianController::class, 'show']
        )
            ->name('ujian.show');

        Route::get(
            '/ujian/{ujian}/mulai',
            [SiswaUjianController::class, 'mulai']
        )
            ->name('ujian.mulai');

        Route::post(
            '/ujian/{ujian}/mulai',
            [SiswaUjianController::class, 'start']
        )
            ->name('ujian.start');

        Route::get(
            '/ujian/{ujian}/kerjakan',
            [SiswaUjianController::class, 'kerjakan']
        )
            ->name('ujian.kerjakan');

        Route::post(
            '/ujian/{ujian}/jawaban',
            [SiswaUjianController::class, 'simpanJawaban']
        )
            ->name('ujian.jawaban');

        Route::post(
            '/ujian/{ujian}/selesai',
            [SiswaUjianController::class, 'selesai']
        )
            ->name('ujian.selesai');

        Route::post(
            '/ujian/{ujian}/pelanggaran',
            [SiswaUjianController::class, 'pelanggaran']
        )
            ->name('ujian.pelanggaran');
    });
