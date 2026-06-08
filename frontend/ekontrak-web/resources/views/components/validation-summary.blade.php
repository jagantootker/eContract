@props([
    'id' => 'validationSummary',
    'title' => 'Terdapat maklumat yang perlu dilengkapkan.',
])

<div id="{{ $id }}" class="ds-validation-summary" style="display:none;" role="alert" aria-live="polite">
    <div class="ds-validation-summary-title">{{ $title }}</div>
    <ul class="ds-validation-summary-list"></ul>
</div>
