@props([
    'action' => '#',
    'method' => 'GET',
])

<form action="{{ $action }}" method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" {{ $attributes->merge(['class' => 'ds-filter']) }}>
    @if(strtoupper($method) !== 'GET')
        @csrf
    @endif
    <div class="ds-filter-grid">
        {{ $slot }}
    </div>
</form>
