@props(['for' => null, 'text' => null, 'required' => true])

@php
    $labelText = trim((string) ($text ?? trim($slot)));
@endphp

<x-form.label :for="$for" :text="$labelText" :required="$required" />
