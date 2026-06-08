@extends('components.layouts.app')

@section('title', 'Laporan Lampiran A')
@section('breadcrumb', 'Laporan')

@section('content')
@php
    $sort = $filters['sort'] ?? '';
    $order = strtolower($filters['order'] ?? 'asc');
    $sortUrl = function (string $column) use ($sort, $order) {
        $nextOrder = ($sort === $column && $order === 'asc') ? 'desc' : 'asc';
        return route('laporan.a', array_merge(request()->except('page'), ['sort' => $column, 'order' => $nextOrder]));
    };
@endphp

<div class="card">
    <div class="card-body">
        <a href="{{ route('laporan.index') }}" class="back-link-btn">&larr; Kembali ke Senarai Laporan</a>

        <div class="section-hdr" style="margin-top:0.5rem;">Pemantauan Status Kontrak Ditandatangani (Lampiran A)</div>
        <form method="GET" action="{{ route('laporan.a') }}" class="report-toolbar">
            <div class="action-bar" style="margin:0;">
                <button type="button" class="btn btn-outline btn-sm" onclick="printTable()">Print</button>
                <a href="{{ route('laporan.a.pdf', request()->query()) }}" class="btn-export blue">PDF</a>
                <a href="{{ route('laporan.a.excel', request()->query()) }}" class="btn-export green">Excel</a>
                <div class="column-chooser" id="laporanAColumnChooser">
                    <button type="button" class="column-chooser-btn" onclick="toggleLaporanAColumnMenu()">
                        <span id="laporanAColumnButtonLabel">Kolum</span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="column-chooser-menu" id="laporanAColumnMenu" style="display:none;">
                        <div class="column-chooser-title">Tunjuk / sembunyi kolum</div>
                        <label class="column-chooser-item"><input type="checkbox" value="bil" checked onchange="toggleLaporanAColumn('bil')"> Bil</label>
                        <label class="column-chooser-item"><input type="checkbox" value="jabatan" checked onchange="toggleLaporanAColumn('jabatan')"> Jabatan / Bahagian</label>
                        <label class="column-chooser-item"><input type="checkbox" value="bahagian_unit" checked onchange="toggleLaporanAColumn('bahagian_unit')"> Bahagian / Unit</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tajuk_kontrak" checked onchange="toggleLaporanAColumn('tajuk_kontrak')"> Tajuk Perolehan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="kaedah_perolehan" checked onchange="toggleLaporanAColumn('kaedah_perolehan')"> Kaedah Perolehan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tarikh_sst" checked onchange="toggleLaporanAColumn('tarikh_sst')"> Tarikh SST</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tarikh_sst_disetujui_terima" checked onchange="toggleLaporanAColumn('tarikh_sst_disetujui_terima')"> Tarikh SST Disetuju Terima</label>
                        <label class="column-chooser-item"><input type="checkbox" value="tarikh_akhir_kontrak_perlu_dimatikan_setem" checked onchange="toggleLaporanAColumn('tarikh_akhir_kontrak_perlu_dimatikan_setem')"> Tarikh Akhir Kontrak</label>
                        <label class="column-chooser-item"><input type="checkbox" value="nama_syarikat" checked onchange="toggleLaporanAColumn('nama_syarikat')"> Nama Pembekal</label>
                        <label class="column-chooser-item"><input type="checkbox" value="telah_tandatangan_tarikh_duti_setem" checked onchange="toggleLaporanAColumn('telah_tandatangan_tarikh_duti_setem')"> Telah Tandatangan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="belum_tandatangan_status_tarikh_pergerakan" checked onchange="toggleLaporanAColumn('belum_tandatangan_status_tarikh_pergerakan')"> Belum Tandatangan</label>
                        <label class="column-chooser-item"><input type="checkbox" value="sebab_lewat_tandatangan" checked onchange="toggleLaporanAColumn('sebab_lewat_tandatangan')"> Sebab Lewat</label>
                        <label class="column-chooser-item"><input type="checkbox" value="catatan_kontrak" checked onchange="toggleLaporanAColumn('catatan_kontrak')"> Catatan</label>
                    </div>
                </div>
            </div>
            <div class="search-inline">
                <label for="laporanYearBtn">Tahun:</label>
                <div class="year-picker-wrap" id="laporanYearPickerWrap">
                    <button type="button" class="year-btn" id="laporanYearBtn" onclick="toggleLaporanYearPicker()">
                        <span id="laporanYearBtnText">{{ $filters['tahun'] ?? 'Semua' }}</span>
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </button>
                    <input type="hidden" id="laporanYear" name="tahun" value="{{ $filters['tahun'] ?? '' }}">
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="resetLaporanAFilter()">Set Semula</button>
            </div>
        </form>

        <div class="year-popover" id="laporanYearPopover" style="display:none;">
            <div class="year-popover-nav">
                <button type="button" class="year-current-btn" style="width:auto;padding:0.2rem 0.55rem;margin:0;background:#f1f5f9;color:#475569;" onclick="clearLaporanYearFilter()">Semua</button>
                <button type="button" class="year-nav-btn" onclick="shiftLaporanYearRange(-9)">«</button>
                <button type="button" class="year-nav-btn" onclick="shiftLaporanYearRange(-3)">‹</button>
                <div class="year-range-label" id="laporanYearRangeLabel">2022 - 2030</div>
                <button type="button" class="year-nav-btn" onclick="shiftLaporanYearRange(3)">›</button>
                <button type="button" class="year-nav-btn" onclick="shiftLaporanYearRange(9)">»</button>
            </div>
            <div class="year-grid" id="laporanYearGrid"></div>
            <button type="button" class="year-current-btn" onclick="selectCurrentLaporanYear()">Tahun Semasa</button>
        </div>

        <div class="report-table-shell" id="report-table-wrap" style="margin-top:1rem;">
            <div class="report-table-shell-body">
            <table id="report-table">
                <thead>
                    <tr class="super-head">
                        <th colspan="4" data-laporan-a-group="left"></th>
                        <th colspan="5" class="group-title" data-laporan-a-group="kontrak">KONTRAK</th>
                        <th colspan="4" data-laporan-a-group="right"></th>
                    </tr>
                    <tr>
                        <th data-laporan-a-col="bil">Bil</th>
                        <th data-laporan-a-col="jabatan"><a class="sort-link" href="{{ $sortUrl('jabatan') }}">JABATAN / BAHAGIAN</a></th>
                        <th data-laporan-a-col="bahagian_unit"><a class="sort-link" href="{{ $sortUrl('bahagian_unit') }}">BAHAGIAN / UNIT</a></th>
                        <th data-laporan-a-col="tajuk_kontrak"><a class="sort-link" href="{{ $sortUrl('tajuk_kontrak') }}">TAJUK PEROLEHAN</a></th>
                        <th data-laporan-a-col="kaedah_perolehan"><a class="sort-link" href="{{ $sortUrl('kaedah_perolehan') }}">KAEDAH PEROLEHAN</a></th>
                        <th data-laporan-a-col="tarikh_sst"><a class="sort-link" href="{{ $sortUrl('tarikh_sst') }}">TARIKH SST</a></th>
                        <th data-laporan-a-col="tarikh_sst_disetujui_terima"><a class="sort-link" href="{{ $sortUrl('tarikh_sst_disetujui_terima') }}">TARIKH SST DISETUJU TERIMA</a></th>
                        <th data-laporan-a-col="tarikh_akhir_kontrak_perlu_dimatikan_setem"><a class="sort-link" href="{{ $sortUrl('tarikh_akhir_kontrak_perlu_dimatikan_setem') }}">TARIKH AKHIR KONTRAK PERLU DIMATIKAN SETEM</a></th>
                        <th data-laporan-a-col="nama_syarikat"><a class="sort-link" href="{{ $sortUrl('nama_syarikat') }}">NAMA PEMBEKAL</a></th>
                        <th data-laporan-a-col="telah_tandatangan_tarikh_duti_setem"><a class="sort-link" href="{{ $sortUrl('telah_tandatangan_tarikh_duti_setem') }}">TELAH TANDATANGAN (TARIKH DUTI SETEM)</a></th>
                        <th data-laporan-a-col="belum_tandatangan_status_tarikh_pergerakan"><a class="sort-link" href="{{ $sortUrl('belum_tandatangan_status_tarikh_pergerakan') }}">BELUM TANDATANGAN (STATUS & TARIKH PERGERAKAN KONTRAK)</a></th>
                        <th data-laporan-a-col="sebab_lewat_tandatangan"><a class="sort-link" href="{{ $sortUrl('sebab_lewat_tandatangan') }}">SILA NYATAKAN SEBAB JIKA KONTRAK DITANDATANGANI LEBIH 3 BULAN DARI TARIKH SST DISETUJU TERIMA</a></th>
                        <th data-laporan-a-col="catatan_kontrak"><a class="sort-link" href="{{ $sortUrl('catatan_kontrak') }}">CATATAN</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $index => $row)
                        <tr>
                            <td data-laporan-a-col="bil">{{ $index + 1 }}</td>
                            <td data-laporan-a-col="jabatan">{{ $row['jabatan'] ?? '-' }}</td>
                            <td data-laporan-a-col="bahagian_unit">{{ $row['bahagian_unit'] ?? '-' }}</td>
                            <td data-laporan-a-col="tajuk_kontrak">{{ $row['tajuk_kontrak'] ?? '-' }}</td>
                            <td data-laporan-a-col="kaedah_perolehan">{{ $row['kaedah_perolehan'] ?? '-' }}</td>
                            <td data-laporan-a-col="tarikh_sst">{{ $row['tarikh_sst'] ?? '-' }}</td>
                            <td data-laporan-a-col="tarikh_sst_disetujui_terima">{{ $row['tarikh_sst_disetujui_terima'] ?? '-' }}</td>
                            <td data-laporan-a-col="tarikh_akhir_kontrak_perlu_dimatikan_setem">{{ $row['tarikh_akhir_kontrak_perlu_dimatikan_setem'] ?? '-' }}</td>
                            <td data-laporan-a-col="nama_syarikat">{{ $row['nama_syarikat'] ?? '-' }}</td>
                            <td data-laporan-a-col="telah_tandatangan_tarikh_duti_setem">{{ $row['telah_tandatangan_tarikh_duti_setem'] ?? '-' }}</td>
                            <td data-laporan-a-col="belum_tandatangan_status_tarikh_pergerakan">{{ $row['belum_tandatangan_status_tarikh_pergerakan'] ?? '-' }}</td>
                            <td data-laporan-a-col="sebab_lewat_tandatangan">{{ $row['sebab_lewat_tandatangan'] ?? '-' }}</td>
                            <td data-laporan-a-col="catatan_kontrak">{{ $row['catatan_kontrak'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" style="text-align:center;padding:1.5rem;color:#94a3b8;">Tiada data ditemui.</td>
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
                'reloadFn' => 'reloadLaporanA',
                'showPerPage' => true,
                'perPageId' => 'perPageLaporanA',
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
    .action-bar { margin:0.85rem 0; display:flex; gap:0.45rem; }
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
    .sort-link { color:#475569; text-decoration:none; font-weight:700; }
    .sort-link:hover { color:#2563eb; }
    #report-table { width:100%; border-collapse:separate; border-spacing:0; min-width:1100px; }
    #report-table th,
    #report-table td { white-space:normal; min-width:130px; vertical-align:top; }
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
    #report-table .super-head .group-title { font-weight:800; color:#334155; }
    #report-table th, #report-table td { transition: background-color .15s ease, opacity .15s ease; }

    #report-table th[data-laporan-a-col],
    #report-table td[data-laporan-a-col] {
        white-space:normal;
        min-width:130px;
        vertical-align:top;
    }
    #report-table th[data-laporan-a-col="bil"],
    #report-table td[data-laporan-a-col="bil"] { min-width:46px; width:46px; text-align:center; }

    #report-table th[data-laporan-a-group] {
        text-align:center;
    }

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
    const laporanAColumnGroups = {
        left: ['bil', 'jabatan', 'bahagian_unit', 'tajuk_kontrak'],
        kontrak: ['kaedah_perolehan', 'tarikh_sst', 'tarikh_sst_disetujui_terima', 'tarikh_akhir_kontrak_perlu_dimatikan_setem', 'nama_syarikat'],
        right: ['telah_tandatangan_tarikh_duti_setem', 'belum_tandatangan_status_tarikh_pergerakan', 'sebab_lewat_tandatangan', 'catatan_kontrak'],
    };

    function getLaporanAColumnState() {
        const fallback = Object.keys(laporanAColumnGroups).reduce(function (acc, groupKey) {
            laporanAColumnGroups[groupKey].forEach(function (columnKey) {
                acc[columnKey] = true;
            });
            return acc;
        }, {});

        try {
            const stored = JSON.parse(localStorage.getItem('laporanA.columnVisibility') || 'null');
            if (stored && typeof stored === 'object') {
                return { ...fallback, ...stored };
            }
        } catch (error) {
            // Ignore malformed local storage values.
        }

        return fallback;
    }

    function saveLaporanAColumnState(state) {
        localStorage.setItem('laporanA.columnVisibility', JSON.stringify(state));
    }

    function updateLaporanAColumnButton(state) {
        const totalColumns = Object.values(laporanAColumnGroups).flat().length;
        const visibleColumns = Object.values(state).filter(Boolean).length;
        const button = document.getElementById('laporanAColumnButtonLabel');
        if (button) {
            button.textContent = `Kolum (${visibleColumns}/${totalColumns})`;
        }
    }

    function setLaporanAColumnMenuState(state) {
        document.querySelectorAll('#laporanAColumnMenu input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.checked = state[checkbox.value] !== false;
        });
    }

    function applyLaporanAColumnVisibility() {
        const state = getLaporanAColumnState();

        Object.entries(laporanAColumnGroups).forEach(function ([groupKey, columns]) {
            const visibleColumns = columns.filter(function (columnKey) {
                return state[columnKey] !== false;
            });

            document.querySelectorAll(`[data-laporan-a-group="${groupKey}"]`).forEach(function (cell) {
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
            document.querySelectorAll(`[data-laporan-a-col="${columnKey}"]`).forEach(function (cell) {
                cell.style.display = visible ? '' : 'none';
            });
        });

        setLaporanAColumnMenuState(state);
        updateLaporanAColumnButton(state);
    }

    function toggleLaporanAColumn(columnKey) {
        const state = getLaporanAColumnState();
        state[columnKey] = !state[columnKey];
        saveLaporanAColumnState(state);
        applyLaporanAColumnVisibility();
    }

    function toggleLaporanAColumnMenu() {
        const menu = document.getElementById('laporanAColumnMenu');
        if (!menu) return;
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', function (event) {
        const chooser = document.getElementById('laporanAColumnChooser');
        const menu = document.getElementById('laporanAColumnMenu');
        if (!chooser || !menu || menu.style.display !== 'block') return;
        if (chooser.contains(event.target)) return;
        menu.style.display = 'none';
    });

    let laporanYearStart = 2022;

    function initLaporanYearPicker() {
        const selectedYear = parseInt(document.getElementById('laporanYear')?.value || '', 10);
        const base = Number.isFinite(selectedYear) ? selectedYear : new Date().getFullYear();
        laporanYearStart = Math.floor(base / 9) * 9;
        renderLaporanYearGrid();
    }

    function toggleLaporanYearPicker() {
        const pop = document.getElementById('laporanYearPopover');
        const wrap = document.getElementById('laporanYearPickerWrap');
        const opening = pop.style.display !== 'block';

        if (!opening) {
            pop.style.display = 'none';
            return;
        }

        renderLaporanYearGrid();
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

    function shiftLaporanYearRange(step) {
        laporanYearStart += step;
        renderLaporanYearGrid();
    }

    function renderLaporanYearGrid() {
        const years = Array.from({ length: 9 }, (_, i) => laporanYearStart + i);
        document.getElementById('laporanYearRangeLabel').textContent = `${years[0]} - ${years[years.length - 1]}`;
        const active = document.getElementById('laporanYear')?.value || '';
        document.getElementById('laporanYearGrid').innerHTML = years.map(y =>
            `<button type="button" class="year-cell ${String(y) === active ? 'active' : ''}" onclick="selectLaporanYear(${y})">${y}</button>`
        ).join('');
    }

    function selectLaporanYear(y) {
        document.getElementById('laporanYear').value = y;
        document.getElementById('laporanYearBtnText').textContent = y;
        document.getElementById('laporanYearPopover').style.display = 'none';
        reloadLaporanA(1);
    }

    function selectCurrentLaporanYear() {
        selectLaporanYear(new Date().getFullYear());
    }

    function clearLaporanYearFilter() {
        document.getElementById('laporanYear').value = '';
        document.getElementById('laporanYearBtnText').textContent = 'Semua';
        document.getElementById('laporanYearPopover').style.display = 'none';
        reloadLaporanA(1);
    }

    function resetLaporanAFilter() {
        const params = new URLSearchParams(window.location.search);
        params.delete('tahun');
        params.delete('page');
        window.location.href = `{{ route('laporan.a') }}?${params.toString()}`;
    }

    function reloadLaporanA(page = 1) {
        const params = new URLSearchParams(window.location.search);
        const perPageEl = document.getElementById('perPageLaporanA');
        const yearEl = document.getElementById('laporanYear');

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

        window.location.href = `{{ route('laporan.a') }}?${params.toString()}`;
    }

    document.addEventListener('click', function (event) {
        const pop = document.getElementById('laporanYearPopover');
        const wrap = document.getElementById('laporanYearPickerWrap');
        if (!pop || pop.style.display !== 'block') return;
        if (pop.contains(event.target) || wrap.contains(event.target)) return;
        pop.style.display = 'none';
    });

    initLaporanYearPicker();
    applyLaporanAColumnVisibility();

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
