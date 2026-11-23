<?php

namespace App\Models;

use App\Models\BankSoal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    protected $table = 'guru';

    protected $primaryKey = 'id_guru';

    protected $fillable = [
        'user_id',
        'nik',
        'nama_guru',
        'jenis_kelamin',
        'email',
        'no_hp',
        'alamat',
    ];

    // Satu guru memiliki satu akun user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Satu guru dapat membuat banyak soal
    public function bankSoal(): HasMany
    {
        return $this->hasMany(BankSoal::class, 'id_guru', 'id_guru');
    }

    // Satu guru dapat membuat banyak ujian
    public function ujian(): HasMany
    {
        return $this->hasMany(Ujian::class, 'id_guru', 'id_guru');
    }

    // Mata pelajaran yang diampu oleh guru (dipilih saat tambah/edit guru oleh admin)
    public function mataPelajaran(): BelongsToMany
    {
        return $this->belongsToMany(
            MataPelajaran::class,
            'guru_mata_pelajaran',
            'id_guru',
            'id_mapel'
        );
    }

    // Kelas yang diajar oleh guru (dipilih saat tambah/edit guru oleh admin)
    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(
            Kelas::class,
            'guru_kelas',
            'id_guru',
            'id_kelas'
        );
    }
}
