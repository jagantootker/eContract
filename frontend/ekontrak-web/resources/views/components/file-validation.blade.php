@props([
    'name',
    'accept' => '',
    'maxKb' => 5120,
    'required' => false,
])

<span
    class="ds-file-rule"
    data-field="{{ $name }}"
    data-accept="{{ $accept }}"
    data-max-kb="{{ $maxKb }}"
    data-required="{{ $required ? '1' : '0' }}"
    hidden
></span>
