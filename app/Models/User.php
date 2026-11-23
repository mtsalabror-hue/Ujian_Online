<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'status',
        'exam_locked_until',
    ];

    /**
     * Alamat email untuk keperluan reset password.
     * Gunakan email kolom users; fallback ke email profil guru.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->email ?: ($this->guru?->email ?? '');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => 'boolean',
            'exam_locked_until' => 'datetime',
        ];
    }

    // Relasi akun dengan data guru
    public function guru(): HasOne
    {
        return $this->hasOne(
            Guru::class,
            'user_id',
            'id'
        );
    }

    // Relasi akun dengan data siswa
    public function siswa(): HasOne
    {
        return $this->hasOne(
            Siswa::class,
            'user_id',
            'id'
        );
    }
}
