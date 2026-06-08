@props([
    'variant' => 'primary',
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'full' => false,
])

@php
    $map = [
        'primary' => 'ds-btn-primary',
        'secondary' => 'ds-btn-secondary',
        'success' => 'ds-btn-success',
        'danger' => 'ds-btn-danger',
        'warning' => 'ds-btn-warning',
    ];
    $variantClass = $map[$variant] ?? $map['primary'];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'ds-btn ' . $variantClass . ($full ? ' ds-btn-full' : ''),
        'data-loading' => $loading ? '1' : '0',
    ]) }}
    @disabled($disabled || $loading)
>
    <span class="ds-btn-spinner" aria-hidden="true"></span>
    @if($icon)
        <span class="ds-btn-icon" aria-hidden="true">{!! $icon !!}</span>
    @endif
    <span class="ds-btn-label">{{ $slot }}</span>
</button>
