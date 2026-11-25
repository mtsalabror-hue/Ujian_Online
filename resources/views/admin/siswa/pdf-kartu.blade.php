<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kartu Ujian Siswa</title>
    <style>
        <?php echo $cssContent ?? ''; ?>
    </style>
</head>

<body>
    @php
    $chunks = $siswa->chunk(6);
    @endphp

    @forelse($chunks as $pageIndex => $pageSiswa)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <table class="grid-table">
            @foreach($pageSiswa->chunk(2) as $row)
            <tr>
                @foreach($row as $s)
                <td class="card-td">
                    <div class="card-box">
                        {{-- Header Kop --}}
                        <table class="header-tbl">
                            <tr>
                                <td style="width: 48px; vertical-align: middle; text-align: center;">
                                    @if(!empty($logoBase64))
                                    <img src="{{ $logoBase64 }}" width="42" height="42" style="width: 42px; height: 42px; max-width: 42px; max-height: 42px; object-fit: contain;" alt="Logo">
                                    @else
                                    <div style="font-weight: bold; font-size: 9pt; color: #0d6efd;">LOGO</div>
                                    @endif
                                </td>
                                <td style="vertical-align: middle;">
                                    <div class="title-school">{{ $setting->nama_sekolah ?? 'SMK IT AL-ABROR' }}</div>
                                    <div class="title-exam">KARTU PESERTA {{ strtoupper($namaUjian) }}</div>
                                    <div class="title-ta">Tahun Ajaran {{ $tahunAjaran }}</div>
                                </td>
                            </tr>
                        </table>

                        {{-- Body Biodata --}}
                        <table class="body-tbl">
                            <tr>
                                <td class="foto-td">
                                    <table style="width:100%; border-collapse:collapse;">
                                        <tr>
                                            @if(!empty($s->foto_base64))
                                            <td style="width:58px; height:75px; text-align:center; vertical-align:middle; border:1px solid #999; border-radius:3px;">
                                                <img src="{{ $s->foto_base64 }}" style="width:58px; height:75px; object-fit:cover; border-radius:2px;" alt="Foto">
                                            </td>
                                            @else
                                            <td class="foto-box">
                                                FOTO<br>3x4
                                            </td>
                                            @endif
                                        </tr>
                                    </table>
                                </td>
                                <td style="vertical-align: top; padding-left: 6px;">
                                    <table class="bio-tbl">
                                        <tr>
                                            <td class="label-td">Nama Peserta</td>
                                            <td>: <strong>{{ $s->nama_siswa }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="label-td">NIS / NISN</td>
                                            <td>: {{ $s->nis ?? '-' }} / {{ $s->nisn ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label-td">Kelas</td>
                                            <td>: {{ $s->kelas->nama_kelas ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label-td">Username</td>
                                            <td>: <span class="code-badge">{{ $s->nama_siswa }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="label-td">Password</td>
                                            <td>: <span class="code-badge">{{ !empty($s->nisn) ? $s->nisn : $s->nis }}</span></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- Footer Tanda Tangan --}}
                        <table class="footer-tbl">
                            <tr>
                                <td style="vertical-align: bottom; color: #666; font-style: italic; font-size: 6.5pt;">
                                    *Harap simpan kartu ini selama ujian.
                                </td>
                                <td class="sig-box" style="vertical-align: bottom; position: relative;">
                                    <div style="font-size: 7.5pt; color: #333; margin-bottom: 2px;">{{ $jabatanPenandatangan ?? 'Panitia Pelaksana' }},</div>
                                    <div style="position: relative; height: 32px; text-align: center;">
                                        @if(!empty($stempelBase64))
                                        <img src="{{ $stempelBase64 }}" style="position:absolute; top:-2px; left:-5px; width:36px; height:36px; object-fit:contain; opacity:0.82; z-index:1;" alt="Stempel">
                                        @endif
                                        @if(!empty($ttdBase64))
                                        <img src="{{ $ttdBase64 }}" style="position:absolute; bottom:0; left:50%; transform:translateX(-50%); max-height:30px; max-width:90px; object-fit:contain; z-index:2;" alt="TTD">
                                        @endif
                                        @if(empty($ttdBase64) && empty($stempelBase64))
                                        <div class="sig-space"></div>
                                        @endif
                                    </div>
                                    <div style="position: relative; z-index: 3; white-space: nowrap;">
                                        <strong class="sig-name" style="font-size: 8pt; white-space: nowrap;">{{ !empty($namaPenandatangan) ? $namaPenandatangan : '( ................................ )' }}</strong>
                                        @if(!empty($nipPenandatangan))
                                        <br><span style="font-size: 6.5pt; color: #555; white-space: nowrap;">NIP. {{ $nipPenandatangan }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                @endforeach
                @if($row->count() == 1)
                <td class="card-td"></td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>
    @empty
    <div style="text-align: center; padding: 50px;">
        <h3>Tidak ada siswa ditemukan untuk dicetak kartunya.</h3>
    </div>
    @endforelse
</body>

</html>