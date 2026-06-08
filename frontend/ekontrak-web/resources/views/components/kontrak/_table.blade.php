@php
    $statusClass = [
        'KONTRAK_SELESAI'   => 'pill-green',
        'DALAM_PELAKSANAAN' => 'pill-blue',
        'MAKLUMAT_TIDAK_LENGKAP' => 'pill-red',
        'DRAF_KONTRAK'      => 'pill-amber',
        'DRAF'              => 'pill-gray',
        'EOT'               => 'pill-purple',
    ];
    $statusLabel = [
        'KONTRAK_SELESAI'   => 'Kontrak Selesai',
        'DALAM_PELAKSANAAN' => 'Dalam Pelaksanaan',
        'MAKLUMAT_TIDAK_LENGKAP' => 'Maklumat Tidak Lengkap',
        'DRAF_KONTRAK'      => 'Draf Kontrak',
        'DRAF'              => 'Draf',
        'EOT'               => 'EOT',
    ];
    $total    = $meta['total'] ?? 0;
    $lastPage = $meta['last_page'] ?? 1;
    $curPage  = $meta['current_page'] ?? 1;
    $from     = $meta['from'] ?? 0;
    $to       = $meta['to'] ?? 0;
    $displayFrom = $total > 0 ? (($from ?? 0) > 0 ? $from : 1) : 0;
    $displayTo   = $total > 0 ? (($to ?? 0) > 0 ? $to : ($displayFrom + max(count($data ?? []) - 1, 0))) : 0;
@endphp

@php
    function fmtDate($d) {
        if (!$d) return '—';
        try {
            $dt = new \DateTime($d);
            return $dt->format('d-m-Y');
        } catch (\Exception $e) { return $d; }
    }
@endphp

<x-table.with-pagination
    :from="$displayFrom"
    :to="$displayTo"
    :total="$total"
    :currentPage="$curPage"
    :lastPage="$lastPage"
    reloadFn="reloadKontrak"
    :showPerPage="true"
    perPageId="perPageKontrak"
    :perPage="request('per_page', 10)"
>
    <x-table
        :headers="['Bil', 'Tajuk Kontrak', 'No.Kontrak', 'Pemilik Projek', 'Tempoh Kontrak', 'Status', 'Status Draf Kontrak', 'Tarikh Tamat Dimatikan Setem']"
        wrap-class="table-scroll"
        table-class="table align-middle pivot-table mb-0"
    >
        @forelse($data as $i => $k)
            <tr style="cursor:pointer;" onclick="openKontrakModal({{ $k['id'] }})">
                <td style="color:#9ca3af;font-size:0.8rem;">{{ (($from ?? 0) > 0 ? $from : 1) + $i }}</td>
                <td>
                    <a href="#" style="color:#2563eb;font-weight:500;text-decoration:none;"
                        onclick="event.preventDefault();openKontrakModal({{ $k['id'] }})">
                        {{ $k['tajuk_kontrak'] ?? '—' }}
                    </a>
                </td>
                <td class="kontrak-no-cell">{{ $k['no_kontrak'] ?? '—' }}</td>
                <td style="font-size:0.82rem;">
                    <div>{{ $k['jabatan']['nama'] ?? '—' }}</div>
                    <div style="color:#9ca3af;font-size:0.75rem;">{{ $k['bahagian_unit']['nama'] ?? '' }}</div>
                </td>
                <td style="font-size:0.8rem;white-space:nowrap;line-height:1.45;color:#475569;">{{ fmtDate($k['mula_tarikh'] ?? null) }} - {{ fmtDate($k['tamat_tarikh'] ?? null) }}</td>
                <td>
                    @php $sc = $k['status_kontrak'] ?? ''; @endphp
                    <span class="pill kontrak-status-pill {{ $statusClass[$sc] ?? 'pill-gray' }}">
                        {{ $statusLabel[$sc] ?? $sc }}
                    </span>
                </td>
                <td style="font-size:0.8rem;color:#64748b;">{{ $k['status_draf_kontrak'] ?? (($k['status_draf_kompan'] ?? false) ? 'Draf Disediakan' : '—') }}</td>
                <td style="font-size:0.8rem;color:#64748b;">{{ fmtDate($k['tarikh_draf_hantar_sistem'] ?? null) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center;padding:3rem;color:#94a3b8;font-size:0.85rem;">
                    Tiada rekod kontrak dijumpai.
                </td>
            </tr>
        @endforelse
    </x-table>
</x-table.with-pagination>
