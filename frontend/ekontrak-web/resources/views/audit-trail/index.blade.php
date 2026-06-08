@extends('components.layouts.app')

@section('title', 'Jejak Audit')
@section('breadcrumb', 'Jejak Audit')

@push('styles')
<style>
    /* ── Audit Trail specific ─────────────────────────────────────────────── */
    .audit-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .audit-stat-card {
        background: white;
        border: 1px solid var(--slate-200);
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.875rem;
        box-shadow: 0 2px 6px rgba(15,23,42,0.05);
    }
    .audit-stat-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .audit-stat-icon svg { width: 18px; height: 18px; }
    .audit-stat-icon.purple { background: #f5f3ff; color: #7e22ce; }
    .audit-stat-icon.blue   { background: #eff6ff; color: #1d4ed8; }
    .audit-stat-icon.amber  { background: #fffbeb; color: #b45309; }
    .audit-stat-icon.green  { background: #ecfdf5; color: #059669; }
    .audit-stat-value { font-size: 1.5rem; font-weight: 800; color: var(--slate-900); line-height: 1; }
    .audit-stat-label { font-size: 0.75rem; font-weight: 600; color: var(--slate-500); margin-top: 0.2rem; }

    /* Timeline-style row badge */
    .action-badge {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.22rem 0.65rem;
        border-radius: 999px;
        font-size: 0.68rem; font-weight: 800;
        letter-spacing: 0.03em;
        white-space: nowrap;
        border: 1px solid transparent;
    }
    .action-badge svg { width: 11px; height: 11px; }
    .action-badge.create  { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
    .action-badge.update  { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
    .action-badge.delete  { background: #fee2e2; color: #b91c1c; border-color: #fecdd3; }
    .action-badge.login   { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .action-badge.logout  { background: #fef9c3; color: #854d0e; border-color: #fef08a; }
    .action-badge.approve { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
    .action-badge.reject  { background: #fff1f2; color: #9f1239; border-color: #fecdd3; }
    .action-badge.default { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

    .model-chip {
        display: inline-flex; align-items: center; gap: 0.25rem;
        padding: 0.2rem 0.55rem;
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.7rem; font-weight: 700; color: #475569;
        font-family: 'Inter', monospace;
    }

    /* Payload expand/collapse */
    .payload-toggle {
        display: inline-flex; align-items: center; gap: 0.25rem;
        padding: 0.18rem 0.5rem;
        border: 1px solid var(--slate-200); border-radius: 6px;
        background: white; color: var(--slate-500);
        font-size: 0.72rem; font-weight: 600;
        cursor: pointer; transition: all 0.15s;
    }
    .payload-toggle:hover { border-color: var(--blue); color: var(--blue); background: #eff6ff; }
    .payload-toggle svg { width: 12px; height: 12px; transition: transform 0.2s; }
    .payload-toggle.open svg { transform: rotate(180deg); }

    .payload-box {
        display: none;
        margin-top: 0.5rem;
        background: #1e293b;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.7rem;
        color: #94a3b8;
        line-height: 1.6;
        max-height: 220px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }
    .payload-box.open { display: block; }

    /* Filter panel */
    .audit-filters {
        background: #f8fafc;
        border: 1px solid var(--slate-200);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: flex-end;
        margin-bottom: 1.25rem;
    }
    .filter-group { display: flex; flex-direction: column; gap: 0.3rem; min-width: 130px; }
    .filter-group label { font-size: 0.7rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.04em; }

    /* Loading skeleton */
    .skeleton-row { display: flex; gap: 0.75rem; padding: 0.94rem 1rem; border-bottom: 1px solid #e2e8f0; }
    .skeleton-cell { height: 14px; border-radius: 6px; background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
    @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

    table { min-width: 860px; }
    td.td-user { min-width: 160px; }
    td.td-action { min-width: 130px; }
    td.td-model { min-width: 150px; }
    td.td-ip { min-width: 130px; white-space: nowrap; font-family: 'Courier New', monospace; font-size: 0.78rem; }
    td.td-time { min-width: 160px; white-space: nowrap; }
    td.td-payload { min-width: 140px; }
</style>
@endpush

@section('content')

<div class="card">
    {{-- Header --}}
    <div class="card-header">
        <div>
            <div class="page-title">Jejak Audit</div>
            <div class="page-subtitle">Rekod aktiviti dan log sistem untuk Admin. Data dikemas kini secara masa nyata.</div>
        </div>
        <div style="display:flex;gap:0.5rem;align-items:center;">
            <button class="btn btn-outline" id="btnRefresh" onclick="refreshTable()">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Muat Semula
            </button>
        </div>
    </div>

    <div class="card-body">

        {{-- ── Stats Row ── --}}
        <div class="audit-stats-grid" id="statsGrid">
            <div class="audit-stat-card">
                <div class="audit-stat-icon purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <div class="audit-stat-value" id="statTotal">—</div>
                    <div class="audit-stat-label">Jumlah Log</div>
                </div>
            </div>
            <div class="audit-stat-card">
                <div class="audit-stat-icon green">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div>
                    <div class="audit-stat-value" id="statCreates">—</div>
                    <div class="audit-stat-label">Rekod Baharu</div>
                </div>
            </div>
            <div class="audit-stat-card">
                <div class="audit-stat-icon blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <div class="audit-stat-value" id="statUpdates">—</div>
                    <div class="audit-stat-label">Kemaskini</div>
                </div>
            </div>
            <div class="audit-stat-card">
                <div class="audit-stat-icon amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </div>
                <div>
                    <div class="audit-stat-value" id="statLogins">—</div>
                    <div class="audit-stat-label">Log Masuk</div>
                </div>
            </div>
        </div>

        {{-- ── Filters ── --}}
        <div class="audit-filters" id="auditFilters">
            <div id="auditFilterValidationSummary" class="ds-validation-summary" style="display:none; width:100%; margin-bottom:0.25rem;">
                <div class="ds-validation-summary-title">Sila lengkapkan maklumat yang diperlukan.</div>
                <ul class="ds-validation-summary-list"></ul>
            </div>
            <div class="filter-group" style="flex:1;min-width:200px;">
                <label>Cari</label>
                <div class="search-wrap" style="max-width:100%;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input type="text" class="search-input" id="searchInput" placeholder="Cari nama pengguna atau tindakan..." value="{{ $filters['search'] }}">
                </div>
            </div>

            <div class="filter-group">
                <label>Tindakan</label>
                <select class="filter-select" id="filterAction">
                    <option value="">Semua Tindakan</option>
                    <option value="__LOGIN__" {{ $filters['action'] === '__LOGIN__' ? 'selected' : '' }}>Login</option>
                    <option value="__LOGOUT__" {{ $filters['action'] === '__LOGOUT__' ? 'selected' : '' }}>Logout</option>
                    @foreach($actions as $action)
                        @continue(in_array(strtolower((string) $action), ['login', 'logout', '__login__', '__logout__'], true))
                        <option value="{{ $action }}" {{ $filters['action'] === $action ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Jenis Model</label>
                <select class="filter-select" id="filterModel">
                    <option value="">Semua Model</option>
                    <option value="Kontrak"  {{ $filters['model_type'] === 'Kontrak'  ? 'selected' : '' }}>Kontrak</option>
                    <option value="Syarikat" {{ $filters['model_type'] === 'Syarikat' ? 'selected' : '' }}>Syarikat</option>
                    <option value="User"     {{ $filters['model_type'] === 'User'     ? 'selected' : '' }}>Pengguna</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Dari Tarikh</label>
                <input type="date" class="filter-input" id="filterDateFrom" value="{{ $filters['date_from'] }}">
            </div>

            <div class="filter-group">
                <label>Hingga Tarikh</label>
                <input type="date" class="filter-input" id="filterDateTo" value="{{ $filters['date_to'] }}">
            </div>

            <div class="filter-group" style="flex-direction:row;align-items:flex-end;gap:0.5rem;min-width:auto;">
                <button class="btn btn-primary btn-sm" onclick="applyFilters()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 2v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Tapis
                </button>
                <button class="btn btn-outline btn-sm" onclick="resetFilters()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </button>
            </div>
        </div>

        {{-- ── Table ── --}}
        <div id="auditTableWrap" class="table-wrap">
            @include('audit-trail._table', ['logs' => $logs])
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentPage = 1;
    let currentPerPage = 15;
    let isLoading = false;

    // ── Initial stats load ────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        loadStats();
        // Auto-search on type
        let debounce;
        document.getElementById('searchInput').addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(applyFilters, 400);
        });
    });

    // ── Compute quick stats from current page data ────────────────────────────
    function loadStats() {
        fetch('/audit-trail/fetch?per_page=200', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const rows = res.data?.data ?? [];
            const total = res.data?.total ?? rows.length;
            let creates = 0, updates = 0, logins = 0;
            rows.forEach(r => {
                const a = (r.action || '').toLowerCase();
                const m = String(r.model_type || '').toLowerCase();
                const isLogin = a.includes('login') || a.includes('log masuk') || m.includes('login') || m.includes('log_masuk');
                const isLogout = a.includes('logout') || m.includes('logout') || m.includes('log_keluar');

                if (!isLogin && !isLogout && (a.includes('creat') || a.includes('store') || a.includes('tambah'))) creates++;
                else if (!isLogin && !isLogout && (a.includes('updat') || a.includes('kemaskini') || a.includes('edit'))) updates++;
                if (isLogin) logins++;
            });
            document.getElementById('statTotal').textContent   = total.toLocaleString();
            document.getElementById('statCreates').textContent = creates.toLocaleString();
            document.getElementById('statUpdates').textContent = updates.toLocaleString();
            document.getElementById('statLogins').textContent  = logins.toLocaleString();
        })
        .catch(() => {});
    }

    // ── Filters ───────────────────────────────────────────────────────────────
    function buildParams(page = 1) {
        const params = new URLSearchParams();
        const search    = document.getElementById('searchInput').value.trim();
        const action    = document.getElementById('filterAction').value;
        const model     = document.getElementById('filterModel').value;
        const dateFrom  = document.getElementById('filterDateFrom').value;
        const dateTo    = document.getElementById('filterDateTo').value;

        if (search)   params.set('search',     search);
        if (action)   params.set('action',     action);
        if (model)    params.set('model_type', model);
        if (dateFrom) params.set('date_from',  dateFrom);
        if (dateTo)   params.set('date_to',    dateTo);

        params.set('page',     page);
        params.set('per_page', currentPerPage);
        return params;
    }

    function applyFilters() {
        const validation = ValidationService.validate(document.getElementById('auditFilters'), {
            date_from: [
                { type: 'dateOrder', startField: 'filterDateFrom', endField: 'filterDateTo', allowEqual: true, message: 'Tarikh hingga mesti sama atau selepas tarikh dari.', label: 'Tarikh hingga mesti sama atau selepas tarikh dari.' },
            ],
        }, 'auditFilterValidationSummary', { showToast: true, scrollToError: true });

        if (!validation.valid) return;

        currentPage = 1;
        fetchTable(1);
    }

    function resetFilters() {
        document.getElementById('searchInput').value  = '';
        document.getElementById('filterAction').value = '';
        document.getElementById('filterModel').value  = '';
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value   = '';
        ValidationService.clearErrors(document.getElementById('auditFilters'));
        currentPage = 1;
        fetchTable(1);
    }

    function refreshTable() {
        loadStats();
        fetchTable(currentPage);
    }

    function goToPage(page) { currentPage = page; fetchTable(page); }
    function changePerPage(val) { currentPerPage = parseInt(val); currentPage = 1; fetchTable(1); }

    // ── AJAX fetch ────────────────────────────────────────────────────────────
    function fetchTable(page = 1) {
        if (isLoading) return;
        isLoading = true;

        const wrap = document.getElementById('auditTableWrap');
        wrap.style.opacity = '0.5';
        wrap.style.pointerEvents = 'none';

        const btn = document.getElementById('btnRefresh');
        btn.disabled = true;
        btn.querySelector('svg').style.animation = 'spin 0.8s linear infinite';

        const params = buildParams(page);

        fetch('/audit-trail/fetch?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                renderTable(res.data);
            } else {
                showError(res.message ?? 'Gagal memuatkan data.', 'Ralat');
            }
        })
        .catch(() => showError('Gagal disambungkan ke pelayan.', 'Ralat'))
        .finally(() => {
            wrap.style.opacity = '';
            wrap.style.pointerEvents = '';
            btn.disabled = false;
            btn.querySelector('svg').style.animation = '';
            isLoading = false;
        });
    }

    // ── Render table HTML ─────────────────────────────────────────────────────
    function renderTable(data) {
        const rows  = data.data ?? [];
        const total = data.total ?? 0;
        const from  = data.from  ?? 0;
        const to    = data.to    ?? 0;
        const lastPage = data.last_page ?? 1;
        const currPage = data.current_page ?? 1;

        let html = `
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tarikh &amp; Masa</th>
                    <th>Pengguna</th>
                    <th>Tindakan</th>
                    <th>Model / Entiti</th>
                    <th>Alamat IP</th>
                    <th>Muatan Data</th>
                </tr>
            </thead>
            <tbody>`;

        if (rows.length === 0) {
            html += `<tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--slate-400);">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:block;margin:0 auto 0.75rem;opacity:0.35;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Tiada rekod ditemui
            </td></tr>`;
        } else {
            rows.forEach((row, idx) => {
                const rowNum = from + idx;
                const user   = row.user ? row.user.name : '<span style="color:#94a3b8;font-style:italic;">Sistem</span>';
                const ic     = row.user?.ic_number ? `<div style="font-size:0.7rem;color:#94a3b8;font-family:monospace;">${row.user.ic_number}</div>` : '';
                const dt     = formatDateTime(row.created_at);
                const badge  = actionBadge(row.action, row.model_type);
                const model  = row.model_type
                    ? `<span class="model-chip">${row.model_type}${row.model_id ? ' #' + row.model_id : ''}</span>`
                    : '<span style="color:#94a3b8;">—</span>';
                const ip     = row.ip_address || '—';
                const hasPayload = row.payload && Object.keys(row.payload).length > 0;
                const payloadHtml = hasPayload
                    ? `<button class="payload-toggle" onclick="togglePayload(this)" data-payload='${JSON.stringify(row.payload ?? {}).replace(/'/g,"&#39;")}'>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            Data
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                       </button>
                       <div class="payload-box"></div>`
                    : '<span style="color:#94a3b8;font-size:0.75rem;">—</span>';

                html += `
                <tr>
                    <td style="color:#94a3b8;font-size:0.75rem;font-weight:600;">${rowNum}</td>
                    <td class="td-time">
                        <div style="font-size:0.82rem;font-weight:600;color:var(--slate-700);">${dt.date}</div>
                        <div style="font-size:0.72rem;color:#94a3b8;">${dt.time}</div>
                    </td>
                    <td class="td-user">
                        <div style="font-weight:600;color:var(--slate-800);font-size:0.84rem;">${user}</div>
                        ${ic}
                    </td>
                    <td class="td-action">${badge}</td>
                    <td class="td-model">${model}</td>
                    <td class="td-ip">${ip}</td>
                    <td class="td-payload">${payloadHtml}</td>
                </tr>`;
            });
        }

        html += `</tbody></table>`;

        // Pagination
        html += buildPagination(currPage, lastPage, from, to, total);

        document.getElementById('auditTableWrap').innerHTML = html;
    }

    function buildPagination(curr, last, from, to, total) {
        if (total === 0) return '';

        let btns = `<button class="page-btn" onclick="goToPage(${curr - 1})" ${curr <= 1 ? 'disabled' : ''}>
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>`;

        const start = Math.max(1, curr - 2);
        const end   = Math.min(last, curr + 2);
        if (start > 1) btns += `<button class="page-btn" onclick="goToPage(1)">1</button>${start > 2 ? '<span style="padding:0 0.25rem;color:#94a3b8;">…</span>' : ''}`;
        for (let p = start; p <= end; p++) {
            btns += `<button class="page-btn ${p === curr ? 'active' : ''}" onclick="goToPage(${p})">${p}</button>`;
        }
        if (end < last) btns += `${end < last - 1 ? '<span style="padding:0 0.25rem;color:#94a3b8;">…</span>' : ''}<button class="page-btn" onclick="goToPage(${last})">${last}</button>`;

        btns += `<button class="page-btn" onclick="goToPage(${curr + 1})" ${curr >= last ? 'disabled' : ''}>
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>`;

        const perPageSelect = `<select class="per-page-select" onchange="changePerPage(this.value)">
            <option value="10"  ${currentPerPage===10?'selected':''}>10</option>
            <option value="15"  ${currentPerPage===15?'selected':''}>15</option>
            <option value="25"  ${currentPerPage===25?'selected':''}>25</option>
            <option value="50"  ${currentPerPage===50?'selected':''}>50</option>
        </select>`;

        return `<div class="pag-wrap">
            <span class="pag-info">Rekod ${from}–${to} daripada ${total.toLocaleString()}</span>
            <div style="display:flex;align-items:center;gap:1rem;">
                <div class="per-page-wrap">Baris: ${perPageSelect}</div>
                <div class="pag-btns">${btns}</div>
            </div>
        </div>`;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    function formatDateTime(iso) {
        if (!iso) return { date: '—', time: '—' };
        const d = new Date(iso);
        const pad = n => String(n).padStart(2, '0');
        return {
            date: `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()}`,
            time: `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`
        };
    }

    function actionBadge(action, modelType) {
        const a = (action || '').toLowerCase();
        const m = String(modelType || '').toLowerCase();
        let cls = 'default', icon = '', label = action || '—';

        if (a.includes('logout') || m.includes('logout') || m.includes('log_keluar')) {
            cls = 'logout';
            label = 'Logout';
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>';
        } else if (a.includes('login') || m.includes('login') || m.includes('log_masuk')) {
            cls = 'login';
            label = 'Login';
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>';
        } else if (a.includes('creat') || a.includes('store') || a.includes('tambah')) {
            cls = 'create';
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>';
        } else if (a.includes('updat') || a.includes('kemaskini') || a.includes('edit')) {
            cls = 'update';
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
        } else if (a.includes('delet') || a.includes('padam') || a.includes('destroy')) {
            cls = 'delete';
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
        } else if (a.includes('approv') || a.includes('lulus')) {
            cls = 'approve';
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        } else if (a.includes('reject') || a.includes('tolak')) {
            cls = 'reject';
            icon = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        }

        const fmt = label.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        return `<span class="action-badge ${cls}">${icon}${fmt}</span>`;
    }

    function togglePayload(btn) {
        const box = btn.nextElementSibling;
        const isOpen = btn.classList.contains('open');
        if (isOpen) {
            btn.classList.remove('open');
            box.classList.remove('open');
        } else {
            try {
                const raw = btn.getAttribute('data-payload');
                const parsed = JSON.parse(raw);
                box.textContent = JSON.stringify(parsed, null, 2);
            } catch (e) {
                box.textContent = btn.getAttribute('data-payload');
            }
            btn.classList.add('open');
            box.classList.add('open');
        }
    }

    // Spin animation for refresh button
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
    document.head.appendChild(style);
</script>
@endpush
