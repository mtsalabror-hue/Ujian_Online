<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Nilai Siswa - {{ $siswa->nama_siswa }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; margin-bottom: 8px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header h3 { margin: 4px 0 0; font-size: 13px; font-weight: normal; }
        hr { border: none; border-top: 2px solid #222; margin: 8px 0 14px; }
        .info { margin-bottom: 14px; }
        .info td { padding: 2px 8px 2px 0; }
        .info td.label { font-weight: bold; }
        table.nilai { width: 100%; border-collapse: collapse; }
        table.nilai th, table.nilai td { border: 1px solid #999; padding: 6px 8px; }
        table.nilai th { background: #eee; text-align: left; }
        table.nilai td.nomor, table.nilai th.nomor { text-align: center; width: 40px; }
        table.nilai td.nilai, table.nilai th.nilai { text-align: center; width: 90px; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 11px; color: #555; }
        .ttd { margin-top: 36px; width: 100%; }
        .ttd td { width: 50%; vertical-align: top; }
        .ttd .kanan { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $setting->nama_sekolah ?? 'MTs Al-Abror' }}</h2>
        <h3>Daftar Nilai Siswa</h3>
    </div>
    <hr>

    <table class="info">
        <tr>
            <td class="label">Nama Siswa</td>
            <td>: {{ $siswa->nama_siswa }}</td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td>: {{ $siswa->kelas->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIS</td>
            <td>: {{ $siswa->nis ?? '-' }}</td>
        </tr>
    </table>

    <table class="nilai">
        <thead>
            <tr>
                <th class="nomor">No</th>
                <th>Mata Pelajaran</th>
                <th class="nilai">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td class="nomor">{{ $loop->iteration }}</td>
                <td>{{ $row['mapel'] }}</td>
                <td class="nilai">{{ number_format($row['nilai'], 1) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; padding: 12px;">Belum ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y') }}
    </div>

    <table class="ttd">
        <tr>
            <td></td>
            <td class="kanan">
                Guru Mata Pelajaran
                <br><br><br><br>
                ( ______________________ )
            </td>
        </tr>
    </table>
</body>
</html>
