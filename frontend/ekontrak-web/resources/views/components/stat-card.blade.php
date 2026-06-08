@props([
    'label',
    'value',
    'icon',
    'colour' => 'blue',
    'clickable' => false,
    'percent' => null,
    'hint' => null,
])

<div class="stat-card stat-card--{{ $colour }} {{ $clickable ? 'stat-card--clickable' : '' }}" {{ $attributes }}>
    <div class="stat-card__top">
        <div class="stat-card__icon">{{ $icon }}</div>
        @if(!is_null($percent))
            <span class="stat-card__pct">{{ $percent }}%</span>
        @endif
    </div>
    <div class="stat-card__value">{{ $value }}</div>
    <div class="stat-card__label">{{ $label }}</div>
    @if($hint)
        <div class="stat-card__hint">{{ $hint }}</div>
    @endif
</div>
