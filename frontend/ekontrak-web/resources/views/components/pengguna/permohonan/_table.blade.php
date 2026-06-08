@php
    $data     = $rows['data'] ?? [];
    $meta     = isset($rows['meta']) ? $rows['meta'] : $rows;
    $total    = $meta['total'] ?? 0;
    $lastPage = $meta['last_page'] ?? 1;
    $curPage  = $meta['current_page'] ?? 1;
    $from     = $meta['from'] ?? 0;
    $to       = $meta['to'] ?? 0;
    $displayFrom = $total > 0 ? (($from ?? 0) > 0 ? $from : 1) : 0;
    $displayTo   = $total > 0 ? (($to ?? 0) > 0 ? $to : ($displayFrom + max(count($data) - 1, 0))) : 0;

    $rolePill = [
        'admin'                 => ['label' => 'Admin',              'class' => 'pill-red'],
        'admin_sistem'          => ['label' => 'Admin Sistem',       'class' => 'pill-orange'],
        'pendaftar_kontrak'     => ['label' => 'Pendaftar Kontrak',  'class' => 'pill-blue'],
        'pemilik_projek'        => ['label' => 'Pemilik Projek',     'class' => 'pill-green'],
        'pegawai_undang_undang' => ['label' => 'Peg. Undang-Undang', 'class' => 'pill-purple'],
    ];

    $statusPill = [
        'pending' => ['label' => 'Dalam Tindakan', 'class' => 'pill-amber'],
        'diluluskan' => ['label' => 'Diluluskan', 'class' => 'pill-green'],
        'ditolak' => ['label' => 'Ditolak', 'class' => 'pill-red'],
    ];

    $jenisLabel = [
        'pendaftaran_online' => 'Pendaftaran Online',
        'pengaktifan_semula_id' => 'Pengaktifan Semula ID',
        'penukaran_peranan' => 'Penukaran Peranan',
    ];
@endphp

<x-table.with-pagination
    :from="$displayFrom"
    :to="$displayTo"
    :total="$total"
    :currentPage="$curPage"
    :lastPage="$lastPage"
    reloadFn="reloadPermohonanTable"
    :showPerPage="true"
    perPageId="perPagePermohonan"
    :perPage="request('per_page', 10)"
>
    <x-table
        :headers="['Bil', 'No. Kad Pengenalan/No. Tentera', 'Nama Penuh', 'Peranan', 'Akses', 'Jenis Permohonan', 'Tarikh Permohonan', 'Status Permohonan', 'Tindakan']"
        wrap-class="table-scroll"
        table-class="table align-middle pivot-table mb-0"
    >
        @forelse($data as $i => $row)
            <tr>
                <td style="color:#9ca3af;font-size:0.8rem;">{{ (($curPage - 1) * ($meta['per_page'] ?? 10)) + $i + 1 }}</td>
                <td style="font-family:monospace;font-size:0.82rem;">
                    {{ $row['ic_number'] ?? '-' }}
                    @if(!empty($row['no_tentera']))
                        <div style="font-family:inherit;font-size:0.75rem;color:#64748b;margin-top:0.2rem;">{{ $row['no_tentera'] }}</div>
                    @endif
                </td>
                <td>
                    <div style="font-weight:600;color:var(--gray-900);">{{ $row['name'] ?? '-' }}</div>
                    <div style="font-size:0.76rem;color:#64748b;">{{ $row['email'] ?? '-' }}</div>
                </td>
                <td>
                    <div style="display:flex;flex-wrap:wrap;gap:0.25rem;">
                        @foreach(($row['roles'] ?? []) as $role)
                            @php $r = is_array($role) ? ($role['name'] ?? '') : (string) $role; @endphp
                            @if(isset($rolePill[$r]))
                                <span class="pill {{ $rolePill[$r]['class'] }}">{{ $rolePill[$r]['label'] }}</span>
                            @endif
                        @endforeach
                    </div>
                </td>
                <td>{{ $row['akses_scope'] ?? '-' }}</td>
                <td>{{ $jenisLabel[$row['jenis_permohonan'] ?? ''] ?? '-' }}</td>
                <td style="font-size:0.8rem;">{{ !empty($row['created_at']) ? \Carbon\Carbon::parse($row['created_at'])->format('d/m/Y H:i') : '-' }}</td>
                <td>
                    @php $sp = $statusPill[$row['permohonan_status'] ?? 'pending'] ?? $statusPill['pending']; @endphp
                    <span class="pill {{ $sp['class'] }}">{{ $sp['label'] }}</span>
                </td>
                <td>
                    <button class="btn btn-outline-blue btn-sm" onclick="viewPermohonan({{ $row['id'] }})">Kelulusan</button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:2rem 1rem;color:#64748b;">Tiada rekod permohonan.</td>
            </tr>
        @endforelse
    </x-table>
</x-table.with-pagination>
