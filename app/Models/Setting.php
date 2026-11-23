<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'alamat',
        'telepon',
        'email',
        'website',
        'kepala_sekolah',
        'logo',
        'ttd_image',
        'stempel_image',
        'durasi_default',
        'acak_soal',
        'acak_jawaban',
        'tampilkan_nilai',
    ];
}
