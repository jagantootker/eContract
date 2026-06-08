@extends('components.layouts.app')

@section('title', 'Maklumat Syarikat')
@section('breadcrumb', 'Maklumat Syarikat')

@section('content')

<div class="card">
    <div class="card-header">
        <div>
            <div class="page-title">Maklumat Syarikat</div>
            <div class="page-subtitle">Senarai syarikat yang berdaftar dalam sistem eKONTRAK.</div>
        </div>
        <button class="btn btn-primary" onclick="openAddModal()">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Syarikat
        </button>
    </div>

    <div class="card-body">
        <div class="section-hdr">Senarai Syarikat</div>
        <div class="filter-bar">
            <div class="search-wrap" style="max-width:360px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" class="search-input" id="searchSyarikat" placeholder="Cari nama syarikat..." value="{{ $search }}">
            </div>
        </div>

        <div class="table-wrap" id="syarikatTableWrap" style="margin-top:1rem;">
            @include('syarikat._table', ['companies' => $companies])
        </div>
    </div>
</div>

{{-- ══════════════ MODAL: Tambah / Kemaskini Syarikat ══════════════ --}}
<div class="modal-overlay" id="modalSyarikat">
    <div class="modal" style="max-width:1100px;width:98%;">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon-bubble blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <div class="modal-title" id="companyModalTitle">Maklumat Syarikat</div>
                    <div class="modal-subtitle" id="companyModalSubtitle">-</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeCompanyModal()">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <div class="section-hdr" id="companyFormSectionTitle">Daftar Syarikat</div>

            <form id="companyForm">
                <input type="hidden" id="companyId" value="">
                <x-validation-summary id="companyValidationSummary" title="Sila lengkapkan maklumat yang diperlukan." />

                <div class="form-group">
                    <x-form.label for="nama_syarikat" text="Nama Syarikat" :required="true" />
                    <input type="text" class="form-control" id="nama_syarikat" name="nama_syarikat" placeholder="Nama syarikat">
                    <div class="invalid-note" id="err_nama_syarikat"></div>
                </div>

                <div class="form-group">
                    <x-form.label for="alamat" text="Alamat" :required="true" />
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Alamat syarikat"></textarea>
                    <div class="invalid-note" id="err_alamat"></div>
                </div>

                <div class="form-group">
                    <x-form.label for="negeri" text="Negeri" :required="true" />
                    <select class="form-control" id="negeri" name="negeri">
                        <option value="">- Pilih Negeri -</option>
                        <option value="Johor">Johor</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Perak">Perak</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Pulau Pinang">Pulau Pinang</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Terengganu">Terengganu</option>
                        <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                        <option value="WP Labuan">WP Labuan</option>
                        <option value="WP Putrajaya">WP Putrajaya</option>
                    </select>
                    <div class="invalid-note" id="err_negeri"></div>
                </div>

                <div class="section-hdr" style="margin-top:1rem;">Nama Pegawai Dihubungi 1</div>
                <div class="contact-grid">
                    <div class="form-group">
                        <x-form.label for="pegawai_hubungi_1_nama" text="Nama" :required="true" />
                        <input type="text" class="form-control" id="pegawai_hubungi_1_nama" name="pegawai_hubungi_1_nama" placeholder="Nama pegawai 1">
                        <div class="invalid-note" id="err_pegawai_hubungi_1_nama"></div>
                    </div>
                    <div class="form-group">
                        <x-form.label for="pegawai_hubungi_1_tel_pejabat" text="No Tel Pejabat" :required="true" />
                        <input type="text" class="form-control" id="pegawai_hubungi_1_tel_pejabat" name="pegawai_hubungi_1_tel_pejabat" placeholder="Format:0388751234">
                        <div class="invalid-note" id="err_pegawai_hubungi_1_tel_pejabat"></div>
                    </div>
                    <div class="form-group">
                        <x-form.label for="pegawai_hubungi_1_email" text="Emel" :required="true" />
                        <input type="text" class="form-control" id="pegawai_hubungi_1_email" name="pegawai_hubungi_1_email" placeholder="nama@email.com">
                        <div class="invalid-note" id="err_pegawai_hubungi_1_email"></div>
                    </div>
                    <div class="form-group">
                        <x-form.label for="pegawai_hubungi_1_tel_hp" text="No H/P" :required="true" />
                        <input type="text" class="form-control" id="pegawai_hubungi_1_tel_hp" name="pegawai_hubungi_1_tel_hp" placeholder="Format:0388751234">
                        <div class="invalid-note" id="err_pegawai_hubungi_1_tel_hp"></div>
                    </div>
                </div>

                <div class="section-hdr" style="margin-top:1.25rem;">Nama Pegawai Dihubungi 2 <span style="font-size:0.72rem;font-weight:400;color:#6b7280;margin-left:0.5rem;">(pilihan)</span></div>
                <div class="contact-grid">
                    <div class="form-group">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="pegawai_hubungi_2_nama" placeholder="Nama pegawai 2">
                        <div class="invalid-note" id="err_pegawai_hubungi_2_nama"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No Tel Pejabat</label>
                        <input type="text" class="form-control" name="pegawai_hubungi_2_tel_pejabat" placeholder="Format:0388751234">
                        <div class="invalid-note" id="err_pegawai_hubungi_2_tel_pejabat"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Emel</label>
                        <input type="text" class="form-control" name="pegawai_hubungi_2_email" placeholder="nama@email.com">
                        <div class="invalid-note" id="err_pegawai_hubungi_2_email"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No H/P</label>
                        <input type="text" class="form-control" name="pegawai_hubungi_2_tel_hp" placeholder="Format:0388751234">
                        <div class="invalid-note" id="err_pegawai_hubungi_2_tel_hp"></div>
                    </div>
                </div>

                <div class="section-hdr" style="margin-top:1.25rem;">Nama Pegawai Dihubungi 3 <span style="font-size:0.72rem;font-weight:400;color:#6b7280;margin-left:0.5rem;">(pilihan)</span></div>
                <div class="contact-grid">
                    <div class="form-group">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="pegawai_hubungi_3_nama" placeholder="Nama pegawai 3">
                        <div class="invalid-note" id="err_pegawai_hubungi_3_nama"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No Tel Pejabat</label>
                        <input type="text" class="form-control" name="pegawai_hubungi_3_tel_pejabat" placeholder="Format:0388751234">
                        <div class="invalid-note" id="err_pegawai_hubungi_3_tel_pejabat"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Emel</label>
                        <input type="text" class="form-control" name="pegawai_hubungi_3_email" placeholder="nama@email.com">
                        <div class="invalid-note" id="err_pegawai_hubungi_3_email"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No H/P</label>
                        <input type="text" class="form-control" name="pegawai_hubungi_3_tel_hp" placeholder="Format:0388751234">
                        <div class="invalid-note" id="err_pegawai_hubungi_3_tel_hp"></div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer" id="companyFooterView">
            <button class="btn btn-outline" onclick="closeCompanyModal()">KEMBALI</button>
            <button class="btn btn-primary" onclick="enableEditMode()">KEMASKINI</button>
        </div>

        <div class="modal-footer" id="companyFooterEdit" style="display:none;">
            <button class="btn btn-primary" id="btnSubmitCompany" onclick="submitCompany()">Simpan</button>
            <button class="btn btn-outline" onclick="closeCompanyModal()">Batal</button>
        </div>

        <div class="modal-footer" id="companyFooterAdd" style="display:none;">
            <button class="btn btn-primary" id="btnCreateCompany" onclick="submitCompany()">Simpan</button>
            <button class="btn btn-outline" onclick="closeCompanyModal()">Batal</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .contact-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.5rem; }
    @media (max-width: 900px) {
        .contact-grid { grid-template-columns:1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let companyMode = 'view';
    let selectedCompany = null;
    let cachedCompany = null;
    const COMPANY_SCHEMA = {
        nama_syarikat: [
            { type: 'required', message: 'Nama syarikat wajib diisi.', label: 'Nama syarikat wajib diisi.' },
            { type: 'maxLength', value: 255, message: 'Nama syarikat tidak boleh melebihi 255 aksara.', label: 'Nama syarikat tidak boleh melebihi 255 aksara.' },
        ],
        alamat: [
            { type: 'required', message: 'Alamat wajib diisi.', label: 'Alamat wajib diisi.' },
        ],
        negeri: [
            { type: 'required', message: 'Negeri wajib dipilih.', label: 'Negeri wajib dipilih.' },
        ],
        pegawai_hubungi_1_nama: [
            { type: 'required', message: 'Nama pegawai dihubungi 1 wajib diisi.', label: 'Nama pegawai dihubungi 1 wajib diisi.' },
        ],
        pegawai_hubungi_1_email: [
            { type: 'required', message: 'Emel pegawai dihubungi 1 wajib diisi.', label: 'Emel pegawai dihubungi 1 wajib diisi.' },
            { type: 'email', message: 'Format emel pegawai dihubungi 1 tidak sah.', label: 'Format emel pegawai dihubungi 1 tidak sah.' },
        ],
        pegawai_hubungi_1_tel_pejabat: [
            { type: 'required', message: 'No tel pejabat pegawai dihubungi 1 wajib diisi.', label: 'No tel pejabat pegawai dihubungi 1 wajib diisi.' },
            { type: 'maxLength', value: 20, message: 'No tel pejabat pegawai dihubungi 1 terlalu panjang.', label: 'No tel pejabat pegawai dihubungi 1 terlalu panjang.' },
        ],
        pegawai_hubungi_1_tel_hp: [
            { type: 'required', message: 'No telefon bimbit pegawai dihubungi 1 wajib diisi.', label: 'No telefon bimbit pegawai dihubungi 1 wajib diisi.' },
            { type: 'maxLength', value: 20, message: 'No telefon bimbit pegawai dihubungi 1 terlalu panjang.', label: 'No telefon bimbit pegawai dihubungi 1 terlalu panjang.' },
        ],
        pegawai_hubungi_2_email: [
            { type: 'email', message: 'Format emel pegawai dihubungi 2 tidak sah.', label: 'Format emel pegawai dihubungi 2 tidak sah.' },
        ],
        pegawai_hubungi_3_email: [
            { type: 'email', message: 'Format emel pegawai dihubungi 3 tidak sah.', label: 'Format emel pegawai dihubungi 3 tidak sah.' },
        ],
    };

    let _sSearch = null;
    document.getElementById('searchSyarikat').addEventListener('input', function () {
        clearTimeout(_sSearch);
        _sSearch = setTimeout(() => reloadSyarikat(1), 450);
    });
    document.getElementById('perPageSyarikat').addEventListener('change', () => reloadSyarikat(1));

    async function reloadSyarikat(page) {
        const search = document.getElementById('searchSyarikat').value;
        const perPage = document.getElementById('perPageSyarikat').value;
        const wrap = document.getElementById('syarikatTableWrap');
        wrap.style.opacity = '0.5';
        try {
            const url = `/syarikat/table?search=${encodeURIComponent(search)}&page=${page ?? 1}&per_page=${perPage}`;
            const res = await fetch(url, { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' } });
            if (res.ok) wrap.innerHTML = await res.text();
        } catch (e) {
            showToast('Gagal memuatkan data.', 'error');
        }
        wrap.style.opacity = '1';
    }

    function openAddModal() {
        companyMode = 'add';
        selectedCompany = null;
        cachedCompany = null;
        document.getElementById('companyId').value = '';
        document.getElementById('companyModalTitle').textContent = 'Tambah Syarikat Baharu';
        document.getElementById('companyModalSubtitle').textContent = 'Isi maklumat syarikat baharu.';
        document.getElementById('companyFormSectionTitle').textContent = 'Daftar Syarikat';
        document.getElementById('companyForm').reset();
        setFieldsEditable(true);
        setFooter('add');
        clearErrors();
        document.getElementById('modalSyarikat').classList.add('open');
    }

    function closeCompanyModal() {
        document.getElementById('modalSyarikat').classList.remove('open');
    }

    function enableEditMode() {
        companyMode = 'edit';
        setFieldsEditable(true);
        setFooter('edit');
        document.getElementById('companyFormSectionTitle').textContent = 'Kemaskini Syarikat';
    }

    function cancelEditMode() {
        companyMode = 'view';
        if (cachedCompany) fillForm(cachedCompany);
        setFieldsEditable(false);
        setFooter('view');
    }

    async function openViewModal(id) {
        document.getElementById('modalSyarikat').classList.add('open');
        companyMode = 'edit';
        setFieldsEditable(true);
        setFooter('edit');
        document.getElementById('companyModalTitle').textContent = 'Kemaskini Syarikat';
        document.getElementById('companyModalSubtitle').textContent = 'Kemaskini maklumat syarikat.';
        document.getElementById('companyFormSectionTitle').textContent = 'Kemaskini Syarikat';
        clearErrors();
        try {
            const res = await fetch(`/syarikat/${id}`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            const company = json.data ?? {};
            selectedCompany = company;
            cachedCompany = company;
            document.getElementById('companyId').value = company.id;
            fillForm(company);
        } catch (e) {
            showToast('Gagal memuatkan data syarikat.', 'error');
        }
    }

    function openCompanyDetail(id) {
        openViewModal(id);
    }

    function fillForm(c) {
        const f = document.getElementById('companyForm');
        const set = (name, val) => { const el = f.elements[name]; if (el) el.value = val ?? ''; };
        set('nama_syarikat', c.nama_syarikat);
        set('alamat', c.alamat);
        set('negeri', c.negeri);
        set('pegawai_hubungi_1_nama', c.pegawai_hubungi_1_nama);
        set('pegawai_hubungi_1_tel_pejabat', c.pegawai_hubungi_1_tel_pejabat);
        set('pegawai_hubungi_1_email', c.pegawai_hubungi_1_email);
        set('pegawai_hubungi_1_tel_hp', c.pegawai_hubungi_1_tel_hp);
        set('pegawai_hubungi_2_nama', c.pegawai_hubungi_2_nama);
        set('pegawai_hubungi_2_tel_pejabat', c.pegawai_hubungi_2_tel_pejabat);
        set('pegawai_hubungi_2_email', c.pegawai_hubungi_2_email);
        set('pegawai_hubungi_2_tel_hp', c.pegawai_hubungi_2_tel_hp);
        set('pegawai_hubungi_3_nama', c.pegawai_hubungi_3_nama);
        set('pegawai_hubungi_3_tel_pejabat', c.pegawai_hubungi_3_tel_pejabat);
        set('pegawai_hubungi_3_email', c.pegawai_hubungi_3_email);
        set('pegawai_hubungi_3_tel_hp', c.pegawai_hubungi_3_tel_hp);
    }

    function setFieldsEditable(editable) {
        document.querySelectorAll('#companyForm input, #companyForm select, #companyForm textarea').forEach(el => {
            el.readOnly = !editable;
            el.disabled = !editable;
        });
    }

    function setFooter(mode) {
        document.getElementById('companyFooterView').style.display = mode === 'view' ? 'flex' : 'none';
        document.getElementById('companyFooterEdit').style.display = mode === 'edit' ? 'flex' : 'none';
        document.getElementById('companyFooterAdd').style.display = mode === 'add' ? 'flex' : 'none';
    }

    async function submitCompany() {
        const id = document.getElementById('companyId').value;
        const isEdit = companyMode === 'edit';
        const isAdd = companyMode === 'add';
        if (!isEdit && !isAdd) return;
        const form = document.getElementById('companyForm');
        const validation = ValidationService.validate(form, COMPANY_SCHEMA, 'companyValidationSummary');
        if (!validation.valid) return;

        const data = Object.fromEntries(new FormData(form).entries());
        const method = isEdit ? 'PUT' : 'POST';
        const url = isEdit ? `/syarikat/${id}` : '/syarikat';
        try {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify(data),
            });
            const json = await res.json();
            if (json.success) {
                showToast(json.message ?? 'Data berjaya disimpan.', 'success');
                closeCompanyModal();
                reloadSyarikat(1);
            } else {
                showToast(json.message ?? 'Gagal menyimpan data.', 'error');
                if (json.errors) {
                    showValidationToast(json.errors, 'Sila lengkapkan maklumat syarikat yang wajib.');
                    showErrors(json.errors);
                }
            }
        } catch (e) {
            showToast('Ralat rangkaian. Sila cuba lagi.', 'error');
        }
    }

    function showErrors(errors) {
        clearErrors();
        Object.entries(errors).forEach(([field, msgs]) => {
            const el = document.getElementById('err_' + field);
            const input = document.querySelector(`[name="${field}"]`);
            if (el) el.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
            if (input) input.classList.add('is-invalid');
        });
    }

    function clearErrors() {
        document.querySelectorAll('.invalid-note').forEach(el => el.textContent = '');
        document.querySelectorAll('#companyForm .form-control').forEach(el => el.classList.remove('is-invalid'));
    }
    // Clear validation error on input/change
    document.querySelectorAll('#companyForm input, #companyForm textarea, #companyForm select').forEach(el => {
        el.addEventListener('input', function () {
            this.classList.remove('is-invalid');
            const err = document.getElementById('err_' + this.name);
            if (err) err.textContent = '';
        });
        el.addEventListener('change', function () {
            this.classList.remove('is-invalid');
            const err = document.getElementById('err_' + this.name);
            if (err) err.textContent = '';
        });
    });

    document.getElementById('modalSyarikat').addEventListener('click', function (e) {
        if (e.target === this) closeCompanyModal();
    });
    ValidationService.bindRealtime(document.getElementById('companyForm'), COMPANY_SCHEMA);

    // Style for red border on invalid
    const style = document.createElement('style');
    style.innerHTML = `.form-control.is-invalid { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important; background: #fff7f7; }`;
    document.head.appendChild(style);
</script>
@endpush
