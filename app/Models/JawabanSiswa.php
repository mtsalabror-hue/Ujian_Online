<?php

namespace App\Models;

use App\Models\BankSoal;
use App\Models\Siswa;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanSiswa extends Model
{
    protected $table = 'jawaban_siswa';

    protected $primaryKey = 'id_jawaban';

    protected $fillable = [
        'id_ujian',
        'id_siswa',
        'id_soal',
        'jawaban',
        'status_benar',
        'skor',
    ];

    protected $casts = [
        'status_benar' => 'boolean',
    ];

    // Jawaban merupakan bagian dari satu ujian
    public function ujian(): BelongsTo
    {
        return $this->belongsTo(
            Ujian::class,
            'id_ujian',
            'id_ujian'
        );
    }

    // Jawaban dimiliki oleh satu siswa
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(
            Siswa::class,
            'id_siswa',
            'id_siswa'
        );
    }

    // Jawaban mengacu pada satu soal
    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(
            BankSoal::class,
            'id_soal',
            'id_soal'
        );
    }
}
