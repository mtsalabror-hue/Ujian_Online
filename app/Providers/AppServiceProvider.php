<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination memakai tampilan Bootstrap (bukan Tailwind bawaan),
        // agar panah prev/next & nomor halaman tampil rapi sesuai tema aplikasi.
        Paginator::useBootstrapFive();

        // Bagikan data pengaturan (nama & logo sekolah) ke semua view,
        // supaya logo yang diupload admin tampil di sidebar, login, dan landing.
        // Data yang dikirim eksplisit dari controller tetap lebih diutamakan.
        // Guard Schema::hasTable agar aman saat migrate/fresh-install.
        if (Schema::hasTable('settings')) {
            View::share('setting', Setting::first());
        }
    }
}
