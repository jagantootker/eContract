@props([
    'label' => null,
    'name',
    'id' => null,
    'required' => false,
    'value' => null,
    'placeholder' => 'DD/MM/YYYY',
])

@php
    $id = $id ?: $name;
    $raw = old($name, $value);
    $normalized = '';
    if (!empty($raw)) {
        try {
            $normalized = \Carbon\Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            $normalized = $raw;
        }
    }
@endphp

<div class="ds-field mb-4">
    @if($label)
        <x-form.label :for="$id" :text="$label" :required="$required" />
    @endif

    <div class="ds-input-wrap @error($name) has-error @enderror">
        <span class="ds-input-icon" aria-hidden="true">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </span>
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="date"
            value="{{ $normalized }}"
            data-display-format="DD/MM/YYYY"
            @required($required)
            {{ $attributes->merge(['class' => 'ds-input ds-date']) }}
        >
        <span class="ds-input-status" aria-hidden="true"></span>
    </div>
    <div class="ds-date-hint">Format: {{ $placeholder }}</div>
    <x-form-error :for="$name" />
</div>
