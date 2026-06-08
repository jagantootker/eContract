@extends('components.layouts.app')

@section('title', $pageTitle ?? 'PPT')
@section('breadcrumb', 'ABM / PPT')

@section('content')
<div class="wip-shell">
    <div class="wip-modal" id="wipModal" role="dialog" aria-modal="true" aria-labelledby="wipTitle">
        <button type="button" class="wip-close" id="wipClose" aria-label="Tutup popup">&times;</button>
        <div class="wip-badge">WORK IN PROGRESS</div>
        <h1 id="wipTitle">{{ $pageTitle ?? 'PPT' }}</h1>
        <p>This page is not ready yet.</p>
    </div>
</div>
@endsection

@push('styles')
<style>
    .wip-shell {
        min-height: calc(100vh - 120px);
        display: grid;
        place-items: center;
        background:
            radial-gradient(circle at top, rgba(37,99,235,.08), transparent 35%),
            linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
    }
    .wip-modal {
        position: relative;
        width: min(92vw, 420px);
        text-align: center;
        padding: 2.5rem 1.75rem 2.25rem;
        border-radius: 24px;
        background: rgba(255,255,255,.96);
        border: 1px solid #dbe7f3;
        box-shadow: 0 18px 45px rgba(15,23,42,.12);
        color: #0f172a;
    }
    .wip-close {
        position: absolute;
        top: .8rem;
        right: .8rem;
        width: 2.25rem;
        height: 2.25rem;
        border: 0;
        border-radius: 999px;
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 1.5rem;
        line-height: 1;
        font-weight: 700;
        cursor: pointer;
    }
    .wip-close:hover {
        background: #dbeafe;
    }
    .wip-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .42rem .75rem;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }
    .wip-modal h1 {
        margin: 0;
        font-size: 1.6rem;
        line-height: 1.15;
        font-weight: 900;
    }
    .wip-modal p {
        margin: .65rem 0 0;
        color: #64748b;
        font-weight: 600;
    }
    .wip-hidden {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('wipModal');
        const closeButton = document.getElementById('wipClose');

        if (!modal || !closeButton) {
            return;
        }

        const closeModal = function () {
            modal.classList.add('wip-hidden');
        };

        closeButton.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    });
</script>
@endpush