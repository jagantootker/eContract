@php
    $from = (int) ($from ?? 0);
    $to = (int) ($to ?? 0);
    $total = (int) ($total ?? 0);
    $currentPage = (int) ($currentPage ?? 1);
    $lastPage = (int) ($lastPage ?? 1);
    $reloadFn = (string) ($reloadFn ?? 'reloadTable');
    $showPerPage = (bool) ($showPerPage ?? true);
    $perPageId = (string) ($perPageId ?? 'perPageSelect');
    $perPage = (string) ($perPage ?? request('per_page', 10));
@endphp

<div class="ds-table-shell">
    <div class="ds-table-shell-body">
        {{ $slot }}
    </div>

    <div class="ds-table-shell-footer">
        @include('components.table.pagination', [
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'reloadFn' => $reloadFn,
            'showPerPage' => $showPerPage,
            'perPageId' => $perPageId,
            'perPage' => $perPage,
        ])
    </div>
</div>