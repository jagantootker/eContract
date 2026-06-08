@extends('components.layouts.app')

@section('content')
@php
    $statusCards = [
        ['label' => 'Draf', 'value' => $draft ?? 0, 'tone' => 'indigo', 'icon' => 'M7 8h10M7 12h6M7 16h8'],
        ['label' => 'Sedang Disemak', 'value' => $sedangDisemak ?? 0, 'tone' => 'amber', 'icon' => 'M12 8v4l3 3'],
        ['label' => 'Diluluskan', 'value' => $approved ?? 0, 'tone' => 'emerald', 'icon' => 'M5 13l4 4L19 7'],
        ['label' => 'Ditolak', 'value' => $ditolak ?? 0, 'tone' => 'rose', 'icon' => 'M6 6l12 12M18 6L6 18'],
        ['label' => 'Selesai', 'value' => $selesai ?? 0, 'tone' => 'slate', 'icon' => 'M9 12l2 2 4-4'],
    ];

    $trendItems = collect($trend ?? [])->values();
    $budgetItems = collect($budgetBreakdown ?? [])->take(6)->values();
    $departmentItems = collect($departmentComparison ?? [])->take(5)->values();
    $recentUploadsItems = collect($recentUploads ?? [])->take(6)->values();
    $recentActivitiesItems = collect($recentActivities ?? [])->take(8)->values();

    $budgetTotal = max(1, (int) collect($budgetBreakdown ?? [])->sum('amount'));
    $trendMax = max(1, (int) $trendItems->max('amount'));

    $palette = ['#38bdf8', '#22c55e', '#f59e0b', '#a78bfa', '#ef4444', '#14b8a6'];
    $donutSlices = [];
    $cursor = 0;

    foreach ($budgetItems as $index => $item) {
        $share = ($item['amount'] ?? 0) / $budgetTotal * 100;
        $start = $cursor;
        $cursor += $share;
        $donutSlices[] = sprintf('%s %.2f%% %.2f%%', $palette[$index % count($palette)], $start, $cursor);
    }

    $donutBackground = count($donutSlices)
        ? 'conic-gradient(' . implode(', ', $donutSlices) . ')'
        : 'conic-gradient(#334155 0% 100%)';

    $totalApprovedRate = max(0, min(100, round((($approved ?? 0) / max(1, (int) ($totalUpload ?? 0))) * 100)));
    $pendingRate = max(0, min(100, round((($pendingReview ?? 0) / max(1, (int) ($totalUpload ?? 0))) * 100)));
@endphp

<div class="ppt-dashboard-shell">
    @if(!empty($dbUnavailableMessage))
        <div class="mb-6 rounded-2xl border border-amber-300/60 bg-amber-50/95 px-4 py-3 text-amber-900 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Peringatan Sistem</div>
                    <div class="mt-1 text-sm font-medium">{{ $dbUnavailableMessage }}</div>
                </div>
            </div>
        </div>
    @endif

    <section class="hero-card">
        <div class="hero-grid">
            <div>
                <div class="eyebrow">Perancangan Perolehan</div>
                <h1>Dashboard ABM / PPT</h1>
                <p class="hero-copy">Paparan ringkas, padat dan moden untuk memantau status muat naik, nilai perbelanjaan, aktiviti kerja dan perbandingan agensi dalam satu skrin.</p>

                <div class="hero-actions">
                    <a href="{{ url('/perancangan-perolehan/import') }}" class="hero-button primary">
                        <span>Muat Naik Dokumen</span>
                    </a>
                    <a href="{{ url('/perancangan-perolehan/pengurusan-abm') }}" class="hero-button secondary">
                        <span>Pengurusan</span>
                    </a>
                    <a href="{{ url('/perancangan-perolehan/repository') }}" class="hero-button secondary">
                        <span>Repositori</span>
                    </a>
                </div>
            </div>

            <div class="hero-insight">
                <div class="hero-badge">Pemantauan masa nyata</div>
                <div class="hero-stat">
                    <div class="hero-stat-label">Jumlah Nilai</div>
                    <div class="hero-stat-value">RM {{ number_format($totalAmount ?? 0, 2) }}</div>
                    <div class="hero-stat-meta">{{ number_format($totalUpload ?? 0) }} fail · {{ number_format($totalPrograms ?? 0) }} program · {{ number_format($totalActivities ?? 0) }} aktiviti</div>
                </div>
                <div class="hero-mini-grid">
                    <div>
                        <div class="mini-label">Terkini Diluluskan</div>
                        <div class="mini-value">{{ number_format($approved ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="mini-label">Menunggu Tindakan</div>
                        <div class="mini-value">{{ number_format($pendingReview ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="kpi-grid">
        <article class="metric-card accent-cyan">
            <div class="metric-label">Jumlah Muat Naik</div>
            <div class="metric-value">{{ number_format($totalUpload ?? 0) }}</div>
            <div class="metric-foot">Dokumen dalam repositori</div>
        </article>
        <article class="metric-card accent-emerald">
            <div class="metric-label">Jumlah Nilai</div>
            <div class="metric-value">RM {{ number_format($totalAmount ?? 0, 2) }}</div>
            <div class="metric-foot">Gabungan semua peruntukan</div>
        </article>
        <article class="metric-card accent-violet">
            <div class="metric-label">Jumlah Program</div>
            <div class="metric-value">{{ number_format($totalPrograms ?? 0) }}</div>
            <div class="metric-foot">Program aktif dipetakan</div>
        </article>
        <article class="metric-card accent-amber">
            <div class="metric-label">Jumlah Aktiviti</div>
            <div class="metric-value">{{ number_format($totalActivities ?? 0) }}</div>
            <div class="metric-foot">Kiraan item aktiviti</div>
        </article>
        <article class="metric-card accent-rose">
            <div class="metric-label">Semakan Tertunggak</div>
            <div class="metric-value">{{ number_format($pendingReview ?? 0) }}</div>
            <div class="metric-foot">Fail belum selesai</div>
        </article>
        <article class="metric-card accent-slate">
            <div class="metric-label">Diluluskan</div>
            <div class="metric-value">{{ number_format($approved ?? 0) }}</div>
            <div class="metric-foot">Fail selesai aliran kerja</div>
        </article>
    </section>

    <section class="content-grid">
        <div class="main-column">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-kicker">Perbandingan Tahun</div>
                        <h2>Trend nilai dan bilangan muat naik</h2>
                    </div>
                    <div class="panel-chip">Visual berasaskan data sebenar</div>
                </div>

                <div class="trend-chart">
                    @forelse($trendItems as $item)
                        @php
                            $height = (int) max(12, round((($item['amount'] ?? 0) / $trendMax) * 100));
                        @endphp
                        <div class="trend-bar">
                            <div class="trend-bar-value">RM {{ number_format($item['amount'] ?? 0, 0) }}</div>
                            <div class="trend-bar-track">
                                <div class="trend-bar-fill" style="height: {{ $height }}%;"></div>
                            </div>
                            <div class="trend-bar-label">{{ $item['year'] ?? '-' }}</div>
                            <div class="trend-bar-meta">{{ number_format($item['uploads'] ?? 0) }} fail</div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-title">Tiada data trend</div>
                            <div class="empty-copy">Muat naik dokumen untuk memaparkan perbandingan tahun.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-kicker">Analisis Perbelanjaan</div>
                        <h2>Pecahan objek AM</h2>
                    </div>
                    <div class="panel-chip">Top {{ count($budgetItems) }} kategori</div>
                </div>

                <div class="analysis-grid">
                    <div class="donut-card">
                        <div class="donut-ring" style="background: {{ $donutBackground }};"></div>
                        <div class="donut-center">
                            <div class="donut-label">Nilai keseluruhan</div>
                            <div class="donut-value">RM {{ number_format($budgetTotal ?? 0, 2) }}</div>
                        </div>
                    </div>

                    <div class="breakdown-list">
                        @forelse($budgetItems as $index => $item)
                            @php
                                $share = ($item['amount'] ?? 0) / $budgetTotal * 100;
                            @endphp
                            <div class="breakdown-row">
                                <div class="breakdown-head">
                                    <span class="swatch" style="background: {{ $palette[$index % count($palette)] }}"></span>
                                    <div>
                                        <div class="breakdown-title">{{ $item['name'] ?? $item['code'] ?? 'Tidak Dinyatakan' }}</div>
                                        <div class="breakdown-sub">{{ number_format($item['programs'] ?? 0) }} program · {{ number_format($item['uploads'] ?? 0) }} fail</div>
                                    </div>
                                </div>
                                <div class="breakdown-amount">RM {{ number_format($item['amount'] ?? 0, 2) }}</div>
                                <div class="progress-line">
                                    <div class="progress-fill" style="width: {{ max(3, (int) round($share)) }}%; background: {{ $palette[$index % count($palette)] }};"></div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state compact">
                                <div class="empty-title">Tiada pecahan perbelanjaan</div>
                                <div class="empty-copy">Sistem akan memaparkan pecahan objek AM selepas data dimuat naik.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head panel-head-tabs">
                    <div>
                        <div class="panel-kicker">Pusat Analisis</div>
                        <h2>Organisasi, aktiviti dan jejak operasi</h2>
                    </div>
                    <div class="tab-strip" data-dashboard-tabs>
                        <button type="button" class="tab-button is-active" data-tab-target="department">Bahagian</button>
                        <button type="button" class="tab-button" data-tab-target="activity">Aktiviti</button>
                        <button type="button" class="tab-button" data-tab-target="trail">Jejak</button>
                    </div>
                </div>

                <div class="tab-panel is-active" data-tab-panel="department">
                    <div class="table-card">
                        <x-table
                            :headers="['Bahagian', 'Nilai', 'Program', 'Aktiviti']"
                            wrap-class="table-scroll"
                            table-class="modern-table"
                        >
                            @forelse($departmentItems as $item)
                                <tr>
                                    <td>
                                        <div class="table-strong">{{ $item['department'] ?? 'Tidak Dinyatakan' }}</div>
                                        <div class="table-muted">Perbandingan jabatan / bahagian</div>
                                    </td>
                                    <td class="table-amount">RM {{ number_format($item['amount'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($item['programs'] ?? 0) }}</td>
                                    <td>{{ number_format($item['activities'] ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-cell">Tiada data bahagian untuk dipaparkan.</td>
                                </tr>
                            @endforelse
                        </x-table>
                    </div>
                </div>

                <div class="tab-panel" data-tab-panel="activity">
                    <div class="stack-grid">
                        @forelse($recentActivitiesItems as $activity)
                            <div class="stack-card">
                                <div class="stack-top">
                                    <div>
                                        <div class="stack-title">{{ $activity->description ?? 'Aktiviti sistem' }}</div>
                                        <div class="stack-sub">{{ $activity->upload?->reference_no ?? 'Tanpa rujukan' }}</div>
                                    </div>
                                    <div class="stack-time">{{ optional($activity->created_at)->diffForHumans() }}</div>
                                </div>
                                <div class="stack-meta">
                                    <span>{{ $activity->performed_by_name ?? 'Sistem' }}</span>
                                    <span>{{ $activity->action ?? 'EVENT' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state compact">
                                <div class="empty-title">Tiada aktiviti terkini</div>
                                <div class="empty-copy">Aliran audit akan muncul apabila pengguna mula memproses fail.</div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="tab-panel" data-tab-panel="trail">
                    <div class="trail-grid">
                        @forelse($recentUploadsItems as $upload)
                            <a class="trail-card" href="{{ url('/perancangan-perolehan/' . $upload->id . '/preview') }}">
                                <div class="trail-top">
                                    <div>
                                        <div class="trail-title">{{ $upload->reference_no ?? 'Tanpa rujukan' }}</div>
                                        <div class="trail-sub">{{ $upload->template_type_label ?? 'ABM / PPT' }}</div>
                                    </div>
                                    <div class="trail-status {{ $upload->status_color ?? 'bg-slate-500' }}">{{ $upload->status_label ?? ($upload->status ?? 'N/A') }}</div>
                                </div>
                                <div class="trail-meta">{{ optional($upload->created_at)->format('d M Y, h:i A') }}</div>
                            </a>
                        @empty
                            <div class="empty-state compact">
                                <div class="empty-title">Tiada muat naik terkini</div>
                                <div class="empty-copy">Muat naik baharu akan dipaparkan di sini.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <aside class="side-column">
            <div class="panel side-panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-kicker">Status Aliran Kerja</div>
                        <h2>Keadaan semasa dokumen</h2>
                    </div>
                </div>

                <div class="status-grid">
                    @foreach($statusCards as $status)
                        <div class="status-card {{ $status['tone'] }}">
                            <div class="status-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $status['icon'] }}"/>
                                </svg>
                            </div>
                            <div>
                                <div class="status-label">{{ $status['label'] }}</div>
                                <div class="status-value">{{ number_format($status['value']) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="status-meter">
                    <div class="meter-row">
                        <span>Kelulusan</span>
                        <strong>{{ $totalApprovedRate }}%</strong>
                    </div>
                    <div class="meter-track"><div class="meter-fill success" style="width: {{ $totalApprovedRate }}%;"></div></div>
                    <div class="meter-row mt-4">
                        <span>Menunggu tindakan</span>
                        <strong>{{ $pendingRate }}%</strong>
                    </div>
                    <div class="meter-track"><div class="meter-fill warning" style="width: {{ $pendingRate }}%;"></div></div>
                </div>
            </div>

            <div class="panel side-panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-kicker">Muat Naik Terkini</div>
                        <h2>Rujukan terakhir</h2>
                    </div>
                </div>

                <div class="recent-list">
                    @forelse($recentUploadsItems as $upload)
                        <a href="{{ url('/perancangan-perolehan/' . $upload->id . '/preview') }}" class="recent-item">
                            <div>
                                <div class="recent-title">{{ $upload->reference_no ?? 'Tanpa rujukan' }}</div>
                                <div class="recent-sub">{{ $upload->template_type_label ?? 'ABM / PPT' }}</div>
                            </div>
                            <div class="recent-meta">
                                <span class="recent-pill">{{ $upload->status_label ?? ($upload->status ?? 'N/A') }}</span>
                                <span>{{ optional($upload->created_at)->format('d M') }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state compact">
                            <div class="empty-title">Tiada muat naik terkini</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="panel side-panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-kicker">Ringkasan Cepat</div>
                        <h2>Peratus semasa</h2>
                    </div>
                </div>

                <div class="quick-summary">
                    <div class="quick-row">
                        <span>Approved</span>
                        <strong>{{ number_format($approved ?? 0) }}</strong>
                    </div>
                    <div class="quick-row">
                        <span>Pending review</span>
                        <strong>{{ number_format($pendingReview ?? 0) }}</strong>
                    </div>
                    <div class="quick-row">
                        <span>Nilai purata / fail</span>
                        <strong>RM {{ number_format(((int) ($totalAmount ?? 0)) / max(1, (int) ($totalUpload ?? 0)), 2) }}</strong>
                    </div>
                </div>
            </div>
        </aside>
    </section>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .ppt-dashboard-shell {
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 1.5rem;
        min-height: calc(100vh - 120px);
        background:
            radial-gradient(900px 500px at 10% -10%, rgba(56, 189, 248, 0.18), transparent 60%),
            radial-gradient(800px 500px at 110% 0%, rgba(168, 85, 247, 0.14), transparent 55%),
            linear-gradient(180deg, #08111f 0%, #0f172a 35%, #111827 100%);
        color: #e5eefb;
    }

    .hero-card,
    .panel,
    .metric-card {
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        background: rgba(15, 23, 42, 0.72);
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow: 0 24px 60px rgba(2, 6, 23, 0.28);
    }

    .hero-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.5rem;
        padding: 1.75rem;
        margin-bottom: 1.25rem;
    }

    .hero-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), transparent 45%, rgba(168, 85, 247, 0.12));
        pointer-events: none;
    }

    .hero-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1.4fr 0.9fr;
        gap: 1.25rem;
        align-items: stretch;
    }

    .eyebrow,
    .panel-kicker,
    .mini-label,
    .metric-label,
    .status-label,
    .recent-sub,
    .trail-sub,
    .breakdown-sub,
    .table-muted,
    .hero-copy,
    .hero-stat-meta,
    .empty-copy {
        color: #94a3b8;
    }

    .eyebrow,
    .panel-kicker {
        text-transform: uppercase;
        letter-spacing: 0.18em;
        font-size: 0.72rem;
        font-weight: 800;
    }

    .hero-card h1 {
        margin: 0.45rem 0 0.65rem;
        font-size: clamp(2rem, 3vw, 3.25rem);
        line-height: 1.02;
        color: #f8fbff;
        font-weight: 800;
    }

    .hero-copy {
        max-width: 60ch;
        font-size: 0.98rem;
        line-height: 1.7;
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }

    .hero-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.85rem 1.1rem;
        border-radius: 999px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .hero-button:hover {
        transform: translateY(-1px);
    }

    .hero-button.primary {
        background: linear-gradient(135deg, #38bdf8, #8b5cf6);
        color: white;
        box-shadow: 0 16px 34px rgba(59, 130, 246, 0.28);
    }

    .hero-button.secondary {
        background: rgba(15, 23, 42, 0.9);
        color: #dbeafe;
        border: 1px solid rgba(148, 163, 184, 0.18);
    }

    .hero-insight {
        border-radius: 1.2rem;
        padding: 1.1rem;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.72));
        border: 1px solid rgba(148, 163, 184, 0.16);
        display: grid;
        gap: 1rem;
        align-content: start;
    }

    .hero-badge {
        width: fit-content;
        border-radius: 999px;
        padding: 0.45rem 0.75rem;
        background: rgba(56, 189, 248, 0.12);
        color: #7dd3fc;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .hero-stat-value {
        font-size: clamp(1.5rem, 2.2vw, 2.6rem);
        color: #f8fbff;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .hero-stat-label,
    .metric-foot,
    .mini-label,
    .status-label,
    .quick-row span,
    .meter-row span,
    .trail-meta,
    .stack-sub,
    .recent-title,
    .trail-title,
    .breakdown-title,
    .trend-bar-label,
    .trend-bar-meta {
        color: #cbd5e1;
    }

    .hero-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .mini-value {
        margin-top: 0.25rem;
        font-size: 1.35rem;
        font-weight: 800;
        color: #f8fbff;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.9rem;
        margin-bottom: 1.25rem;
    }

    .metric-card {
        border-radius: 1.1rem;
        padding: 1rem 1rem 1.05rem;
        min-height: 120px;
        position: relative;
        overflow: hidden;
    }

    .metric-card::after {
        content: '';
        position: absolute;
        inset: auto -10% -40% auto;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(255,255,255,0.12), transparent 65%);
        pointer-events: none;
    }

    .accent-cyan { box-shadow: inset 0 1px 0 rgba(125, 211, 252, 0.18); }
    .accent-emerald { box-shadow: inset 0 1px 0 rgba(52, 211, 153, 0.18); }
    .accent-violet { box-shadow: inset 0 1px 0 rgba(196, 181, 253, 0.18); }
    .accent-amber { box-shadow: inset 0 1px 0 rgba(251, 191, 36, 0.18); }
    .accent-rose { box-shadow: inset 0 1px 0 rgba(251, 113, 133, 0.18); }
    .accent-slate { box-shadow: inset 0 1px 0 rgba(148, 163, 184, 0.18); }

    .metric-value {
        margin-top: 0.35rem;
        font-size: clamp(1.35rem, 1.8vw, 1.95rem);
        font-weight: 800;
        color: #f8fbff;
        letter-spacing: -0.03em;
    }

    .content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 1rem;
        align-items: start;
    }

    .main-column,
    .side-column {
        display: grid;
        gap: 1rem;
    }

    .panel {
        border-radius: 1.2rem;
        padding: 1rem;
    }

    .panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .panel-head h2 {
        margin-top: 0.3rem;
        font-size: 1.08rem;
        font-weight: 800;
        color: #f8fbff;
    }

    .panel-chip {
        border-radius: 999px;
        padding: 0.4rem 0.7rem;
        background: rgba(148, 163, 184, 0.08);
        border: 1px solid rgba(148, 163, 184, 0.12);
        color: #cbd5e1;
        font-size: 0.76rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .panel-head-tabs {
        align-items: center;
    }

    .tab-strip {
        display: inline-flex;
        gap: 0.35rem;
        padding: 0.3rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.72);
        border: 1px solid rgba(148, 163, 184, 0.12);
    }

    .tab-button {
        border: 0;
        background: transparent;
        color: #94a3b8;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
    }

    .tab-button.is-active {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.18), rgba(168, 85, 247, 0.18));
        color: #f8fbff;
    }

    .tab-panel {
        display: none;
    }

    .tab-panel.is-active {
        display: block;
    }

    .trend-chart {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(78px, 1fr));
        gap: 0.9rem;
        align-items: end;
    }

    .trend-bar {
        display: grid;
        gap: 0.45rem;
        align-items: end;
    }

    .trend-bar-value {
        font-size: 0.72rem;
        color: #cbd5e1;
        min-height: 1.8rem;
    }

    .trend-bar-track {
        height: 180px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(148, 163, 184, 0.08), rgba(15, 23, 42, 0.4));
        border: 1px solid rgba(148, 163, 184, 0.08);
        padding: 0.45rem;
    }

    .trend-bar-fill {
        width: 100%;
        border-radius: 0.85rem 0.85rem 0.4rem 0.4rem;
        background: linear-gradient(180deg, #7dd3fc, #8b5cf6);
        box-shadow: 0 12px 28px rgba(56, 189, 248, 0.24);
        min-height: 12px;
    }

    .trend-bar-label,
    .trend-bar-meta {
        text-align: center;
        font-size: 0.78rem;
    }

    .analysis-grid {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr);
        gap: 1rem;
    }

    .donut-card {
        position: relative;
        display: grid;
        place-items: center;
        min-height: 290px;
        border-radius: 1.15rem;
        background: linear-gradient(180deg, rgba(2, 6, 23, 0.42), rgba(15, 23, 42, 0.75));
        border: 1px solid rgba(148, 163, 184, 0.12);
    }

    .donut-ring {
        width: 220px;
        height: 220px;
        border-radius: 50%;
        position: absolute;
        filter: saturate(1.1);
    }

    .donut-ring::after {
        content: '';
        position: absolute;
        inset: 18%;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.96);
        border: 1px solid rgba(148, 163, 184, 0.12);
    }

    .donut-center {
        position: relative;
        z-index: 1;
        text-align: center;
        padding: 1rem;
    }

    .donut-label {
        color: #94a3b8;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        font-weight: 700;
    }

    .donut-value {
        margin-top: 0.35rem;
        color: #f8fbff;
        font-size: 1.45rem;
        font-weight: 800;
    }

    .breakdown-list {
        display: grid;
        gap: 0.8rem;
    }

    .breakdown-row {
        padding: 0.9rem;
        border-radius: 1rem;
        background: rgba(15, 23, 42, 0.56);
        border: 1px solid rgba(148, 163, 184, 0.1);
    }

    .breakdown-head {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .swatch {
        width: 0.9rem;
        height: 0.9rem;
        margin-top: 0.25rem;
        border-radius: 999px;
        flex: 0 0 auto;
        box-shadow: 0 0 0 3px rgba(255,255,255,0.04);
    }

    .breakdown-title,
    .table-strong,
    .recent-title,
    .trail-title,
    .stack-title {
        font-weight: 700;
        color: #f8fbff;
    }

    .breakdown-amount,
    .table-amount {
        margin-top: 0.5rem;
        color: #f8fbff;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .progress-line,
    .meter-track {
        margin-top: 0.65rem;
        height: 8px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.12);
        overflow: hidden;
    }

    .progress-fill,
    .meter-fill {
        height: 100%;
        border-radius: inherit;
    }

    .meter-fill.success { background: linear-gradient(90deg, #34d399, #14b8a6); }
    .meter-fill.warning { background: linear-gradient(90deg, #f59e0b, #fb7185); }

    .table-card {
        overflow-x: auto;
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.08);
        background: rgba(15, 23, 42, 0.5);
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 560px;
    }

    .modern-table thead th {
        padding: 0.95rem 1rem;
        text-align: left;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #94a3b8;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    }

    .modern-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        vertical-align: top;
        color: #dbeafe;
    }

    .empty-cell {
        text-align: center;
        color: #94a3b8 !important;
        padding: 1.5rem !important;
    }

    .stack-grid,
    .trail-grid,
    .recent-list {
        display: grid;
        gap: 0.75rem;
    }

    .stack-card,
    .trail-card,
    .recent-item {
        display: block;
        border-radius: 1rem;
        padding: 0.9rem 1rem;
        text-decoration: none;
        background: rgba(15, 23, 42, 0.56);
        border: 1px solid rgba(148, 163, 184, 0.1);
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
    }

    .stack-card:hover,
    .trail-card:hover,
    .recent-item:hover {
        transform: translateY(-1px);
        border-color: rgba(56, 189, 248, 0.3);
        background: rgba(15, 23, 42, 0.68);
    }

    .stack-top,
    .trail-top,
    .recent-meta,
    .meter-row,
    .quick-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .stack-meta {
        margin-top: 0.7rem;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        font-size: 0.78rem;
        color: #94a3b8;
    }

    .trail-status,
    .recent-pill {
        padding: 0.35rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        color: white;
        background: #475569;
        white-space: nowrap;
    }

    .status-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .status-card {
        padding: 0.85rem;
        border-radius: 0.95rem;
        background: rgba(15, 23, 42, 0.56);
        border: 1px solid rgba(148, 163, 184, 0.1);
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .status-card .status-icon {
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.8rem;
        display: grid;
        place-items: center;
        background: rgba(148, 163, 184, 0.1);
        color: #f8fbff;
        flex: 0 0 auto;
    }

    .status-card .status-icon svg {
        width: 1.1rem;
        height: 1.1rem;
    }

    .status-card.indigo .status-icon { background: rgba(99, 102, 241, 0.2); }
    .status-card.amber .status-icon { background: rgba(245, 158, 11, 0.2); }
    .status-card.emerald .status-icon { background: rgba(16, 185, 129, 0.2); }
    .status-card.rose .status-icon { background: rgba(244, 63, 94, 0.2); }
    .status-card.slate .status-icon { background: rgba(100, 116, 139, 0.2); }

    .status-value {
        margin-top: 0.2rem;
        font-size: 1.15rem;
        font-weight: 800;
        color: #f8fbff;
    }

    .status-meter,
    .quick-summary {
        margin-top: 1rem;
        padding: 0.9rem;
        border-radius: 1rem;
        background: rgba(15, 23, 42, 0.42);
        border: 1px solid rgba(148, 163, 184, 0.08);
    }

    .quick-summary {
        display: grid;
        gap: 0.85rem;
    }

    .quick-row strong,
    .meter-row strong,
    .recent-title,
    .trail-title,
    .stack-time {
        color: #f8fbff;
    }

    .stack-time,
    .trail-meta,
    .recent-meta {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .empty-state {
        border-radius: 1rem;
        padding: 1.1rem;
        background: rgba(15, 23, 42, 0.52);
        border: 1px dashed rgba(148, 163, 184, 0.15);
        text-align: center;
    }

    .empty-state.compact {
        text-align: left;
    }

    .empty-title {
        color: #f8fbff;
        font-weight: 700;
    }

    .mt-4 { margin-top: 1rem; }

    @media (max-width: 1280px) {
        .kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .content-grid {
            grid-template-columns: 1fr;
        }

        .side-column {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .ppt-dashboard-shell {
            padding: 1rem;
        }

        .hero-grid,
        .analysis-grid,
        .side-column {
            grid-template-columns: 1fr;
        }

        .kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .panel-head,
        .panel-head-tabs {
            flex-direction: column;
            align-items: flex-start;
        }

        .tab-strip {
            width: 100%;
            justify-content: space-between;
        }
    }

    @media (max-width: 640px) {
        .kpi-grid,
        .hero-mini-grid,
        .status-grid {
            grid-template-columns: 1fr;
        }

        .hero-card,
        .panel,
        .metric-card {
            border-radius: 1rem;
        }
    }
</style>

<script>
    (() => {
        const tabRoot = document.querySelector('[data-dashboard-tabs]');
        if (!tabRoot) {
            return;
        }

        const buttons = Array.from(tabRoot.querySelectorAll('[data-tab-target]'));
        const panels = Array.from(document.querySelectorAll('[data-tab-panel]'));

        const setActive = (target) => {
            buttons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.tabTarget === target);
            });

            panels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.dataset.tabPanel === target);
            });
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => setActive(button.dataset.tabTarget));
        });
    })();
</script>
@endsection