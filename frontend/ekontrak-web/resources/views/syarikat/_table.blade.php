@php
    $payload = is_array($companies ?? null) ? $companies : [];

    if (isset($payload['data']) && is_array($payload['data']) && array_key_exists('data', $payload['data'])) {
        $data = $payload['data']['data'] ?? [];
        $meta = $payload['data']['meta'] ?? $payload['data'];
    } else {
        $data = $payload['data'] ?? $payload;
        $meta = isset($payload['meta']) ? $payload['meta'] : $payload;
    }

    if (!is_array($data)) {
        $data = [];
    }

    $total    = (int) ($meta['total'] ?? 0);
    $lastPage = (int) ($meta['last_page'] ?? 1);
    $curPage  = (int) ($meta['current_page'] ?? 1);
    $from     = (int) ($meta['from'] ?? 0);
    $to       = (int) ($meta['to'] ?? 0);
    $perPage  = (int) ($meta['per_page'] ?? request('per_page', 10));
    $startNo  = $from > 0 ? $from : (($curPage - 1) * max($perPage, 1) + 1);
    $displayFrom = $total > 0 ? (($from ?? 0) > 0 ? $from : 1) : 0;
    $displayTo   = $total > 0 ? (($to ?? 0) > 0 ? $to : ($displayFrom + max(count($data ?? []) - 1, 0))) : 0;
@endphp

<x-table.with-pagination
    :from="$displayFrom"
    :to="$displayTo"
    :total="$total"
    :currentPage="$curPage"
    :lastPage="$lastPage"
    reloadFn="reloadSyarikat"
    :showPerPage="true"
    perPageId="perPageSyarikat"
    :perPage="request('per_page', 10)"
>
    <x-table
        :headers="['Bil', 'Nama Syarikat', 'Alamat', 'Pegawai Dihubungi 1', 'Pegawai Dihubungi 2', 'Pegawai Dihubungi 3']"
        wrap-class="table-scroll"
        table-class="table align-middle pivot-table mb-0"
    >
        @forelse($data as $company)
            @continue(!is_array($company))
            <tr>
                <td style="color:#9ca3af;font-size:0.8rem;">{{ $startNo + $loop->index }}</td>
                <td>
                    <a href="#" style="color:#2563eb;font-weight:600;text-decoration:none;"
                        onclick="event.preventDefault();openCompanyDetail({{ $company['id'] }})">
                        {{ $company['nama_syarikat'] ?? '—' }}
                    </a>
                </td>
                <td style="max-width:320px;font-size:0.82rem;line-height:1.45;">{{ $company['alamat'] ?? '—' }}</td>
                <td style="font-size:0.82rem;">{{ !empty($company['pegawai_hubungi_1_nama']) ? $company['pegawai_hubungi_1_nama'] : 'TIADA' }}</td>
                <td style="font-size:0.82rem;">{{ !empty($company['pegawai_hubungi_2_nama']) ? $company['pegawai_hubungi_2_nama'] : 'TIADA' }}</td>
                <td style="font-size:0.82rem;">{{ !empty($company['pegawai_hubungi_3_nama']) ? $company['pegawai_hubungi_3_nama'] : 'TIADA' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:2rem;color:#9ca3af;">
                    Tiada rekod syarikat dijumpai.
                </td>
            </tr>
        @endforelse
    </x-table>
</x-table.with-pagination>
