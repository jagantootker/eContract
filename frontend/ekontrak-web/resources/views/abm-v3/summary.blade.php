@extends('components.layouts.app')

@section('title', 'Ringkasan ABM')
@section('breadcrumb', 'ABM')

@section('content')
<div class="abm-wip-overlay" id="abmSummaryWipOverlay" role="presentation">
    <div class="abm-wip-modal" role="dialog" aria-modal="true" aria-labelledby="abmSummaryWipTitle">
        <button type="button" class="abm-wip-close" id="abmSummaryWipClose" aria-label="Tutup popup">&times;</button>
        <div class="abm-wip-badge">WORK IN PROGRESS</div>
        <h2 id="abmSummaryWipTitle">Ringkasan ABM</h2>
        <p>Halaman ini masih dalam pembangunan.</p>
    </div>
</div>

<div class="page-shell">
    <div class="hero-card">
        <div>
            <p class="eyebrow">Summary / Ringkasan</p>
            <h1>Ringkasan ABM</h1>
            <p class="hero-copy">ANGGARAN PERBELANJAAN MENGURUS BAGI TAHUN {{ $selectedYear }} DAN {{ $previousYear }} MENGIKUT OBJEK AM.</p>
        </div>
        <div class="hero-badges">
            <a href="{{ route('abm.v3.dashboard') }}" class="btn btn-secondary">Dashboard</a>
            <a href="{{ route('abm.v3.import') }}" class="btn btn-primary">Muat Naik</a>
        </div>
    </div>

    <div class="glass-panel mt-4">
        <form method="GET" action="{{ route('abm.v3.summary') }}" class="year-filter">
            <div>
                <label for="year" class="filter-label">Tahun Ringkasan</label>
                <select id="year" name="year" class="filter-select" onchange="this.form.submit()">
                    @foreach($yearOptions as $year)
                        <option value="{{ $year }}" {{ (int) $selectedYear === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <noscript>
                <button type="submit" class="btn btn-primary">Tukar Tahun</button>
            </noscript>
        </form>
    </div>

    <div class="glass-panel mt-6">
        <div class="panel-head">
            <h2>ANGGARAN PERBELANJAAN MENGURUS BAGI TAHUN {{ $selectedYear }} DAN {{ $previousYear }} MENGIKUT OBJEK AM</h2>
            <span class="panel-chip">Jumlah semua jenis perolehan yang dimuat naik</span>
        </div>
        <div class="overflow-x-auto table-scroll">
            <x-table
                :headers="['Kod (Objek Am)', 'Jenis Perbelanjaan', 'Jumlah Tahun ' . $selectedYear . ' (RM)', 'Jumlah Tahun ' . $previousYear . ' (RM)']"
                wrap-class="table-scroll"
                table-class="modern-table overall-table object-am-table"
            >
                @forelse($objectAmSummary as $row)
                    <tr>
                        <td><strong>{{ $row['code'] }}</strong></td>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ number_format($row['selected_year'], 2) }}</td>
                        <td>{{ number_format($row['previous_year'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">Tiada data untuk tahun {{ $selectedYear }} dan {{ $previousYear }}.</td>
                    </tr>
                @endforelse
                <x-slot:tableFooter>
                    <tr class="total-row">
                        <td><strong>JUMLAH ANGGARAN PERBELANJAAN MENGURUS</strong></td>
                        <td></td>
                        <td>{{ number_format($objectAmGrandTotal['selected_year'], 2) }}</td>
                        <td>{{ number_format($objectAmGrandTotal['previous_year'], 2) }}</td>
                    </tr>
                </x-slot:tableFooter>
            </x-table>
        </div>
    </div>
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
    .hero-badges { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
    .glass-panel { padding: 1rem 1.1rem; }
    .panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:.85rem; gap:1rem; }
    .panel-head h2 { font-size:1rem; font-weight:900; color:#0f172a; }
    .panel-chip { font-size:.72rem; padding:.35rem .55rem; border-radius:999px; background:#eff6ff; color:#2563eb; font-weight:800; }
    .year-filter { display:flex; align-items:end; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .filter-label { display:block; font-size:.72rem; color:#64748b; text-transform:uppercase; letter-spacing:.08em; font-weight:800; margin-bottom:.35rem; }
    .filter-select { min-width:180px; border:1px solid #dbe7f3; border-radius:14px; padding:.8rem .95rem; background:#fff; color:#0f172a; font-weight:700; }
    .table-scroll { overflow-x:auto; -webkit-overflow-scrolling: touch; }
    .modern-table { width:100%; min-width:900px; border-collapse:separate; border-spacing:0; }
    .modern-table th { text-align:left; font-size:.72rem; color:#64748b; text-transform:uppercase; letter-spacing:.08em; padding:.7rem .8rem; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
    .modern-table td { padding:.85rem .8rem; border-bottom:1px solid #edf2f7; font-size:.84rem; color:#334155; white-space:nowrap; }
    .modern-table tbody tr:hover { background:#f8fbff; }
    .modern-table tfoot td { padding:.9rem .8rem; border-top:2px solid #cbd5e1; background:#f8fafc; font-weight:800; color:#0f172a; }
    .total-row { background:#f8fafc; font-weight:800; }
    .total-row td { border-top:2px solid #cbd5e1; color:#0f172a; }
    .empty-state { padding:1rem; color:#64748b; text-align:center; }
    @media (max-width: 1100px) { .hero-card { flex-direction:column; } }
    @media (max-width: 768px) { .page-shell { padding:1rem; } }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('abmSummaryWipOverlay');
        const closeButton = document.getElementById('abmSummaryWipClose');

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
