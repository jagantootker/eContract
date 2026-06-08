@props([
    'label' => null,
    'name',
    'id' => null,
    'type' => 'text',
    'required' => false,
    'value' => null,
    'placeholder' => null,
    'icon' => null,
    'autocomplete' => null,
])

@php
    $id = $id ?: $name;
    $fieldValue = old($name, $value);
@endphp

<div class="ds-field form-group mb-4">
    @if($label)
        <x-form.label :for="$id" :text="$label" :required="$required" />
    @endif

    <div class="ds-input-wrap @if($icon) has-icon @endif @error($name) has-error @enderror">
        @if($icon)
            <span class="ds-input-icon" aria-hidden="true">{!! $icon !!}</span>
        @endif
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ $fieldValue }}"
            placeholder="{{ $placeholder }}"
            autocomplete="{{ $autocomplete }}"
            @required($required)
            {{ $attributes->merge(['class' => 'form-control ds-input'])->class(['is-invalid' => $errors->has($name)]) }}
        >
        <span class="ds-input-status" aria-hidden="true"></span>
    </div>

    <x-form-error :for="$name" />
</div>
