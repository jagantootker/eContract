@extends('components.layouts.app')

@section('title', 'Muat Naik ABM')
@section('breadcrumb', 'ABM')

@section('content')
<div class="page-shell">
    <div class="hero-strip">
        <div>
            <p class="eyebrow">ABM / Excel Import</p>
            <h1>Muat Naik Fail ABM</h1>
            <p class="hero-copy">Muat naik workbook ABM, sistem akan baca setiap sheet, asingkan Butiran dan Program/Aktiviti, kemudian bina ringkasan yang boleh dikembangkan.</p>
        </div>
        <a href="{{ route('abm.v3.dashboard') }}" class="btn btn-secondary hero-link">Kembali ke Dashboard</a>
    </div>

    <div class="form-shell mt-3">
        <div class="glass-panel upload-panel">
            <form id="uploadForm" class="space-y-10">
                @csrf
                <input type="hidden" name="template_type" value="ABM_TEMPLATE">
                <div class="dropzone" id="dropzone">
                    <input type="file" id="fileInput" name="file" accept=".xlsx,.xls" hidden required>
                    <div class="dropzone-inner">
                        <div class="drop-icon-wrap">
                            <div class="drop-icon">⇪</div>
                        </div>
                        <div class="drop-copy-wrap">
                            <h3>Seret fail Excel di sini atau klik untuk pilih</h3>
                            <p>ABM TEMPLATE sahaja disokong: .xlsx / .xls</p>
                        </div>
                    </div>
                </div>

                <div class="form-stack">
                    <div class="field-block">
                        <label class="field-label">Nama Fail</label>
                        <div class="file-pill" id="fileName">Tiada fail dipilih</div>
                    </div>

                    <div class="action-row">
                        <button type="submit" class="btn btn-primary" id="submitBtn">Muat Naik dan Ekstrak</button>
                    </div>
                </div>

                <div id="successBox" class="hidden rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800"></div>
                <div id="errorBox" class="hidden rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-800"></div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .page-shell {
        padding: 2rem;
        min-height: calc(100vh - 120px);
        background:
            radial-gradient(circle at top left, rgba(37,99,235,.16), transparent 28%),
            radial-gradient(circle at top right, rgba(14,165,233,.12), transparent 24%),
            linear-gradient(180deg, #f8fbff 0%, #f3f7fc 100%);
    }

    .hero-strip,
    .glass-panel {
        background: rgba(255,255,255,.94);
        border: 1px solid #dbe7f3;
        border-radius: 28px;
        box-shadow: 0 18px 45px rgba(15,23,42,.08);
        backdrop-filter: blur(10px);
    }

    .hero-strip {
        max-width: 920px;
        margin: 0 auto;
        padding: 1.1rem 1.2rem;
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:1rem;
    }

    .eyebrow {
        text-transform: uppercase;
        letter-spacing:.18em;
        font-size:.7rem;
        color:#2563eb;
        font-weight:800;
    }

    .hero-strip h1,
    .panel-head h2 {
        font-size: 1.9rem;
        line-height:1.05;
        color:#0f172a;
        font-weight:900;
        margin-top:.25rem;
    }

    .hero-copy { color:#475569; max-width: 58rem; margin-top:.35rem; }

    .form-shell {
        max-width: 920px;
        margin: 0 auto;
    }

    .glass-panel { padding: 1.15rem 1.2rem; }

    .panel-head {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
        margin-bottom:1rem;
    }

    .panel-kicker {
        text-transform: uppercase;
        letter-spacing:.14em;
        font-size:.68rem;
        color:#2563eb;
        font-weight:800;
        margin-bottom:.25rem;
    }

    .panel-head h2 {
        font-size:1.35rem;
        font-weight:900;
        color:#0f172a;
    }

    .hero-link {
        white-space:nowrap;
    }

    .dropzone {
        border: 1.5px dashed #93c5fd;
        border-radius: 24px;
        background: linear-gradient(180deg,#f8fbff,#eef6ff);
        padding: 1.4rem;
        cursor:pointer;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .dropzone:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(37,99,235,.08);
    }

    .dropzone-inner {
        min-height: 260px;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        text-align:center;
        gap:.9rem;
    }

    .drop-icon-wrap {
        width: 76px;
        height: 76px;
        border-radius: 24px;
        background: rgba(37,99,235,.1);
        display:grid;
        place-items:center;
    }

    .drop-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        background:#2563eb;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size: 1.7rem;
        box-shadow: 0 18px 30px rgba(37,99,235,.25);
    }

    .drop-copy-wrap h3 {
        font-size: 1.02rem;
        font-weight: 900;
        color:#0f172a;
    }

    .drop-copy-wrap p {
        color:#64748b;
        font-size:.88rem;
        margin-top:.35rem;
    }

    .field-label {
        display:block;
        font-size:.78rem;
        font-weight:800;
        color:#334155;
        margin-bottom:.35rem;
    }

    .form-stack {
        display:grid;
        gap:1rem;
        margin-top:.25rem;
    }

    .field-block {
        display:grid;
        gap:.45rem;
    }

    .file-pill {
        border:1px solid #dbe7f3;
        border-radius:16px;
        padding:.9rem 1rem;
        background:#fff;
        color:#475569;
        min-height: 48px;
        display:flex;
        align-items:center;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.6);
    }

    .action-row {
        display:flex;
        gap:1rem;
        padding-top:.5rem;
    }

    @media (max-width: 768px) {
        .page-shell { padding: 1rem; }
        .hero-strip { flex-direction:column; align-items:flex-start; }
        .hero-link { margin-left: 0; }
        .glass-panel, .hero-strip { border-radius: 24px; }
        .dropzone-inner { min-height: 220px; }
    }
</style>
@endpush

@push('scripts')
<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    const fileName = document.getElementById('fileName');
    const successBox = document.getElementById('successBox');
    const errorBox = document.getElementById('errorBox');

    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('dragover', (event) => { event.preventDefault(); dropzone.style.borderColor = '#2563eb'; });
    dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = '#93c5fd'; });
    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.style.borderColor = '#93c5fd';
        fileInput.files = event.dataTransfer.files;
        setFileLabel();
    });

    fileInput.addEventListener('change', setFileLabel);

    function setFileLabel() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileName.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        } else {
            fileName.textContent = 'Tiada fail dipilih';
        }
    }

    uploadForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        successBox.classList.add('hidden');
        errorBox.classList.add('hidden');

        if (!fileInput.files.length) {
            errorBox.textContent = 'Sila pilih fail Excel terlebih dahulu.';
            errorBox.classList.remove('hidden');
            return;
        }

        const formData = new FormData(uploadForm);
        const response = await fetch('{{ route('abm.v3.upload') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            errorBox.textContent = result.message || 'Muat naik gagal.';
            errorBox.classList.remove('hidden');
            return;
        }

        window.location.href = result.data.preview_url;
    });
</script>
@endpush
@endsection