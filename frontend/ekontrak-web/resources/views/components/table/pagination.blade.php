@php
    $from = $from ?? 0;
    $to = $to ?? 0;
    $total = $total ?? 0;
    $currentPage = $currentPage ?? 1;
    $lastPage = $lastPage ?? 1;
    $reloadFn = $reloadFn ?? 'reloadTable';
    $showPerPage = $showPerPage ?? false;
    $perPageId = $perPageId ?? 'perPageSelect';
    $perPage = (string)($perPage ?? request('per_page', 5));
@endphp

<x-pagination
    :from="$from"
    :to="$to"
    :total="$total"
    :currentPage="$currentPage"
    :lastPage="$lastPage"
    :reloadFn="$reloadFn"
    :showPerPage="$showPerPage"
    :perPageId="$perPageId"
    :perPage="$perPage"
/>
