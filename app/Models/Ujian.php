<?php

namespace App\Models;

use App\Models\BankSoal;
use App\Models\DetailUjian;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ujian extends Model
{
    protected $table = 'ujian';

    protected $primaryKey = 'id_ujian';

    protected $fillable = [
        'id_guru',
        'id_mapel',
        'id_kelas',
        'id_tahun',
        'judul_ujian',
        'tanggal',
        'jam_mulai',
        'durasi',
        'status',
    ];

    // Ujian dibuat oleh satu guru
    public function guru(): BelongsTo
    {
        return $this->belongsTo(
            Guru::class,
            'id_guru',
            'id_guru'
        );
    }

    // Ujian memiliki satu mata pelajaran
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(
            MataPelajaran::class,
            'id_mapel',
            'id_mapel'
        );
    }

    // Ujian ditujukan untuk satu kelas
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(
            Kelas::class,
            'id_kelas',
            'id_kelas'
        );
    }

    // Ujian berada pada satu tahun ajaran
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(
            TahunAjaran::class,
            'id_tahun',
            'id_tahun'
        );
    }

    // Satu ujian memiliki banyak detail ujian
    public function detailUjian(): HasMany
    {
        return $this->hasMany(
            DetailUjian::class,
            'id_ujian',
            'id_ujian'
        );
    }

    // Satu ujian memiliki banyak soal melalui detail_ujian
    public function bankSoal(): BelongsToMany
    {
        return $this->belongsToMany(
            BankSoal::class,
            'detail_ujian',
            'id_ujian',
            'id_soal'
        );
    }

    // Satu ujian memiliki banyak jawaban siswa
    public function jawabanSiswa(): HasMany
    {
        return $this->hasMany(
            JawabanSiswa::class,
            'id_ujian',
            'id_ujian'
        );
    }

    // Satu ujian memiliki banyak hasil ujian siswa
    public function hasilUjian(): HasMany
    {
        return $this->hasMany(
            HasilUjian::class,
            'id_ujian',
            'id_ujian'
        );
    }

    /**
     * Waktu pelaksanaan ujian dimulai (tanggal + jam_mulai).
     */
    public function waktuMulai(): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::parse($this->tanggal . ' ' . $this->jam_mulai);
    }

    /**
     * Waktu selesai ujian (jam_mulai + durasi) — batas akhir yang sama untuk semua siswa.
     * Siswa yang telat akan mendapat sisa waktu yang tersisa sampai batas ini.
     */
    public function waktuSelesai(): \Illuminate\Support\Carbon
    {
        return $this->waktuMulai()->addMinutes((int) $this->durasi);
    }

    /**
     * Apakah waktu pelaksanaan ujian sudah tiba (boleh diakses siswa).
     */
    public function sudahMulai(): bool
    {
        return now()->greaterThanOrEqualTo($this->waktuMulai());
    }
}
