<?php

namespace App\Models;

use App\Models\BankSoal;
use App\Models\Siswa;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $primaryKey = 'id_kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
    ];

    // Satu kelas memiliki banyak siswa
    public function siswa(): HasMany
    {
        return $this->hasMany(
            Siswa::class,
            'id_kelas',
            'id_kelas'
        );
    }

    // Satu kelas dapat memiliki banyak ujian
    public function ujian(): HasMany
    {
        return $this->hasMany(
            Ujian::class,
            'id_kelas',
            'id_kelas'
        );
    }

    // Satu kelas dapat memiliki banyak bank soal
    public function bankSoal(): HasMany
    {
        return $this->hasMany(
            BankSoal::class,
            'id_kelas',
            'id_kelas'
        );
    }
}
