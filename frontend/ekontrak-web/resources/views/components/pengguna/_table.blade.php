@php
    $data     = $users['data'] ?? [];
    $meta     = isset($users['meta']) ? $users['meta'] : $users;
    $total    = $meta['total'] ?? 0;
        <x-table
            :headers="['Bil', 'Pengguna', 'Jabatan & Unit', 'No. K/P', 'Peranan', 'Tindakan']"
            wrap-class="table-scroll"
            table-class="table align-middle pivot-table mb-0"
        >
            @forelse($data as $i => $user)
                <tr>
                    <td style="color:#9ca3af;font-size:0.8rem;">{{ (($from ?? 0) > 0 ? $from : 1) + $i }}</td>
                    <td>
                        <div style="font-weight:600;color:var(--gray-900);">{{ $user['name'] ?? '—' }}</div>
                        <div style="font-size:0.775rem;color:var(--gray-500);">{{ $user['email'] ?? '—' }}</div>
                        @if(!($user['is_active'] ?? true))
                            <span class="pill pill-gray" style="margin-top:0.25rem;font-size:0.65rem;">Disekat</span>
                        @endif
                    </td>
                    <td style="font-size:0.82rem;color:var(--gray-600);">
                        <div>{{ $user['jabatan_bahagian'] ?? '—' }}</div>
                        <div style="color:var(--gray-400);">{{ $user['bahagian_unit'] ?? '' }}</div>
                    </td>
                    <td style="font-family:monospace;font-size:0.85rem;letter-spacing:0.05em;">
                        {{ $user['ic_number'] ?? '—' }}
                    </td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:0.25rem;">
                            @php
                                $roleNames = [];
                                foreach (($user['roles'] ?? []) as $role) {
                                    $name = is_array($role) ? ($role['name'] ?? '') : (string) $role;
                                    if ($name !== '' && ! in_array($name, $roleNames, true)) {
                                        $roleNames[] = $name;
                                    }
                                }
                            @endphp
                            @foreach($roleNames as $roleName)
                                @if(isset($rolePill[$roleName]))
                                    <span class="pill {{ $rolePill[$roleName]['class'] }}">
                                        {{ $rolePill[$roleName]['label'] }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;gap:0.375rem;">
                            <button class="btn btn-outline-blue btn-sm"
                                onclick='editUser(@json($user))'>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Kemaskini
                            </button>
                            <button class="btn btn-outline-red btn-sm"
                                onclick="deleteUser({{ $user['id'] }}, '{{ addslashes($user['name'] ?? '') }}')">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2.2rem 1rem;color:var(--gray-500);">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:0.35rem;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
                            <strong style="font-size:0.84rem;color:#64748b;">Tiada rekod ditemui</strong>
                            <span style="font-size:0.75rem;color:#94a3b8;">Cuba ubah kata kunci carian anda.</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-table>
        </tr>
        @endforelse
    </tbody>
</table>

@include('components.table.pagination', [
    'from' => $displayFrom,
    'to' => $displayTo,
    'total' => $total,
    'currentPage' => $curPage,
    'lastPage' => $lastPage,
    'reloadFn' => 'reloadTable',
    'showPerPage' => true,
    'perPageId' => 'perPageSelect',
    'perPage' => request('per_page', 5),
])