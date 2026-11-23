<?php

namespace App\Models;

use App\Models\Siswa;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilUjian extends Model
{
    protected $table = 'hasil_ujian';

    protected $primaryKey = 'id_hasil';

    protected $fillable = [
        'id_ujian',
        'id_siswa',
        'nilai',
        'tanggal_selesai',
        'status',
        'pelanggaran',
        'urutan_soal',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'tanggal_selesai' => 'datetime',
        'urutan_soal' => 'array',
    ];

    // Hasil merupakan hasil dari satu ujian
    public function ujian(): BelongsTo
    {
        return $this->belongsTo(
            Ujian::class,
            'id_ujian',
            'id_ujian'
        );
    }

    // Hasil ujian dimiliki oleh satu siswa
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(
            Siswa::class,
            'id_siswa',
            'id_siswa'
        );
    }
}
