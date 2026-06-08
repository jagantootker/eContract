@extends('components.layouts.app')

@section('title', 'Permohonan Pengguna')
@section('breadcrumb', 'Permohonan Pengguna')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="page-title">Senarai Permohonan Pengguna</div>
            <div class="page-subtitle">Semak, luluskan atau tolak permohonan pendaftaran pengguna.</div>
        </div>
        <div></div>
    </div>

    <div class="card-body">
        <div class="filter-bar" style="display:grid;grid-template-columns:1fr 220px;gap:0.75rem;">
            <div class="search-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" class="search-input" id="searchPermohonan" placeholder="Cari nama, IC, no. tentera, no. rujukan..." value="{{ $search }}">
            </div>
            <select class="search-input" id="statusPermohonan" style="height:2.5rem;padding-left:0.85rem;">
                <option value="" {{ $status === '' ? 'selected' : '' }}>Semua Status</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Dalam Tindakan</option>
                <option value="diluluskan" {{ $status === 'diluluskan' ? 'selected' : '' }}>Diluluskan</option>
                <option value="ditolak" {{ $status === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        <div class="table-wrap" id="permohonanTableWrap" style="margin-top:0.9rem;">
            @include('components.pengguna.permohonan._table', ['rows' => $rows])
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalPermohonan">
    <div class="modal" style="max-width:960px;">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon-bubble blue">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <div class="modal-title">Kelulusan Permohonan</div>
                    <div class="modal-subtitle">Semak maklumat permohonan sebelum membuat keputusan.</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('modalPermohonan')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body" id="permohonanDetailBody">
            <div style="padding:1rem;color:#64748b;">Memuatkan maklumat permohonan...</div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" id="btnSimpanKelulusan" onclick="submitKeputusan()">Simpan Keputusan</button>
            <button class="btn btn-outline" onclick="closeModal('modalPermohonan')">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let permohonanCurrentId = null;

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    let timer;
    document.getElementById('searchPermohonan').addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { reloadPermohonanTable(1); }, 300);
    });

    document.getElementById('statusPermohonan').addEventListener('change', function () {
        reloadPermohonanTable(1);
    });

    async function reloadPermohonanTable(page = 1) {
        const search = document.getElementById('searchPermohonan').value;
        const status = document.getElementById('statusPermohonan').value;
        const perPage = document.getElementById('perPagePermohonan') ? document.getElementById('perPagePermohonan').value : 10;

        const url = `/pengguna/permohonan?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}&page=${page}&per_page=${perPage}&_partial=1`;
        const res = await fetch(url, { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' } });
        document.getElementById('permohonanTableWrap').innerHTML = await res.text();
    }

    function badgeStatus(status) {
        if (status === 'diluluskan') return '<span class="pill pill-green">Diluluskan</span>';
        if (status === 'ditolak') return '<span class="pill pill-red">Ditolak</span>';
        return '<span class="pill pill-amber">Dalam Tindakan</span>';
    }

    function roleLabel(role) {
        const map = {
            admin: 'Admin',
            admin_sistem: 'Admin Sistem',
            pendaftar_kontrak: 'Pentadbir Kontrak',
            pemilik_projek: 'Pemilik Projek',
            pegawai_undang_undang: 'Peg. Undang-Undang'
        };
        return map[role] || role;
    }

    function jenisLabel(jenis) {
        const map = {
            pendaftaran_online: 'Pendaftaran Online',
            pengaktifan_semula_id: 'Pengaktifan Semula ID',
            penukaran_peranan: 'Penukaran Peranan'
        };
        return map[jenis] || '-';
    }

    function escapeHtml(val) {
        return String(val ?? '-')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    const roleOptions = [
        { value: 'admin', label: 'Admin' },
        { value: 'pendaftar_kontrak', label: 'Pentadbir Kontrak' },
        { value: 'pemilik_projek', label: 'Pemilik Projek' },
        { value: 'admin_sistem', label: 'Admin Sistem' },
        { value: 'pegawai_undang_undang', label: 'Pegawai Undang-Undang' }
    ];

    function readonlyField(label, value) {
        return `
            <div>
                <div style="font-size:0.74rem;font-weight:700;color:#64748b;letter-spacing:0.02em;margin-bottom:0.32rem;">${label}</div>
                <input type="text" value="${escapeHtml(value)}" readonly style="width:100%;border:1px solid #dbe5f0;background:#f8fafc;color:#0f172a;border-radius:10px;padding:0.62rem 0.7rem;font-size:0.82rem;">
            </div>
        `;
    }

    function fileCard(item, title, desc) {
        const hasFile = item && item.url;
        return `
            <div style="border:1px solid #dbe6f2;border-radius:14px;padding:0.85rem;background:linear-gradient(180deg,#f9fcff,#f1f7ff);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.7rem;">
                    <div>
                        <div style="font-size:0.82rem;font-weight:700;color:#0f172a;line-height:1.3;">${title}</div>
                        <div style="font-size:0.72rem;color:#64748b;margin-top:0.2rem;">${desc}</div>
                    </div>
                    ${hasFile
                        ? `<a href="${escapeHtml(item.url)}" target="_blank" rel="noopener" style="white-space:nowrap;text-decoration:none;background:#1d4ed8;color:#fff;padding:0.35rem 0.6rem;border-radius:8px;font-size:0.72rem;font-weight:700;">Muat Turun</a>`
                        : '<span style="font-size:0.72rem;color:#94a3b8;font-weight:700;">Tiada Fail</span>'}
                </div>
            </div>
        `;
    }

    async function viewPermohonan(id) {
        permohonanCurrentId = id;
        document.getElementById('modalPermohonan').classList.add('open');
        document.getElementById('permohonanDetailBody').innerHTML = '<div style="padding:1rem;color:#64748b;">Memuatkan maklumat permohonan...</div>';

        const res = await fetch(`/pengguna/permohonan/${id}`, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();

        if (!json.success) {
            showToast(json.message || 'Gagal memuatkan maklumat permohonan.', 'error');
            return;
        }

        const d = json.data || {};
        const isPending = d.permohonan_status === 'pending';
        const selectedRoles = Array.isArray(d.roles) ? d.roles : [];
        const roleCheckboxes = roleOptions.map(function (role) {
            const checked = selectedRoles.includes(role.value) ? 'checked' : '';
            const disabled = isPending ? '' : 'disabled';
            return `
                <label style="display:flex;align-items:center;gap:0.42rem;font-size:0.8rem;color:#334155;">
                    <input type="checkbox" name="peranan[]" value="${role.value}" ${checked} ${disabled}>
                    ${role.label}
                </label>
            `;
        }).join('');

        const aksesValue = String(d.akses_scope || '');
        const aksesOptions = [
            { value: '', label: '--- Sila Pilih ---' },
            { value: 'PTJ', label: 'PTJ' },
            { value: 'AGENSI', label: 'Agensi' },
            { value: 'KEMENTERIAN', label: 'Kementerian' }
        ].map(function (opt) {
            const selected = aksesValue === opt.value ? 'selected' : '';
            return `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
        }).join('');

        const checkedLulus = d.permohonan_status === 'ditolak' ? '' : 'checked';
        const checkedTolak = d.permohonan_status === 'ditolak' ? 'checked' : '';

        document.getElementById('permohonanDetailBody').innerHTML = `
            <form id="kelulusanForm">
                <div id="kelulusanValidationSummary" class="ds-validation-summary" style="display:none;" role="alert" aria-live="polite">
                    <div class="ds-validation-summary-title">Sila lengkapkan maklumat yang diperlukan.</div>
                    <ul class="ds-validation-summary-list"></ul>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem 0.95rem;">
                    ${readonlyField('No. Rujukan', d.no_rujukan_permohonan)}
                    <div>
                        <div style="font-size:0.74rem;font-weight:700;color:#64748b;letter-spacing:0.02em;margin-bottom:0.32rem;">Status Semasa</div>
                        <div>${badgeStatus(d.permohonan_status)}</div>
                    </div>
                    ${readonlyField('No. Kad Pengenalan / Tentera', `${d.ic_number || '-'}${d.no_tentera ? ' / ' + d.no_tentera : ''}`)}
                    ${readonlyField('Tarikh Permohonan', d.created_at || '-')}
                    ${readonlyField('Nama Penuh', d.name)}
                    ${readonlyField('Emel', d.email)}
                    ${readonlyField('Jabatan/Bahagian', d.jabatan_bahagian)}
                    ${readonlyField('Bahagian/Unit', d.bahagian_unit)}
                    ${readonlyField('Telefon Pejabat', d.telefon_pejabat)}
                    ${readonlyField('Telefon Bimbit', d.telefon_bimbit)}
                    ${readonlyField('Jenis Permohonan', jenisLabel(d.jenis_permohonan))}
                </div>

                <div style="margin-top:1rem;display:grid;grid-template-columns:1fr 1fr;gap:0.85rem;">
                    <div>
                        <div style="font-size:0.74rem;font-weight:700;color:#64748b;letter-spacing:0.02em;margin-bottom:0.35rem;">Peranan</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.45rem 0.8rem;border:1px solid #dbe5f0;border-radius:12px;padding:0.75rem 0.85rem;background:#fcfdff;${isPending ? '' : 'opacity:0.68;'}">
                            ${roleCheckboxes}
                        </div>
                        <div id="err_peranan[]" class="invalid-note ds-error runtime" role="alert" aria-live="polite"></div>
                    </div>
                    <div>
                        <div style="font-size:0.74rem;font-weight:700;color:#64748b;letter-spacing:0.02em;margin-bottom:0.35rem;">Akses</div>
                        <select name="akses_scope" style="width:100%;border:1px solid #dbe5f0;background:#fff;color:#0f172a;border-radius:10px;padding:0.62rem 0.7rem;font-size:0.82rem;" ${isPending ? '' : 'disabled'}>
                            ${aksesOptions}
                        </select>
                    </div>
                </div>

                <div style="margin-top:1rem;border:1px solid #dbe5f0;border-radius:12px;background:#f8fafc;padding:0.8rem 0.9rem;${isPending ? '' : 'opacity:0.68;'}">
                    <div style="font-size:0.78rem;font-weight:700;color:#0f172a;margin-bottom:0.45rem;">Status Kelulusan Permohonan</div>
                    <label style="display:inline-flex;align-items:center;gap:0.4rem;margin-right:1rem;font-size:0.82rem;color:#334155;">
                        <input type="radio" name="status" value="diluluskan" ${checkedLulus} ${isPending ? '' : 'disabled'}>
                        Lulus
                    </label>
                    <label style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.82rem;color:#334155;">
                        <input type="radio" name="status" value="ditolak" ${checkedTolak} ${isPending ? '' : 'disabled'}>
                        Tolak
                    </label>
                    <div id="err_status" class="invalid-note ds-error runtime" role="alert" aria-live="polite"></div>
                </div>

                <div style="margin-top:1rem;padding:0.95rem;border:1px solid #dbe5f0;border-radius:12px;background:#f8fbff;">
                    <div style="font-size:0.82rem;font-weight:700;color:#0f172a;margin-bottom:0.6rem;">Lampiran</div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:0.65rem;">
                        ${fileCard(d.lampiran?.borang_permohonan, 'Borang Permohonan', 'Format: PDF / DOC / DOCX')}
                        ${fileCard(d.lampiran?.kp_tentera, 'Salinan Kad Pengenalan / Tentera', 'Format: PDF / JPG / PNG')}
                        ${fileCard(d.lampiran?.pas_pekerja, 'Pas Pekerja', 'Format: PDF / JPG / PNG')}
                    </div>
                </div>
            </form>
        `;

        const btnSimpan = document.getElementById('btnSimpanKelulusan');
        btnSimpan.disabled = !isPending;
    }

    async function submitKeputusan() {
        if (!permohonanCurrentId) return;

        const form = document.getElementById('kelulusanForm');
        if (!form) return;

        const kelulusanSchema = {
            status: [
                { type: 'required', message: 'Sila pilih status kelulusan.', label: 'Sila pilih status kelulusan.' },
            ],
            'peranan[]': [
                { type: 'required', message: 'Sila pilih sekurang-kurangnya satu peranan.', label: 'Sila pilih sekurang-kurangnya satu peranan.' },
            ],
        };
        const validation = ValidationService.validate(form, kelulusanSchema, 'kelulusanValidationSummary');
        if (!validation.valid) {
            return;
        }

        const statusInput = form.querySelector('input[name="status"]:checked');

        const roles = Array.from(form.querySelectorAll('input[name="peranan[]"]:checked')).map(function (input) {
            return input.value;
        });

        const aksesInput = form.querySelector('select[name="akses_scope"]');
        const status = statusInput.value;
        const label = status === 'diluluskan' ? 'luluskan' : 'tolak';

        const confirmed = status === 'diluluskan'
            ? await ConfirmationService.confirmApprove({
                title: 'Lulus Permohonan?',
                text: `Anda pasti untuk ${label} permohonan ini?`,
                confirmText: 'Ya, Luluskan',
                cancelText: 'Batal'
            })
            : await ConfirmationService.confirmReject({
                title: 'Tolak Permohonan?',
                text: `Anda pasti untuk ${label} permohonan ini?`,
                confirmText: 'Ya, Tolak',
                cancelText: 'Batal'
            });
        if (!confirmed) return;

        const res = await fetch(`/pengguna/permohonan/${permohonanCurrentId}/keputusan`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status,
                peranan: roles,
                akses_scope: aksesInput ? aksesInput.value : null,
            })
        });

        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Keputusan berjaya dikemas kini.', 'success');
            closeModal('modalPermohonan');
            reloadPermohonanTable();
        } else {
            showToast(json.message || 'Gagal mengemas kini keputusan.', 'error');
        }
    }
</script>
@endpush
