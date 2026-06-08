<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Lampiran B</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h3 { margin: 0 0 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h3>Pemantauan Tempoh Kontrak (Lampiran B)</h3>
    <table>
        <thead>
            <tr>
                <th rowspan="2">#</th>
                <th rowspan="2">JABATAN / BAHAGIAN</th>
                <th rowspan="2">BAHAGIAN / UNIT</th>
                <th rowspan="2">TAJUK PEROLEHAN</th>
                <th rowspan="2">KAEDAH PEROLEHAN</th>
                <th rowspan="2">TARIKH SST DISETUJU TERIMA</th>
                <th colspan="3" style="text-align:center;">TARIKH KONTRAK</th>
                <th rowspan="2">NAMA PEMBEKAL</th>
                <th rowspan="2">CATATAN</th>
            </tr>
            <tr>
                <th>TARIKH MULA PERKHIDMATAN</th>
                <th>TARIKH TAMAT PERKHIDMATAN</th>
                <th>TEMPOH KONTRAK</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $row)
                @php $tempoh = (int) ($row['tempoh_bulan'] ?? 0); @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['jabatan'] ?? '-' }}</td>
                    <td>{{ $row['bahagian_unit'] ?? '-' }}</td>
                    <td>{{ $row['tajuk_kontrak'] ?? '-' }}</td>
                    <td>{{ $row['kaedah_perolehan'] ?? '-' }}</td>
                    <td>{{ $row['tarikh_sst_disetujui_terima'] ?? '-' }}</td>
                    <td>{{ $row['mula_tarikh'] ?? '-' }}</td>
                    <td>{{ $row['tamat_tarikh'] ?? '-' }}</td>
                    <td>{{ $tempoh > 0 ? $tempoh . ' Bulan' : '-' }}</td>
                    <td>{{ $row['nama_syarikat'] ?? '-' }}</td>
                    <td>{{ $row['catatan_kontrak'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
