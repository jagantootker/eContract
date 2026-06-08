@extends('components.layouts.app')

@section('title', 'Laporan Lampiran B')
@section('breadcrumb', 'Laporan')

@section('content')
@php
    $sort = $filters['sort'] ?? '';
    $order = strtolower($filters['order'] ?? 'asc');
    $sortUrl = function (string $column) use ($sort, $order) {
        $nextOrder = ($sort === $column && $order === 'asc') ? 'desc' : 'asc';
        return route('laporan.b', array_merge(request()->except('page'), ['sort' => $column, 'order' => $nextOrder]));
    };
@endphp

<div class="card">
    <div class="card-body">
        <a href="{{ route('laporan.index') }}" class="back-link-btn">&larr; Kembali ke Senarai Laporan</a>

        <div class="section-hdr" style="margin-top:0.5rem;">Pemantauan Tempoh Kontrak (Lampiran B)</div>
        <form method="GET" action="{{ route('laporan.b') }}" class="report-toolbar">
            <div class="action-bar" style="margin:0;">
                <button type="button" class="btn btn-outline btn-sm" onclick="printTable()">Print</button>
                <a href="{{ route('laporan.b.pdf', request()->query()) }}" class="btn-export blue">PDF</a>
                <a href="{{ route('laporan.b.excel', request()->query()) }}" class="btn-export green">Excel</a>
                <div class="column-chooser" id="laporanBColumnChooser">
                    <button type="button" class="column-chooser-btn" onclick="toggleLaporanBColumnMenu()">
                        <span id="laporanBColumnButtonLabel">Kolum</span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="column-chooser-menu" id="laporanBColumnMenu" style="display:none;">
                        <div class="column-chooser-title">Tunjuk / sembunyi kolum</div>
                        <label class="column-chooser-item"><input type="checkbox" value="bil" checked onchange="toggleLaporanBColumn('bil')"> Bil</label>
                        <label class="column-chooser-item"><input type="checkbox" value="jabatan" checked onchange="toggleLaporanBColumn('jabatan')"> Jabatan / Bahagian</label>
                        <label class="column-chooser-item"><input type="checkbox" value="bahagian_unit" checked onchange="toggleLaporanBColumn('bahagian_unit')"> Bahagian / Unit</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tajuk_kontrak" checked onchange="toggleLaporanBColumn('tajuk_kontrak')"> Tajuk Perolehan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="kaedah_perolehan" checked onchange="toggleLaporanBColumn('kaedah_perolehan')"> Kaedah Perolehan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tarikh_sst_disetujui_terima" checked onchange="toggleLaporanBColumn('tarikh_sst_disetujui_terima')"> Tarikh SST Disetuju Terima</label>
                        <label class="column-chooser-item"><input type="checkbox" value="mula_tarikh" checked onchange="toggleLaporanBColumn('mula_tarikh')"> Tarikh Mula Perkhidmatan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tamat_tarikh" checked onchange="toggleLaporanBColumn('tamat_tarikh')"> Tarikh Tamat Perkhidmatan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tempoh_bulan" checked onchange="toggleLaporanBColumn('tempoh_bulan')"> Tempoh Kontrak</label>
                        <label class="column-chooser-item"><input type="checkbox" value="nama_syarikat" checked onchange="toggleLaporanBColumn('nama_syarikat')"> Nama Pembekal</label>
                        <label class="column-chooser-item"><input type="checkbox" value="catatan_kontrak" checked onchange="toggleLaporanBColumn('catatan_kontrak')"> Catatan</label>
                    </div>
                </div>
            </div>
            <div class="search-inline">
                <label for="laporanBYearBtn">Tahun:</label>
                <div class="year-picker-wrap" id="laporanBYearPickerWrap">
                    <button type="button" class="year-btn" id="laporanBYearBtn" onclick="toggleLaporanBYearPicker()">
                        <span id="laporanBYearBtnText">{{ $filters['tahun'] ?? 'Semua' }}</span>
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </button>
                    <input type="hidden" id="laporanBYear" name="tahun" value="{{ $filters['tahun'] ?? '' }}">
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="resetLaporanBFilter()">Set Semula</button>
            </div>
        </form>

        <div class="year-popover" id="laporanBYearPopover" style="display:none;">
            <div class="year-popover-nav">
                <button type="button" class="year-current-btn" style="width:auto;padding:0.2rem 0.55rem;margin:0;background:#f1f5f9;color:#475569;" onclick="clearLaporanBYearFilter()">Semua</button>
                <button type="button" class="year-nav-btn" onclick="shiftLaporanBYearRange(-9)">«</button>
                <button type="button" class="year-nav-btn" onclick="shiftLaporanBYearRange(-3)">‹</button>
                <div class="year-range-label" id="laporanBYearRangeLabel">2022 - 2030</div>
                <button type="button" class="year-nav-btn" onclick="shiftLaporanBYearRange(3)">›</button>
                <button type="button" class="year-nav-btn" onclick="shiftLaporanBYearRange(9)">»</button>
            </div>
            <div class="year-grid" id="laporanBYearGrid"></div>
            <button type="button" class="year-current-btn" onclick="selectCurrentLaporanBYear()">Tahun Semasa</button>
        </div>

        <div class="report-table-shell" id="report-table-wrap" style="margin-top:1rem;">
            <div class="report-table-shell-body">
            <table id="report-table">
                <thead>
                    <tr class="super-head">
                        <th colspan="6" data-laporan-b-group="left"></th>
                            <th colspan="3" class="group-title" data-laporan-b-group="tarikh" style="text-align:center !important; vertical-align:middle;">TARIKH KONTRAK</th>
                        <th colspan="2" data-laporan-b-group="right"></th>
                    </tr>
                    <tr>
                        <th data-laporan-b-col="bil">Bil</th>
                        <th data-laporan-b-col="jabatan"><a class="sort-link" href="{{ $sortUrl('jabatan') }}">JABATAN / BAHAGIAN</a></th>
                        <th data-laporan-b-col="bahagian_unit"><a class="sort-link" href="{{ $sortUrl('bahagian_unit') }}">BAHAGIAN / UNIT</a></th>
                        <th data-laporan-b-col="tajuk_kontrak"><a class="sort-link" href="{{ $sortUrl('tajuk_kontrak') }}">TAJUK PEROLEHAN</a></th>
                        <th data-laporan-b-col="kaedah_perolehan"><a class="sort-link" href="{{ $sortUrl('kaedah_perolehan') }}">KAEDAH PEROLEHAN</a></th>
                        <th data-laporan-b-col="tarikh_sst_disetujui_terima"><a class="sort-link" href="{{ $sortUrl('tarikh_sst_disetujui_terima') }}">TARIKH SST DISETUJU TERIMA</a></th>
                        <th data-laporan-b-col="mula_tarikh"><a class="sort-link" href="{{ $sortUrl('mula_tarikh') }}">TARIKH MULA PERKHIDMATAN</a></th>
                        <th data-laporan-b-col="tamat_tarikh"><a class="sort-link" href="{{ $sortUrl('tamat_tarikh') }}">TARIKH TAMAT PERKHIDMATAN</a></th>
                        <th data-laporan-b-col="tempoh_bulan"><a class="sort-link" href="{{ $sortUrl('tempoh_bulan') }}">TEMPOH KONTRAK</a></th>
                        <th data-laporan-b-col="nama_syarikat"><a class="sort-link" href="{{ $sortUrl('nama_syarikat') }}">NAMA PEMBEKAL</a></th>
                        <th data-laporan-b-col="catatan_kontrak"><a class="sort-link" href="{{ $sortUrl('catatan_kontrak') }}">CATATAN</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $index => $row)
                        <tr>
                                <td data-laporan-b-col="bil">{{ $index + 1 }}</td>
                                <td data-laporan-b-col="jabatan">{{ $row['jabatan'] ?? '-' }}</td>
                                <td data-laporan-b-col="bahagian_unit">{{ $row['bahagian_unit'] ?? '-' }}</td>
                                <td data-laporan-b-col="tajuk_kontrak">{{ $row['tajuk_kontrak'] ?? '-' }}</td>
                                <td data-laporan-b-col="kaedah_perolehan">{{ $row['kaedah_perolehan'] ?? '-' }}</td>
                                <td data-laporan-b-col="tarikh_sst_disetujui_terima">{{ $row['tarikh_sst_disetujui_terima'] ?? '-' }}</td>
                                <td data-laporan-b-col="mula_tarikh">{{ $row['mula_tarikh'] ?? '-' }}</td>
                                <td data-laporan-b-col="tamat_tarikh">{{ $row['tamat_tarikh'] ?? '-' }}</td>
                                <td data-laporan-b-col="tempoh_bulan">{{ ($row['tempoh_bulan'] ?? 0) > 0 ? $row['tempoh_bulan'] . ' Bulan' : '-' }}</td>
                                <td data-laporan-b-col="nama_syarikat">{{ $row['nama_syarikat'] ?? '-' }}</td>
                                <td data-laporan-b-col="catatan_kontrak">{{ $row['catatan_kontrak'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align:center;padding:1.5rem;color:#94a3b8;">Tiada data ditemui.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            <div class="report-table-shell-footer">
            @include('components.table.pagination', [
                'from' => $pagination['from'] ?? 0,
                'to' => $pagination['to'] ?? 0,
                'total' => $pagination['total'] ?? 0,
                'currentPage' => $pagination['current_page'] ?? 1,
                'lastPage' => $pagination['last_page'] ?? 1,
                'reloadFn' => 'reloadLaporanB',
                'showPerPage' => true,
                'perPageId' => 'perPageLaporanB',
                'perPage' => $pagination['per_page'] ?? ($filters['per_page'] ?? 10),
            ])
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .report-toolbar { margin-top:1rem; display:flex; align-items:center; justify-content:space-between; gap:0.8rem; flex-wrap:wrap; }
    .action-bar { margin:0.85rem 0; display:flex; align-items:center; gap:0.45rem; flex-wrap:wrap; }
    .column-chooser { position:relative; }
    .column-chooser-btn {
        display:inline-flex; align-items:center; gap:0.35rem; height:34px; padding:0 0.7rem;
        border:1px solid #dbe3ee; border-radius:8px; background:#fff; color:#334155; font-size:0.78rem; font-weight:700;
        cursor:pointer; box-shadow:0 1px 2px rgba(15,23,42,.04);
    }
    .column-chooser-btn:hover { border-color:#bfdbfe; background:#f8fbff; color:#2563eb; }
    .column-chooser-menu {
        position:absolute; right:0; top:calc(100% + 0.4rem); z-index:700; width:280px; max-height:320px; overflow:auto;
        background:#fff; border:1px solid #dbe3ee; border-radius:12px; box-shadow:0 16px 32px rgba(15,23,42,.16); padding:0.65rem;
    }
    .column-chooser-title { font-size:0.74rem; font-weight:800; color:#334155; margin-bottom:0.45rem; text-transform:uppercase; letter-spacing:.04em; }
    .column-chooser-item {
        display:flex; align-items:center; gap:0.5rem; font-size:0.78rem; color:#334155; padding:0.42rem 0.45rem; border-radius:8px; cursor:pointer;
    }
    .column-chooser-item:hover { background:#f8fafc; }
    .column-chooser-item input { width:14px; height:14px; accent-color:#2563eb; }
    .report-table-shell {
        border:1px solid #d7e1ee;
        border-radius:16px;
        overflow:hidden;
        background:#fff;
        box-shadow:0 6px 18px rgba(15,23,42,.06);
    }
    .report-table-shell-body { overflow:auto; }
    .report-table-shell-footer {
        padding:0.8rem 1rem;
        background:linear-gradient(180deg,#fbfdff 0%,#f8fbff 100%);
        border-top:1px solid #e2e8f0;
    }
    .search-inline { display:flex; align-items:center; gap:0.55rem; color:#475569; font-weight:600; }
    .year-picker-wrap { position: relative; }
    .year-btn {
        display:inline-flex;
        align-items:center;
        gap:0.4rem;
        border:1px solid #dbe3ee;
        border-radius:8px;
        height:34px;
        padding:0 0.65rem;
        background:#f8fbff;
        color:#334155;
        font-size:0.78rem;
        font-weight:600;
        cursor:pointer;
        min-width:120px;
        justify-content:space-between;
    }
    .year-btn:hover { border-color:#bfdbfe; background:#f0f7ff; }
    .year-popover {
        position: fixed;
        z-index: 620;
        width: 215px;
        background: #fff;
        border: 1px solid #dbe3ee;
        border-radius: 10px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
        padding: 0.6rem;
    }
    .year-popover-nav { display:flex; align-items:center; gap:0.35rem; margin-bottom:0.5rem; }
    .year-nav-btn {
        width:24px; height:24px; border:0; border-radius:6px; background:#eef3f8; color:#64748b; cursor:pointer;
        font-size:0.82rem; font-weight:700;
    }
    .year-nav-btn:hover { background:#e2e8f0; }
    .year-range-label { flex:1; text-align:center; font-size:0.74rem; font-weight:700; color:#334155; }
    .year-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:0.3rem; }
    .year-cell {
        border:0; border-radius:7px; background:#fff; color:#475569; font-size:0.74rem; font-weight:600;
        padding:0.42rem 0.2rem; cursor:pointer;
    }
    .year-cell:hover { background:#f1f5f9; }
    .year-cell.active { background:#dbeafe; color:#1d4ed8; }
    .year-current-btn {
        margin-top:0.45rem; width:100%; border:0; background:#eff6ff; color:#2563eb; border-radius:7px;
        font-size:0.72rem; font-weight:700; padding:0.45rem 0;
    }
    .sort-link { color:#475569; text-decoration:none; font-weight:700; }
    .sort-link:hover { color:#2563eb; }
    #report-table { width:100%; border-collapse:separate; border-spacing:0; min-width:980px; }
    #report-table th, #report-table td { white-space:normal; min-width:130px; vertical-align:top; }
    #report-table th {
        background:linear-gradient(180deg,#f8fbff 0%,#f4f8fc 100%);
        color:#334155;
        font-size:0.75rem;
        font-weight:800;
        text-align:left;
        padding:1rem 1rem .95rem;
        border-bottom:1px solid #d7e1ee;
        letter-spacing:0.04em;
    }
    #report-table td {
        padding:.95rem 1rem;
        border-bottom:1px solid #e7edf5;
        font-size:.84rem;
        color:#334155;
        line-height:1.55;
    }
    #report-table tbody tr:nth-child(even) { background:#fcfdff; }
    #report-table tbody tr:hover { background:#f6faff; }
    #report-table tbody tr:last-child td { border-bottom:none; }
    #report-table th:first-child, #report-table td:first-child { min-width:46px; width:46px; text-align:center; }
    #report-table th:nth-child(2), #report-table th:nth-child(3), #report-table th:nth-child(5), #report-table th:nth-child(6), #report-table th:nth-child(7), #report-table th:nth-child(8), #report-table th:nth-child(9) { min-width:120px; }
    #report-table .super-head th { background:#dbe6f2; font-size:0.82rem; letter-spacing:0.02em; text-align:center; }
    #report-table .super-head .group-title { font-weight:800; color:#334155; text-align:center; vertical-align:middle; }
    #report-table th, #report-table td { transition: background-color .15s ease, opacity .15s ease; }

    @media (max-width: 680px) {
        .report-toolbar { flex-direction:column; align-items:stretch; }
        .search-inline { justify-content:space-between; }
        .column-chooser-menu { right:auto; left:0; width:min(92vw, 280px); }
        .report-table-shell-footer { padding:0.7rem 0.85rem; }
    }
</style>
@endpush

@push('scripts')
<script>
    let laporanBYearStart = 2022;

    function initLaporanBYearPicker() {
        const selectedYear = parseInt(document.getElementById('laporanBYear')?.value || '', 10);
        const base = Number.isFinite(selectedYear) ? selectedYear : new Date().getFullYear();
        laporanBYearStart = Math.floor(base / 9) * 9;
        renderLaporanBYearGrid();
    }

    function toggleLaporanBYearPicker() {
        const pop = document.getElementById('laporanBYearPopover');
        const wrap = document.getElementById('laporanBYearPickerWrap');
        const opening = pop.style.display !== 'block';

        if (!opening) {
            pop.style.display = 'none';
            return;
        }

        renderLaporanBYearGrid();
        const r = wrap.getBoundingClientRect();
        const popWidth = 215;
        const popHeight = 270;
        const gap = 8;
        const viewportPadding = 12;
        const maxLeft = window.innerWidth - popWidth - viewportPadding;
        const maxTop = window.innerHeight - popHeight - viewportPadding;
        const left = Math.max(viewportPadding, Math.min(r.left, maxLeft));
        const below = r.bottom + gap;
        const above = r.top - gap - popHeight;
        const top = below <= maxTop ? below : Math.max(viewportPadding, above);

        pop.style.left = `${left}px`;
        pop.style.top = `${top}px`;
        pop.style.display = 'block';
    }

    function shiftLaporanBYearRange(step) {
        laporanBYearStart += step;
        renderLaporanBYearGrid();
    }

    function renderLaporanBYearGrid() {
        const years = Array.from({ length: 9 }, (_, i) => laporanBYearStart + i);
        document.getElementById('laporanBYearRangeLabel').textContent = `${years[0]} - ${years[years.length - 1]}`;
        const active = document.getElementById('laporanBYear')?.value || '';
        document.getElementById('laporanBYearGrid').innerHTML = years.map(y =>
            `<button type="button" class="year-cell ${String(y) === active ? 'active' : ''}" onclick="selectLaporanBYear(${y})">${y}</button>`
        ).join('');
    }

    function selectLaporanBYear(y) {
        document.getElementById('laporanBYear').value = y;
        document.getElementById('laporanBYearBtnText').textContent = y;
        document.getElementById('laporanBYearPopover').style.display = 'none';
        reloadLaporanB(1);
    }

    function selectCurrentLaporanBYear() {
        selectLaporanBYear(new Date().getFullYear());
    }

    function clearLaporanBYearFilter() {
        document.getElementById('laporanBYear').value = '';
        document.getElementById('laporanBYearBtnText').textContent = 'Semua';
        document.getElementById('laporanBYearPopover').style.display = 'none';
        reloadLaporanB(1);
    }

    function resetLaporanBFilter() {
        const params = new URLSearchParams(window.location.search);
        params.delete('tahun');
        params.delete('page');
        window.location.href = `{{ route('laporan.b') }}?${params.toString()}`;
    }

    function reloadLaporanB(page = 1) {
        const params = new URLSearchParams(window.location.search);
        const perPageEl = document.getElementById('perPageLaporanB');
        const yearEl = document.getElementById('laporanBYear');

        params.set('page', String(page));

        if (perPageEl && perPageEl.value) {
            params.set('per_page', perPageEl.value);
        }

        if (yearEl) {
            if (yearEl.value && yearEl.value.trim() !== '') {
                params.set('tahun', yearEl.value.trim());
            } else {
                params.delete('tahun');
            }
        }

        window.location.href = `{{ route('laporan.b') }}?${params.toString()}`;
    }

    const laporanBColumnGroups = {
        left: ['bil', 'jabatan', 'bahagian_unit', 'tajuk_kontrak', 'kaedah_perolehan', 'tarikh_sst_disetujui_terima'],
        tarikh: ['mula_tarikh', 'tamat_tarikh', 'tempoh_bulan'],
        right: ['nama_syarikat', 'catatan_kontrak'],
    };

    function getLaporanBColumnState() {
        const fallback = Object.values(laporanBColumnGroups).flat().reduce(function (acc, columnKey) {
            acc[columnKey] = true;
            return acc;
        }, {});

        try {
            const stored = JSON.parse(localStorage.getItem('laporanB.columnVisibility') || 'null');
            if (stored && typeof stored === 'object') {
                return { ...fallback, ...stored };
            }
        } catch (error) {
            // Ignore malformed local storage values.
        }

        return fallback;
    }

    function saveLaporanBColumnState(state) {
        localStorage.setItem('laporanB.columnVisibility', JSON.stringify(state));
    }

    function updateLaporanBColumnButton(state) {
        const totalColumns = Object.values(laporanBColumnGroups).flat().length;
        const visibleColumns = Object.values(state).filter(Boolean).length;
        const button = document.getElementById('laporanBColumnButtonLabel');
        if (button) {
            button.textContent = `Kolum (${visibleColumns}/${totalColumns})`;
        }
    }

    function setLaporanBColumnMenuState(state) {
        document.querySelectorAll('#laporanBColumnMenu input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.checked = state[checkbox.value] !== false;
        });
    }

    function applyLaporanBColumnVisibility() {
        const state = getLaporanBColumnState();

        Object.entries(laporanBColumnGroups).forEach(function ([groupKey, columns]) {
            const visibleColumns = columns.filter(function (columnKey) {
                return state[columnKey] !== false;
            });

            document.querySelectorAll(`[data-laporan-b-group="${groupKey}"]`).forEach(function (cell) {
                if (!visibleColumns.length) {
                    cell.style.display = 'none';
                    return;
                }

                cell.style.display = '';
                cell.colSpan = visibleColumns.length;
            });
        });

        Object.keys(state).forEach(function (columnKey) {
            const visible = state[columnKey] !== false;
            document.querySelectorAll(`[data-laporan-b-col="${columnKey}"]`).forEach(function (cell) {
                cell.style.display = visible ? '' : 'none';
            });
        });

        setLaporanBColumnMenuState(state);
        updateLaporanBColumnButton(state);
    }

    function toggleLaporanBColumn(columnKey) {
        const state = getLaporanBColumnState();
        state[columnKey] = !state[columnKey];
        saveLaporanBColumnState(state);
        applyLaporanBColumnVisibility();
    }

    function toggleLaporanBColumnMenu() {
        const menu = document.getElementById('laporanBColumnMenu');
        if (!menu) return;
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', function (event) {
        const pop = document.getElementById('laporanBYearPopover');
        const wrap = document.getElementById('laporanBYearPickerWrap');
        if (pop && pop.style.display === 'block' && !pop.contains(event.target) && !wrap.contains(event.target)) {
            pop.style.display = 'none';
        }

        const chooser = document.getElementById('laporanBColumnChooser');
        const menu = document.getElementById('laporanBColumnMenu');
        if (chooser && menu && menu.style.display === 'block' && !chooser.contains(event.target)) {
            menu.style.display = 'none';
        }
    });

    initLaporanBYearPicker();
    applyLaporanBColumnVisibility();

    function printTable() {
        const printArea = document.getElementById('report-table').outerHTML;
        const win = window.open('', '_blank');
        win.document.write(`<html><head><title>Laporan</title>
            <style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #000;padding:4px;font-size:11px}</style>
            </head><body>${printArea}</body></html>`);
        win.document.close();
        win.focus();
        win.print();
    }
</script>
@endpush

@section('content')
@php
    $sort = $filters['sort'] ?? '';
    $order = strtolower($filters['order'] ?? 'asc');
    $sortUrl = function (string $column) use ($sort, $order) {
        $nextOrder = ($sort === $column && $order === 'asc') ? 'desc' : 'asc';
        return route('laporan.b', array_merge(request()->except('page'), ['sort' => $column, 'order' => $nextOrder]));
    };
@endphp

<div class="card">
    <div class="card-body">
        <a href="{{ route('laporan.index') }}" class="back-link-btn">&larr; Kembali ke Senarai Laporan</a>

        <div class="section-hdr" style="margin-top:0.5rem;">Pemantauan Tempoh Kontrak (Lampiran B)</div>

            <form method="GET" action="{{ route('laporan.b') }}" class="filter-wrap" id="laporanBFilterForm">
                <div id="laporanBFilterValidationSummary" class="ds-validation-summary" style="display:none; margin-bottom:0.8rem;">
                    <div class="ds-validation-summary-title">Sila lengkapkan maklumat yang diperlukan.</div>
                    <ul class="ds-validation-summary-list"></ul>
                </div>
                <div class="filter-note">Carian by tahun mula dan tamat</div>

                <div class="filter-grid">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Tahun Mula</label>
                        <select class="form-control" name="tahun_mula">
                            <option value="">Semua</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ (string)($filters['tahun_mula'] ?? '') === (string)$year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Tahun Tamat</label>
                        <select class="form-control" name="tahun_tamat">
                            <option value="">Semua</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ (string)($filters['tahun_tamat'] ?? '') === (string)$year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Carian</label>
                        <div class="search-wrap" style="max-width:none;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                            <input type="text" class="search-input" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari...">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                        <a href="{{ route('laporan.b') }}" class="btn btn-outline btn-sm">Reset</a>
                    </div>
                </div>
            </form>

            <div class="action-bar">
                <button type="button" class="btn btn-outline btn-sm" onclick="printTable()">Print</button>
                <a href="{{ route('laporan.b.pdf', request()->query()) }}" class="btn-export blue">PDF</a>
                <a href="{{ route('laporan.b.excel', request()->query()) }}" class="btn-export green">Excel</a>
                <div class="column-chooser" id="laporanBColumnChooser">
                    <button type="button" class="column-chooser-btn" onclick="toggleLaporanBColumnMenu()">
                        <span id="laporanBColumnButtonLabel">Kolum</span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="column-chooser-menu" id="laporanBColumnMenu" style="display:none;">
                        <div class="column-chooser-title">Tunjuk / sembunyi kolum</div>
                        <label class="column-chooser-item"><input type="checkbox" value="bil" checked onchange="toggleLaporanBColumn('bil')"> Bil</label>
                        <label class="column-chooser-item"><input type="checkbox" value="jabatan" checked onchange="toggleLaporanBColumn('jabatan')"> Jabatan / Bahagian</label>
                        <label class="column-chooser-item"><input type="checkbox" value="bahagian_unit" checked onchange="toggleLaporanBColumn('bahagian_unit')"> Bahagian / Unit</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tajuk_kontrak" checked onchange="toggleLaporanBColumn('tajuk_kontrak')"> Tajuk Perolehan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="kaedah_perolehan" checked onchange="toggleLaporanBColumn('kaedah_perolehan')"> Kaedah Perolehan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tarikh_sst_disetujui_terima" checked onchange="toggleLaporanBColumn('tarikh_sst_disetujui_terima')"> Tarikh SST Disetuju Terima</label>
                        <label class="column-chooser-item"><input type="checkbox" value="mula_tarikh" checked onchange="toggleLaporanBColumn('mula_tarikh')"> Tarikh Mula Perkhidmatan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tamat_tarikh" checked onchange="toggleLaporanBColumn('tamat_tarikh')"> Tarikh Tamat Perkhidmatan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tempoh_bulan" checked onchange="toggleLaporanBColumn('tempoh_bulan')"> Tempoh Kontrak</label>
                        <label class="column-chooser-item"><input type="checkbox" value="nama_syarikat" checked onchange="toggleLaporanBColumn('nama_syarikat')"> Nama Pembekal</label>
                        <label class="column-chooser-item"><input type="checkbox" value="status_kontrak" checked onchange="toggleLaporanBColumn('status_kontrak')"> Status</label>
                    </div>
                </div>
            </div>

            <div class="table-wrap" id="report-table-wrap">
                <table id="report-table">
                    <thead>
                        <tr>
                            <th data-laporan-b-group="left" colspan="6"></th>
                            <th data-laporan-b-group="tarikh" colspan="3" class="group-title" style="text-align:center !important; vertical-align:middle;">TARIKH KONTRAK</th>
                            <th data-laporan-b-group="right" colspan="2"></th>
                        </tr>
                        <tr>
                            <th data-laporan-b-col="bil">Bil</th>
                            <th data-laporan-b-col="jabatan"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg><a class="sort-link" href="{{ $sortUrl('jabatan') }}">JABATAN / BAHAGIAN</a></span></th>
                            <th data-laporan-b-col="bahagian_unit"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg><a class="sort-link" href="{{ $sortUrl('bahagian_unit') }}">BAHAGIAN / UNIT</a></span></th>
                            <th data-laporan-b-col="tajuk_kontrak"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg><a class="sort-link" href="{{ $sortUrl('tajuk_kontrak') }}">TAJUK PEROLEHAN</a></span></th>
                            <th data-laporan-b-col="kaedah_perolehan"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><a class="sort-link" href="{{ $sortUrl('kaedah_perolehan') }}">KAEDAH PEROLEHAN</a></span></th>
                            <th data-laporan-b-col="tarikh_sst_disetujui_terima"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg><a class="sort-link" href="{{ $sortUrl('tarikh_sst_disetujui_terima') }}">TARIKH SST DISETUJU TERIMA</a></span></th>
                            <th data-laporan-b-col="mula_tarikh"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg><a class="sort-link" href="{{ $sortUrl('mula_tarikh') }}">TARIKH MULA PERKHIDMATAN</a></span></th>
                            <th data-laporan-b-col="tamat_tarikh"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg><a class="sort-link" href="{{ $sortUrl('tamat_tarikh') }}">TARIKH TAMAT PERKHIDMATAN</a></span></th>
                            <th data-laporan-b-col="tempoh_bulan"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/><rect x="7" y="7" width="10" height="10" rx="2"/></svg><a class="sort-link" href="{{ $sortUrl('tempoh_bulan') }}">TEMPOH (BULAN)</a></span></th>
                            <th data-laporan-b-col="nama_syarikat"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg><a class="sort-link" href="{{ $sortUrl('nama_syarikat') }}">NAMA PEMBEKAL</a></span></th>
                            <th data-laporan-b-col="status_kontrak"><span class="th-icon-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.59 13.41L11 3.83 4.41 10.41a2 2 0 000 2.83l6.34 6.34a2 2 0 002.83 0l7.01-7.01a2 2 0 000-2.83z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01"/></svg><a class="sort-link" href="{{ $sortUrl('status_kontrak') }}">STATUS</a></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $index => $row)
                            <tr>
                                <td data-laporan-b-col="bil">{{ $index + 1 }}</td>
                                <td data-laporan-b-col="jabatan">{{ $row['jabatan'] ?? '-' }}</td>
                                <td data-laporan-b-col="bahagian_unit">{{ $row['bahagian_unit'] ?? '-' }}</td>
                                <td data-laporan-b-col="tajuk_kontrak">{{ $row['tajuk_kontrak'] ?? '-' }}</td>
                                <td data-laporan-b-col="kaedah_perolehan">{{ $row['kaedah_perolehan'] ?? '-' }}</td>
                                <td data-laporan-b-col="tarikh_sst_disetujui_terima">{{ $row['tarikh_sst_disetujui_terima'] ?? '-' }}</td>
                                <td data-laporan-b-col="mula_tarikh">{{ $row['mula_tarikh'] ?? '-' }}</td>
                                <td data-laporan-b-col="tamat_tarikh">{{ $row['tamat_tarikh'] ?? '-' }}</td>
                                <td data-laporan-b-col="tempoh_bulan">{{ $row['tempoh_bulan'] ?? 0 }}</td>
                                <td data-laporan-b-col="nama_syarikat">{{ $row['nama_syarikat'] ?? '-' }}</td>
                                <td data-laporan-b-col="status_kontrak">{{ $row['status_kontrak'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align:center;padding:1.5rem;color:#94a3b8;">Tiada data ditemui.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .filter-wrap { margin-top:0.8rem; }
    .filter-note { color:#ef4444; font-size:0.75rem; margin-bottom:0.5rem; font-weight:600; }
    .filter-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:0.75rem; align-items:end; }
    .filter-actions { display:flex; gap:0.5rem; }
    .action-bar { margin:0.85rem 0; display:flex; align-items:center; justify-content:space-between; gap:0.75rem; flex-wrap:wrap; }
    .column-chooser { position:relative; }
    .column-chooser-btn {
        display:inline-flex; align-items:center; gap:0.35rem; height:34px; padding:0 0.7rem;
        border:1px solid #dbe3ee; border-radius:8px; background:#fff; color:#334155; font-size:0.78rem; font-weight:700;
        cursor:pointer; box-shadow:0 1px 2px rgba(15,23,42,.04);
    }
    .column-chooser-btn:hover { border-color:#bfdbfe; background:#f8fbff; color:#2563eb; }
    .column-chooser-menu {
        position:absolute; right:0; top:calc(100% + 0.4rem); z-index:700; width:280px; max-height:320px; overflow:auto;
        background:#fff; border:1px solid #dbe3ee; border-radius:12px; box-shadow:0 16px 32px rgba(15,23,42,.16); padding:0.65rem;
    }
    .column-chooser-title { font-size:0.74rem; font-weight:800; color:#334155; margin-bottom:0.45rem; text-transform:uppercase; letter-spacing:.04em; }
    .column-chooser-item {
        display:flex; align-items:center; gap:0.5rem; font-size:0.78rem; color:#334155; padding:0.42rem 0.45rem; border-radius:8px; cursor:pointer;
    }
    .column-chooser-item:hover { background:#f8fafc; }
    .column-chooser-item input { width:14px; height:14px; accent-color:#2563eb; }
    .sort-link { color:#475569; text-decoration:none; font-weight:700; }
    .sort-link:hover { color:#2563eb; }
    #report-table th[data-laporan-b-col], #report-table td[data-laporan-b-col] { white-space:normal; min-width:130px; vertical-align:top; }
    #report-table th[data-laporan-b-col="bil"], #report-table td[data-laporan-b-col="bil"] { min-width:46px; width:46px; text-align:center; }
    #report-table .super-head th { background:#dbe6f2; font-size:0.82rem; letter-spacing:0.02em; text-align:center; }
    #report-table .super-head .group-title { font-weight:800; color:#334155; }

    @media (max-width: 1100px) {
        .filter-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }

    @media (max-width: 680px) {
        .filter-grid { grid-template-columns:1fr; }
        .column-chooser-menu { right:auto; left:0; width:min(92vw, 280px); }
    }
</style>
@endpush

@push('scripts')
<script>
    const laporanBColumnGroups = {
        left: ['bil', 'jabatan', 'bahagian_unit', 'tajuk_kontrak', 'kaedah_perolehan', 'tarikh_sst_disetujui_terima'],
        tarikh: ['mula_tarikh', 'tamat_tarikh', 'tempoh_bulan'],
        right: ['nama_syarikat', 'status_kontrak'],
    };

    function getLaporanBColumnState() {
        const fallback = Object.keys(laporanBColumnGroups).reduce(function (acc, groupKey) {
            laporanBColumnGroups[groupKey].forEach(function (columnKey) {
                acc[columnKey] = true;
            });
            return acc;
        }, {});

        try {
            const stored = JSON.parse(localStorage.getItem('laporanB.columnVisibility') || 'null');
            if (stored && typeof stored === 'object') {
                return { ...fallback, ...stored };
            }
        } catch (error) {
            // Ignore malformed local storage values.
        }

        return fallback;
    }

    function saveLaporanBColumnState(state) {
        localStorage.setItem('laporanB.columnVisibility', JSON.stringify(state));
    }

    function updateLaporanBColumnButton(state) {
        const totalColumns = Object.values(laporanBColumnGroups).flat().length;
        const visibleColumns = Object.values(state).filter(Boolean).length;
        const button = document.getElementById('laporanBColumnButtonLabel');
        if (button) {
            button.textContent = `Kolum (${visibleColumns}/${totalColumns})`;
        }
    }

    function setLaporanBColumnMenuState(state) {
        document.querySelectorAll('#laporanBColumnMenu input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.checked = state[checkbox.value] !== false;
        });
    }

    function applyLaporanBColumnVisibility() {
        const state = getLaporanBColumnState();

        Object.entries(laporanBColumnGroups).forEach(function ([groupKey, columns]) {
            const visibleColumns = columns.filter(function (columnKey) {
                return state[columnKey] !== false;
            });

            document.querySelectorAll(`[data-laporan-b-group="${groupKey}"]`).forEach(function (cell) {
                if (!visibleColumns.length) {
                    cell.style.display = 'none';
                    return;
                }

                cell.style.display = '';
                cell.colSpan = visibleColumns.length;
            });
        });

        Object.keys(state).forEach(function (columnKey) {
            const visible = state[columnKey] !== false;
            document.querySelectorAll(`[data-laporan-b-col="${columnKey}"]`).forEach(function (cell) {
                cell.style.display = visible ? '' : 'none';
            });
        });

        setLaporanBColumnMenuState(state);
        updateLaporanBColumnButton(state);
    }

    function toggleLaporanBColumn(columnKey) {
        const state = getLaporanBColumnState();
        state[columnKey] = !state[columnKey];
        saveLaporanBColumnState(state);
        applyLaporanBColumnVisibility();
    }

    function toggleLaporanBColumnMenu() {
        const menu = document.getElementById('laporanBColumnMenu');
        if (!menu) return;
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', function (event) {
        const chooser = document.getElementById('laporanBColumnChooser');
        const menu = document.getElementById('laporanBColumnMenu');
        if (!chooser || !menu || menu.style.display !== 'block') return;
        if (chooser.contains(event.target)) return;
        menu.style.display = 'none';
    });

    function printTable() {
        const printArea = document.getElementById('report-table').outerHTML;
        const win = window.open('', '_blank');
        win.document.write(`<html><head><title>Laporan</title>
            <style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #000;padding:4px;font-size:11px}</style>
            </head><body>${printArea}</body></html>`);
        win.document.close();
        win.focus();
        win.print();
    }

    applyLaporanBColumnVisibility();
</script>
@endpush

@push('scripts')
<script>
    ValidationService.mount('laporanBFilterForm', {
        summaryId: 'laporanBFilterValidationSummary',
        schema: {
            tahun_mula: [
                { type: 'dateOrder', startField: 'tahun_mula', endField: 'tahun_tamat', allowEqual: true, message: 'Tahun tamat mesti sama atau selepas tahun mula.', label: 'Tahun tamat mesti sama atau selepas tahun mula.' },
            ],
        },
    });
</script>
@endpush
