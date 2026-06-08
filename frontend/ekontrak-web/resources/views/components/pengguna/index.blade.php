@extends('components.layouts.app')

@section('title', 'Urus Pengguna')
@section('breadcrumb', 'Urus Pengguna')

@section('content')

<div class="card">
    {{-- Header --}}
    <div class="card-header">
        <div>
            <div class="page-title">Peranan Pengguna</div>
            <div class="page-subtitle">Urus akaun pengguna, peranan, dan akses sistem eKONTRAK.</div>
        </div>
        <div></div>
    </div>

    <div class="card-body">
        {{-- Table controls --}}
        {{-- Filter Bar --}}
        <div class="filter-bar">
            <div class="search-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" class="search-input" id="searchInput" placeholder="Cari nama, IC, jabatan, peranan..." value="{{ $search }}">
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrap" id="tableWrap">
            @include('components.pengguna._table', ['users' => $users])
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL: TAMBAH / KEMASKINI PENGGUNA
══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalTambah">
    <div class="modal" style="max-width:920px;">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon-bubble blue">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1112 21a9 9 0 01-6.879-3.196z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="modal-title" id="modalTambahTitle">Tambah Pengguna Baru</div>
                    <div class="modal-subtitle">Tambah akaun dan tetapkan akses.</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('modalTambah')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="formPengguna">
                <input type="hidden" id="formUserId" name="_user_id" value="">
                <input type="hidden" id="formSource" name="source" value="JBPM">
                <x-validation-summary id="penggunaValidationSummary" title="Sila lengkapkan maklumat yang diperlukan." />

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <x-form.label for="fieldIc" text="No Kad Pengenalan" :required="true" />
                        <input type="text" class="form-control" name="ic_number" id="fieldIc" placeholder="Contoh: 890101145678" maxlength="12" minlength="12" pattern="[0-9]{12}" inputmode="numeric">
                        <div class="invalid-feedback" id="errIc">Nombor IC mestilah 12 digit tanpa sempang.</div>
                    </div>
                    <div class="form-group">
                        <x-form.label for="formName" text="Nama Penuh" :required="true" />
                        <input type="text" class="form-control" id="formName" name="name" placeholder="Nama penuh">
                        <div class="invalid-note" id="errName"></div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <x-form.label for="formEmail" text="Emel" :required="true" />
                        <input type="email" class="form-control" id="formEmail" name="email" placeholder="nama@agensi.gov.my">
                        <div class="invalid-note" id="errEmail"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Akses</label>
                        <select class="form-control" name="akses_scope" id="fieldAksesScope">
                            <option value="">— Pilih Akses —</option>
                            <option value="PTJ">PTJ</option>
                            <option value="AGENSI">Agensi</option>
                            <option value="KEMENTERIAN">Kementerian</option>
                        </select>
                        <div class="form-hint" style="margin-top:0.35rem;">Paparan modul Laporan akan ikut akses pengguna.</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Jabatan/Bahagian</label>
                        <select class="form-control" name="jabatan_id" id="selectJabatan" onchange="loadBahagian(this.value)">
                            <option value="">— Pilih Jabatan —</option>
                            @foreach($jabatan as $jab)
                                <option value="{{ $jab['id'] }}">{{ $jab['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bahagian/Unit</label>
                        <select class="form-control" name="bahagian_unit_id" id="selectBahagian">
                            <option value="">— Pilih Unit —</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Telefon Pejabat</label>
                        <input type="text" class="form-control" name="telefon_pejabat" placeholder="03-XXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefon Bimbit</label>
                        <input type="text" class="form-control" name="telefon_bimbit" placeholder="01X-XXXXXXXX">
                    </div>
                </div>

                {{-- Password --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <x-form.label for="fieldPw" text="Kata Laluan Baru" :required="true" />
                        <div class="input-wrap">
                            <input type="password" class="form-control pw" name="password" id="fieldPw">
                            <button type="button" class="toggle-pw" onclick="togglePw('fieldPw', this)">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        <div class="invalid-note" id="errPw"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Taip Semula</label>
                        <div class="input-wrap">
                            <input type="password" class="form-control pw" name="password_confirmation" id="fieldPwConfirm">
                            <button type="button" class="toggle-pw" onclick="togglePw('fieldPwConfirm', this)">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-hint" id="pwHint" style="display:none;margin-top:-0.5rem;margin-bottom:0.75rem;">Biarkan kosong jika tidak mahu tukar kata laluan.</div>

                <div id="pwStrength" style="display:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:0.75rem 0.875rem;margin-top:-0.15rem;margin-bottom:0.9rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:0.65rem;margin-bottom:0.65rem;">
                        <strong style="font-size:0.72rem;color:#334155;letter-spacing:0.02em;">Kekuatan Kata Laluan</strong>
                        <span id="pwStrengthLabel" style="font-size:0.68rem;font-weight:700;color:#64748b;">-</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:0.375rem;margin-bottom:0.75rem;">
                        <span class="pw-meter-seg" data-seg="1" style="height:5px;background:#e2e8f0;border-radius:99px;"></span>
                        <span class="pw-meter-seg" data-seg="2" style="height:5px;background:#e2e8f0;border-radius:99px;"></span>
                        <span class="pw-meter-seg" data-seg="3" style="height:5px;background:#e2e8f0;border-radius:99px;"></span>
                        <span class="pw-meter-seg" data-seg="4" style="height:5px;background:#e2e8f0;border-radius:99px;"></span>
                        <span class="pw-meter-seg" data-seg="5" style="height:5px;background:#e2e8f0;border-radius:99px;"></span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.35rem 0.8rem;">
                        <div id="reqMin" style="font-size:0.68rem;color:#94a3b8;">Minimum 12 aksara</div>
                        <div id="reqUpper" style="font-size:0.68rem;color:#94a3b8;">Huruf besar (A-Z)</div>
                        <div id="reqLower" style="font-size:0.68rem;color:#94a3b8;">Huruf kecil (a-z)</div>
                        <div id="reqNumber" style="font-size:0.68rem;color:#94a3b8;">Nombor (0-9)</div>
                        <div id="reqSpecial" style="font-size:0.68rem;color:#94a3b8;">Aksara khas</div>
                    </div>
                </div>

                <div id="pwMatchNote" style="display:none;font-size:0.7rem;font-weight:600;margin-top:-0.4rem;margin-bottom:0.75rem;"></div>

                {{-- Peranan --}}
                <div class="form-group">
                    <x-form.label for="rolesGroup" text="Peranan" :required="true" />
                    <div class="checkbox-group" id="rolesGroup">
                        @foreach(($roles ?? []) as $role)
                            <label class="checkbox-item">
                                <input type="checkbox" name="roles[]" value="{{ $role['name'] ?? '' }}"> {{ $role['label'] ?? ($role['name'] ?? '') }}
                            </label>
                        @endforeach
                    </div>
                    <div class="invalid-note" id="errRoles"></div>
                </div>

                <p style="font-size:0.75rem;color:#b91c1c;margin-top:0.5rem;" id="securityNote">
                    Kata laluan mesti mematuhi standard keselamatan minimum sistem.
                </p>
            </form>
        </div>

        <div class="modal-footer">
            <button class="btn btn-primary" id="btnSimpan" onclick="submitForm()">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
            <button class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
            <button class="btn btn-danger" id="btnHapusModal" style="display:none;margin-left:auto;" onclick="deleteCurrentUser()">
                Hapus
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let currentMode = 'tambah'; // 'tambah' | 'kemaskini'
    let currentUserId = null;
    const USER_BASE_SCHEMA = {
        ic_number: [
            { type: 'required', message: 'Nombor IC diperlukan.', label: 'Nombor IC diperlukan.' },
            { type: 'ic12', message: 'Nombor IC mestilah 12 digit tanpa sempang.', label: 'Nombor IC mestilah 12 digit tanpa sempang.' },
        ],
        name: [
            { type: 'required', message: 'Nama penuh diperlukan.', label: 'Nama penuh diperlukan.' },
            { type: 'maxLength', value: 255, message: 'Nama penuh tidak boleh melebihi 255 aksara.', label: 'Nama penuh tidak boleh melebihi 255 aksara.' },
        ],
        email: [
            { type: 'required', message: 'Emel diperlukan.', label: 'Emel diperlukan.' },
            { type: 'email', message: 'Format emel tidak sah.', label: 'Format emel tidak sah.' },
        ],
        'roles[]': [
            { type: 'required', message: 'Sila pilih sekurang-kurangnya satu peranan.', label: 'Sila pilih sekurang-kurangnya satu peranan.' },
        ],
    };

    function buildUserSchema() {
        const schema = { ...USER_BASE_SCHEMA };

        if (currentMode === 'kemaskini') {
            delete schema.ic_number;
            delete schema['roles[]'];
            if (!document.getElementById('fieldPw').value) {
                return schema;
            }
        }

        schema.password = [
            { type: 'required', message: 'Kata laluan diperlukan.', label: 'Kata laluan diperlukan.' },
            { type: 'minLength', value: 8, message: 'Kata laluan mestilah sekurang-kurangnya 8 aksara.', label: 'Kata laluan mestilah sekurang-kurangnya 8 aksara.' },
            { type: 'pattern', value: '(?=.*[A-Z])', message: 'Kata laluan mesti mengandungi huruf besar.', label: 'Kata laluan mesti mengandungi huruf besar.' },
            { type: 'pattern', value: '(?=.*[a-z])', message: 'Kata laluan mesti mengandungi huruf kecil.', label: 'Kata laluan mesti mengandungi huruf kecil.' },
            { type: 'pattern', value: '(?=.*[0-9])', message: 'Kata laluan mesti mengandungi nombor.', label: 'Kata laluan mesti mengandungi nombor.' },
            { type: 'pattern', value: '(?=.*[^A-Za-z0-9])', message: 'Kata laluan mesti mengandungi aksara khas.', label: 'Kata laluan mesti mengandungi aksara khas.' },
        ];
        schema.password_confirmation = [
            { type: 'required', message: 'Taip semula kata laluan diperlukan.', label: 'Taip semula kata laluan diperlukan.' },
            { type: 'sameAs', value: 'password', message: 'Pengesahan kata laluan tidak sepadan.', label: 'Pengesahan kata laluan tidak sepadan.' },
        ];

        return schema;
    }

    // ── Modal open/close ─────────────────────────────────────────────────────
    function openModal(id) {
        resetForm();
        currentMode   = 'tambah';
        currentUserId = null;
        document.getElementById('modalTambahTitle').textContent = 'Tambah Pengguna Baru';
        document.getElementById('btnHapusModal').style.display = 'none';
        document.getElementById('pwHint').style.display   = 'none';
        document.querySelector('label[for="fieldPw"] .ds-required')?.style.setProperty('display', 'inline');
        document.getElementById(id).classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    // Click outside to close
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
    });

    // ── Edit user ────────────────────────────────────────────────────────────
    function editUser(user) {
        resetForm();
        currentMode   = 'kemaskini';
        currentUserId = user.id;

        document.getElementById('modalTambahTitle').textContent = 'Kemaskini Pengguna';
        document.getElementById('btnHapusModal').style.display = 'inline-flex';
        document.getElementById('pwHint').style.display   = 'block';
        const pwStar = document.querySelector('label[for="fieldPw"] .ds-required');
        if (pwStar) pwStar.style.display = 'none';
        document.getElementById('formUserId').value = user.id;
        document.getElementById('formSource').value = user.source || 'JBPM';

        // Pre-fill fields
        document.querySelector('[name="ic_number"]').value = user.ic_number ?? '';
        document.querySelector('[name="ic_number"]').readOnly = true;

        document.querySelector('[name="name"]').value  = user.name ?? '';
        document.querySelector('[name="email"]').value = user.email ?? '';
        document.querySelector('[name="telefon_pejabat"]').value = user.telefon_pejabat ?? '';
        document.querySelector('[name="telefon_bimbit"]').value  = user.telefon_bimbit ?? '';
        document.getElementById('fieldAksesScope').value = user.akses_scope ?? '';

        if (user.jabatan_id) {
            document.getElementById('selectJabatan').value = user.jabatan_id;
            loadBahagian(user.jabatan_id, user.bahagian_unit_id);
        }

        // Pre-check roles
        const userRoles = (user.roles ?? []).map(role => typeof role === 'string' ? role : (role?.name || ''));
        document.querySelectorAll('[name="roles[]"]').forEach(cb => {
            cb.checked = userRoles.includes(cb.value);
        });

        document.getElementById('modalTambah').classList.add('open');
        updatePasswordIndicators();
    }

    // ── Load Bahagian/Unit ───────────────────────────────────────────────────
    async function loadBahagian(jabatanId, selectedId = null) {
        const select = document.getElementById('selectBahagian');
        select.innerHTML = '<option value="">Memuatkan...</option>';

        const res  = await fetch(`/pengguna/bahagian-unit?jabatan_id=${jabatanId}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        const list = data.data ?? [];

        select.innerHTML = '<option value="">— Pilih Unit —</option>';
        list.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.id;
            opt.textContent = `${b.kod} — ${b.nama}`;
            if (selectedId && b.id == selectedId) opt.selected = true;
            select.appendChild(opt);
        });
    }

    // ── Submit form ──────────────────────────────────────────────────────────
    async function submitForm() {
        clearErrors();

        const form    = document.getElementById('formPengguna');
        const validation = ValidationService.validate(form, buildUserSchema(), 'penggunaValidationSummary');
        if (!validation.valid) {
            return;
        }

        const data    = buildPayload(form);
        const isEdit  = currentMode === 'kemaskini';
        const url     = isEdit ? `/pengguna/${currentUserId}` : '/pengguna';
        const method  = isEdit ? 'PUT' : 'POST';

        const btnSimpan = document.getElementById('btnSimpan');
        btnSimpan.disabled = true;
        btnSimpan.textContent = 'Menyimpan...';

        try {
            const res  = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify(data),
            });
            const json = await res.json();

            if (json.success) {
                showToast(json.message, 'success');
                closeModal('modalTambah');
                reloadTable();
            } else {
                showToast(json.message ?? 'Ralat berlaku.', 'error');
                if (json.errors) {
                    showValidationToast(json.errors, 'Sila lengkapkan maklumat pengguna yang wajib.');
                    showErrors(json.errors);
                }
            }
        } catch (e) {
            showToast('Ralat sambungan.', 'error');
        } finally {
            btnSimpan.disabled = false;
            btnSimpan.textContent = 'Simpan';
        }
    }

    function buildPayload(form) {
        const data = { source: document.getElementById('formSource').value };
        data.ic_number = form.querySelector('[name="ic_number"]').value;
        data.name             = form.querySelector('[name="name"]').value;
        data.email            = form.querySelector('[name="email"]').value;
        const jabatanSelect = document.getElementById('selectJabatan');
        const unitSelect = document.getElementById('selectBahagian');
        data.jabatan_id       = jabatanSelect.value || null;
        data.bahagian_unit_id = unitSelect.value || null;
        data.jabatan_bahagian = jabatanSelect.options[jabatanSelect.selectedIndex]?.textContent?.trim() || null;
        data.bahagian_unit    = unitSelect.options[unitSelect.selectedIndex]?.textContent?.trim() || null;
        data.telefon_pejabat  = form.querySelector('[name="telefon_pejabat"]').value;
        data.telefon_bimbit   = form.querySelector('[name="telefon_bimbit"]').value;
        data.akses_scope      = form.querySelector('[name="akses_scope"]').value || null;

        const pw = form.querySelector('[name="password"]').value;
        if (pw) {
            data.password              = pw;
            data.password_confirmation = form.querySelector('[name="password_confirmation"]').value;
        }

        data.roles = Array.from(form.querySelectorAll('[name="roles[]"]:checked')).map(cb => cb.value);

        return data;
    }

    async function deleteCurrentUser() {
        if (!currentUserId) return;

        const name = document.querySelector('[name="name"]').value || '';
        const confirmed = await ConfirmationService.confirmDelete({
            title: 'Hapus Pengguna',
            text: `Padam pengguna "${name}"? Tindakan ini tidak boleh dibatalkan.`,
            icon: 'warning',
            confirmText: 'Ya, Hapus',
            cancelText: 'Batal'
        });
        if (!confirmed) return;

        const res  = await fetch(`/pengguna/${currentUserId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const json = await res.json();

        if (json.success) {
            showToast(json.message, 'success');
            closeModal('modalTambah');
            reloadTable();
        } else {
            showToast(json.message ?? 'Gagal memadam.', 'error');
        }
    }

    // ── Delete ────────────────────────────────────────────────────────────────
    async function deleteUser(id, name) {
        const confirmed = await ConfirmationService.confirmDelete({
            title: 'Padam Pengguna',
            text: `Padam pengguna "${name}"? Tindakan ini tidak boleh dibatalkan.`,
            icon: 'warning',
            confirmText: 'Ya, Padam',
            cancelText: 'Batal'
        });
        if (!confirmed) return;

        const res  = await fetch(`/pengguna/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const json = await res.json();

        if (json.success) {
            showToast(json.message, 'success');
            reloadTable();
        } else {
            showToast(json.message ?? 'Gagal memadam.', 'error');
        }
    }

    // ── Search (debounced) ────────────────────────────────────────────────────
    let searchTimer;
    document.getElementById('searchInput').addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(reloadTable, 300);
    });

    // ── Reload table via AJAX ─────────────────────────────────────────────────
    async function reloadTable(page = 1) {
        const search  = document.getElementById('searchInput').value;
        const perPage = document.getElementById('perPageSelect').value;
        const url     = `/pengguna?search=${encodeURIComponent(search)}&page=${page}&per_page=${perPage}&_partial=1`;

        const res  = await fetch(url, { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' } });
        const html = await res.text();
        document.getElementById('tableWrap').innerHTML = html;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function resetForm() {
        document.getElementById('formPengguna').reset();
        document.getElementById('formUserId').value = '';
        document.querySelector('[name="ic_number"]').readOnly = false;
        document.getElementById('selectBahagian').innerHTML = '<option value="">— Pilih Unit —</option>';
        const pwStar = document.querySelector('label[for="fieldPw"] .ds-required');
        if (pwStar) pwStar.style.display = 'inline';
        document.getElementById('pwStrength').style.display = 'none';
        document.getElementById('pwMatchNote').style.display = 'none';
        document.querySelectorAll('.pw-meter-seg').forEach(el => { el.style.background = '#e2e8f0'; });
        ['reqMin','reqUpper','reqLower','reqNumber','reqSpecial'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.color = '#94a3b8';
        });
        const label = document.getElementById('pwStrengthLabel');
        if (label) {
            label.textContent = '-';
            label.style.color = '#64748b';
        }
        clearErrors();
    }

    function clearErrors() {
        ['errIc','errName','errEmail','errPw','errRoles'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = '';
        });
    }

    function showErrors(errors) {
        const map = { ic_number: 'errIc', name: 'errName', email: 'errEmail', password: 'errPw', roles: 'errRoles' };
        Object.entries(map).forEach(([field, elId]) => {
            if (errors[field]) {
                const el = document.getElementById(elId);
                if (el) el.textContent = errors[field][0];
            }
        });
    }

    function updatePasswordIndicators() {
        const pw = document.getElementById('fieldPw').value || '';
        const confirm = document.getElementById('fieldPwConfirm').value || '';
        const strengthWrap = document.getElementById('pwStrength');
        const strengthLabel = document.getElementById('pwStrengthLabel');
        const matchNote = document.getElementById('pwMatchNote');

        const checks = {
            min: pw.length >= 12,
            upper: /[A-Z]/.test(pw),
            lower: /[a-z]/.test(pw),
            number: /[0-9]/.test(pw),
            special: /[^A-Za-z0-9]/.test(pw),
        };
        const score = Object.values(checks).filter(Boolean).length;

        if (pw.length > 0) {
            strengthWrap.style.display = 'block';
            const color = score <= 2 ? '#ef4444' : (score <= 3 ? '#f59e0b' : (score <= 4 ? '#3b82f6' : '#10b981'));
            const label = score <= 2 ? 'Lemah' : (score <= 3 ? 'Sederhana' : (score <= 4 ? 'Kuat' : 'Sangat Kuat'));

            if (strengthLabel) {
                strengthLabel.textContent = label;
                strengthLabel.style.color = color;
            }
            document.querySelectorAll('.pw-meter-seg').forEach((el, i) => {
                el.style.background = (i < score) ? color : '#e2e8f0';
            });

            const reqMap = { reqMin: checks.min, reqUpper: checks.upper, reqLower: checks.lower, reqNumber: checks.number, reqSpecial: checks.special };
            Object.entries(reqMap).forEach(([id, pass]) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.style.color = pass ? '#047857' : '#94a3b8';
            });
        } else {
            strengthWrap.style.display = 'none';
        }

        if (confirm.length > 0 || pw.length > 0) {
            matchNote.style.display = 'block';
            if (pw.length === 0 || confirm.length === 0) {
                matchNote.textContent = 'Sila lengkapkan kedua-dua medan kata laluan.';
                matchNote.style.color = '#64748b';
            } else if (pw === confirm) {
                matchNote.textContent = 'Kata laluan sepadan.';
                matchNote.style.color = '#059669';
            } else {
                matchNote.textContent = 'Kata laluan tidak sepadan.';
                matchNote.style.color = '#dc2626';
            }
        } else {
            matchNote.style.display = 'none';
        }
    }

    document.getElementById('fieldPw').addEventListener('input', updatePasswordIndicators);
    document.getElementById('fieldPwConfirm').addEventListener('input', updatePasswordIndicators);
    ValidationService.bindRealtime(document.getElementById('formPengguna'), USER_BASE_SCHEMA);
</script>
@endpush
