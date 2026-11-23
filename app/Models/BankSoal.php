<?php

namespace App\Models;

use App\Models\DetailUjian;
use App\Models\JawabanSiswa;
use App\Models\MataPelajaran;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BankSoal extends Model
{
    protected $table = 'bank_soal';

    protected $primaryKey = 'id_soal';

    protected $fillable = [
        'id_guru',
        'id_mapel',
        'id_kelas',
        'jenis_soal',
        'pertanyaan',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'jawaban_benar',
        'bobot',
    ];

    // Soal dibuat oleh satu guru
    public function guru(): BelongsTo
    {
        return $this->belongsTo(
            Guru::class,
            'id_guru',
            'id_guru'
        );
    }

    // Soal termasuk dalam satu mata pelajaran
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(
            MataPelajaran::class,
            'id_mapel',
            'id_mapel'
        );
    }

    // Soal diperuntukkan bagi kelas tertentu
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(
            Kelas::class,
            'id_kelas',
            'id_kelas'
        );
    }

    // Satu soal dapat memiliki banyak detail ujian
    public function detailUjian(): HasMany
    {
        return $this->hasMany(
            DetailUjian::class,
            'id_soal',
            'id_soal'
        );
    }

    // Satu soal dapat digunakan di banyak ujian
    public function ujian(): BelongsToMany
    {
        return $this->belongsToMany(
            Ujian::class,
            'detail_ujian',
            'id_soal',
            'id_ujian'
        );
    }

    // Satu soal dapat memiliki banyak jawaban siswa
    public function jawabanSiswa(): HasMany
    {
        return $this->hasMany(
            JawabanSiswa::class,
            'id_soal',
            'id_soal'
        );
    }
}
