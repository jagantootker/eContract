@props([
    'id',
    'title',
    'subtitle' => null,
    'size' => 'lg',
    'tone' => 'blue',
    'showFooter' => false,
])

@php
    $maxWidth = match($size) {
        'sm' => '520px',
        'md' => '700px',
        'xl' => '1100px',
        default => '860px',
    };
@endphp

<div id="{{ $id }}" class="modal-overlay" style="display:none;" onclick="if(event.target===this) closeModal('{{ $id }}')" aria-hidden="true">
    <div class="modal rounded-xl shadow-lg" style="max-width:{{ $maxWidth }};width:95%;">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon-bubble {{ $tone }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <div class="modal-title">{{ $title }}</div>
                    @if($subtitle)
                        <div class="modal-subtitle">{{ $subtitle }}</div>
                    @endif
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('{{ $id }}')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body p-6">{{ $slot }}</div>
        @if($showFooter)
            <div class="modal-footer p-6">
                {{ $footer ?? '' }}
            </div>
        @endif
    </div>
</div>
