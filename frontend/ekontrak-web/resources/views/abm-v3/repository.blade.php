@extends('components.layouts.app')

@section('title', 'Repositori ABM')
@section('breadcrumb', 'ABM')

@section('content')
<div class="abm-wip-overlay" id="abmRepoWipOverlay" role="presentation">
    <div class="abm-wip-modal" role="dialog" aria-modal="true" aria-labelledby="abmRepoWipTitle">
        <button type="button" class="abm-wip-close" id="abmRepoWipClose" aria-label="Tutup popup">&times;</button>
        <div class="abm-wip-badge">WORK IN PROGRESS</div>
        <h2 id="abmRepoWipTitle">Repositori ABM</h2>
        <p>Halaman ini masih dalam pembangunan.</p>
    </div>
</div>

<div class="page-shell">
    <div class="hero-card">
        <div>
            <p class="eyebrow">Repository</p>
            <h1>Repositori ABM</h1>
            <p class="hero-copy">Senarai semua dokumen yang telah dimuat naik dan diekstrak.</p>
        </div>
        <a href="{{ route('abm.v3.import') }}" class="btn btn-primary">Muat Naik</a>
    </div>

    <div class="glass-panel mt-6 overflow-x-auto">
        <x-table
            :headers="['Rujukan', 'Template', 'Baris', 'Seksyen', 'Jumlah', 'Status', 'Dimuat Naik Oleh', 'Tarikh', 'Tindakan']"
            wrap-class="table-scroll"
            table-class="modern-table w-full"
        >
            @forelse($uploads as $upload)
                <tr>
                    <td>{{ $upload->reference_no }}</td>
                    <td>{{ $upload->template_type_label }}</td>
                    <td>{{ $upload->total_rows }}</td>
                    <td>{{ $upload->total_sections }}</td>
                    <td>{{ number_format($upload->total_amount, 2) }}</td>
                    <td><span class="status-pill {{ $upload->status_color }}">{{ $upload->status_label }}</span></td>
                    <td>{{ $upload->uploaded_by_name ?? '-' }}</td>
                    <td>{{ optional($upload->created_at)->format('d M Y H:i') }}</td>
                    <td><a href="{{ route('abm.v3.preview', $upload->id) }}" class="text-blue-600 font-bold">Preview</a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="empty-cell">Tiada dokumen ditemui.</td></tr>
            @endforelse
        </x-table>
    </div>

    <div class="mt-4">{{ $uploads->links() }}</div>
</div>

@push('styles')
<style>
    .abm-wip-overlay { position: fixed; inset: 0; z-index: 5000; display: grid; place-items: center; background: rgba(15, 23, 42, 0.34); backdrop-filter: blur(4px); }
    .abm-wip-modal { position: relative; width: min(92vw, 440px); padding: 2.5rem 1.8rem 2.1rem; border-radius: 24px; border: 1px solid #dbe7f3; background: rgba(255, 255, 255, 0.98); box-shadow: 0 28px 70px rgba(15, 23, 42, 0.25); text-align: center; color: #0f172a; }
    .abm-wip-close { position: absolute; top: .8rem; right: .8rem; width: 2.25rem; height: 2.25rem; border: 0; border-radius: 999px; background: #eff6ff; color: #1e3a8a; font-size: 1.5rem; line-height: 1; font-weight: 700; cursor: pointer; }
    .abm-wip-badge { display: inline-flex; align-items: center; justify-content: center; padding: .42rem .75rem; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 1rem; }
    .abm-wip-modal h2 { margin: 0; font-size: 1.6rem; line-height: 1.15; font-weight: 900; }
    .abm-wip-modal p { margin: .65rem 0 0; color: #64748b; font-weight: 600; }
    .abm-wip-hidden { display: none !important; }
    .page-shell { padding: 2rem; background: radial-gradient(circle at top left, rgba(37,99,235,.14), transparent 30%), linear-gradient(180deg, #f8fbff 0%, #f5f7fb 100%); min-height: calc(100vh - 120px); }
    .hero-card, .glass-panel { background: rgba(255,255,255,.92); border:1px solid #dbe7f3; border-radius:24px; box-shadow:0 18px 45px rgba(15,23,42,.08); backdrop-filter: blur(8px); }
    .hero-card { padding: 1.5rem 1.6rem; display:flex; justify-content:space-between; gap:1rem; }
    .eyebrow { text-transform: uppercase; letter-spacing:.18em; font-size:.7rem; color:#2563eb; font-weight:800; }
    .hero-card h1 { font-size: 2rem; line-height:1.1; color:#0f172a; font-weight:900; margin-top:.3rem; }
    .hero-copy { color:#475569; margin-top:.5rem; }
    .glass-panel { padding: 1rem 1.1rem; }
    .modern-table { width:100%; border-collapse:separate; border-spacing:0; }
    .modern-table th { text-align:left; font-size:.72rem; color:#64748b; text-transform:uppercase; letter-spacing:.08em; padding:.7rem .8rem; border-bottom:1px solid #e2e8f0; }
    .modern-table td { padding:.85rem .8rem; border-bottom:1px solid #edf2f7; font-size:.84rem; color:#334155; }
    .modern-table tbody tr:hover { background:#f8fbff; }
    .status-pill { display:inline-flex; align-items:center; justify-content:center; padding:.28rem .55rem; border-radius:999px; font-size:.7rem; font-weight:800; }
    .empty-cell { padding:1rem .2rem; color:#64748b; text-align:center; }
    @media (max-width: 768px) { .page-shell { padding:1rem; } .hero-card { flex-direction:column; } }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('abmRepoWipOverlay');
        const closeButton = document.getElementById('abmRepoWipClose');

        if (!overlay || !closeButton) {
            return;
        }

        const closeOverlay = function () {
            overlay.classList.add('abm-wip-hidden');
        };

        closeButton.addEventListener('click', closeOverlay);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeOverlay();
            }
        });
    });
</script>
@endpush

@endsection