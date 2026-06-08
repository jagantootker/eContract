@if(empty($rows))
    <div style="padding:1.25rem;text-align:center;color:#9ca3af;font-size:0.85rem;">
        Tiada kontrak dalam kategori ini.
    </div>
@else
<x-table
    :headers="['#', 'Tajuk Kontrak', 'No. Kontrak', 'Tempoh Kontrak', 'Pemilik Projek']"
    wrap-class="table-scroll"
    table-class="table align-middle pivot-table mb-0"
>
    @foreach($rows as $i => $row)
        @php
            $rawNoKontrak = strtoupper(trim((string) ($row['no_kontrak'] ?? '')));
            $formattedNoKontrak = $rawNoKontrak;

            if ($rawNoKontrak === '') {
                $formattedNoKontrak = '—';
            } elseif (! preg_match('/^[A-Z0-9]+-\d{4}$/', $rawNoKontrak)) {
                if (preg_match('/^(.+?)[\/-]?(\d{4})$/', $rawNoKontrak, $matches)) {
                    $prefix = preg_replace('/[^A-Z0-9]/', '', $matches[1]);
                    $formattedNoKontrak = $prefix !== '' ? ($prefix . '-' . $matches[2]) : $rawNoKontrak;
                }
            }

            $modalPayload = [
                'tajuk_kontrak' => $row['tajuk_kontrak'] ?? null,
                'no_kontrak' => $formattedNoKontrak,
                'status_kontrak' => $row['status_kontrak'] ?? null,
                'nilai_kontrak' => $row['nilai_kontrak'] ?? null,
                'mula_tarikh' => isset($row['mula_tarikh']) ? \Carbon\Carbon::parse($row['mula_tarikh'])->format('d/m/Y') : null,
                'tamat_tarikh' => isset($row['tamat_tarikh']) ? \Carbon\Carbon::parse($row['tamat_tarikh'])->format('d/m/Y') : null,
                'jabatan' => $row['jabatan']['kod'] ?? null,
                'syarikat' => $row['syarikat']['nama_syarikat'] ?? null,
                'pegawai' => $row['pegawai_bertanggungjawab']['name'] ?? null,
                'kaedah_perolehan' => $row['kaedah_perolehan'] ?? null,
                'kategori_perolehan' => $row['kategori_perolehan'] ?? null,
                'catatan_kontrak' => $row['catatan_kontrak'] ?? null,
            ];
        @endphp
        <tr style="{{ $i % 2 === 0 ? 'background:white;' : 'background:#f9fafb;' }}">
            <td style="padding:0.7rem 1rem;color:#9ca3af;">{{ $i + 1 }}</td>
            <td style="padding:0.7rem 1rem;">
                <a href="#"
                   onclick='event.preventDefault(); openKontrakAlertModalById({{ (int)($row['id'] ?? 0) }}, @json($headerLabel ?? null), @json($modalPayload));'
                   style="color:#2563eb;font-weight:500;text-decoration:none;">
                    {{ $row['tajuk_kontrak'] ?? '—' }}
                </a>
            </td>
            <td style="padding:0.7rem 1rem;font-family:monospace;font-size:0.8rem;">{{ $formattedNoKontrak }}</td>
            <td style="padding:0.7rem 1rem;font-size:0.8rem;white-space:nowrap;">
                {{ isset($row['mula_tarikh']) ? \Carbon\Carbon::parse($row['mula_tarikh'])->format('d/m/Y') : '—' }}
                –
                {{ isset($row['tamat_tarikh']) ? \Carbon\Carbon::parse($row['tamat_tarikh'])->format('d/m/Y') : '—' }}
            </td>
            <td style="padding:0.7rem 1rem;font-size:0.82rem;">
                {{ $row['pegawai_bertanggungjawab']['name'] ?? '—' }}
            </td>
        </tr>
    @endforeach
</x-table>
@endif
