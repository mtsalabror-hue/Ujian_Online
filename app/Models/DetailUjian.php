<?php

namespace App\Models;

use App\Models\BankSoal;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailUjian extends Model
{
    protected $table = 'detail_ujian';

    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_ujian',
        'id_soal',
    ];

    // Detail ujian dimiliki oleh satu ujian
    public function ujian(): BelongsTo
    {
        return $this->belongsTo(
            Ujian::class,
            'id_ujian',
            'id_ujian'
        );
    }

    // Detail ujian mengacu pada satu soal
    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(
            BankSoal::class,
            'id_soal',
            'id_soal'
        );
    }
}
