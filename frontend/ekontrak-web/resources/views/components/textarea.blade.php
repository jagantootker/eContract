@props([
    'label' => null,
    'name',
    'id' => null,
    'required' => false,
    'value' => null,
    'placeholder' => null,
    'rows' => 4,
])

@php
    $id = $id ?: $name;
    $fieldValue = old($name, $value);
@endphp

<div class="ds-field mb-4">
    @if($label)
        <x-form.label :for="$id" :text="$label" :required="$required" />
    @endif

    <div class="ds-input-wrap @error($name) has-error @enderror">
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            @required($required)
            {{ $attributes->merge(['class' => 'ds-input ds-textarea']) }}
        >{{ $fieldValue }}</textarea>
        <span class="ds-input-status" aria-hidden="true"></span>
    </div>

    <x-form-error :for="$name" />
</div>
