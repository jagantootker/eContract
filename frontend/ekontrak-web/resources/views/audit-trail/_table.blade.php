@php
    $rows = $logs['data'] ?? [];
    $from = $logs['from'] ?? 1;
    $total    = $logs['total'] ?? 0;
    $fromItem = $logs['from']  ?? 0;
    $toItem   = $logs['to']    ?? 0;
    $lastPage = $logs['last_page'] ?? 1;
    $currPage = $logs['current_page'] ?? 1;
@endphp

<x-table.with-pagination
    :from="$fromItem"
    :to="$toItem"
    :total="$total"
    :currentPage="$currPage"
    :lastPage="$lastPage"
    reloadFn="fetchTable"
    :showPerPage="true"
    perPageId="perPageAuditTrail"
    :perPage="request('per_page', 10)"
>
    <x-table
        :headers="['#', 'Tarikh & Masa', 'Pengguna', 'Tindakan', 'Model / Entiti', 'Alamat IP', 'Muatan Data']"
        wrap-class="table-scroll"
        table-class="table align-middle pivot-table mb-0"
    >
        @forelse($rows as $idx => $row)
        @php
            $a = strtolower($row['action'] ?? '');
            $m = strtolower((string)($row['model_type'] ?? ''));
            $badgeCls = 'default';
            if (str_contains($a, 'logout') || str_contains($m, 'logout'))         $badgeCls = 'logout';
            elseif (str_contains($a, 'login') || str_contains($m, 'login'))       $badgeCls = 'login';
            elseif (str_contains($a, 'creat') || str_contains($a, 'store'))       $badgeCls = 'create';
            elseif (str_contains($a, 'updat') || str_contains($a, 'kemaskini'))   $badgeCls = 'update';
            elseif (str_contains($a, 'delet') || str_contains($a, 'destroy'))     $badgeCls = 'delete';
            elseif (str_contains($a, 'approv') || str_contains($a, 'lulus'))      $badgeCls = 'approve';
            elseif (str_contains($a, 'reject') || str_contains($a, 'tolak'))      $badgeCls = 'reject';
            if (str_contains($a, 'logout') || str_contains($m, 'logout')) {
                $actionLabel = 'Logout';
            } elseif (str_contains($a, 'login') || str_contains($m, 'login')) {
                $actionLabel = 'Login';
            } else {
                $actionLabel = ucwords(str_replace('_', ' ', $row['action'] ?? '—'));
            }
            $dt = $row['created_at'] ? \Carbon\Carbon::parse($row['created_at']) : null;
            $payload = $row['payload'] ?? null;
        @endphp
        <tr>
            <td style="color:#94a3b8;font-size:0.75rem;font-weight:600;">{{ ($from ?? 1) + $idx }}</td>
            <td class="td-time">
                <div style="font-size:0.82rem;font-weight:600;color:var(--slate-700);">
                    {{ $dt ? $dt->format('d/m/Y') : '—' }}
                </div>
                <div style="font-size:0.72rem;color:#94a3b8;">
                    {{ $dt ? $dt->format('H:i:s') : '' }}
                </div>
            </td>
            <td class="td-user">
                <div style="font-weight:600;color:var(--slate-800);font-size:0.84rem;">
                    {{ $row['user']['name'] ?? '' }}
                    @if(empty($row['user']))
                        <span style="color:#94a3b8;font-style:italic;">Sistem</span>
                    @endif
                </div>
                @if(!empty($row['user']['ic_number']))
                    <div style="font-size:0.7rem;color:#94a3b8;font-family:monospace;">{{ $row['user']['ic_number'] }}</div>
                @endif
            </td>
            <td class="td-action">
                <span class="action-badge {{ $badgeCls }}">{{ $actionLabel }}</span>
            </td>
            <td class="td-model">
                @if(!empty($row['model_type']))
                    <span class="model-chip">{{ $row['model_type'] }}{{ !empty($row['model_id']) ? ' #'.$row['model_id'] : '' }}</span>
                @else
                    <span style="color:#94a3b8;">—</span>
                @endif
            </td>
            <td class="td-ip">{{ $row['ip_address'] ?? '—' }}</td>
            <td class="td-payload">
                @if(!empty($payload))
                    <button class="payload-toggle"
                        onclick="togglePayload(this)"
                        data-payload="{{ htmlspecialchars(json_encode($payload), ENT_QUOTES) }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        Data
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="payload-box"></div>
                @else
                    <span style="color:#94a3b8;font-size:0.75rem;">—</span>
                @endif
            </td>
        </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center;padding:3rem;color:var(--slate-400);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:block;margin:0 auto 0.75rem;opacity:0.35;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Tiada rekod ditemui
                </td>
            </tr>
        @endforelse
    </x-table>
</x-table.with-pagination>
