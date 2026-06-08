@extends('components.layouts.app')

@section('title', 'Preview ABM')
@section('breadcrumb', 'ABM')

@section('content')
@php
    $headerCards = [
        ['label' => 'Sektor', 'value' => $headerInformation['sektor'] ?? '-'],
        ['label' => 'Maksud', 'value' => $headerInformation['maksud'] ?? '-'],
        ['label' => 'Program', 'value' => $headerInformation['program'] ?? '-'],
        ['label' => 'Aktiviti', 'value' => $headerInformation['aktiviti'] ?? '-'],
        ['label' => 'Jenis Aktiviti', 'value' => $headerInformation['jenis_aktiviti'] ?? '-'],
        ['label' => 'Dasar', 'value' => $headerInformation['dasar'] ?? '-'],
        ['label' => 'Tahun', 'value' => $headerInformation['tahun'] ?? '-'],
        ['label' => 'Tajuk', 'value' => $headerInformation['tajuk'] ?? '-'],
        ['label' => 'Jumlah', 'value' => number_format($upload->total_amount, 2)],
    ];

    $overallTotals = [
        'peruntukan_2024' => 0.0,
        'perbelanjaan_sebenar_2024' => 0.0,
        'peruntukan_asal_2025' => 0.0,
        'anggaran_dipohon_2026' => 0.0,
        'anggaran_disyorkan_2026' => 0.0,
        'beza_rm' => 0.0,
        'beza_pct' => 0.0,
        'anggaran_dipohon_2027' => 0.0,
        'anggaran_disyorkan_2027' => 0.0,
        'beza_rm_2027' => 0.0,
        'beza_pct_2027' => 0.0,
        'amount' => 0.0,
    ];

    foreach ($budgetSections as $section) {
        $sectionTotals = $section['totals'] ?? [];
        foreach ($overallTotals as $key => $value) {
            if ($key === 'amount') {
                continue;
            }

            $overallTotals[$key] += (float) ($sectionTotals[$key] ?? 0);
        }
    }

    $overallTotals['beza_rm'] = ($overallTotals['anggaran_disyorkan_2026'] > 0 && $overallTotals['peruntukan_asal_2025'] > 0)
        ? $overallTotals['anggaran_disyorkan_2026'] - $overallTotals['peruntukan_asal_2025']
        : 0.0;
    $overallTotals['beza_pct'] = ($overallTotals['anggaran_disyorkan_2026'] > 0 && $overallTotals['peruntukan_asal_2025'] > 0)
        ? ($overallTotals['beza_rm'] / $overallTotals['peruntukan_asal_2025']) * 100
        : 0.0;
    $overallTotals['beza_rm_2027'] = ($overallTotals['anggaran_disyorkan_2027'] > 0 && $overallTotals['anggaran_disyorkan_2026'] > 0)
        ? $overallTotals['anggaran_disyorkan_2027'] - $overallTotals['anggaran_disyorkan_2026']
        : 0.0;
    $overallTotals['beza_pct_2027'] = ($overallTotals['anggaran_disyorkan_2027'] > 0 && $overallTotals['anggaran_disyorkan_2026'] > 0)
        ? ($overallTotals['beza_rm_2027'] / $overallTotals['anggaran_disyorkan_2026']) * 100
        : 0.0;
    $overallTotals['amount'] = $overallTotals['anggaran_disyorkan_2027'] > 0
        ? $overallTotals['anggaran_disyorkan_2027']
        : $overallTotals['anggaran_disyorkan_2026'];
@endphp

<div class="page-shell">
    <div class="hero-card">
        <div>
            <p class="eyebrow">Preview Dokumen</p>
            <h1>{{ $upload->reference_no }}</h1>
            <p class="hero-copy">{{ $upload->original_filename }} · {{ $upload->template_type_label }} · {{ $upload->total_rows }} baris diekstrak daripada satu struktur template.</p>
        </div>
        <div class="hero-badges">
            <span class="badge {{ $upload->status_color }}">{{ $upload->status_label }}</span>
            <a href="{{ route('abm.v3.dashboard') }}" class="btn btn-secondary">Dashboard</a>
            <a href="{{ route('abm.v3.summary') }}" class="btn btn-primary">Ringkasan</a>
        </div>
    </div>

    <div class="glass-panel mt-4">
        <div class="panel-head">
            <h2>Header ABM</h2>
            <span class="panel-chip">Template tunggal</span>
        </div>
        <div class="header-card-grid">
            @foreach($headerCards as $card)
                <div class="stat-card header-card">
                    <p>{{ $card['label'] }}</p>
                    <strong>{{ $card['value'] }}</strong>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6 space-y-4">
        @forelse($budgetSections as $section)
            <details class="accordion-panel" {{ $loop->first ? 'open' : '' }}>
                <summary>
                    <div>
                        <strong>{{ $section['name'] }}</strong>
                        <span>{{ $section['row_count'] }} baris · {{ $section['code'] }}</span>
                    </div>
                    <div class="summary-pill">{{ number_format($section['amount'], 2) }}</div>
                </summary>

                <div class="accordion-body">
                    <div class="overflow-x-auto table-scroll">
                        @php
                            $sectionTotals = $section['totals'] ?? [];
                            $sectionFooterLabel = trim((string) (($section['code'] ?? '') . ' ' . ($section['name'] ?? '')));
                        @endphp
                        <x-table
                            :headers="['Kod', 'Jenis Perbelanjaan', 'Peruntukan 2024', 'Perbelanjaan Sebenar 2024', 'Peruntukan Asal 2025', 'Anggaran Dipohon 2026', 'Anggaran Disyorkan 2026', 'Beza RM', 'Beza %', 'Anggaran Dipohon 2027', 'Anggaran Disyorkan 2027', 'Beza RM 2027', 'Beza % 2027']"
                            wrap-class="table-scroll"
                            table-class="modern-table"
                        >
                            @foreach($section['rows'] as $row)
                                @if(in_array($row['row_type'] ?? '', ['GROUP_HEADER', 'TOTAL'], true))
                                    @continue
                                @endif
                                <tr class="{{ in_array($row['row_type'] ?? '', ['GROUP_HEADER', 'TOTAL'], true) ? 'group-row' : '' }}">
                                    <td>{{ $row['code'] ?? '-' }}</td>
                                    <td>{{ $row['jenis_perbelanjaan'] ?? '-' }}</td>
                                    <td>{{ number_format($row['peruntukan_2024'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['perbelanjaan_sebenar_2024'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['peruntukan_asal_2025'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['anggaran_dipohon_2026'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['anggaran_disyorkan_2026'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['beza_rm'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['beza_pct'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['anggaran_dipohon_2027'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['anggaran_disyorkan_2027'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['beza_rm_2027'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($row['beza_pct_2027'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                            <x-slot:tableFooter>
                                <tr class="total-row">
                                    <td><strong>{{ $sectionFooterLabel !== '' ? $sectionFooterLabel : 'JUMLAH' }}</strong></td>
                                    <td></td>
                                    <td>{{ number_format($sectionTotals['peruntukan_2024'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['perbelanjaan_sebenar_2024'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['peruntukan_asal_2025'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['anggaran_dipohon_2026'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['anggaran_disyorkan_2026'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['beza_rm'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['beza_pct'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['anggaran_dipohon_2027'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['anggaran_disyorkan_2027'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['beza_rm_2027'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($sectionTotals['beza_pct_2027'] ?? 0, 2) }}</td>
                                </tr>
                            </x-slot:tableFooter>
                        </x-table>
                    </div>
                </div>
            </details>
        @empty
            <div class="glass-panel empty-state">Tiada data diekstrak.</div>
        @endforelse
    </div>

    <div class="glass-panel mt-6">
        <div class="panel-head">
            <h2>JUMLAH BESAR</h2>
            <span class="panel-chip">Jumlah semua</span>
        </div>
        <div class="overflow-x-auto table-scroll">
            <x-table
                :headers="['Kod', 'Jenis Perbelanjaan', 'Peruntukan 2024', 'Perbelanjaan Sebenar 2024', 'Peruntukan Asal 2025', 'Anggaran Dipohon 2026', 'Anggaran Disyorkan 2026', 'Beza RM', 'Beza %', 'Anggaran Dipohon 2027', 'Anggaran Disyorkan 2027', 'Beza RM 2027', 'Beza % 2027']"
                wrap-class="table-scroll"
                table-class="modern-table overall-table"
            >
                <tr class="total-row">
                    <td><strong>JUMLAH</strong></td>
                    <td></td>
                    <td>{{ number_format($overallTotals['peruntukan_2024'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['perbelanjaan_sebenar_2024'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['peruntukan_asal_2025'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['anggaran_dipohon_2026'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['anggaran_disyorkan_2026'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['beza_rm'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['beza_pct'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['anggaran_dipohon_2027'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['anggaran_disyorkan_2027'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['beza_rm_2027'] ?? 0, 2) }}</td>
                    <td>{{ number_format($overallTotals['beza_pct_2027'] ?? 0, 2) }}</td>
                </tr>
            </x-table>
        </div>
    </div>

    <div class="glass-panel mt-6">
        <div class="panel-head"><h2>Sejarah</h2></div>
        <div class="timeline">
            @foreach($workflowHistory as $history)
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div>
                        <strong>{{ $history->action_label }}</strong>
                        <p>{{ $history->description }}</p>
                        <span>{{ $history->performed_by_name ?? 'Sistem' }} · {{ $history->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('styles')
<style>
    .page-shell { padding: 2rem; background: radial-gradient(circle at top left, rgba(37,99,235,.14), transparent 30%), linear-gradient(180deg, #f8fbff 0%, #f5f7fb 100%); min-height: calc(100vh - 120px); }
    .hero-card, .glass-panel, .accordion-panel { background: rgba(255,255,255,.92); border:1px solid #dbe7f3; border-radius:24px; box-shadow:0 18px 45px rgba(15,23,42,.08); backdrop-filter: blur(8px); }
    .hero-card { padding: 1.5rem 1.6rem; display:flex; justify-content:space-between; gap:1rem; }
    .eyebrow { text-transform: uppercase; letter-spacing:.18em; font-size:.7rem; color:#2563eb; font-weight:800; }
    .hero-card h1 { font-size: 2rem; line-height:1.1; color:#0f172a; font-weight:900; margin-top:.3rem; }
    .hero-copy { color:#475569; margin-top:.5rem; }
    .hero-badges { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
    .badge { padding:.4rem .7rem; border-radius:999px; font-size:.72rem; font-weight:800; }
    .glass-panel { padding: 1rem 1.1rem; }
    .panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:.85rem; }
    .panel-head h2 { font-size:1rem; font-weight:900; color:#0f172a; }
    .panel-chip { font-size:.72rem; padding:.35rem .55rem; border-radius:999px; background:#eff6ff; color:#2563eb; font-weight:800; }
    .header-card-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.75rem; }
    .header-card-grid .header-card { min-height: 112px; background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); box-shadow: 0 12px 30px rgba(15,23,42,.05); }
    .header-card-grid .header-card p { margin-bottom:.35rem; }
    @media (max-width: 1024px) { .header-card-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width: 640px) { .header-card-grid { grid-template-columns:1fr; } }
    .stats-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; }
    .stats-grid-single { grid-template-columns: minmax(0, 1fr); }
    .stat-card { border-radius:18px; padding:.95rem 1rem; border:1px solid #dbe7f3; background:#fff; }
    .amount-card { min-height: 100%; }
    .stat-card p { font-size:.72rem; color:#64748b; text-transform:uppercase; letter-spacing:.08em; font-weight:800; }
    .stat-card strong { display:block; font-size:1.5rem; color:#0f172a; margin-top:.4rem; }
    .accordion-panel { overflow:hidden; }
    .accordion-panel summary { list-style:none; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1rem 1.05rem; }
    .accordion-panel summary::-webkit-details-marker { display:none; }
    .accordion-panel strong { display:block; color:#0f172a; }
    .accordion-panel span { color:#64748b; font-size:.78rem; }
    .summary-pill { padding:.35rem .55rem; border-radius:999px; background:#eff6ff; color:#2563eb; font-weight:800; font-size:.74rem; }
    .accordion-body { padding:0 1rem 1rem; }
    .table-scroll { overflow-x:auto; -webkit-overflow-scrolling: touch; }
    .modern-table { width:100%; min-width:1320px; border-collapse:separate; border-spacing:0; }
    .modern-table th { text-align:left; font-size:.72rem; color:#64748b; text-transform:uppercase; letter-spacing:.08em; padding:.7rem .8rem; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
    .modern-table td { padding:.85rem .8rem; border-bottom:1px solid #edf2f7; font-size:.84rem; color:#334155; white-space:nowrap; }
    .modern-table tbody tr:hover { background:#f8fbff; }
    .modern-table tfoot td { padding:.9rem .8rem; border-top:2px solid #cbd5e1; background:#f8fafc; font-weight:800; color:#0f172a; }
    .total-row { background:#f8fafc; font-weight:800; }
    .total-row td { border-top:2px solid #cbd5e1; color:#0f172a; }
    .group-row { background:#f1f5ff; font-weight:700; }
    .group-row td { color:#1e293b; }
    .timeline { display:flex; flex-direction:column; gap:.9rem; }
    .timeline-item { display:grid; grid-template-columns:auto 1fr; gap:.75rem; }
    .timeline-dot { width:10px; height:10px; border-radius:999px; background:#2563eb; margin-top:.35rem; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
    .timeline-item strong { display:block; font-size:.86rem; color:#0f172a; }
    .timeline-item p { margin:.18rem 0; font-size:.8rem; color:#475569; }
    .timeline-item span { font-size:.72rem; color:#94a3b8; }
    .empty-state { padding:1rem; color:#64748b; text-align:center; }
    @media (max-width: 1100px) { .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .hero-card { flex-direction:column; } }
    @media (max-width: 768px) { .page-shell { padding:1rem; } .stats-grid { grid-template-columns:1fr; } }
</style>
@endpush

@endsection