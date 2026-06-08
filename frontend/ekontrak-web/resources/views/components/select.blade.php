@props([
    'label' => null,
    'name',
    'id' => null,
    'required' => false,
    'options' => [],
    'value' => null,
    'placeholder' => '-- Sila Pilih --',
])

@php
    $id = $id ?: $name;
    $selected = old($name, $value);
@endphp

<div class="ds-field mb-4">
    @if($label)
        <x-form.label :for="$id" :text="$label" :required="$required" />
    @endif

    <div class="ds-input-wrap @error($name) has-error @enderror">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @required($required)
            {{ $attributes->merge(['class' => 'ds-input ds-select']) }}
        >
            @if($placeholder !== null)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $key => $text)
                <option value="{{ $key }}" @selected((string) $selected === (string) $key)>{{ $text }}</option>
            @endforeach
            {{ $slot }}
        </select>
        <span class="ds-input-status" aria-hidden="true"></span>
    </div>

    <x-form-error :for="$name" />
</div>
