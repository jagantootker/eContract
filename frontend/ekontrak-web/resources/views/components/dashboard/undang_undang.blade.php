@extends('components.layouts.app')

@section('title', 'Laman Utama')
@section('breadcrumb', 'Laman Utama')

@section('content')
<div class="uu-wip-overlay" id="uuWipOverlay" role="presentation">
    <div class="uu-wip-modal" role="dialog" aria-modal="true" aria-labelledby="uuWipTitle">
        <button type="button" class="uu-wip-close" id="uuWipClose" aria-label="Tutup popup">&times;</button>
        <div class="uu-wip-badge">WORK IN PROGRESS</div>
        <h2 id="uuWipTitle">Laman Utama</h2>
        <p>Halaman ini masih dalam pembangunan.</p>
    </div>
</div>

@php
    $agencyRows = collect($data['data']['agency_rows'] ?? [])->filter(function ($row) {
        $isGrandTotal = strtoupper((string)($row['kod'] ?? '')) === 'JUMLAH KESELURUHAN';
        $hasData = (int)($row['jumlah_keseluruhan'] ?? 0) > 0;
        return $isGrandTotal || $hasData;
    })->values()->all();

    $alertsData = $alerts['data'] ?? [];
    $tamat = $alertsData['tempoh_tamat_telah_tamat'] ?? [];
    $hampir = $alertsData['tempoh_tamat_dalam_2_minggu'] ?? [];

    $today = now();
    $twoWeeks = now()->addWeeks(2);
@endphp

<div class="uu-title-wrap">
    <div class="page-title">Laman Utama</div>
    <div class="page-subtitle">Paparan ringkasan status kontrak mengikut jabatan.</div>
</div>

<div class="card uu-agency-card">
    <div class="card-header uu-agency-head">
        <div>
            <div class="uu-head-title">Ringkasan Kontrak Mengikut Agensi</div>
            <div class="uu-head-sub">Klik pada baris agensi untuk lihat perincian.</div>
        </div>
        <div class="uu-head-actions">
            <div class="search-wrap" style="max-width:240px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input id="agencySearch" type="text" class="search-input" placeholder="Cari agensi...">
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h2m10 0H7m10 0v4a1 1 0 01-1 1H8a1 1 0 01-1-1v-4m10 0H7"/></svg>
                Muat Turun
            </button>
        </div>
    </div>

    <div class="table-wrap">
        <x-table
            :headers="['Agensi', 'Draf Kontrak', 'EOT Kontrak (Dalam Tempoh)']"
            wrap-class="table-scroll"
            table-class="uu-agency-table"
            table-id="agencyTable"
        >
            @forelse($agencyRows as $row)
                @php $isGrandTotal = strtoupper((string)($row['kod'] ?? '')) === 'JUMLAH KESELURUHAN'; @endphp
                <tr class="{{ $isGrandTotal ? 'grand-total' : '' }}">
                    <td class="agency-name">{{ $row['kod'] ?? '-' }}</td>
                    <td>{{ (int)($row['draf_kontrak'] ?? 0) }}</td>
                    <td>{{ (int)($row['eot_kontrak_dalam_tempoh'] ?? 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;padding:2rem;color:#94a3b8;">Tiada data dijumpai.</td></tr>
            @endforelse
        </x-table>
    </div>
</div>

<div class="uu-alert-list">
    <button type="button" class="uu-alert red" onclick="togglePanel('panel-tamat', this)">
        <div class="alert-main">
            <span class="icon">⚠</span>
            <span>({{ count($tamat) }} Kontrak) - Tempoh Tandatangan Draf Kontrak Telah Tamat (Sila Mohon EGPA)</span>
        </div>
        <span class="chev">⌄</span>
    </button>
    <div id="panel-tamat" class="uu-panel" style="display:none;">
        @include('components.dashboard._alert_table', [
            'rows' => $tamat,
            'headerLabel' => 'Tempoh Tandatangan Draf Kontrak Telah Tamat',
        ])
    </div>

    <button type="button" class="uu-alert amber" onclick="togglePanel('panel-hampir', this)">
        <div class="alert-main">
            <span class="icon">◷</span>
            <span>({{ count($hampir) }} Kontrak) - Tempoh Tandatangan Draf Kontrak Akan Tamat (Dalam tempoh 2 minggu) — ({{ $today->format('d-m-Y') }} sehingga {{ $twoWeeks->format('d-m-Y') }})</span>
        </div>
        <span class="chev">⌄</span>
    </button>
    <div id="panel-hampir" class="uu-panel" style="display:none;">
        @include('components.dashboard._alert_table', [
            'rows' => $hampir,
            'headerLabel' => 'Tempoh Tandatangan Draf Kontrak Akan Tamat (Dalam tempoh 2 minggu)',
        ])
    </div>
</div>

<div class="modal-overlay" id="modalAlertKontrak">
    <div class="modal" style="max-width:900px;width:95%;">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon-bubble blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="modal-title">Perincian Kontrak</div>
                    <div class="modal-subtitle" id="alertModalNoKontrak">-</div>
                    <div class="uu-modal-chip-row">
                        <span class="uu-modal-chip" id="alertModalKategoriChip">-</span>
                        <span class="uu-modal-chip status" id="alertModalStatusChip">-</span>
                    </div>
                </div>
            </div>
            <button class="modal-close" onclick="closeKontrakAlertModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="modal-body" style="max-height:70vh;overflow:auto;">
            <div class="uu-detail-grid">
                <div class="uu-detail-item"><span class="uu-detail-label">Kategori Amaran</span><span class="uu-detail-value" id="alertModalKategori">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">Tajuk Kontrak</span><span class="uu-detail-value" id="alertModalTajuk">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">No. Kontrak</span><span class="uu-detail-value uu-mono" id="alertModalNo">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">Status Kontrak</span><span class="uu-detail-value" id="alertModalStatus">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">Tempoh Kontrak</span><span class="uu-detail-value" id="alertModalTempoh">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">Nilai Kontrak</span><span class="uu-detail-value" id="alertModalNilai">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">Jabatan</span><span class="uu-detail-value" id="alertModalJabatan">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">Syarikat</span><span class="uu-detail-value" id="alertModalSyarikat">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">Pemilik Projek</span><span class="uu-detail-value" id="alertModalPegawai">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">Kaedah Perolehan</span><span class="uu-detail-value" id="alertModalKaedah">-</span></div>
                <div class="uu-detail-item"><span class="uu-detail-label">Kategori Perolehan</span><span class="uu-detail-value" id="alertModalKategoriPerolehan">-</span></div>
                <div class="uu-detail-item uu-detail-full"><span class="uu-detail-label">Catatan Kontrak</span><span class="uu-detail-value" id="alertModalCatatan">-</span></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .uu-wip-overlay { position: fixed; inset: 0; z-index: 5000; display: grid; place-items: center; background: rgba(15, 23, 42, 0.34); backdrop-filter: blur(4px); }
    .uu-wip-modal { position: relative; width: min(92vw, 440px); padding: 2.5rem 1.8rem 2.1rem; border-radius: 24px; border: 1px solid #dbe7f3; background: rgba(255, 255, 255, 0.98); box-shadow: 0 28px 70px rgba(15, 23, 42, 0.25); text-align: center; color: #0f172a; }
    .uu-wip-close { position: absolute; top: .8rem; right: .8rem; width: 2.25rem; height: 2.25rem; border: 0; border-radius: 999px; background: #eff6ff; color: #1e3a8a; font-size: 1.5rem; line-height: 1; font-weight: 700; cursor: pointer; }
    .uu-wip-badge { display: inline-flex; align-items: center; justify-content: center; padding: .42rem .75rem; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 1rem; }
    .uu-wip-modal h2 { margin: 0; font-size: 1.6rem; line-height: 1.15; font-weight: 900; }
    .uu-wip-modal p { margin: .65rem 0 0; color: #64748b; font-weight: 600; }
    .uu-wip-hidden { display: none !important; }

    .uu-title-wrap {
        margin-bottom: 1rem;
        padding: 1rem 1.1rem;
        background: linear-gradient(135deg, #ffffff 0%, #eef6ff 100%);
        border: 1px solid #d9e6f5;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
        position: relative;
        overflow: hidden;
    }
    .uu-title-wrap::after {
        content: '';
        position: absolute;
        width: 180px;
        height: 180px;
        right: -70px;
        top: -90px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.18) 0%, rgba(37, 99, 235, 0) 70%);
        pointer-events: none;
    }

    .uu-agency-card {
        margin-bottom: 1.1rem;
        border-radius: 16px;
        border: 1px solid #d7e3f2;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .uu-agency-head {
        padding: 1rem 1.1rem;
        align-items: flex-end;
        background: linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
        border-bottom: 1px solid #d9e6f5;
    }
    .uu-head-title {
        font-size: 1.06rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    .uu-head-sub {
        font-size: 0.83rem;
        color: #64748b;
        margin-top: 0.18rem;
    }
    .uu-head-actions {
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }
    .uu-head-actions .search-wrap {
        border: 1px solid #c8d9ef;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
    }
    .uu-head-actions .search-wrap svg { color: #7c93b2; }
    .uu-head-actions .search-input {
        border: none;
        background: transparent;
        color: #1e3a5f;
        font-weight: 600;
    }
    .uu-head-actions .search-input::placeholder { color: #8aa0bc; }
    .uu-head-actions .btn {
        border-radius: 10px;
        border-color: #b6cbe4;
        color: #1e3a5f;
        font-weight: 700;
        background: #ffffff;
    }
    .uu-head-actions .btn:hover {
        border-color: #7fa7d8;
        color: #123f7a;
        background: #f5f9ff;
    }

    .uu-agency-table {
        min-width: 760px;
        font-variant-numeric: tabular-nums;
    }
    .uu-agency-table thead th {
        background: linear-gradient(180deg, #f4f8fd 0%, #eaf1f9 100%);
        color: #3f5f85;
        font-size: 0.77rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #d7e3f2;
    }
    .uu-agency-table tbody td {
        font-size: 0.93rem;
        color: #1e3a5f;
        border-bottom: 1px solid #e2ebf5;
        transition: background-color 0.15s ease;
    }
    .uu-agency-table tbody tr:nth-child(even) td { background: #fbfdff; }
    .uu-agency-table tbody tr:hover td { background: #f2f8ff; }
    .uu-agency-table .agency-name {
        font-weight: 800;
        color: #123d72;
        letter-spacing: 0.01em;
    }
    .uu-agency-table tbody tr.grand-total td {
        background: linear-gradient(180deg, #e5edff 0%, #dde8ff 100%) !important;
        color: #0f2e87;
        font-weight: 900;
    }

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
    .uu-alert:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(15, 23, 42, 0.1); }
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
    .uu-alert .alert-main { display: flex; align-items: center; gap: 0.7rem; }
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

    .uu-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }
    .uu-modal-chip-row {
        margin-top: 0.45rem;
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
    }
    .uu-modal-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.22rem 0.62rem;
        border: 1px solid #c7d8ee;
        background: #eef5ff;
        color: #20416b;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.01em;
    }
    .uu-modal-chip.status {
        background: #eefcf4;
        border-color: #bde6cb;
        color: #166534;
    }
    .uu-detail-item {
        border: 1px solid #dce8f6;
        border-radius: 10px;
        padding: 0.75rem 0.85rem;
        background: #f8fbff;
        display: flex;
        flex-direction: column;
        gap: 0.22rem;
    }
    .uu-detail-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #5f7493;
        font-weight: 800;
    }
    .uu-detail-value {
        color: #102a4a;
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1.35;
    }
    .uu-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
    .uu-detail-full {
        grid-column: 1 / -1;
    }

    @media (max-width: 900px) {
        .uu-head-actions { width: 100%; }
        .uu-head-actions .search-wrap { max-width: none !important; flex: 1; }
        .uu-head-actions .btn { white-space: nowrap; }
        .uu-alert { padding: 0.9rem 0.95rem; font-size: 0.84rem; }
        .uu-detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
    function togglePanel(panelId, btn) {
        const panel = document.getElementById(panelId);
        const isOpen = panel.style.display !== 'none';
        panel.style.display = isOpen ? 'none' : 'block';
        btn.classList.toggle('open', !isOpen);
    }

    function closeKontrakAlertModal() {
        document.getElementById('modalAlertKontrak').classList.remove('open');
    }

    function normalizeNoKontrak(noKontrak) {
        const raw = String(noKontrak || '').trim().toUpperCase();
        if (!raw) return '-';
        if (/^[A-Z0-9]+-\d{4}$/.test(raw)) return raw;

        const m = raw.match(/^(.+?)[\/-]?(\d{4})$/);
        if (!m) return raw;

        const prefix = (m[1] || '').replace(/[^A-Z0-9]/g, '');
        return prefix ? `${prefix}-${m[2]}` : raw;
    }

    function toDisplayDate(v) {
        if (!v) return '-';
        const d = new Date(v);
        if (Number.isNaN(d.getTime())) return String(v);
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yy = d.getFullYear();
        return `${dd}/${mm}/${yy}`;
    }

    function renderKontrakAlertModal(p, headerLabel) {
        const money = Number.isFinite(Number(p.nilai_kontrak))
            ? new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' }).format(Number(p.nilai_kontrak))
            : '-';

        const tempoh = `${toDisplayDate(p.mula_tarikh)} - ${toDisplayDate(p.tamat_tarikh)}`;
        const noKontrak = normalizeNoKontrak(p.no_kontrak);

        document.getElementById('alertModalKategori').textContent = headerLabel || '-';
        document.getElementById('alertModalTajuk').textContent = p.tajuk_kontrak || '-';
        document.getElementById('alertModalNo').textContent = noKontrak;
        document.getElementById('alertModalNoKontrak').textContent = noKontrak;
        document.getElementById('alertModalStatus').textContent = p.status_kontrak || '-';
        document.getElementById('alertModalTempoh').textContent = tempoh;
        document.getElementById('alertModalNilai').textContent = money;
        document.getElementById('alertModalJabatan').textContent = p.jabatan || '-';
        document.getElementById('alertModalSyarikat').textContent = p.syarikat || '-';
        document.getElementById('alertModalPegawai').textContent = p.pegawai || '-';
        document.getElementById('alertModalKaedah').textContent = p.kaedah_perolehan || '-';
        document.getElementById('alertModalKategoriPerolehan').textContent = p.kategori_perolehan || '-';
        document.getElementById('alertModalCatatan').textContent = p.catatan_kontrak || '-';
        document.getElementById('alertModalKategoriChip').textContent = headerLabel || 'Kategori Amaran';
        document.getElementById('alertModalStatusChip').textContent = p.status_kontrak || 'TIADA STATUS';

        document.getElementById('modalAlertKontrak').classList.add('open');
    }

    function openKontrakAlertModal(payload, headerLabel) {
        renderKontrakAlertModal(payload || {}, headerLabel);
    }

    async function openKontrakAlertModalById(id, headerLabel, fallbackPayload) {
        if (!id) {
            renderKontrakAlertModal(fallbackPayload || {}, headerLabel);
            return;
        }

        try {
            const res = await fetch(`/kontrak/${id}`, { headers: { Accept: 'application/json' } });
            const json = await res.json();
            const k = json?.data || {};

            const payload = {
                tajuk_kontrak: k.tajuk_kontrak ?? fallbackPayload?.tajuk_kontrak ?? null,
                no_kontrak: k.no_kontrak ?? fallbackPayload?.no_kontrak ?? null,
                status_kontrak: k.status_kontrak ?? fallbackPayload?.status_kontrak ?? null,
                nilai_kontrak: k.nilai_kontrak ?? fallbackPayload?.nilai_kontrak ?? null,
                mula_tarikh: k.mula_tarikh ?? fallbackPayload?.mula_tarikh ?? null,
                tamat_tarikh: k.tamat_tarikh ?? fallbackPayload?.tamat_tarikh ?? null,
                jabatan: k.jabatan?.kod ?? fallbackPayload?.jabatan ?? null,
                syarikat: k.syarikat?.nama_syarikat ?? fallbackPayload?.syarikat ?? null,
                pegawai: k.pegawai_bertanggungjawab?.name ?? fallbackPayload?.pegawai ?? null,
                kaedah_perolehan: k.kaedah_perolehan ?? fallbackPayload?.kaedah_perolehan ?? null,
                kategori_perolehan: k.kategori_perolehan ?? fallbackPayload?.kategori_perolehan ?? null,
                catatan_kontrak: k.catatan_kontrak ?? fallbackPayload?.catatan_kontrak ?? null,
            };

            renderKontrakAlertModal(payload, headerLabel);
        } catch (_err) {
            renderKontrakAlertModal(fallbackPayload || {}, headerLabel);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('agencySearch');
        const table = document.getElementById('agencyTable');
        if (input && table) {
            input.addEventListener('input', (e) => {
                const keyword = e.target.value.toLowerCase().trim();
                table.querySelectorAll('tbody tr').forEach((row) => {
                    const agency = (row.cells[0]?.textContent || '').toLowerCase();
                    if (agency.includes('jumlah keseluruhan')) {
                        row.style.display = '';
                        return;
                    }
                    row.style.display = keyword === '' || agency.includes(keyword) ? '' : 'none';
                });
            });
        }

        const modal = document.getElementById('modalAlertKontrak');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeKontrakAlertModal();
                }
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('uuWipOverlay');
        const closeButton = document.getElementById('uuWipClose');

        if (!overlay || !closeButton) {
            return;
        }

        const closeOverlay = function () {
            overlay.classList.add('uu-wip-hidden');
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
