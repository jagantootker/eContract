@extends('components.layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="page-header">
        <div>
            <p class="eyebrow">Perancangan Perolehan</p>
            <h1>Import Dokumen ABM / PPT</h1>
            <p class="text-slate-500">Muat naik fail, pilih template, dan semak data yang diekstrak sebelum dihantar.</p>
        </div>
        <div class="status-chip">
            <span class="dot"></span>
            Proses Automatik Aktif
        </div>
    </div>

    <div class="upload-grid max-w-6xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form id="uploadForm" class="space-y-6">
                    @csrf

                    {{-- File Dropzone --}}
                    <div>
                        <label class="section-label">Pilih Fail</label>
                        <div class="dropzone" id="dropzone">
                            <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.pdf" hidden required>

                            <svg class="mx-auto h-12 w-12 text-sky-500 mb-3" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-18-8h8m-4 4v12m-6 4h12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                            <p class="text-base font-semibold text-slate-900 mb-2">Seret fail di sini atau klik untuk memilih</p>
                            <p class="text-sm text-slate-500">Excel (.xlsx, .xls) atau PDF yang disokong</p>
                            <div class="meta-row">
                                <span>Saiz maksimum: 10 MB</span>
                                <span>Disyorkan: fail terkini</span>
                            </div>

                            <button type="button" class="mt-4 btn btn-primary" onclick="document.getElementById('fileInput').click()">
                                Pilih Fail
                            </button>
                        </div>
                        <div id="fileName" class="selected-file hidden">
                            <span class="file-badge">Fail Dipilih</span>
                            <span id="selectedFileName"></span>
                        </div>
                    </div>

                    {{-- Template Type Selection --}}
                    <div>
                        <label for="templateType" class="section-label">
                            <span>Jenis Template</span>
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="space-y-2">
                            <div class="template-switch">
                                <label class="switch-item is-active" id="labelABM" for="groupABM">
                                    <input type="radio" id="groupABM" name="templateGroup" value="ABM" checked>
                                    <span>Template ABM</span>
                                </label>
                                <label class="switch-item" id="labelPPT" for="groupPPT">
                                    <input type="radio" id="groupPPT" name="templateGroup" value="PPT">
                                    <span>Template PPT</span>
                                </label>
                            </div>

                            <select id="templateType" name="template_type" class="input-modern" required>
                                <option value="">Pilih template ABM</option>
                                <option value="ABM1">ABM 1 - Permohonan Belanja Operasi</option>
                                <option value="ABM2">ABM 2 - Permohonan Belanja Pembangunan</option>
                                <option value="ABM3">ABM 3 - Permohonan Belanja Aset</option>
                                <option value="ABM4">ABM 4 - Permohonan Belanja Khusus</option>
                                <option value="ABM5">ABM 5 - Pusat Kos</option>
                                <option value="ABM6">ABM 6 - Analisis Kos</option>
                                <option value="ABM7">ABM 7 - Analisis Sosial</option>
                                <option value="ABM7A">ABM 7A - Analisis Sosial Lanjutan</option>
                                <option value="ABM7B">ABM 7B - Impak Alam Sekitar</option>
                                <option value="ABM8">ABM 8 - Ringkasan Pelaksanaan</option>
                            </select>

                            <select id="templateTypePPT" name="template_type" class="input-modern hidden" disabled>
                                <option value="">Pilih template PPT</option>
                                <option value="PPT_BARU">PPT Baru - Perancangan Perolehan Tahunan Baru</option>
                                <option value="PPT_KEMAS_KINI">PPT Kemas Kini - Perancangan Perolehan Tahunan Kemaskini</option>
                            </select>
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div id="progressContainer" class="hidden">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-slate-900">Memuat naik fail...</span>
                            <span id="progressPercent" class="text-sm font-medium text-slate-600">0%</span>
                        </div>
                        <div class="progress-track">
                            <div id="progressBar" class="progress-fill" style="width: 0%"></div>
                        </div>
                    </div>

                    {{-- Error Message --}}
                    <div id="errorMessage" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p id="errorText" class="text-sm text-red-700"></p>
                    </div>

                    {{-- Success Message --}}
                    <div id="successMessage" class="hidden p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p id="successText" class="text-sm text-green-700"></p>
                        <a href="" id="previewLink" class="text-sm text-green-600 font-medium mt-2 inline-block">Lihat Preview &rarr;</a>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3 pt-4">
                        <button type="submit" id="submitBtn" class="btn btn-primary flex-1">
                            Muat Naik
                        </button>
                        <a href="{{ url('/perancangan-perolehan/dashboard-abm') }}" class="btn btn-secondary flex-1 text-center">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <aside class="guide-card">
            <h3>Maklumat Muat Naik</h3>
            <p>Pastikan fail dan template sepadan supaya data boleh diekstrak dengan kemas.</p>
            <ul>
                <li>Format fail: Excel (.xlsx, .xls) atau PDF</li>
                <li>Saiz maksimum fail: 10 MB</li>
                <li>Sistem akan jana pratonton data secara automatik</li>
                <li>Data pratonton boleh disunting sebelum submit</li>
            </ul>
            <div class="guide-note">
                Tip: Gunakan nama fail yang jelas seperti ABM1_UnitA_2026.xlsx untuk rujukan lebih mudah.
            </div>
        </aside>
    </div>

    <div class="max-w-6xl mx-auto mt-5">
        <div class="flow-strip">
            <span class="flow-step active">1. Import</span>
            <span class="flow-arrow">→</span>
            <span class="flow-step">2. Semak Pratonton</span>
            <span class="flow-arrow">→</span>
            <span class="flow-step">3. Hantar / Simpan Draf</span>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .page-wrapper {
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 2rem;
        background:
            radial-gradient(980px 520px at -15% -20%, rgba(14, 165, 233, 0.2), transparent 60%),
            radial-gradient(860px 460px at 120% -10%, rgba(16, 185, 129, 0.16), transparent 60%),
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
        color: #0369a1;
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
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.75rem;
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(5, 150, 105, 0.22);
        color: #065f46;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .dot {
        width: 0.45rem;
        height: 0.45rem;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.14);
    }

    .upload-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.25rem;
    }

    .card {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(6px);
    }

    .card-body {
        padding: 2rem;
    }

    .section-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.75rem;
    }

    .dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 1rem;
        padding: 2.5rem 1.5rem;
        text-align: center;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        transition: all 0.25s ease;
        cursor: pointer;
    }

    .dropzone:hover {
        border-color: #0ea5e9;
        transform: translateY(-1px);
        box-shadow: 0 10px 28px rgba(2, 132, 199, 0.12);
    }

    .dropzone.dragging {
        border-color: #0284c7;
        background: #f0f9ff;
    }

    .meta-row {
        margin-top: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: #64748b;
        flex-wrap: wrap;
        justify-content: center;
    }

    .meta-row span {
        background: #eef2ff;
        color: #334155;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
    }

    .selected-file {
        margin-top: 0.85rem;
        font-size: 0.85rem;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .file-badge {
        background: #dbeafe;
        color: #1e3a8a;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .template-switch {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .switch-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.7rem;
        border-radius: 0.75rem;
        border: 1px solid #cbd5e1;
        background: #fff;
        cursor: pointer;
        color: #334155;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .switch-item input {
        accent-color: #0ea5e9;
    }

    .switch-item.is-active {
        border-color: #7dd3fc;
        background: #f0f9ff;
        color: #0c4a6e;
        box-shadow: inset 0 0 0 1px rgba(14, 165, 233, 0.2);
    }

    .input-modern {
        width: 100%;
        padding: 0.7rem 0.95rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        outline: none;
        color: #0f172a;
        background: #fff;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .input-modern:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.18);
    }

    .progress-track {
        width: 100%;
        background: #e2e8f0;
        border-radius: 999px;
        height: 0.5rem;
        overflow: hidden;
    }

    .progress-fill {
        background: linear-gradient(90deg, #0ea5e9, #06b6d4);
        height: 100%;
        border-radius: 999px;
        transition: width 0.35s ease;
    }

    .guide-card {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid #dbeafe;
        border-radius: 1rem;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.08);
        padding: 1.1rem 1rem;
        height: fit-content;
        position: sticky;
        top: 1rem;
    }

    .guide-card h3 {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0c4a6e;
        margin-bottom: 0.5rem;
    }

    .guide-card p {
        font-size: 0.82rem;
        color: #475569;
        margin-bottom: 0.7rem;
    }

    .guide-card ul {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 0.45rem;
    }

    .guide-card li {
        font-size: 0.78rem;
        color: #334155;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.55rem;
        padding: 0.55rem 0.6rem;
    }

    .guide-note {
        margin-top: 0.8rem;
        font-size: 0.76rem;
        color: #155e75;
        border: 1px dashed #7dd3fc;
        background: #ecfeff;
        border-radius: 0.55rem;
        padding: 0.55rem;
    }

    .flow-strip {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        padding: 0.8rem 1rem;
        background: rgba(255, 255, 255, 0.75);
        border: 1px solid #dbeafe;
        border-radius: 0.9rem;
    }

    .flow-step {
        font-size: 0.76rem;
        font-weight: 700;
        color: #64748b;
        background: #f8fafc;
        border-radius: 999px;
        padding: 0.35rem 0.65rem;
    }

    .flow-step.active {
        color: #0c4a6e;
        background: #e0f2fe;
    }

    .flow-arrow {
        color: #94a3b8;
    }

    @media (max-width: 1024px) {
        .upload-grid {
            grid-template-columns: 1fr;
        }

        .guide-card {
            position: static;
        }
    }

    @media (max-width: 640px) {
        .page-wrapper {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-header h1 {
            font-size: 1.4rem;
        }

        .card-body {
            padding: 1rem;
        }

        .template-switch {
            grid-template-columns: 1fr;
        }
    }

    /* Compact modern overrides: smaller icons, softer shadows, tighter spacing */
    .page-wrapper svg { width: 1rem !important; height: 1rem !important; }
    .dropzone svg { width: 1.25rem !important; height: 1.25rem !important; }
    .card { box-shadow: 0 8px 20px rgba(15,23,42,0.05) !important; border-radius: 0.85rem !important; }
    .page-header h1 { font-weight: 700 !important; }
    .card-header, .card-body { padding: 1rem !important; }
    .flow-strip { padding: 0.6rem 0.8rem !important; }
</style>

<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    const fileName = document.getElementById('fileName');
    const selectedFileName = document.getElementById('selectedFileName');
    const templateTypeABM = document.getElementById('templateType');
    const templateTypePPT = document.getElementById('templateTypePPT');
    const groupABM = document.getElementById('groupABM');
    const groupPPT = document.getElementById('groupPPT');

    // Drag and drop
    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragging');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragging');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragging');
        fileInput.files = e.dataTransfer.files;
        updateFileName();
    });

    fileInput.addEventListener('change', updateFileName);

    function updateFileName() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            selectedFileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
            fileName.classList.remove('hidden');
        }
    }

    // Template group switching
    groupABM.addEventListener('change', () => {
        templateTypeABM.disabled = false;
        templateTypeABM.classList.remove('hidden');
        templateTypePPT.disabled = true;
        templateTypePPT.classList.add('hidden');
        document.getElementById('labelABM').classList.add('is-active');
        document.getElementById('labelPPT').classList.remove('is-active');
        templateTypeABM.focus();
    });

    groupPPT.addEventListener('change', () => {
        templateTypeABM.disabled = true;
        templateTypeABM.classList.add('hidden');
        templateTypePPT.disabled = false;
        templateTypePPT.classList.remove('hidden');
        document.getElementById('labelPPT').classList.add('is-active');
        document.getElementById('labelABM').classList.remove('is-active');
        templateTypePPT.focus();
    });

    // Form submission
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!fileInput.files[0]) {
            showError('Sila pilih fail untuk dimuat naik');
            return;
        }

        const templateType = groupABM.checked ? templateTypeABM.value : templateTypePPT.value;

        if (!templateType) {
            showError('Sila pilih jenis template');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('template_type', templateType);
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        try {
            document.getElementById('progressContainer').classList.remove('hidden');
            document.getElementById('submitBtn').disabled = true;

            let progressValue = 0;
            const progressTimer = setInterval(() => {
                progressValue = Math.min(progressValue + 12, 88);
                document.getElementById('progressBar').style.width = progressValue + '%';
                document.getElementById('progressPercent').textContent = progressValue + '%';
            }, 180);

            const response = await fetch('{{ url("/perancangan-perolehan/upload") }}', {
                method: 'POST',
                body: formData
            });

            clearInterval(progressTimer);
            document.getElementById('progressBar').style.width = '100%';
            document.getElementById('progressPercent').textContent = '100%';

            const data = await response.json();

            if (data.success) {
                showSuccess('Fail telah dimuat naik dengan berjaya! Data akan diekstrak untuk disahkan.');
                document.getElementById('previewLink').href = `/perancangan-perolehan/${data.data.id}/preview`;
                uploadForm.reset();
                fileName.classList.add('hidden');
                setTimeout(() => {
                    window.location.href = `/perancangan-perolehan/${data.data.id}/preview`;
                }, 2000);
            } else {
                showError(data.message || 'Ralat semasa muat naik');
                document.getElementById('submitBtn').disabled = false;
            }
        } catch (error) {
            showError('Ralat jaringan: ' + error.message);
            document.getElementById('submitBtn').disabled = false;
        } finally {
            setTimeout(() => {
                document.getElementById('progressContainer').classList.add('hidden');
                document.getElementById('progressBar').style.width = '0%';
                document.getElementById('progressPercent').textContent = '0%';
            }, 350);
        }
    });

    function showError(message) {
        const errorDiv = document.getElementById('errorMessage');
        document.getElementById('errorText').textContent = message;
        errorDiv.classList.remove('hidden');
        document.getElementById('successMessage').classList.add('hidden');
    }

    function showSuccess(message) {
        const successDiv = document.getElementById('successMessage');
        document.getElementById('successText').textContent = message;
        successDiv.classList.remove('hidden');
        document.getElementById('errorMessage').classList.add('hidden');
    }
</script>
@endsection
