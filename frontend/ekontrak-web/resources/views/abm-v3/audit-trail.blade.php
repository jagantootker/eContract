@extends('components.layouts.app')

@section('title', 'Audit Trail ABM')
@section('breadcrumb', 'ABM')

@section('content')
<div class="page-shell">
    <div class="hero-card">
        <div>
            <p class="eyebrow">Audit Trail</p>
            <h1>Sejarah Aktiviti ABM</h1>
            <p class="hero-copy">Jejak operasi muat naik dan ekstrak bagi setiap dokumen.</p>
        </div>
        <a href="{{ route('abm.v3.dashboard') }}" class="btn btn-secondary">Dashboard</a>
    </div>

    <div class="glass-panel mt-6">
        <div class="timeline">
            @forelse($histories as $history)
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div>
                        <strong>{{ $history->action_label }} · {{ $history->upload?->reference_no }}</strong>
                        <p>{{ $history->description }}</p>
                        <span>{{ $history->performed_by_name ?? 'Sistem' }} · {{ $history->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state">Tiada audit trail ditemui.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">{{ $histories->links() }}</div>
</div>

@push('styles')
<style>
    .page-shell { padding: 2rem; background: radial-gradient(circle at top left, rgba(37,99,235,.14), transparent 30%), linear-gradient(180deg, #f8fbff 0%, #f5f7fb 100%); min-height: calc(100vh - 120px); }
    .hero-card, .glass-panel { background: rgba(255,255,255,.92); border:1px solid #dbe7f3; border-radius:24px; box-shadow:0 18px 45px rgba(15,23,42,.08); backdrop-filter: blur(8px); }
    .hero-card { padding: 1.5rem 1.6rem; display:flex; justify-content:space-between; gap:1rem; }
    .eyebrow { text-transform: uppercase; letter-spacing:.18em; font-size:.7rem; color:#2563eb; font-weight:800; }
    .hero-card h1 { font-size: 2rem; line-height:1.1; color:#0f172a; font-weight:900; margin-top:.3rem; }
    .hero-copy { color:#475569; margin-top:.5rem; }
    .glass-panel { padding: 1rem 1.1rem; }
    .timeline { display:flex; flex-direction:column; gap:.9rem; }
    .timeline-item { display:grid; grid-template-columns:auto 1fr; gap:.75rem; }
    .timeline-dot { width:10px; height:10px; border-radius:999px; background:#2563eb; margin-top:.35rem; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
    .timeline-item strong { display:block; font-size:.86rem; color:#0f172a; }
    .timeline-item p { margin:.18rem 0; font-size:.8rem; color:#475569; }
    .timeline-item span { font-size:.72rem; color:#94a3b8; }
    .empty-state { padding:1rem; color:#64748b; text-align:center; }
    @media (max-width: 768px) { .page-shell { padding:1rem; } .hero-card { flex-direction:column; } }
</style>
@endpush

@endsection