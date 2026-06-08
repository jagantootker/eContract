@props(['for'])

@error($for)
    <div id="err_{{ $for }}" class="invalid-note ds-error ds-status-message" role="alert" aria-live="polite">
        <span class="ds-error-icon" aria-hidden="true">!</span>
        <span>{{ $message }}</span>
    </div>
@else
    <div id="err_{{ $for }}" class="invalid-note ds-error ds-status-message runtime" role="alert" aria-live="polite"></div>
@enderror
