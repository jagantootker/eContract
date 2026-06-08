@props([
    'from' => 0,
    'to' => 0,
    'total' => 0,
    'currentPage' => 1,
    'lastPage' => 1,
    'reloadFn' => 'reloadTable',
    'showPerPage' => false,
    'perPageId' => 'perPageSelect',
    'perPage' => 10,
])

<div class="ds-pagination-wrap">
    <div class="ds-pagination-meta">
        @if($showPerPage)
            <label class="ds-inline-label" for="{{ $perPageId }}">Papar</label>
            <select class="ds-per-page" id="{{ $perPageId }}" onchange="{{ $reloadFn }}(1)">
                <option value="5" {{ (string)$perPage === '5' ? 'selected' : '' }}>5</option>
                <option value="10" {{ (string)$perPage === '10' ? 'selected' : '' }}>10</option>
                <option value="25" {{ (string)$perPage === '25' ? 'selected' : '' }}>25</option>
                <option value="50" {{ (string)$perPage === '50' ? 'selected' : '' }}>50</option>
            </select>
            <span>entri</span>
        @endif
    </div>

    <div class="ds-pagination-meta">Showing {{ $from }}-{{ $to }} of {{ $total }} records</div>

    @if($lastPage > 1)
        <div class="ds-pagination-btns">
            <button class="ds-page-btn" onclick="{{ $reloadFn }}(1)" {{ $currentPage <= 1 ? 'disabled' : '' }}>Prev</button>
            @for($p = max(1, $currentPage - 2); $p <= min($lastPage, $currentPage + 2); $p++)
                <button class="ds-page-btn {{ $p == $currentPage ? 'active' : '' }}" onclick="{{ $reloadFn }}({{ $p }})">{{ $p }}</button>
            @endfor
            <button class="ds-page-btn" onclick="{{ $reloadFn }}({{ $lastPage }})" {{ $currentPage >= $lastPage ? 'disabled' : '' }}>Next</button>
        </div>
    @endif
</div>
