@props([
    'for' => null,
    'text' => null,
    'required' => false,
])

@php
    $labelText = trim(preg_replace('/\s*\*+\s*$/', '', (string) ($text ?? trim($slot))));
@endphp

<label @if($for) for="{{ $for }}" @endif class="ds-label">
    {{ $labelText }}@if($required) <span class="ds-required" aria-hidden="true">*</span>@endif
</label>