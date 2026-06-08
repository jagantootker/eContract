@extends('components.layouts.app')

@section('title', 'Laman Utama')

@section('content')
<div class="db-wip-overlay" id="dbWipOverlay" role="presentation">
    <div class="db-wip-modal" role="dialog" aria-modal="true" aria-labelledby="dbWipTitle">
        <button type="button" class="db-wip-close" id="dbWipClose" aria-label="Tutup popup">&times;</button>
        <div class="db-wip-badge">WORK IN PROGRESS</div>
        <h2 id="dbWipTitle">Laman Utama</h2>
        <p>Halaman ini masih dalam pembangunan.</p>
    </div>
</div>

@php
    $summary   = $data['data']['summary']   ?? [];
    $alertData = $alerts['data']            ?? [];
    $tamat     = $alertData['tempoh_tamat_telah_tamat']    ?? [];
    $hampir    = $alertData['tempoh_tamat_dalam_2_minggu'] ?? [];
    $aktif     = $alertData['tempoh_aktif_6_bulan']        ?? [];
    $today     = now();
    $twoWeeks  = now()->addWeeks(2);
    $sixMonths = now()->addMonths(6);
    $tahunSekarang = date('Y');
    $userName  = \App\Helpers\AuthHelper::userName();

    $cMaklumat   = (int)($summary['maklumat_tidak_lengkap'] ?? 0);
    $cDraf       = (int)($summary['draf_kontrak']           ?? 0);
    $cPelaksanaan= (int)($summary['dalam_pelaksanaan']      ?? 0);
    $cEot        = (int)($summary['eot']                    ?? 0);
    $cSelesai    = (int)($summary['kontrak_selesai']        ?? 0);
    $total       = $cMaklumat + $cDraf + $cPelaksanaan + $cEot + $cSelesai;

    $pct = function($v) use ($total) {
        if ($total == 0) return 0;
        return round(($v / $total) * 100);
    };
@endphp

{{-- ========== BANNER ========== --}}
<div class="db-banner">
    <div class="db-banner-orb db-orb-1"></div>
    <div class="db-banner-orb db-orb-2"></div>
    <div class="db-banner-inner">
        <div class="db-banner-left">
            <div class="db-greeting-row">
                <div class="db-avatar">{{ strtoupper(substr($userName, 0, 1)) }}</div>
                <div>
                    <p class="db-greeting">Selamat datang, <span class="db-greeting-name">{{ $userName }}</span></p>
                    <p class="db-date">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $today->translatedFormat('l, d F Y') }}
                    </p>
                </div>
            </div>
            <p class="db-tagline">Pantau dan urus semua kontrak KPKT dari satu papan kawalan.</p>
        </div>
        <div class="db-banner-stats">
            <div class="db-banner-stat">
                <span class="db-banner-stat-num">{{ $total }}</span>
                <span class="db-banner-stat-lbl">Jumlah Kontrak</span>
            </div>
            <div class="db-banner-divider"></div>
            <div class="db-banner-stat">
                <span class="db-banner-stat-num db-num-amber">{{ $cMaklumat + $cDraf }}</span>
                <span class="db-banner-stat-lbl">Perlu Tindakan</span>
            </div>
            <div class="db-banner-divider"></div>
            <div class="db-banner-stat">
                <span class="db-banner-stat-num db-num-emerald">{{ $cSelesai }}</span>
                <span class="db-banner-stat-lbl">Selesai</span>
            </div>
        </div>
    </div>
</div>

{{-- ========== STAT CARDS ========== --}}
<p class="db-section-label">Status Kontrak</p>
<div class="db-cards">

    <div class="db-card db-card-c1" onclick="openStatModal('maklumat-tidak-lengkap')" title="Klik untuk lihat senarai">
        <div class="db-card-top">
            <div class="db-card-icon db-ci-1">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <span class="db-card-pct db-pct-1">{{ $pct($cMaklumat) }}%</span>
        </div>
        <div class="db-card-num">{{ $cMaklumat }}</div>
        <div class="db-card-lbl">Maklumat Tidak Lengkap</div>
        <div class="db-card-bar"><div class="db-bar-fill db-bar-1" style="width:{{ $pct($cMaklumat) }}%"></div></div>
        <div class="db-card-cta">Lihat senarai &rarr;</div>
    </div>

    <div class="db-card db-card-c2" onclick="openStatModal('draf-kontrak')" title="Klik untuk lihat senarai">
        <div class="db-card-top">
            <div class="db-card-icon db-ci-2">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <span class="db-card-pct db-pct-2">{{ $pct($cDraf) }}%</span>
        </div>
        <div class="db-card-num">{{ $cDraf }}</div>
        <div class="db-card-lbl">Draf Kontrak</div>
        <div class="db-card-bar"><div class="db-bar-fill db-bar-2" style="width:{{ $pct($cDraf) }}%"></div></div>
        <div class="db-card-cta">Lihat senarai &rarr;</div>
    </div>

    <div class="db-card db-card-c3" onclick="openStatModal('dalam-pelaksanaan')" title="Klik untuk lihat senarai">
        <div class="db-card-top">
            <div class="db-card-icon db-ci-3">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <span class="db-card-pct db-pct-3">{{ $pct($cPelaksanaan) }}%</span>
        </div>
        <div class="db-card-num">{{ $cPelaksanaan }}</div>
        <div class="db-card-lbl">Dalam Pelaksanaan</div>
        <div class="db-card-bar"><div class="db-bar-fill db-bar-3" style="width:{{ $pct($cPelaksanaan) }}%"></div></div>
        <div class="db-card-cta">Lihat senarai &rarr;</div>
    </div>

    <div class="db-card db-card-c4" onclick="openStatModal('eot')" title="Klik untuk lihat senarai">
        <div class="db-card-top">
            <div class="db-card-icon db-ci-4">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="db-card-pct db-pct-4">{{ $pct($cEot) }}%</span>
        </div>
        <div class="db-card-num">{{ $cEot }}</div>
        <div class="db-card-lbl">Extension Of Time (EOT)</div>
        <div class="db-card-bar"><div class="db-bar-fill db-bar-4" style="width:{{ $pct($cEot) }}%"></div></div>
        <div class="db-card-cta">Lihat senarai &rarr;</div>
    </div>

    <div class="db-card db-card-c5" onclick="openStatModal('kontrak-selesai')" title="Klik untuk lihat senarai">
        <div class="db-card-top">
            <div class="db-card-icon db-ci-5">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="db-card-pct db-pct-5">{{ $pct($cSelesai) }}%</span>
        </div>
        <div class="db-card-num">{{ $cSelesai }}</div>
        <div class="db-card-lbl">Kontrak Selesai</div>
        <div class="db-card-bar"><div class="db-bar-fill db-bar-5" style="width:{{ $pct($cSelesai) }}%"></div></div>
        <div class="db-card-cta">Lihat senarai &rarr;</div>
    </div>

</div>

{{-- ========== CHART + BREAKDOWN ========== --}}
<div class="db-mid-row">

    {{-- Donut Chart --}}
    <div class="db-chart-card">
        <div class="db-chart-hdr">
            <div>
                <p class="db-chart-title">Taburan Status Kontrak</p>
                <p class="db-chart-sub">{{ $tahunSekarang }} &mdash; Jumlah: {{ $total }} kontrak</p>
            </div>
        </div>
        <div class="db-chart-body">
            <div class="db-donut-wrap">
                <canvas id="statusChart"></canvas>
                <div class="db-donut-center">
                    <span class="db-donut-total">{{ $total }}</span>
                    <span class="db-donut-lbl">Kontrak</span>
                </div>
            </div>
            <div class="db-chart-legend">
                <div class="db-legend-item" onclick="highlightSegment(0)" style="cursor:pointer;">
                    <span class="db-legend-dot" style="background:#f43f5e;"></span>
                    <div class="db-legend-text">
                        <span class="db-legend-name">Maklumat Tidak Lengkap</span>
                        <span class="db-legend-val">{{ $cMaklumat }}</span>
                    </div>
                </div>
                <div class="db-legend-item" onclick="highlightSegment(1)" style="cursor:pointer;">
                    <span class="db-legend-dot" style="background:#f59e0b;"></span>
                    <div class="db-legend-text">
                        <span class="db-legend-name">Draf Kontrak</span>
                        <span class="db-legend-val">{{ $cDraf }}</span>
                    </div>
                </div>
                <div class="db-legend-item" onclick="highlightSegment(2)" style="cursor:pointer;">
                    <span class="db-legend-dot" style="background:#2563eb;"></span>
                    <div class="db-legend-text">
                        <span class="db-legend-name">Dalam Pelaksanaan</span>
                        <span class="db-legend-val">{{ $cPelaksanaan }}</span>
                    </div>
                </div>
                <div class="db-legend-item" onclick="highlightSegment(3)" style="cursor:pointer;">
                    <span class="db-legend-dot" style="background:#8b5cf6;"></span>
                    <div class="db-legend-text">
                        <span class="db-legend-name">Extension Of Time</span>
                        <span class="db-legend-val">{{ $cEot }}</span>
                    </div>
                </div>
                <div class="db-legend-item" onclick="highlightSegment(4)" style="cursor:pointer;">
                    <span class="db-legend-dot" style="background:#10b981;"></span>
                    <div class="db-legend-text">
                        <span class="db-legend-name">Kontrak Selesai</span>
                        <span class="db-legend-val">{{ $cSelesai }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress breakdown --}}
    <div class="db-progress-card">
        <div class="db-chart-hdr">
            <div>
                <p class="db-chart-title">Pecahan Mengikut Status</p>
                <p class="db-chart-sub">Peratus daripada jumlah keseluruhan</p>
            </div>
        </div>
        <div class="db-prog-list">

            <div class="db-prog-item">
                <div class="db-prog-row">
                    <div class="db-prog-label-wrap">
                        <span class="db-prog-dot" style="background:#f43f5e;"></span>
                        <span class="db-prog-name">Maklumat Tidak Lengkap</span>
                    </div>
                    <span class="db-prog-count">{{ $cMaklumat }} <span class="db-prog-pct">({{ $pct($cMaklumat) }}%)</span></span>
                </div>
                <div class="db-prog-track">
                    <div class="db-prog-fill" style="width:{{ $pct($cMaklumat) }}%;background:linear-gradient(90deg,#f43f5e,#fb7185);"></div>
                </div>
            </div>

            <div class="db-prog-item">
                <div class="db-prog-row">
                    <div class="db-prog-label-wrap">
                        <span class="db-prog-dot" style="background:#f59e0b;"></span>
                        <span class="db-prog-name">Draf Kontrak</span>
                    </div>
                    <span class="db-prog-count">{{ $cDraf }} <span class="db-prog-pct">({{ $pct($cDraf) }}%)</span></span>
                </div>
                <div class="db-prog-track">
                    <div class="db-prog-fill" style="width:{{ $pct($cDraf) }}%;background:linear-gradient(90deg,#f59e0b,#fcd34d);"></div>
                </div>
            </div>

            <div class="db-prog-item">
                <div class="db-prog-row">
                    <div class="db-prog-label-wrap">
                        <span class="db-prog-dot" style="background:#2563eb;"></span>
                        <span class="db-prog-name">Dalam Pelaksanaan</span>
                    </div>
                    <span class="db-prog-count">{{ $cPelaksanaan }} <span class="db-prog-pct">({{ $pct($cPelaksanaan) }}%)</span></span>
                </div>
                <div class="db-prog-track">
                    <div class="db-prog-fill" style="width:{{ $pct($cPelaksanaan) }}%;background:linear-gradient(90deg,#2563eb,#60a5fa);"></div>
                </div>
            </div>

            <div class="db-prog-item">
                <div class="db-prog-row">
                    <div class="db-prog-label-wrap">
                        <span class="db-prog-dot" style="background:#8b5cf6;"></span>
                        <span class="db-prog-name">Extension Of Time (EOT)</span>
                    </div>
                    <span class="db-prog-count">{{ $cEot }} <span class="db-prog-pct">({{ $pct($cEot) }}%)</span></span>
                </div>
                <div class="db-prog-track">
                    <div class="db-prog-fill" style="width:{{ $pct($cEot) }}%;background:linear-gradient(90deg,#8b5cf6,#c4b5fd);"></div>
                </div>
            </div>

            <div class="db-prog-item">
                <div class="db-prog-row">
                    <div class="db-prog-label-wrap">
                        <span class="db-prog-dot" style="background:#10b981;"></span>
                        <span class="db-prog-name">Kontrak Selesai</span>
                    </div>
                    <span class="db-prog-count">{{ $cSelesai }} <span class="db-prog-pct">({{ $pct($cSelesai) }}%)</span></span>
                </div>
                <div class="db-prog-track">
                    <div class="db-prog-fill" style="width:{{ $pct($cSelesai) }}%;background:linear-gradient(90deg,#10b981,#34d399);"></div>
                </div>
            </div>

        </div>

        {{-- Quick action shortcuts --}}
        <div class="db-shortcuts">
            <p class="db-shortcuts-title">Tindakan Pantas</p>
            <div class="db-shortcut-btns">
                <a href="{{ url('/kontrak') }}" class="db-shortcut-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Senarai Kontrak
                </a>
                <a href="{{ url('/laporan') }}" class="db-shortcut-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Laporan
                </a>
                <a href="{{ url('/syarikat') }}" class="db-shortcut-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Maklumat Syarikat
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ========== ALERT PANELS ========== --}}
<p class="db-section-label" style="margin-top:0.5rem;">Pemakluman Kontrak</p>
<div class="uu-alert-list">
    <button type="button" class="uu-alert red" onclick="togglePanel('panel-tamat-pegawai', this)">
        <div class="alert-main">
            <span class="icon">⚠</span>
            <span>({{ count($tamat) }} Kontrak) - Tempoh Tandatangan Draf Kontrak Telah Tamat (Sila Mohon EGPA)</span>
        </div>
        <span class="chev">⌄</span>
    </button>
    <div id="panel-tamat-pegawai" class="uu-panel" style="display:none;">
        @include('components.dashboard._alert_table', [
            'rows' => $tamat,
            'headerLabel' => 'Tempoh Tandatangan Draf Kontrak Telah Tamat',
        ])
    </div>

    <button type="button" class="uu-alert amber" onclick="togglePanel('panel-hampir-pegawai', this)">
        <div class="alert-main">
            <span class="icon">◷</span>
            <span>({{ count($hampir) }} Kontrak) - Tempoh Tandatangan Draf Kontrak Akan Tamat (Dalam tempoh 2 minggu) — ({{ $today->format('d-m-Y') }} sehingga {{ $twoWeeks->format('d-m-Y') }})</span>
        </div>
        <span class="chev">⌄</span>
    </button>
    <div id="panel-hampir-pegawai" class="uu-panel" style="display:none;">
        @include('components.dashboard._alert_table', [
            'rows' => $hampir,
            'headerLabel' => 'Tempoh Tandatangan Draf Kontrak Akan Tamat',
        ])
    </div>

    <button type="button" class="uu-alert green" onclick="togglePanel('panel-aktif-pegawai', this)">
        <div class="alert-main">
            <span class="icon">✓</span>
            <span>({{ count($aktif) }} Kontrak) - Kontrak Akan Tamat Dalam Tempoh 6 Bulan — ({{ $today->format('d-m-Y') }} sehingga {{ $sixMonths->format('d-m-Y') }})</span>
        </div>
        <span class="chev">⌄</span>
    </button>
    <div id="panel-aktif-pegawai" class="uu-panel" style="display:block;">
        @include('components.dashboard._alert_table', [
            'rows' => $aktif,
            'headerLabel' => 'Kontrak Akan Tamat Dalam Tempoh 6 Bulan',
        ])
    </div>
</div>

{{-- ========== MODALS ========== --}}
<div class="modal-overlay" id="modalKontrakDetailPegawai">
    <div class="modal" style="max-width:900px;width:95%;">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon-bubble blue">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="modal-title">Perincian Kontrak</div>
                    <div class="modal-subtitle" id="pegawaiDetailNoKontrak">-</div>
                    <div class="db-kchip-row">
                        <span class="db-kchip" id="pegawaiDetailKategoriChip">-</span>
                        <span class="db-kchip status" id="pegawaiDetailStatusChip">-</span>
                    </div>
                </div>
            </div>
            <button class="modal-close" onclick="closeKontrakDetailModal()">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" style="max-height:70vh;overflow:auto;">
            <div class="db-kdetail-grid">
                <div class="db-kdetail-item"><span class="db-kdetail-label">Kategori</span><span class="db-kdetail-value" id="pegawaiDetailKategori">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">Tajuk Kontrak</span><span class="db-kdetail-value" id="pegawaiDetailTajuk">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">No. Kontrak</span><span class="db-kdetail-value db-kmono" id="pegawaiDetailNo">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">Status Kontrak</span><span class="db-kdetail-value" id="pegawaiDetailStatus">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">Tempoh Kontrak</span><span class="db-kdetail-value" id="pegawaiDetailTempoh">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">Nilai Kontrak</span><span class="db-kdetail-value" id="pegawaiDetailNilai">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">Jabatan</span><span class="db-kdetail-value" id="pegawaiDetailJabatan">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">Syarikat</span><span class="db-kdetail-value" id="pegawaiDetailSyarikat">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">Pemilik Projek</span><span class="db-kdetail-value" id="pegawaiDetailPegawai">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">Kaedah Perolehan</span><span class="db-kdetail-value" id="pegawaiDetailKaedah">-</span></div>
                <div class="db-kdetail-item"><span class="db-kdetail-label">Kategori Perolehan</span><span class="db-kdetail-value" id="pegawaiDetailKategoriPerolehan">-</span></div>
                <div class="db-kdetail-item db-kdetail-full"><span class="db-kdetail-label">Catatan Kontrak</span><span class="db-kdetail-value" id="pegawaiDetailCatatan">-</span></div>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalMaklumat">
    <div class="modal" style="max-width:1220px;width:99%;">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="padding:0.625rem;background:rgba(244,63,94,0.1);border-radius:0.75rem;">
                    <svg width="20" height="20" fill="none" stroke="#f43f5e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div class="modal-title">Maklumat Tidak Lengkap</div>
                    <div class="modal-subtitle">Senarai kontrak dengan maklumat tidak lengkap</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('modalMaklumat')">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding:0;">
            @include('components.dashboard._modal_table', ['modalId' => 'maklumat-tidak-lengkap', 'tahunSekarang' => $tahunSekarang])
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalSelesai">
    <div class="modal" style="max-width:1220px;width:99%;">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="padding:0.625rem;background:rgba(16,185,129,0.1);border-radius:0.75rem;">
                    <svg width="20" height="20" fill="none" stroke="#10b981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="modal-title">Kontrak Selesai</div>
                    <div class="modal-subtitle">Senarai kontrak yang telah selesai</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('modalSelesai')">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding:0;">
            @include('components.dashboard._modal_table', ['modalId' => 'kontrak-selesai', 'tahunSekarang' => $tahunSekarang])
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalDraf">
    <div class="modal" style="max-width:1220px;width:99%;">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="padding:0.625rem;background:rgba(245,158,11,0.12);border-radius:0.75rem;">
                    <svg width="20" height="20" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <div class="modal-title">Draf Kontrak</div>
                    <div class="modal-subtitle">Senarai kontrak berstatus draf</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('modalDraf')">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding:0;">
            @include('components.dashboard._modal_table', ['modalId' => 'draf-kontrak', 'tahunSekarang' => $tahunSekarang])
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalPelaksanaan">
    <div class="modal" style="max-width:1220px;width:99%;">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="padding:0.625rem;background:rgba(37,99,235,0.12);border-radius:0.75rem;">
                    <svg width="20" height="20" fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <div class="modal-title">Dalam Pelaksanaan</div>
                    <div class="modal-subtitle">Senarai kontrak dalam pelaksanaan</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('modalPelaksanaan')">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding:0;">
            @include('components.dashboard._modal_table', ['modalId' => 'dalam-pelaksanaan', 'tahunSekarang' => $tahunSekarang])
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalEot">
    <div class="modal" style="max-width:1220px;width:99%;">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="padding:0.625rem;background:rgba(139,92,246,0.12);border-radius:0.75rem;">
                    <svg width="20" height="20" fill="none" stroke="#8b5cf6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="modal-title">Extension Of Time (EOT)</div>
                    <div class="modal-subtitle">Senarai kontrak berstatus EOT</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('modalEot')">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding:0;">
            @include('components.dashboard._modal_table', ['modalId' => 'eot', 'tahunSekarang' => $tahunSekarang])
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* WIP overlay */
.db-wip-overlay {
    position: fixed;
    inset: 0;
    z-index: 5000;
    display: grid;
    place-items: center;
    background: rgba(15, 23, 42, 0.34);
    backdrop-filter: blur(4px);
}
.db-wip-modal {
    position: relative;
    width: min(92vw, 440px);
    padding: 2.5rem 1.8rem 2.1rem;
    border-radius: 24px;
    border: 1px solid #dbe7f3;
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 28px 70px rgba(15, 23, 42, 0.25);
    text-align: center;
    color: #0f172a;
}
.db-wip-close {
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
.db-wip-badge {
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
.db-wip-modal h2 {
    margin: 0;
    font-size: 1.6rem;
    line-height: 1.15;
    font-weight: 900;
}
.db-wip-modal p {
    margin: .65rem 0 0;
    color: #64748b;
    font-weight: 600;
}
.db-wip-hidden {
    display: none !important;
}

/* ============================================================
   BANNER
   ============================================================ */
.db-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #1e3a8a 100%);
    border-radius: 20px;
    padding: 2rem 2.5rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}
.db-banner-orb {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.db-orb-1 {
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(37,99,235,0.3) 0%, transparent 70%);
    top: -100px; right: -60px;
}
.db-orb-2 {
    width: 180px; height: 180px;
    background: radial-gradient(circle, rgba(250,204,21,0.12) 0%, transparent 70%);
    bottom: -60px; left: 40%;
}
.db-banner-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    position: relative;
    z-index: 1;
    flex-wrap: wrap;
}
.db-greeting-row { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem; }
.db-avatar {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, #facc15, #f59e0b);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; font-weight: 800; color: #0f172a;
    flex-shrink: 0;
}
.db-greeting { font-size: 1.3rem; font-weight: 700; color: white; line-height: 1.2; }
.db-greeting-name { color: #facc15; }
.db-date { font-size: 0.75rem; color: #94a3b8; display: flex; align-items: center; gap: 0.3rem; margin-top: 0.2rem; }
.db-tagline { font-size: 0.84rem; color: #94a3b8; margin-top: 0.5rem; }
.db-banner-stats {
    display: flex;
    align-items: center;
    gap: 0;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 16px;
    padding: 1.125rem 1.75rem;
    backdrop-filter: blur(10px);
    flex-shrink: 0;
    flex-wrap: wrap;
    gap: 1rem;
}
.db-banner-stat { text-align: center; }
.db-banner-stat-num {
    display: block;
    font-size: 1.875rem; font-weight: 800; color: white;
    letter-spacing: -0.04em; line-height: 1;
}
.db-banner-stat-lbl {
    display: block;
    font-size: 0.68rem; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.1em;
    font-weight: 600; margin-top: 0.25rem;
}
.db-num-amber  { color: #fbbf24 !important; }
.db-num-emerald{ color: #34d399 !important; }
.db-banner-divider { width: 1px; height: 40px; background: rgba(255,255,255,0.1); }

/* ============================================================
   SECTION LABEL
   ============================================================ */
.db-section-label {
    font-size: 0.68rem; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.14em;
    margin-bottom: 1rem;
}

/* ============================================================
   STAT CARDS
   ============================================================ */
.db-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}
@media (min-width:640px)  { .db-cards { grid-template-columns: repeat(3,1fr); } }
@media (min-width:1280px) { .db-cards { grid-template-columns: repeat(5,1fr); } }

.db-card {
    background: white;
    border-radius: 18px;
    padding: 1.375rem 1.25rem 1.125rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
    cursor: default;
}
.db-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 18px 18px 0 0;
}
.db-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
.db-card[onclick] { cursor: pointer; }

.db-card-c1::after { background: linear-gradient(90deg,#f43f5e,#fb7185); }
.db-card-c2::after { background: linear-gradient(90deg,#f59e0b,#fcd34d); }
.db-card-c3::after { background: linear-gradient(90deg,#2563eb,#60a5fa); }
.db-card-c4::after { background: linear-gradient(90deg,#8b5cf6,#c4b5fd); }
.db-card-c5::after { background: linear-gradient(90deg,#10b981,#34d399); }

.db-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem; }
.db-card-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.db-ci-1 { background: rgba(244,63,94,0.1);  color: #f43f5e; }
.db-ci-2 { background: rgba(245,158,11,0.1); color: #f59e0b; }
.db-ci-3 { background: rgba(37,99,235,0.1);  color: #2563eb; }
.db-ci-4 { background: rgba(139,92,246,0.1); color: #8b5cf6; }
.db-ci-5 { background: rgba(16,185,129,0.1); color: #10b981; }

.db-card-pct { font-size: 0.72rem; font-weight: 700; border-radius: 99px; padding: 0.125rem 0.5rem; }
.db-pct-1 { background: rgba(244,63,94,0.1);  color: #f43f5e; }
.db-pct-2 { background: rgba(245,158,11,0.1); color: #d97706; }
.db-pct-3 { background: rgba(37,99,235,0.1);  color: #2563eb; }
.db-pct-4 { background: rgba(139,92,246,0.1); color: #8b5cf6; }
.db-pct-5 { background: rgba(16,185,129,0.1); color: #059669; }

.db-card-num { font-size: 2.25rem; font-weight: 800; color: #0f172a; letter-spacing: -0.04em; line-height: 1; margin-bottom: 0.3rem; }
.db-card-lbl { font-size: 0.68rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; line-height: 1.45; margin-bottom: 0.75rem; }
.db-card-bar { height: 4px; background: #f1f5f9; border-radius: 99px; overflow: hidden; margin-bottom: 0.6rem; }
.db-bar-fill { height: 100%; border-radius: 99px; transition: width 1s ease; min-width: 4px; }
.db-bar-1 { background: linear-gradient(90deg,#f43f5e,#fb7185); }
.db-bar-2 { background: linear-gradient(90deg,#f59e0b,#fcd34d); }
.db-bar-3 { background: linear-gradient(90deg,#2563eb,#60a5fa); }
.db-bar-4 { background: linear-gradient(90deg,#8b5cf6,#c4b5fd); }
.db-bar-5 { background: linear-gradient(90deg,#10b981,#34d399); }
.db-card-cta { font-size: 0.68rem; font-weight: 700; color: #2563eb; opacity: 0.6; transition: opacity 0.2s; text-transform: uppercase; letter-spacing: 0.05em; }
.db-card:hover .db-card-cta { opacity: 1; }

/* ============================================================
   MIDDLE ROW (chart + progress)
   ============================================================ */
.db-mid-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    margin-bottom: 2rem;
}
@media (min-width:900px) { .db-mid-row { grid-template-columns: 1fr 1fr; } }

.db-chart-card, .db-progress-card {
    background: white;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    padding: 1.5rem;
}
.db-chart-hdr { margin-bottom: 1.25rem; }
.db-chart-title { font-size: 1rem; font-weight: 700; color: #0f172a; }
.db-chart-sub   { font-size: 0.75rem; color: #64748b; margin-top: 0.2rem; }

.db-chart-body { display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; }
.db-donut-wrap {
    position: relative;
    width: 180px; height: 180px;
    flex-shrink: 0;
    margin: 0 auto;
}
.db-donut-center {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%,-50%);
    text-align: center;
    pointer-events: none;
}
.db-donut-total { display: block; font-size: 1.75rem; font-weight: 800; color: #0f172a; letter-spacing: -0.04em; line-height: 1; }
.db-donut-lbl   { display: block; font-size: 0.65rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 0.15rem; }

.db-chart-legend { flex: 1; display: flex; flex-direction: column; gap: 0.75rem; min-width: 0; }
.db-legend-item { display: flex; align-items: center; gap: 0.625rem; padding: 0.375rem 0.5rem; border-radius: 8px; transition: background 0.15s; }
.db-legend-item:hover { background: #f8fafc; }
.db-legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.db-legend-text { display: flex; align-items: center; justify-content: space-between; flex: 1; gap: 0.5rem; min-width: 0; }
.db-legend-name { font-size: 0.78rem; color: #334155; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.db-legend-val  { font-size: 0.78rem; font-weight: 700; color: #0f172a; flex-shrink: 0; }

/* Progress card */
.db-prog-list { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; }
.db-prog-item {}
.db-prog-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem; }
.db-prog-label-wrap { display: flex; align-items: center; gap: 0.5rem; }
.db-prog-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.db-prog-name { font-size: 0.78rem; font-weight: 500; color: #334155; }
.db-prog-count { font-size: 0.78rem; font-weight: 700; color: #0f172a; }
.db-prog-pct   { font-weight: 400; color: #64748b; }
.db-prog-track { height: 6px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.db-prog-fill  { height: 100%; border-radius: 99px; transition: width 1.2s cubic-bezier(0.4,0,0.2,1); min-width: 4px; }

/* Shortcut buttons */
.db-shortcuts { border-top: 1px solid #f1f5f9; padding-top: 1.25rem; }
.db-shortcuts-title { font-size: 0.68rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 0.75rem; }
.db-shortcut-btns { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.db-shortcut-btn {
    display: inline-flex; align-items: center; gap: 0.375rem;
    padding: 0.45rem 0.875rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.78rem; font-weight: 600; color: #334155;
    text-decoration: none;
    transition: all 0.18s;
}
.db-shortcut-btn:hover { background: #2563eb; color: white; border-color: #2563eb; }
.db-shortcut-btn:hover svg { stroke: white; }

/* ============================================================
   ALERT PANELS
   ============================================================ */
.db-alerts { display: flex; flex-direction: column; gap: 0.625rem; }
.db-alert-card {
    background: white;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    border-left-width: 4px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.db-alert-red   { border-left-color: #ef4444; }
.db-alert-amber { border-left-color: #f59e0b; }
.db-alert-green { border-left-color: #10b981; }
.db-alert-hdr {
    width: 100%; display: flex; align-items: center;
    justify-content: space-between; padding: 1rem 1.25rem;
    background: none; border: none; cursor: pointer; gap: 1rem;
    transition: background 0.15s;
}
.db-alert-hdr:hover { background: #f8fafc; }
.db-alert-hdr-left { display: flex; align-items: center; gap: 0.875rem; flex: 1; text-align: left; min-width: 0; }
.db-alert-icon-wrap {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.db-aicon-red   { background: #fee2e2; color: #dc2626; }
.db-aicon-amber { background: #fef3c7; color: #d97706; }
.db-aicon-green { background: #d1fae5; color: #059669; }
.db-alert-title { font-size: 0.875rem; font-weight: 600; color: #0f172a; }
.db-alert-sub   { font-size: 0.72rem; color: #64748b; margin-top: 0.125rem; }
.db-alert-hdr-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }
.db-badge {
    display: inline-flex; align-items: center; padding: 0.2rem 0.7rem;
    border-radius: 99px; font-size: 0.7rem; font-weight: 700;
}
.db-badge-red   { background: #fee2e2; color: #dc2626; }
.db-badge-amber { background: #fef3c7; color: #d97706; }
.db-badge-green { background: #d1fae5; color: #059669; }
.db-chev { transition: transform 0.3s; color: #94a3b8; flex-shrink: 0; }
.db-chev.open { transform: rotate(180deg); }
.db-alert-body { border-top: 1px solid #f1f5f9; }

/* ============================================================
   TABLES INSIDE ALERTS
   ============================================================ */
.db-tbl-wrap { overflow-x: auto; }
.db-tbl { width: 100%; border-collapse: collapse; min-width: 680px; }
.db-tbl thead tr { background: #f8fafc; }
.db-tbl thead th {
    padding: 0.625rem 1rem;
    text-align: left; font-size: 0.66rem; font-weight: 700;
    color: #64748b; text-transform: uppercase; letter-spacing: 0.09em;
    border-bottom: 1px solid #e2e8f0; white-space: nowrap;
}
.db-tbl tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.1s; }
.db-tbl tbody tr:hover { background: #f8fafc; }
.db-tbl tbody tr:last-child { border-bottom: none; }
.td-num   { padding: 0.875rem 1rem; font-size: 0.75rem; color: #94a3b8; font-weight: 600; }
.td-title { padding: 0.875rem 1rem; font-size: 0.84rem; font-weight: 600; color: #1e293b; }
.td-link  { color: #2563eb; text-decoration: none; font-weight: 600; font-size: 0.84rem; }
.td-link:hover { text-decoration: underline; }
.td-mono  { padding: 0.875rem 1rem; font-size: 0.75rem; color: #475569; font-family: monospace; }
.td-date  { padding: 0.875rem 1rem; font-size: 0.75rem; color: #64748b; white-space: nowrap; }
.td-dept  { padding: 0.875rem 1rem; font-size: 0.75rem; color: #64748b; }
.td-empty { padding: 2rem; text-align: center; font-size: 0.875rem; color: #94a3b8; }

.uu-alert-list {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    margin-top: 0.7rem;
    margin-bottom: 1rem;
}
.uu-alert {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid transparent;
    border-radius: 14px;
    padding: 1.05rem 1.2rem;
    font-size: 0.9rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
    box-shadow: 0 3px 12px rgba(15, 23, 42, 0.06);
}
.uu-alert:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.1);
}
.uu-alert.red {
    background: linear-gradient(180deg, #fff5f5 0%, #ffefef 100%);
    border-color: #ffc9c9;
    color: #c32020;
}
.uu-alert.amber {
    background: linear-gradient(180deg, #fffdf0 0%, #fff8de 100%);
    border-color: #f3dc89;
    color: #9a5700;
}
.uu-alert.green {
    background: linear-gradient(180deg, #f2fff8 0%, #e9fff4 100%);
    border-color: #b7efce;
    color: #0f7a48;
}
.uu-alert .alert-main {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}
.uu-alert .icon {
    width: 30px;
    height: 30px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.86);
    box-shadow: 0 1px 2px rgba(15,23,42,0.08);
    font-weight: 900;
    flex-shrink: 0;
}
.uu-alert .chev {
    font-size: 0.9rem;
    width: 24px;
    height: 24px;
    border-radius: 999px;
    background: rgba(255,255,255,0.8);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #8aa0bc;
    transition: transform 0.2s ease;
}
.uu-alert.open .chev { transform: rotate(180deg); }

.uu-panel {
    background: #ffffff;
    border: 1px solid #dce8f6;
    border-radius: 12px;
    overflow: hidden;
    margin-top: -0.35rem;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
}

.db-kchip-row {
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}
.db-kchip {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    border: 1px solid #dbeafe;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 0.67rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.db-kchip.status {
    border-color: #dcfce7;
    background: #f0fdf4;
    color: #166534;
}
.db-kdetail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 0.75rem;
}
.db-kdetail-item {
    padding: 0.75rem 0.85rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #f8fafc;
}
.db-kdetail-full { grid-column: 1 / -1; }
.db-kdetail-label {
    display: block;
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    font-weight: 700;
    margin-bottom: 0.35rem;
}
.db-kdetail-value {
    display: block;
    font-size: 0.83rem;
    color: #0f172a;
    font-weight: 600;
    line-height: 1.35;
}
.db-kmono { font-family: monospace; }

#modalKontrakDetailPegawai {
    z-index: 320;
}

/* Modal table toolbar/footer to mirror reference UI */
.db-modal-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.55rem 0.85rem 0.45rem;
    border-bottom: 1px solid #e2e8f0;
}
.db-modal-left-actions,
.db-modal-right-actions {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.db-modal-filter-row {
    padding: 0.4rem 0.85rem 0.1rem;
}
.db-modal-footer-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.6rem 0.85rem 0.7rem;
    gap: 0.75rem;
    border-top: 1px solid #e2e8f0;
    background: #fbfcfe;
    flex-wrap: nowrap;
}

.db-year-picker {
    position: relative;
}
.db-year-btn {
    padding: 0.42rem 0.75rem;
    min-width: 92px;
    border: 1.5px solid #dbe3ee;
    background: #fff;
    color: #475569;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    text-align: left;
}
.db-year-btn::after {
    content: '▾';
    float: right;
    color: #94a3b8;
}
.db-year-panel {
    position: absolute;
    right: 0;
    top: calc(100% + 0.35rem);
    width: 240px;
    background: #fff;
    border: 1px solid #dbe3ee;
    border-radius: 10px;
    box-shadow: 0 12px 26px rgba(15,23,42,0.16);
    z-index: 30;
    padding: 0.5rem;
}
.db-year-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 700;
    margin-bottom: 0.35rem;
}
.db-year-nav-btn {
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    cursor: pointer;
}
.db-year-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.28rem;
}
.db-year-item {
    border: 1px solid transparent;
    border-radius: 7px;
    background: #f8fafc;
    color: #475569;
    font-size: 0.73rem;
    font-weight: 600;
    padding: 0.38rem 0.2rem;
    cursor: pointer;
}
.db-year-item:hover {
    border-color: #bfdbfe;
    color: #1d4ed8;
    background: #eff6ff;
}
.db-year-item.active {
    border-color: #93c5fd;
    color: #1d4ed8;
    background: #dbeafe;
}
.db-year-foot {
    margin-top: 0.4rem;
    border-top: 1px solid #eef2f7;
    padding-top: 0.35rem;
}
.db-year-clear {
    border: none;
    background: none;
    color: #2563eb;
    font-size: 0.72rem;
    font-weight: 700;
    cursor: pointer;
    padding: 0.2rem 0.05rem;
}

/* Modal pagination */
.page-btn { padding: 0.3rem 0.65rem; border: 1.5px solid #e2e8f0; background: white; border-radius: 6px; font-size: 0.8rem; cursor: pointer; font-weight: 600; color: #475569; transition: all 0.15s; }
.page-btn:hover:not(:disabled) { border-color: #2563eb; color: #2563eb; }
.page-btn.active { background: #2563eb; border-color: #2563eb; color: white; }
.page-btn:disabled { opacity: 0.35; cursor: default; }

@media (max-width: 900px) {
    .db-modal-bar { flex-wrap: wrap; }
    .db-modal-right-actions { width: 100%; justify-content: flex-end; }
    .db-modal-footer-row { flex-wrap: wrap; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    var counts = [{{ $cMaklumat }}, {{ $cDraf }}, {{ $cPelaksanaan }}, {{ $cEot }}, {{ $cSelesai }}];
    var labels = ['Maklumat Tidak Lengkap','Draf Kontrak','Dalam Pelaksanaan','Extension of Time','Kontrak Selesai'];
    var colors = ['#f43f5e','#f59e0b','#2563eb','#8b5cf6','#10b981'];
    var hovers = ['#e11d48','#d97706','#1d4ed8','#7c3aed','#059669'];

    var ctx = document.getElementById('statusChart');
    if (!ctx) return;

    var chartInst = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: counts,
                backgroundColor: colors,
                hoverBackgroundColor: hovers,
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverBorderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            cutout: '70%',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce(function(a,b){ return a+b; }, 0);
                            var pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                            return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                        }
                    },
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#f8fafc',
                    padding: 10,
                    cornerRadius: 8
                }
            },
            animation: { animateRotate: true, duration: 1000 }
        }
    });

    window.highlightSegment = function(idx) {
        var meta = chartInst.getDatasetMeta(0);
        var arc  = meta.data[idx];
        if (!arc) return;
        chartInst.setDatasetVisibility(0, true);
        var activeElements = [{ datasetIndex: 0, index: idx }];
        chartInst.tooltip.setActiveElements(activeElements, { x: 0, y: 0 });
        chartInst.setActiveElements(activeElements);
        chartInst.update();
    };
})();

function togglePanel(panelId, btn) {
    var panel = document.getElementById(panelId);
    if (!panel) return;
    var isOpen = panel.style.display !== 'none';
    panel.style.display = isOpen ? 'none' : 'block';
    if (btn) btn.classList.toggle('open', !isOpen);
}

function toggleAlert(id) {
    var body = document.getElementById(id);
    if (!body) return;
    var open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
}
var modalConfig = {
    'maklumat-tidak-lengkap': { modalId: 'modalMaklumat' },
    'draf-kontrak': { modalId: 'modalDraf' },
    'dalam-pelaksanaan': { modalId: 'modalPelaksanaan' },
    'eot': { modalId: 'modalEot' },
    'kontrak-selesai': { modalId: 'modalSelesai' }
};

var modalState = {};
var modalCache = {};
var yearPickerState = {};
var YEAR_WINDOW_SIZE = 9;
var CURRENT_YEAR = {{ (int) $tahunSekarang }};
var modalTitleMap = {
    'maklumat-tidak-lengkap': 'Maklumat Tidak Lengkap',
    'draf-kontrak': 'Draf Kontrak',
    'dalam-pelaksanaan': 'Dalam Pelaksanaan',
    'eot': 'Extension Of Time (EOT)',
    'kontrak-selesai': 'Kontrak Selesai'
};

function closeKontrakDetailModal() {
    var modal = document.getElementById('modalKontrakDetailPegawai');
    if (modal) modal.classList.remove('open');
}

function normalizeNoKontrak(noKontrak) {
    var raw = String(noKontrak || '').trim().toUpperCase();
    if (!raw) return '-';
    if (/^[A-Z0-9]+-\d{4}$/.test(raw)) return raw;

    var m = raw.match(/^(.+?)[\/-]?(\d{4})$/);
    if (!m) return raw;

    var prefix = (m[1] || '').replace(/[^A-Z0-9]/g, '');
    return prefix ? (prefix + '-' + m[2]) : raw;
}

function toDisplayDate(v) {
    if (!v) return '-';
    var d = new Date(v);
    if (Number.isNaN(d.getTime())) return String(v);
    var dd = String(d.getDate()).padStart(2, '0');
    var mm = String(d.getMonth() + 1).padStart(2, '0');
    var yy = d.getFullYear();
    return dd + '/' + mm + '/' + yy;
}

function mapKontrakPayload(raw) {
    var jabatan = raw && raw.jabatan;
    var syarikat = raw && raw.syarikat;
    var pegawai = raw && raw.pegawai_bertanggungjawab;

    return {
        tajuk_kontrak: raw && raw.tajuk_kontrak ? raw.tajuk_kontrak : null,
        no_kontrak: raw && raw.no_kontrak ? raw.no_kontrak : null,
        status_kontrak: raw && raw.status_kontrak ? raw.status_kontrak : null,
        nilai_kontrak: raw && raw.nilai_kontrak ? raw.nilai_kontrak : null,
        mula_tarikh: raw && raw.mula_tarikh ? raw.mula_tarikh : null,
        tamat_tarikh: raw && raw.tamat_tarikh ? raw.tamat_tarikh : null,
        jabatan: jabatan && typeof jabatan === 'object' ? (jabatan.kod || null) : (jabatan || null),
        syarikat: syarikat && typeof syarikat === 'object' ? (syarikat.nama_syarikat || null) : (syarikat || null),
        pegawai: pegawai && typeof pegawai === 'object'
            ? (pegawai.name || null)
            : ((jabatan && typeof jabatan === 'object') ? (jabatan.kod || null) : (jabatan || null)),
        kaedah_perolehan: raw && raw.kaedah_perolehan ? raw.kaedah_perolehan : null,
        kategori_perolehan: raw && raw.kategori_perolehan ? raw.kategori_perolehan : null,
        catatan_kontrak: raw && raw.catatan_kontrak ? raw.catatan_kontrak : null
    };
}

function renderKontrakDetailModal(p, headerLabel) {
    var money = Number.isFinite(Number(p.nilai_kontrak))
        ? new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' }).format(Number(p.nilai_kontrak))
        : '-';
    var tempoh = toDisplayDate(p.mula_tarikh) + ' - ' + toDisplayDate(p.tamat_tarikh);
    var noKontrak = normalizeNoKontrak(p.no_kontrak);

    document.getElementById('pegawaiDetailKategori').textContent = headerLabel || '-';
    document.getElementById('pegawaiDetailTajuk').textContent = p.tajuk_kontrak || '-';
    document.getElementById('pegawaiDetailNo').textContent = noKontrak;
    document.getElementById('pegawaiDetailNoKontrak').textContent = noKontrak;
    document.getElementById('pegawaiDetailStatus').textContent = p.status_kontrak || '-';
    document.getElementById('pegawaiDetailTempoh').textContent = tempoh;
    document.getElementById('pegawaiDetailNilai').textContent = money;
    document.getElementById('pegawaiDetailJabatan').textContent = p.jabatan || '-';
    document.getElementById('pegawaiDetailSyarikat').textContent = p.syarikat || '-';
    document.getElementById('pegawaiDetailPegawai').textContent = p.pegawai || '-';
    document.getElementById('pegawaiDetailKaedah').textContent = p.kaedah_perolehan || '-';
    document.getElementById('pegawaiDetailKategoriPerolehan').textContent = p.kategori_perolehan || '-';
    document.getElementById('pegawaiDetailCatatan').textContent = p.catatan_kontrak || '-';
    document.getElementById('pegawaiDetailKategoriChip').textContent = headerLabel || 'Kategori';
    document.getElementById('pegawaiDetailStatusChip').textContent = p.status_kontrak || 'TIADA STATUS';

    document.getElementById('modalKontrakDetailPegawai').classList.add('open');
}

async function openKontrakDetailModalById(id, headerLabel, fallbackPayload) {
    if (!id) {
        renderKontrakDetailModal(fallbackPayload || {}, headerLabel);
        return;
    }

    try {
        var res = await fetch('/kontrak/' + id, { headers: { Accept: 'application/json' } });
        var json = await res.json();
        var k = json && json.data ? json.data : {};

        var payload = {
            tajuk_kontrak: (k.tajuk_kontrak ?? fallbackPayload?.tajuk_kontrak ?? null),
            no_kontrak: (k.no_kontrak ?? fallbackPayload?.no_kontrak ?? null),
            status_kontrak: (k.status_kontrak ?? fallbackPayload?.status_kontrak ?? null),
            nilai_kontrak: (k.nilai_kontrak ?? fallbackPayload?.nilai_kontrak ?? null),
            mula_tarikh: (k.mula_tarikh ?? fallbackPayload?.mula_tarikh ?? null),
            tamat_tarikh: (k.tamat_tarikh ?? fallbackPayload?.tamat_tarikh ?? null),
            jabatan: (k.jabatan && k.jabatan.kod) ? k.jabatan.kod : (fallbackPayload?.jabatan ?? null),
            syarikat: (k.syarikat && k.syarikat.nama_syarikat) ? k.syarikat.nama_syarikat : (fallbackPayload?.syarikat ?? null),
            pegawai: (k.pegawai_bertanggungjawab && k.pegawai_bertanggungjawab.name)
                ? k.pegawai_bertanggungjawab.name
                : (fallbackPayload?.pegawai ?? null),
            kaedah_perolehan: (k.kaedah_perolehan ?? fallbackPayload?.kaedah_perolehan ?? null),
            kategori_perolehan: (k.kategori_perolehan ?? fallbackPayload?.kategori_perolehan ?? null),
            catatan_kontrak: (k.catatan_kontrak ?? fallbackPayload?.catatan_kontrak ?? null)
        };

        renderKontrakDetailModal(payload, headerLabel);
    } catch (_err) {
        renderKontrakDetailModal(fallbackPayload || {}, headerLabel);
    }
}

function openKontrakDetailFromModalRow(index, type) {
    var rows = modalCache[type] || [];
    var row = rows[index] || {};
    var label = modalTitleMap[type] || 'Status Kontrak';
    openKontrakDetailModalById(row.id || 0, label, mapKontrakPayload(row));
}

window.openKontrakDetailModalById = openKontrakDetailModalById;
window.openKontrakDetailFromModalRow = openKontrakDetailFromModalRow;
window.openKontrakAlertModalById = openKontrakDetailModalById;

function ensureYearState(type) {
    if (!yearPickerState[type]) {
        yearPickerState[type] = {
            start: CURRENT_YEAR - 4,
            selected: ''
        };
    }
}

function closeAllYearPanels(exceptType) {
    Object.keys(modalConfig).forEach(function(type) {
        if (exceptType && type === exceptType) return;
        var panel = document.getElementById('year-panel-' + type);
        if (panel) panel.style.display = 'none';
    });
}

function renderYearPicker(type) {
    ensureYearState(type);
    var state = yearPickerState[type];
    var rangeEl = document.getElementById('year-range-' + type);
    var gridEl = document.getElementById('year-grid-' + type);
    var btnEl = document.getElementById('year-btn-' + type);

    if (!rangeEl || !gridEl || !btnEl) return;

    var start = Number(state.start);
    var end = start + YEAR_WINDOW_SIZE - 1;
    rangeEl.textContent = start + ' - ' + end;
    btnEl.textContent = state.selected ? String(state.selected) : 'Semua';

    var html = '';
    for (var y = start; y <= end; y++) {
        var active = String(state.selected) === String(y) ? ' active' : '';
        html += '<button type="button" class="db-year-item' + active + '" onclick="setYearFilter(\'' + type + '\', \'' + y + '\')">' + y + '</button>';
    }
    gridEl.innerHTML = html;
}

function toggleYearPicker(type) {
    var panel = document.getElementById('year-panel-' + type);
    if (!panel) return;

    var willOpen = panel.style.display === 'none' || panel.style.display === '';
    closeAllYearPanels(type);
    renderYearPicker(type);
    panel.style.display = willOpen ? 'block' : 'none';
}

function shiftYearWindow(type, delta) {
    ensureYearState(type);
    yearPickerState[type].start += Number(delta || 0);
    renderYearPicker(type);
}

function setYearFilter(type, year) {
    ensureYearState(type);
    yearPickerState[type].selected = year || '';
    renderYearPicker(type);
    closeAllYearPanels();
    fetchModalData(type, 1, year || '');
}

function openStatModal(type) {
    var cfg = modalConfig[type];
    if (!cfg) return;

    document.getElementById(cfg.modalId).classList.add('open');
    ensureYearState(type);
    renderYearPicker(type);
    fetchModalData(type, 1, yearPickerState[type].selected || '');
}

function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(function(o) {
    o.addEventListener('click', function(e) {
        if (e.target === o) {
            o.classList.remove('open');
            closeAllYearPanels();
        }
    });
});
document.addEventListener('click', function(e) {
    var wrap = e.target.closest('.db-year-picker');
    if (!wrap) closeAllYearPanels();
});

function getFilterValue(id, fallback) {
    var el = document.getElementById(id);
    return el ? el.value : (fallback || '');
}

function normalizeDate(value) {
    if (!value) return '-';
    var d = new Date(value);
    if (Number.isNaN(d.getTime())) return String(value);
    var dd = String(d.getDate()).padStart(2, '0');
    var mm = String(d.getMonth() + 1).padStart(2, '0');
    var yy = d.getFullYear();
    return dd + '-' + mm + '-' + yy;
}

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

async function fetchModalData(type, page, tahun, search) {
    page = page || 1;
    ensureYearState(type);
    tahun = tahun !== undefined ? tahun : (yearPickerState[type].selected || '');
    search = search !== undefined ? search : getFilterValue('search-' + type, '');
    var perPage = getFilterValue('per-page-' + type, '10');

    yearPickerState[type].selected = tahun || '';
    renderYearPicker(type);

    modalState[type] = { page: page, tahun: tahun, search: search, perPage: perPage };

    var el = document.getElementById('modal-table-' + type);
    if (el) el.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;">Memuatkan...</td></tr>';
    var params = new URLSearchParams({ page: page, per_page: perPage });
    if (tahun)  params.append('tahun', tahun);
    if (search) params.append('search', search);

    var url = type === 'maklumat-tidak-lengkap'
        ? '/dashboard/maklumat-tidak-lengkap?' + params.toString()
        : '/dashboard/status/' + type + '?' + params.toString();

    try {
        var res  = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        var json = await res.json();
        renderModalTable(type, json.data || {});
    } catch(e) {
        if (el) el.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;">Tiada data tersedia.</td></tr>';
    }
}
function renderModalTable(type, data) {
    var rows    = data.data || [];
    var meta    = data.meta || data || {};
    var tableEl = document.getElementById('modal-table-' + type);
    var metaEl  = document.getElementById('modal-meta-'  + type);
    var paginEl = document.getElementById('modal-pagin-' + type);
    if (!tableEl) return;
    if (rows.length === 0) {
        tableEl.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;">Tiada rekod dijumpai.</td></tr>';
        if (metaEl)  metaEl.textContent = '';
        if (paginEl) paginEl.innerHTML  = '';
        modalCache[type] = [];
        return;
    }

    modalCache[type] = rows;

    tableEl.innerHTML = rows.map(function(k, i) {
        var owner = (k.pegawai_bertanggungjawab && k.pegawai_bertanggungjawab.name)
            ? k.pegawai_bertanggungjawab.name
            : ((k.jabatan && k.jabatan.kod) ? k.jabatan.kod : (k.jabatan || '-'));

        return '<tr style="border-bottom:1px solid #f1f5f9;">' +
            '<td style="padding:1rem;font-size:0.8rem;color:#94a3b8;">' + ((meta.from || 0) + i) + '</td>' +
            '<td style="padding:1rem;font-size:0.85rem;font-weight:600;color:#1e3a8a;"><a href="#" class="td-link" onclick="event.preventDefault(); openKontrakDetailFromModalRow(' + i + ', \'' + type + '\')">' + escapeHtml(k.tajuk_kontrak || '-') + '</a></td>' +
            '<td style="padding:1rem;font-size:0.8rem;color:#475569;font-family:monospace;">' + escapeHtml(k.no_kontrak || '-') + '</td>' +
            '<td style="padding:1rem;font-size:0.8rem;color:#64748b;">' + escapeHtml(owner) + '</td>' +
            '<td style="padding:1rem;font-size:0.8rem;color:#64748b;white-space:nowrap;">' + normalizeDate(k.mula_tarikh) + ' - ' + normalizeDate(k.tamat_tarikh) + '</td>' +
            '</tr>';
    }).join('');

    var from = meta.from || (rows.length ? ((meta.current_page - 1) * (meta.per_page || rows.length) + 1) : 0);
    var to = meta.to || (rows.length ? (from + rows.length - 1) : 0);
    var total = meta.total || rows.length;
    if (metaEl) metaEl.textContent = 'Memaparkan ' + from + ' hingga ' + to + ' daripada ' + total + ' entri';

    var lastPage = Number(meta.last_page || 1);
    var currentPage = Number(meta.current_page || 1);

    if (paginEl && lastPage > 1) {
        var btns = '<button class="page-btn" onclick="fetchModalData(\'' + type + '\',' + (currentPage - 1) + ')" ' + (currentPage <= 1 ? 'disabled' : '') + '>&lt;</button>';
        var start = Math.max(1, currentPage - 2);
        var end = Math.min(lastPage, currentPage + 2);
        for (var p = start; p <= end; p++) {
            btns += '<button class="page-btn ' + (p === currentPage ? 'active' : '') + '" onclick="fetchModalData(\'' + type + '\',' + p + ')">' + p + '</button>';
        }
        btns += '<button class="page-btn" onclick="fetchModalData(\'' + type + '\',' + (currentPage + 1) + ')" ' + (currentPage >= lastPage ? 'disabled' : '') + '>&gt;</button>';
        paginEl.innerHTML = btns;
    } else if (paginEl) { paginEl.innerHTML = ''; }
}

function exportModalData(type, mode) {
    if (mode === 'print' || mode === 'pdf') {
        window.print();
        return;
    }

    if (mode !== 'excel') return;

    var rows = modalCache[type] || [];
    if (!rows.length) return;

    var csv = [
        ['Bil', 'Tajuk Kontrak', 'No. Kontrak', 'Pemilik Projek', 'Mula Tarikh', 'Tamat Tarikh']
    ];

    rows.forEach(function(k, i) {
        var owner = (k.pegawai_bertanggungjawab && k.pegawai_bertanggungjawab.name)
            ? k.pegawai_bertanggungjawab.name
            : (k.jabatan && k.jabatan.kod ? k.jabatan.kod : '-');

        csv.push([
            i + 1,
            k.tajuk_kontrak || '-',
            k.no_kontrak || '-',
            owner,
            normalizeDate(k.mula_tarikh),
            normalizeDate(k.tamat_tarikh)
        ]);
    });

    var content = csv.map(function(r) {
        return r.map(function(col) {
            var text = String(col).replace(/"/g, '""');
            return '"' + text + '"';
        }).join(',');
    }).join('\n');

    var blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = type + '-senarai.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

document.addEventListener('DOMContentLoaded', function () {
    var overlay = document.getElementById('dbWipOverlay');
    var closeButton = document.getElementById('dbWipClose');

    if (!overlay || !closeButton) {
        return;
    }

    var closeOverlay = function () {
        overlay.classList.add('db-wip-hidden');
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