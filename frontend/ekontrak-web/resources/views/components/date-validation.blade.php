@props([
    'startField',
    'endField',
    'allowEqual' => true,
])

<span
    class="ds-date-rule"
    data-start-field="{{ $startField }}"
    data-end-field="{{ $endField }}"
    data-allow-equal="{{ $allowEqual ? '1' : '0' }}"
    hidden
></span>
