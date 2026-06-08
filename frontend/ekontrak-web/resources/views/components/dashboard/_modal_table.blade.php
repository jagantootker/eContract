{{-- Export + Filter bar --}}
<div class="export-bar db-modal-bar">
    <div class="db-modal-left-actions">
    <button class="btn btn-outline btn-sm" onclick="exportModalData('{{ $modalId }}', 'print')">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Print
    </button>
    <button class="btn btn-outline btn-sm" style="color:#ef4444;border-color:#fecaca;background:#fff5f5;" onclick="exportModalData('{{ $modalId }}', 'pdf')">
        PDF
    </button>
    <button class="btn btn-outline btn-sm" style="color:#059669;border-color:#a7f3d0;background:#ecfdf5;" onclick="exportModalData('{{ $modalId }}', 'excel')">
        Excel
    </button>
    </div>
    <div class="db-modal-right-actions">
        <div class="db-year-picker" id="year-picker-{{ $modalId }}">
            <button type="button" class="db-year-btn" id="year-btn-{{ $modalId }}" onclick="toggleYearPicker('{{ $modalId }}')">Semua</button>
            <div class="db-year-panel" id="year-panel-{{ $modalId }}" style="display:none;">
                <div class="db-year-nav">
                    <button type="button" class="db-year-nav-btn" onclick="shiftYearWindow('{{ $modalId }}', -9)">&lsaquo;&lsaquo;</button>
                    <span id="year-range-{{ $modalId }}">-</span>
                    <button type="button" class="db-year-nav-btn" onclick="shiftYearWindow('{{ $modalId }}', 9)">&rsaquo;&rsaquo;</button>
                </div>
                <div class="db-year-grid" id="year-grid-{{ $modalId }}"></div>
                <div class="db-year-foot">
                    <button type="button" class="db-year-clear" onclick="setYearFilter('{{ $modalId }}', '')">Tahun Semua</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="db-modal-filter-row">
    <div class="search-wrap" style="flex:1;max-width:260px;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
        <input id="search-{{ $modalId }}" type="text" class="search-input" placeholder="Cari tajuk, no kontrak..."
            oninput="debounceModal('{{ $modalId }}', this.value)">
    </div>
</div>

{{-- Table --}}
<x-table
    :headers="['#', 'Tajuk Kontrak', 'No. Kontrak', 'Pemilik Projek', 'Tempoh Kontrak']"
    wrap-class="table-scroll"
    table-class="table align-middle pivot-table mb-0"
    table-style="min-width:760px;"
    tbody-id="modal-table-{{ $modalId }}"
>
    <tr><td colspan="5" style="text-align:center;padding:2rem;color:#9ca3af;">Klik kad untuk muatkan data.</td></tr>
</x-table>

{{-- Meta + Pagination --}}
<div class="db-modal-footer-row">
    <div style="display:flex;align-items:center;gap:0.4rem;font-size:0.8rem;color:#6b7280;">
        <span>Papar</span>
        <select id="per-page-{{ $modalId }}" class="per-page-select" style="min-width:64px;padding:0.28rem 0.5rem;" onchange="fetchModalData('{{ $modalId }}', 1)">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
        </select>
        <span>entri</span>
    </div>
    <div id="modal-meta-{{ $modalId }}" style="font-size:0.8rem;color:#6b7280;text-align:center;flex:1;"></div>
    <div id="modal-pagin-{{ $modalId }}" class="pagination"></div>
</div>

<script>
    // Debounce for modal search
    let modalSearchTimers = {};
    function debounceModal(type, search) {
        clearTimeout(modalSearchTimers[type]);
        modalSearchTimers[type] = setTimeout(() => {
            const tahun = (window.yearPickerState && window.yearPickerState[type]) ? (window.yearPickerState[type].selected || '') : '';
            fetchModalData(type, 1, tahun, search);
        }, 300);
    }
</script>
