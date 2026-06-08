<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Lampiran A</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h3 { margin: 0 0 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h3>Pemantauan Status Kontrak Ditandatangani (Lampiran A)</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>JABATAN / BAHAGIAN</th>
                <th>BAHAGIAN / UNIT</th>
                <th>TAJUK PEROLEHAN</th>
                <th>KAEDAH PEROLEHAN</th>
                <th>TARIKH SST</th>
                <th>TARIKH SST DISETUJU TERIMA</th>
                <th>TARIKH AKHIR KONTRAK PERLU DIMATIKAN SETEM</th>
                <th>NAMA PEMBEKAL</th>
                <th>TELAH TANDATANGAN (TARIKH DUTI SETEM)</th>
                <th>BELUM TANDATANGAN (STATUS & TARIKH PERGERAKAN KONTRAK)</th>
                <th>SILA NYATAKAN SEBAB JIKA KONTRAK DITANDATANGANI LEBIH 3 BULAN DARI TARIKH SST DISETUJU TERIMA</th>
                <th>CATATAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['jabatan'] ?? '-' }}</td>
                    <td>{{ $row['bahagian_unit'] ?? '-' }}</td>
                    <td>{{ $row['tajuk_kontrak'] ?? '-' }}</td>
                    <td>{{ $row['kaedah_perolehan'] ?? '-' }}</td>
                    <td>{{ $row['tarikh_sst'] ?? '-' }}</td>
                    <td>{{ $row['tarikh_sst_disetujui_terima'] ?? '-' }}</td>
                    <td>{{ $row['tarikh_akhir_kontrak_perlu_dimatikan_setem'] ?? '-' }}</td>
                    <td>{{ $row['nama_syarikat'] ?? '-' }}</td>
                    <td>{{ $row['telah_tandatangan_tarikh_duti_setem'] ?? '-' }}</td>
                    <td>{{ $row['belum_tandatangan_status_tarikh_pergerakan'] ?? '-' }}</td>
                    <td>{{ $row['sebab_lewat_tandatangan'] ?? '-' }}</td>
                    <td>{{ $row['catatan_kontrak'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
