<?php

namespace App\Models;

use App\Models\BankSoal;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';

    protected $primaryKey = 'id_mapel';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
    ];

    // Satu mata pelajaran memiliki banyak soal
    public function bankSoal(): HasMany
    {
        return $this->hasMany(
            BankSoal::class,
            'id_mapel',
            'id_mapel'
        );
    }

    // Satu mata pelajaran dapat memiliki banyak ujian
    public function ujian(): HasMany
    {
        return $this->hasMany(
            Ujian::class,
            'id_mapel',
            'id_mapel'
        );
    }

    // Guru-guru yang mengampu mata pelajaran ini
    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Guru::class,
            'guru_mata_pelajaran',
            'id_mapel',
            'id_guru'
        );
    }
}
