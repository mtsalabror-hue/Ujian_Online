<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $skipped = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $data = $this->normalizeRow($row);

            $nis = trim((string) ($data['nis'] ?? ''));
            $nama = trim((string) ($data['nama_siswa'] ?? ''));
            // Normalisasi nama: hanya huruf awal tiap kata yang kapital
            // (mis. "SINTA NUR FAUZIAH" -> "Sinta Nur Fauziah").
            $nama = mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8');

            if ($nis === '' || $nama === '') {
                $this->skipped++;
                continue;
            }

            $kelas = $this->findKelas((string) ($data['kelas'] ?? ''));
            if (! $kelas) {
                $this->skipped++;
                continue;
            }

            // Skip baris dengan NIS yang sudah dipakai (hindari crash keseluruhan import)
            if (Siswa::where('nis', $nis)->exists()) {
                $this->skipped++;
                continue;
            }

            $jenisKelamin = strtoupper(trim((string) ($data['jenis_kelamin'] ?? '')));
            if (! in_array($jenisKelamin, ['L', 'P'])) {
                $jenisKelamin = 'L';
            }

            DB::transaction(function () use ($data, $kelas, $jenisKelamin, $nis, $nama) {
                // Password default = NISN (atau NIS kalau NISN kosong).
                $password = !empty($data['nisn']) ? trim((string) $data['nisn']) : $nis;

                $user = User::create([
                    'username' => $nis,
                    'password' => Hash::make($password),
                    'role'     => 'siswa',
                    'status'   => true,
                ]);

                Siswa::create([
                    'user_id'       => $user->id,
                    'id_kelas'      => $kelas->id_kelas,
                    'nis'           => $nis,
                    'nisn'          => $data['nisn'] ?? null,
                    'nama_siswa'    => $nama,
                    'jenis_kelamin' => $jenisKelamin,
                ]);
            });

            $this->imported++;
        }
    }

    /**
     * Ubah key baris menjadi lowercase + underscore, supaya heading seperti
     * "Nama Siswa", "Jenis Kelamin", dsb tetap terbaca.
     */
    private function normalizeRow($row): array
    {
        $result = [];
        foreach ($row->toArray() as $key => $value) {
            $normalized = strtolower(trim((string) $key));
            $normalized = str_replace([' ', '-', '/'], '_', $normalized);
            $normalized = preg_replace('/_+/', '_', $normalized);
            if ($normalized !== '') {
                $result[$normalized] = $value;
            }
        }
        return $result;
    }

    private function findKelas(string $nama): ?Kelas
    {
        return Kelas::whereRaw('LOWER(nama_kelas) = ?', [mb_strtolower(trim($nama))])->first();
    }
}
