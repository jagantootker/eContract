@extends('components.layouts.app')

@section('content')
<div class="page-wrapper">
    @if(session('success'))
        <div class="mb-6 p-4 border border-emerald-200 bg-emerald-50 text-emerald-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="page-header">
        <div>
            <p class="eyebrow">Langkah 2: Semakan</p>
            <h1>Semakan Dokumen</h1>
            <p class="text-slate-500">Ref: <strong>{{ $upload->reference_no }}</strong></p>
        </div>
        <div class="status-chip">Boleh Disunting</div>
    </div>

    <div class="max-w-6xl mx-auto">
        {{-- Status Section --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-tile">
                <p class="text-xs text-slate-500 font-medium">TEMPLATE</p>
                <p class="text-sm font-semibold text-slate-900 mt-1">{{ $upload->template_type_label }}</p>
            </div>
            <div class="stat-tile">
                <p class="text-xs text-slate-500 font-medium">JENIS FAIL</p>
                <p class="text-sm font-semibold text-slate-900 mt-1">{{ $upload->file_type }}</p>
            </div>
            <div class="stat-tile">
                <p class="text-xs text-slate-500 font-medium">JUMLAH REKOD</p>
                <p class="text-sm font-semibold text-slate-900 mt-1">{{ $upload->total_records }}</p>
            </div>
            <div class="stat-tile">
                <p class="text-xs text-slate-500 font-medium">STATUS</p>
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $upload->status_color }} mt-1">
                    {{ $upload->status_label }}
                </span>
            </div>
        </div>

        {{-- Workflow Progress --}}
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Kemajuan Proses</h3>
            </div>
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-8">
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mb-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-xs font-medium">Dimuat Naik</p>
                        </div>
                        <div class="h-1 flex-1 bg-green-500"></div>
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mb-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-xs font-medium">Diekstrak</p>
                        </div>
                        <div class="h-1 flex-1 {{ $upload->status !== 'DRAFT' ? 'bg-green-500' : 'bg-slate-300' }}"></div>
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full {{ $upload->status !== 'DRAFT' ? 'bg-green-100' : 'bg-slate-200' }} flex items-center justify-center mb-2">
                                <svg class="w-5 h-5 {{ $upload->status !== 'DRAFT' ? 'text-green-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-xs font-medium">Disemak</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- File Information --}}
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Maklumat Fail</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Nama Fail Asal</p>
                        <p class="text-sm font-semibold text-slate-900 mt-1">{{ $upload->original_filename }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Dimuat Naik Oleh</p>
                        <p class="text-sm font-semibold text-slate-900 mt-1">{{ $upload->uploaded_by_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Tarikh Muat Naik</p>
                        <p class="text-sm font-semibold text-slate-900 mt-1">{{ $upload->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Catatan</p>
                        <p class="text-sm text-slate-900 mt-1">{{ $upload->notes ?? '- Tiada -' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Extracted Data Preview --}}
        <div class="card mb-6">
            <div class="card-header flex items-center justify-between gap-3 flex-wrap">
                <h3 class="text-lg font-semibold">Pratonton Data Diekstrak</h3>
                <div class="flex items-center gap-3">
                    <span class="record-chip" id="recordCounter">{{ $upload->total_records }} rekod</span>
                    <button type="button" class="btn btn-secondary" id="btnAddRow">+ Tambah Baris</button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($upload->extraction_data && count($upload->extraction_data) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full" id="editableExtractionTable">
                            <thead class="bg-slate-50 border-b">
                                <tr>
                                    @php
                                        $firstRecord = $upload->extraction_data[0];
                                        $keys = array_keys((array)$firstRecord);
                                    @endphp
                                    @foreach($keys as $key)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">
                                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                                        </th>
                                    @endforeach
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach(array_slice($upload->extraction_data, 0, 100) as $row)
                                    <tr class="hover:bg-slate-50">
                                        @foreach($keys as $key)
                                            <td class="px-6 py-4 text-sm text-slate-900">
                                                <input
                                                    type="text"
                                                    class="table-input"
                                                    data-key="{{ $key }}"
                                                    value="{{ $row[$key] ?? '' }}"
                                                >
                                            </td>
                                        @endforeach
                                        <td class="px-6 py-4 text-sm text-slate-900">
                                            <button type="button" class="btn btn-danger btn-remove-row">Buang</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($upload->total_records > 100)
                        <div class="px-6 py-4 border-t text-center text-sm text-slate-500">
                            Menunjukkan 100 daripada {{ $upload->total_records }} rekod
                        </div>
                    @endif
                @else
                    <div class="px-6 py-12 text-center text-slate-500">
                        <p>Tiada data untuk dipaparkan</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Workflow History --}}
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Sejarah Proses</h3>
            </div>
            <div class="card-body p-0">
                @if($workflowHistory->count() > 0)
                    <div class="space-y-0">
                        @foreach($workflowHistory as $history)
                            <div class="px-6 py-4 border-b last:border-b-0 flex gap-4">
                                <div class="flex-shrink-0 pt-1">
                                    @if($history->action === 'UPLOADED')
                                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                    @elseif($history->action === 'EXTRACTED')
                                        <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                                    @elseif($history->action === 'APPROVED')
                                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    @elseif($history->action === 'REJECTED')
                                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    @else
                                        <div class="w-3 h-3 rounded-full bg-slate-400"></div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-slate-900">{{ $history->action_label }}</p>
                                        <p class="text-xs text-slate-500">{{ $history->created_at->format('H:i d M Y') }}</p>
                                    </div>
                                    <p class="text-sm text-slate-600 mt-1">{{ $history->description }}</p>
                                    @if($history->performed_by_name)
                                        <p class="text-xs text-slate-500 mt-1">Oleh: {{ $history->performed_by_name }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-slate-500">
                        <p>Tiada sejarah proses</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="action-bar">
            @if($upload->status === 'DRAFT')
                <form method="POST" action="{{ url('/perancangan-perolehan/' . $upload->id . '/submit') }}" class="inline" id="submitForVerificationForm">
                    @csrf
                    <input type="hidden" name="extraction_data" id="submitExtractionData">
                    <button type="submit" class="btn btn-primary" id="btnSubmitVerification">
                        Serahkan untuk Pengesahan
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" onclick="showEditNotesModal()">
                    Sunting Catatan
                </button>
            @endif

            @if($upload->status === 'SEDANG_DISEMAK')
                <form method="POST" action="{{ url('/perancangan-perolehan/' . $upload->id . '/approve') }}" class="inline" id="approveForm">
                    @csrf
                    <button type="submit" class="btn btn-success" id="btnApprove">
                        Luluskan
                    </button>
                </form>
                <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                    Tolak
                </button>
            @endif

            @if($upload->status === 'DITOLAK')
                <form method="POST" action="{{ url('/perancangan-perolehan/' . $upload->id . '/submit') }}" class="inline" id="resubmitForm">
                    @csrf
                    <input type="hidden" name="extraction_data" id="resubmitExtractionData">
                    <button type="submit" class="btn btn-primary" id="btnResubmit">
                        Serahkan Semula
                    </button>
                </form>
            @endif

            <a href="{{ route('abm.repository') }}" class="btn btn-secondary">
                Kembali ke Repositori
            </a>
        </div>

        @if($upload->rejection_reason)
            <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm font-medium text-red-900 mb-1">Alasan Penolakan:</p>
                <p class="text-sm text-red-700">{{ $upload->rejection_reason }}</p>
            </div>
        @endif
    </div>
</div>

{{-- Edit Notes Modal --}}
<div id="editNotesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Sunting Catatan</h2>
        </div>
        <form method="POST" action="{{ url('/perancangan-perolehan/' . $upload->id . '/draft') }}" id="saveDraftForm">
            @csrf
            <div class="p-6">
                <textarea name="notes" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4" placeholder="Tambah catatan atau keterangan...">{{ $upload->notes }}</textarea>
                <input type="hidden" name="extraction_data" id="draftExtractionData">
            </div>
            <div class="p-6 border-t flex gap-3">
                <button type="submit" class="btn btn-primary flex-1">Simpan</button>
                <button type="button" class="btn btn-secondary flex-1" onclick="document.getElementById('editNotesModal').classList.add('hidden')">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Tolak Dokumen</h2>
        </div>
        <form method="POST" action="{{ url('/perancangan-perolehan/' . $upload->id . '/reject') }}" id="rejectForm">
            @csrf
            <div class="p-6">
                <label class="block text-sm font-medium text-slate-900 mb-2">Alasan Penolakan *</label>
                <textarea name="reason" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" rows="4" placeholder="Berikan alasan untuk penolakan..." required></textarea>
            </div>
            <div class="p-6 border-t flex gap-3">
                <button type="submit" class="btn btn-danger flex-1" id="btnRejectSubmit">Tolak</button>
                <button type="button" class="btn btn-secondary flex-1" onclick="document.getElementById('rejectModal').classList.add('hidden')">Batal</button>
            </div>
        </form>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .page-wrapper {
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 2rem;
        background:
            radial-gradient(1100px 520px at -8% -20%, rgba(14, 165, 233, 0.18), transparent 60%),
            radial-gradient(920px 480px at 110% 8%, rgba(34, 197, 94, 0.13), transparent 60%),
            linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
        min-height: calc(100vh - 120px);
    }
    
    .page-header {
        margin-bottom: 2rem;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
    }

    .eyebrow {
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #0284c7;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    
    .page-header h1 {
        font-size: 1.875rem;
        font-weight: 800;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .status-chip {
        font-size: 0.78rem;
        font-weight: 700;
        color: #0c4a6e;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        border-radius: 999px;
        padding: 0.45rem 0.72rem;
    }

    .stat-tile {
        padding: 1rem;
        border: 1px solid #dbeafe;
        border-radius: 0.85rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }
    
    .card {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 14px 34px rgba(2, 6, 23, 0.07);
        backdrop-filter: blur(6px);
        overflow: hidden;
    }
    
    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .record-chip {
        font-size: 0.78rem;
        font-weight: 700;
        color: #334155;
        background: #e2e8f0;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 0.35rem 0.7rem;
    }

    .table-input {
        width: 100%;
        min-width: 120px;
        padding: 0.5rem 0.65rem;
        border-radius: 0.55rem;
        border: 1px solid #cbd5e1;
        outline: none;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .table-input:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.16);
    }

    .action-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 1rem;
        border: 1px solid #dbeafe;
        border-radius: 0.85rem;
        background: rgba(255, 255, 255, 0.72);
        position: sticky;
        bottom: 0.8rem;
        backdrop-filter: blur(6px);
    }

    @media (max-width: 768px) {
        .page-wrapper {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-header,
        .card-body {
            padding: 1rem;
        }

        .action-bar {
            position: static;
            padding: 0.8rem;
        }
    }

    /* Compact modern overrides */
    .page-wrapper svg { width: 1rem !important; height: 1rem !important; }
    .card { box-shadow: 0 8px 18px rgba(15,23,42,0.05) !important; border-radius: 0.85rem !important; }
    .card-header, .card-body { padding: 1rem !important; }
    .table-input { padding: 0.45rem 0.6rem !important; border-radius: 0.45rem !important; }
    .page-header h1 { font-weight: 700 !important; }
    .record-chip { padding: 0.3rem 0.5rem !important; }
</style>

<script>
    const extractionKeys = @json(isset($keys) ? $keys : []);

    function showEditNotesModal() {
        document.getElementById('editNotesModal').classList.remove('hidden');
    }
    
    function showRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function collectExtractionData() {
        const table = document.getElementById('editableExtractionTable');
        if (!table) return '[]';

        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const data = rows.map((row) => {
            const obj = {};
            const inputs = row.querySelectorAll('input[data-key]');
            inputs.forEach((input) => {
                obj[input.dataset.key] = input.value;
            });
            return obj;
        });

        return JSON.stringify(data);
    }

    function updateRecordCounter() {
        const table = document.getElementById('editableExtractionTable');
        const counter = document.getElementById('recordCounter');
        if (!table || !counter) return;
        const total = table.querySelectorAll('tbody tr').length;
        counter.textContent = `${total} rekod`;
    }

    function bindRemoveRowButtons() {
        document.querySelectorAll('.btn-remove-row').forEach((btn) => {
            btn.onclick = async function () {
                const row = this.closest('tr');
                if (!row) return;

                const ok = await showConfirmDialog({
                    title: 'Buang baris ini?',
                    text: 'Baris data ini akan dipadam dari pratonton.',
                    icon: 'warning',
                    confirmText: 'Ya, Buang',
                    cancelText: 'Batal'
                });

                if (!ok) return;
                row.remove();
                updateRecordCounter();
            };
        });
    }

    function addNewExtractionRow() {
        const table = document.getElementById('editableExtractionTable');
        if (!table || !Array.isArray(extractionKeys) || extractionKeys.length === 0) {
            showToast('Tiada struktur kolum untuk tambah baris baharu.', 'warning', 'Tidak Boleh Tambah');
            return;
        }

        const tbody = table.querySelector('tbody');
        const row = document.createElement('tr');
        row.className = 'hover:bg-slate-50';

        extractionKeys.forEach((key) => {
            const td = document.createElement('td');
            td.className = 'px-6 py-4 text-sm text-slate-900';
            td.innerHTML = `<input type="text" class="table-input" data-key="${key}" value="">`;
            row.appendChild(td);
        });

        const actionTd = document.createElement('td');
        actionTd.className = 'px-6 py-4 text-sm text-slate-900';
        actionTd.innerHTML = '<button type="button" class="btn btn-danger btn-remove-row">Buang</button>';
        row.appendChild(actionTd);

        tbody.appendChild(row);
        bindRemoveRowButtons();
        updateRecordCounter();
    }

    const submitForm = document.getElementById('submitForVerificationForm');
    if (submitForm) {
        submitForm.addEventListener('submit', () => {
            const hidden = document.getElementById('submitExtractionData');
            if (hidden) hidden.value = collectExtractionData();
        });
    }

    const resubmitForm = document.getElementById('resubmitForm');
    if (resubmitForm) {
        resubmitForm.addEventListener('submit', () => {
            const hidden = document.getElementById('resubmitExtractionData');
            if (hidden) hidden.value = collectExtractionData();
        });
    }

    const draftForm = document.getElementById('saveDraftForm');
    if (draftForm) {
        draftForm.addEventListener('submit', () => {
            const hidden = document.getElementById('draftExtractionData');
            if (hidden) hidden.value = collectExtractionData();
        });
    }

    const addRowButton = document.getElementById('btnAddRow');
    if (addRowButton) {
        addRowButton.addEventListener('click', addNewExtractionRow);
    }

    bindRemoveRowButtons();
    updateRecordCounter();

    function bindSweetConfirm(formId, title, text, confirmText, beforeSubmit) {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (beforeSubmit) beforeSubmit();

            const ok = await showConfirmDialog({
                title,
                text,
                icon: 'question',
                confirmText,
                cancelText: 'Batal'
            });

            if (ok) {
                form.submit();
            }
        });
    }

    bindSweetConfirm(
        'submitForVerificationForm',
        'Serahkan untuk pengesahan?',
        'Pastikan data yang diedit adalah betul sebelum dihantar.',
        'Ya, Serahkan',
        () => {
            const hidden = document.getElementById('submitExtractionData');
            if (hidden) hidden.value = collectExtractionData();
        }
    );

    bindSweetConfirm(
        'resubmitForm',
        'Serahkan semula dokumen?',
        'Dokumen akan masuk semula ke proses semakan.',
        'Ya, Serahkan Semula',
        () => {
            const hidden = document.getElementById('resubmitExtractionData');
            if (hidden) hidden.value = collectExtractionData();
        }
    );

    bindSweetConfirm(
        'approveForm',
        'Luluskan dokumen ini?',
        'Status dokumen akan ditukar kepada Diluluskan.',
        'Ya, Luluskan'
    );

    bindSweetConfirm(
        'rejectForm',
        'Sahkan penolakan?',
        'Dokumen akan ditanda sebagai Ditolak.',
        'Ya, Tolak'
    );
</script>
@endsection
