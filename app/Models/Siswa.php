<?php

namespace App\Models;

use App\Models\JawabanSiswa;
use App\Models\HasilUjian;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    protected $fillable = [
        'user_id',
        'id_kelas',
        'nis',
        'nisn',
        'nama_siswa',
        'jenis_kelamin',
        'foto',
    ];

    // Siswa memiliki satu akun user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Siswa berada dalam satu kelas
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(
            Kelas::class,
            'id_kelas',
            'id_kelas'
        );
    }

    // Siswa memiliki banyak jawaban
    public function jawabanSiswa(): HasMany
    {
        return $this->hasMany(
            JawabanSiswa::class,
            'id_siswa',
            'id_siswa'
        );
    }

    // Siswa memiliki banyak hasil ujian
    public function hasilUjian(): HasMany
    {
        return $this->hasMany(
            HasilUjian::class,
            'id_siswa',
            'id_siswa'
        );
    }
}
