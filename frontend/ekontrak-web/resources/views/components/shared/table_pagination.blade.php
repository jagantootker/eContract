@php
    $total = (int) ($total ?? 0);
    $curPage = max(1, (int) ($curPage ?? 1));
    $lastPage = max(1, (int) ($lastPage ?? 1));
    $displayFrom = (int) ($displayFrom ?? 0);
    $displayTo = (int) ($displayTo ?? 0);
    $reloadFn = (string) ($reloadFn ?? 'reloadTable');
@endphp

<div class="pag-wrap">
    <div class="pag-info">
        Memaparkan <strong>{{ $displayFrom }}</strong>–<strong>{{ $displayTo }}</strong> daripada <strong>{{ $total }}</strong> rekod
    </div>

    @if($lastPage > 1)
    <div class="pag-btns">
        <button class="page-btn" onclick="{{ $reloadFn }}(1)" {{ $curPage <= 1 ? 'disabled' : '' }}>«</button>
        <button class="page-btn" onclick="{{ $reloadFn }}({{ $curPage - 1 }})" {{ $curPage <= 1 ? 'disabled' : '' }}>‹</button>
        @for($p = max(1, $curPage - 2); $p <= min($lastPage, $curPage + 2); $p++)
            <button class="page-btn {{ $p == $curPage ? 'active' : '' }}" onclick="{{ $reloadFn }}({{ $p }})">{{ $p }}</button>
        @endfor
        <button class="page-btn" onclick="{{ $reloadFn }}({{ $curPage + 1 }})" {{ $curPage >= $lastPage ? 'disabled' : '' }}>›</button>
        <button class="page-btn" onclick="{{ $reloadFn }}({{ $lastPage }})" {{ $curPage >= $lastPage ? 'disabled' : '' }}>»</button>
    </div>
    @endif
</div>
