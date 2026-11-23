<?php

namespace App\Models;

use App\Models\Ujian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $primaryKey = 'id_tahun';

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'status',
    ];

    // Satu tahun ajaran dapat memiliki banyak ujian
    public function ujian(): HasMany
    {
        return $this->hasMany(
            Ujian::class,
            'id_tahun',
            'id_tahun'
        );
    }
}
