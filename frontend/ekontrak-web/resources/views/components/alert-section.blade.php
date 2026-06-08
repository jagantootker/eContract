@props(['type' => 'warning', 'count' => 0, 'message' => '', 'contracts' => []])

<div class="alert-section alert-section--{{ $type }}">
    <div class="alert-section__header" onclick="toggleAlert(this)">
        <span class="alert-section__icon">!</span>
        <span>({{ $count }} Kontrak) — {{ $message }}</span>
        <span class="alert-section__chevron">›</span>
    </div>
    <div class="alert-section__body">
        {{ $slot }}
    </div>
</div>
