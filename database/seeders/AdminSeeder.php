<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Kredensial admin dibaca dari .env (ADMIN_USERNAME / ADMIN_PASSWORD),
     * bukan hardcoded di seeder. Kalau ADMIN_PASSWORD belum diatur,
     * seeder dilewati supaya tidak membuat akun dengan password kosong.
     */
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD');

        if (empty($password)) {
            $this->command->warn('ADMIN_PASSWORD belum diatur di .env — akun admin tidak dibuat/diubah.');

            return;
        }

        User::updateOrCreate(
            [
                'username' => $username,
            ],
            [
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => true,
            ]
        );
    }
}
