<x-layouts.guest title="Borang Pendaftaran">
    <style>
        .reg-shell { border: 1px solid #dbe5f0; border-radius: 16px; padding: 1.35rem; background: #fff; box-shadow: 0 12px 30px rgba(15,23,42,0.06); }
        .reg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .upload-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.9rem; margin-top: 0.9rem; }
        .upload-card {
            border: 1.5px dashed #bfd3ea;
            border-radius: 14px;
            padding: 1rem 0.95rem;
            text-align: left;
            background: linear-gradient(180deg, #f8fbff, #eef6ff);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .upload-card:hover {
            border-color: #7ca8d7;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.07);
        }
        .upload-head { display: flex; align-items: center; gap: 0.65rem; }
        .upload-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #dbeafe;
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .upload-title { font-size: 0.88rem; font-weight: 700; color: #0f172a; line-height: 1.3; }
        .upload-sub { font-size: 0.74rem; color: #64748b; margin-top: 0.2rem; line-height: 1.35; }
        .upload-card input[type='file'] {
            width: 100%;
            font-size: 0.74rem;
            margin-top: 0.72rem;
            color: #334155;
            border: 1px solid #dbe5f0;
            background: #ffffff;
            border-radius: 10px;
            padding: 0.35rem;
        }
        .upload-card input[type='file']::file-selector-button {
            border: 0;
            border-radius: 8px;
            background: #1e40af;
            color: #ffffff;
            padding: 0.38rem 0.68rem;
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            margin-right: 0.55rem;
        }
        .upload-card input[type='file']::file-selector-button:hover { background: #1e3a8a; }
        .password-wrap { position: relative; }
        .strength-wrap { margin-top: 0.7rem; display: none; }
        .strength-wrap.show { display: block; }
        .strength-bar { display: flex; gap: 0.3rem; margin-bottom: 0.45rem; }
        .strength-segment { flex: 1; height: 6px; border-radius: 999px; background: #cbd5e1; transition: background 0.2s ease; }
        .strength-meta { font-size: 0.72rem; font-weight: 700; margin-bottom: 0.45rem; }
        .strength-meta.weak { color: #dc2626; }
        .strength-meta.medium { color: #ca8a04; }
        .strength-meta.strong { color: #2563eb; }
        .strength-meta.very-strong { color: #059669; }
        .strength-checks { display: grid; grid-template-columns: 1fr 1fr; gap: 0.35rem 0.6rem; }
        .strength-check { display: flex; align-items: center; gap: 0.35rem; font-size: 0.72rem; color: #94a3b8; }
        .strength-dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 700;
            color: #ffffff;
            flex-shrink: 0;
        }
        .strength-check.pass { color: #065f46; }
        .strength-check.pass .strength-dot { background: #10b981; }
        .confirm-hint {
            margin-top: 0.35rem;
            font-size: 0.72rem;
            font-weight: 700;
            display: none;
            align-items: center;
            gap: 0.3rem;
        }
        .confirm-hint.show { display: inline-flex; }
        .confirm-hint.pass { color: #059669; }
        .confirm-hint.fail { color: #ef4444; }
        #perananGroup.is-invalid,
        .upload-card.is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.12) !important;
        }
        .upload-card.is-invalid {
            border-style: solid;
            background: linear-gradient(180deg, #ffe4e6, #ffeef0) !important;
        }
        .upload-card.is-invalid .upload-icon {
            background: #fecdd3;
            color: #be123c;
        }
        .upload-card.is-invalid input[type='file'] {
            border-color: #fda4af;
            background: #fff8f8;
        }
        .upload-card .invalid-feedback {
            text-align: left;
            margin-top: 0.5rem;
            line-height: 1.35;
        }
        @media (max-width: 900px) {
            .reg-grid { grid-template-columns: 1fr; }
            .upload-grid { grid-template-columns: 1fr; }
            .strength-checks { grid-template-columns: 1fr; }
        }
    </style>

    <h2 class="login-card-title" style="margin-bottom:0.4rem;">Borang Pendaftaran Pengguna</h2>
    <p style="font-size:0.83rem;color:#64748b;margin-bottom:1.1rem;line-height:1.5;">Halaman ini khusus untuk pengisian borang penuh. Semua dokumen muat naik perlu dipilih sebelum borang boleh dihantar.</p>

        <form method="POST" action="{{ route('register.submit') }}" enctype="multipart/form-data" id="fullRegisterForm" novalidate autocomplete="off" style="max-width:900px;margin:2.5rem auto 0 auto;width:100%;background:none;box-shadow:none;border:none;padding:0;">
            @csrf
            <x-validation-summary id="registerValidationSummary" title="Sila lengkapkan maklumat yang diperlukan." />
            <input type="text" name="fake_username" value="" autocomplete="username" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:0;width:0;pointer-events:none;">
            <input type="password" name="fake_password" value="" autocomplete="new-password" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:0;width:0;pointer-events:none;">
            <input type="hidden" name="jenis_permohonan" value="{{ old('jenis_permohonan', $jenis_permohonan ?? '') }}">
            <input type="hidden" name="ic_number" value="{{ old('ic_number', $ic_number ?? '') }}">
            <input type="hidden" name="no_tentera" value="{{ old('no_tentera', $no_tentera ?? '') }}">
            <input type="hidden" name="source" id="sourceField" value="{{ old('source', 'BTM') }}">

            <x-input
                label="Jenis Permohonan"
                name="jenis_permohonan_display"
                :value="str_replace('_', ' ', old('jenis_permohonan', $jenis_permohonan ?? ''))"
                readonly
            />

            <x-input
                label="No. Kad Pengenalan"
                name="identifier_display"
                :value="old('identifier', $identifier ?? '')"
                readonly
            />

            <x-select
                label="Kategori Permohonan"
                name="kategori_permohonan"
                id="kategoriPermohonan"
                required
                placeholder="-- Sila Pilih --"
                :value="old('kategori_permohonan', 'pengguna')"
            >
                <option value="agensi">Agensi</option>
                <option value="pengguna">Pengguna</option>
            </x-select>

            <x-input
                label="Nama Penuh"
                name="name"
                id="name"
                required
                autocomplete="name"
                placeholder="Masukkan nama penuh"
                :value="old('name')"
            />

            <x-input
                label="Emel"
                name="email"
                id="email"
                type="email"
                required
                autocomplete="new-email"
                placeholder="Contoh: nama@domain.com"
                :value="old('email')"
            />

            <div class="reg-grid" style="margin-top:0.95rem;">
                <x-select
                    label="Jabatan/Bahagian"
                    name="selectJabatan"
                    id="selectJabatan"
                    placeholder="-- Sila Pilih --"
                    :value="old('jabatan_id_temp')"
                >
                </x-select>
                <x-select
                    label="Bahagian/Unit"
                    name="selectBahagian"
                    id="selectBahagian"
                    placeholder="-- Sila Pilih --"
                    :value="old('bahagian_unit_id_temp')"
                />
                <input type="hidden" name="jabatan_bahagian" id="jabatanBahagianHidden" value="{{ old('jabatan_bahagian') }}">
                <input type="hidden" name="jabatan_id_temp" id="jabatanIdTemp" value="{{ old('jabatan_id_temp') }}">
                <input type="hidden" name="bahagian_unit" id="bahagianUnitHidden" value="{{ old('bahagian_unit') }}">
                <input type="hidden" name="bahagian_unit_id_temp" id="bahagianUnitIdTemp" value="{{ old('bahagian_unit_id_temp') }}">
            </div>

            <div class="reg-grid" style="margin-top:0.95rem;">
                <x-input
                    label="Telefon Pejabat"
                    name="telefon_pejabat"
                    id="telefon_pejabat"
                    :value="old('telefon_pejabat')"
                />
                <x-input
                    label="Telefon Bimbit"
                    name="telefon_bimbit"
                    id="telefon_bimbit"
                    :value="old('telefon_bimbit')"
                />
            </div>

            <div class="form-group" style="margin-top:0.95rem;">
                <x-form.label for="perananGroup" text="Peranan" :required="true" />
                <div id="perananGroup" style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem 0.85rem;border:1px solid #e2e8f0;border-radius:12px;padding:0.8rem 0.9rem;background:#fcfdff;">
                    <label style="display:flex;align-items:center;gap:0.45rem;font-size:0.84rem;color:#334155;"><input type="checkbox" name="peranan[]" value="admin" {{ in_array('admin', old('peranan', [])) ? 'checked' : '' }}> Admin</label>
                    <label style="display:flex;align-items:center;gap:0.45rem;font-size:0.84rem;color:#334155;"><input type="checkbox" name="peranan[]" value="pendaftar_kontrak" {{ in_array('pendaftar_kontrak', old('peranan', [])) ? 'checked' : '' }}> Pentadbir Kontrak</label>
                    <label style="display:flex;align-items:center;gap:0.45rem;font-size:0.84rem;color:#334155;"><input type="checkbox" name="peranan[]" value="pemilik_projek" {{ in_array('pemilik_projek', old('peranan', [])) ? 'checked' : '' }}> Pemilik Projek</label>
                    <label style="display:flex;align-items:center;gap:0.45rem;font-size:0.84rem;color:#334155;"><input type="checkbox" name="peranan[]" value="admin_sistem" {{ in_array('admin_sistem', old('peranan', [])) ? 'checked' : '' }}> Admin Sistem</label>
                    <label style="display:flex;align-items:center;gap:0.45rem;font-size:0.84rem;color:#334155;"><input type="checkbox" name="peranan[]" value="pegawai_undang_undang" {{ in_array('pegawai_undang_undang', old('peranan', [])) ? 'checked' : '' }}> Pegawai Undang-Undang</label>
                </div>
                @error('peranan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @error('peranan.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <x-select
                label="Akses"
                name="akses_scope"
                id="akses_scope"
                placeholder="--- Sila Pilih ---"
                :value="old('akses_scope')"
            >
                <option value="PTJ">PTJ</option>
                <option value="AGENSI">Agensi</option>
                <option value="KEMENTERIAN">Kementerian</option>
            </x-select>

            <div class="reg-grid" style="margin-top:0.95rem;">
                <div class="form-group">
                    <x-input
                        label="Kata Laluan"
                        name="password"
                        id="password"
                        type="password"
                        required
                        autocomplete="new-password"
                    />
                    <div class="strength-wrap" id="passwordStrength">
                        <div class="strength-bar">
                            <span class="strength-segment"></span>
                            <span class="strength-segment"></span>
                            <span class="strength-segment"></span>
                            <span class="strength-segment"></span>
                            <span class="strength-segment"></span>
                        </div>
                        <div class="strength-meta" id="passwordStrengthMeta">Kekuatan: Lemah</div>
                        <div class="strength-checks" id="passwordChecks">
                            <div class="strength-check" data-rule="minLength"><span class="strength-dot"></span>Minimum 12 aksara</div>
                            <div class="strength-check" data-rule="uppercase"><span class="strength-dot"></span>Huruf besar (A-Z)</div>
                            <div class="strength-check" data-rule="lowercase"><span class="strength-dot"></span>Huruf kecil (a-z)</div>
                            <div class="strength-check" data-rule="number"><span class="strength-dot"></span>Nombor (0-9)</div>
                            <div class="strength-check" data-rule="special"><span class="strength-dot"></span>Aksara khas (!@#$%...)</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <x-input
                        label="Taip Semula Kata Laluan"
                        name="password_confirmation"
                        id="passwordConfirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                    />
                    <p class="confirm-hint" id="passwordConfirmHint"></p>
                </div>
            </div>

            <div style="margin-top:1.1rem; font-size:0.88rem; font-weight:700; color:#0f172a;">Lampiran</div>
            <div class="upload-grid">
                <div class="upload-card">
                    <div class="upload-head">
                        <span class="upload-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <path d="M14 2v6h6"></path>
                            </svg>
                        </span>
                        <div>
                            <div class="upload-title">Borang Permohonan</div>
                            <div class="upload-sub">Format: PDF / DOC / DOCX</div>
                        </div>
                    </div>
                    <input type="file" name="lampiran_borang_permohonan" accept=".pdf,.doc,.docx" required>
                    @error('lampiran_borang_permohonan')<div class="invalid-feedback" style="text-align:left;">{{ $message }}</div>@enderror
                </div>
                <div class="upload-card">
                    <div class="upload-head">
                        <span class="upload-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                <path d="M2 10h20"></path>
                            </svg>
                        </span>
                        <div>
                            <div class="upload-title">Salinan Kad Pengenalan / Tentera</div>
                            <div class="upload-sub">Format: PDF / JPG / PNG</div>
                        </div>
                    </div>
                    <input type="file" name="lampiran_kp_tentera" accept=".pdf,.jpg,.jpeg,.png" required>
                    @error('lampiran_kp_tentera')<div class="invalid-feedback" style="text-align:left;">{{ $message }}</div>@enderror
                </div>
                <div class="upload-card">
                    <div class="upload-head">
                        <span class="upload-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                                <path d="M9 8h6"></path>
                                <path d="M8 13h8"></path>
                            </svg>
                        </span>
                        <div>
                            <div class="upload-title">Pas Pekerja</div>
                            <div class="upload-sub">Format: PDF / JPG / PNG</div>
                        </div>
                    </div>
                    <input type="file" name="lampiran_pas_pekerja" accept=".pdf,.jpg,.jpeg,.png" required>
                    @error('lampiran_pas_pekerja')<div class="invalid-feedback" style="text-align:left;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="btn-group" style="margin-top:0.95rem;">
                <x-button type="submit" variant="primary" full>
                    HANTAR PERMOHONAN
                </x-button>
                <a href="{{ route('register') }}" class="ds-btn ds-btn-secondary ds-btn-full" style="text-decoration:none;">
                    KEMBALI
                </a>
            </div>
        </form>

    <script>
        (function () {
            const sourceField = document.getElementById('sourceField');
            const kategoriPermohonan = document.getElementById('kategoriPermohonan');
            const selectJabatan = document.getElementById('selectJabatan');
            const selectBahagian = document.getElementById('selectBahagian');
            const jabatanBahagianHidden = document.getElementById('jabatanBahagianHidden');
            const bahagianUnitHidden = document.getElementById('bahagianUnitHidden');
            const jabatanIdTemp = document.getElementById('jabatanIdTemp');
            const bahagianUnitIdTemp = document.getElementById('bahagianUnitIdTemp');
            const form = document.getElementById('fullRegisterForm');
            const jabatanApiUrl = @json(config('api.base_url') . '/ref/jabatan');

            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('passwordConfirmation');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordStrengthMeta = document.getElementById('passwordStrengthMeta');
            const passwordChecks = document.getElementById('passwordChecks');
            const passwordConfirmHint = document.getElementById('passwordConfirmHint');

            const nameInput = form.querySelector('input[name="name"]');
            const emailInput = form.querySelector('input[name="email"]');
            const perananGroup = document.getElementById('perananGroup');
            const perananCheckboxes = form.querySelectorAll('input[name="peranan[]"]');
            const lampiranBorangInput = form.querySelector('input[name="lampiran_borang_permohonan"]');
            const lampiranKpInput = form.querySelector('input[name="lampiran_kp_tentera"]');
            const lampiranPasInput = form.querySelector('input[name="lampiran_pas_pekerja"]');
            const registerSchema = {
                kategori_permohonan: [
                    { type: 'required', message: 'Kategori permohonan perlu dipilih.', label: 'Kategori permohonan perlu dipilih.' },
                ],
                name: [
                    { type: 'required', message: 'Nama penuh diperlukan.', label: 'Nama penuh diperlukan.' },
                    { type: 'maxLength', value: 255, message: 'Nama penuh tidak boleh melebihi 255 aksara.', label: 'Nama penuh tidak boleh melebihi 255 aksara.' },
                ],
                email: [
                    { type: 'required', message: 'Emel diperlukan.', label: 'Emel diperlukan.' },
                    { type: 'email', message: 'Format emel tidak sah.', label: 'Format emel tidak sah.' },
                ],
                'peranan[]': [
                    { type: 'required', message: 'Sila pilih sekurang-kurangnya satu peranan.', label: 'Sila pilih sekurang-kurangnya satu peranan.' },
                ],
                password: [
                    { type: 'required', message: 'Kata laluan diperlukan.', label: 'Kata laluan diperlukan.' },
                    { type: 'minLength', value: 12, message: 'Kata laluan mestilah sekurang-kurangnya 12 aksara.', label: 'Kata laluan mestilah sekurang-kurangnya 12 aksara.' },
                    { type: 'pattern', value: '(?=.*[A-Z])', message: 'Kata laluan mesti mengandungi huruf besar.', label: 'Kata laluan mesti mengandungi huruf besar.' },
                    { type: 'pattern', value: '(?=.*[a-z])', message: 'Kata laluan mesti mengandungi huruf kecil.', label: 'Kata laluan mesti mengandungi huruf kecil.' },
                    { type: 'pattern', value: '(?=.*[0-9])', message: 'Kata laluan mesti mengandungi nombor.', label: 'Kata laluan mesti mengandungi nombor.' },
                    { type: 'pattern', value: '(?=.*[!@#$%^&*()_+\\-=\\[\\]{};\':"\\\\|,.<>\/?])', message: 'Kata laluan mesti mengandungi aksara khas.', label: 'Kata laluan mesti mengandungi aksara khas.' },
                ],
                password_confirmation: [
                    { type: 'required', message: 'Taip semula kata laluan diperlukan.', label: 'Taip semula kata laluan diperlukan.' },
                    { type: 'sameAs', value: 'password', message: 'Taip semula kata laluan mesti sepadan dengan kata laluan.', label: 'Taip semula kata laluan mesti sepadan dengan kata laluan.' },
                ],
                lampiran_borang_permohonan: [
                    { type: 'fileRequired', message: 'Borang permohonan wajib dimuat naik.', label: 'Borang permohonan wajib dimuat naik.' },
                    { type: 'fileType', value: ['pdf', 'doc', 'docx'], message: 'Format fail borang permohonan tidak dibenarkan.', label: 'Format fail borang permohonan tidak dibenarkan.' },
                    { type: 'fileMaxKb', value: 5120, message: 'Saiz fail borang permohonan melebihi had maksimum.', label: 'Saiz fail borang permohonan melebihi had maksimum.' },
                ],
                lampiran_kp_tentera: [
                    { type: 'fileRequired', message: 'Salinan kad pengenalan/tentera wajib dimuat naik.', label: 'Salinan kad pengenalan/tentera wajib dimuat naik.' },
                    { type: 'fileType', value: ['pdf', 'jpg', 'jpeg', 'png'], message: 'Format fail kad pengenalan/tentera tidak dibenarkan.', label: 'Format fail kad pengenalan/tentera tidak dibenarkan.' },
                    { type: 'fileMaxKb', value: 5120, message: 'Saiz fail kad pengenalan/tentera melebihi had maksimum.', label: 'Saiz fail kad pengenalan/tentera melebihi had maksimum.' },
                ],
                lampiran_pas_pekerja: [
                    { type: 'fileRequired', message: 'Pas pekerja wajib dimuat naik.', label: 'Pas pekerja wajib dimuat naik.' },
                    { type: 'fileType', value: ['pdf', 'jpg', 'jpeg', 'png'], message: 'Format fail pas pekerja tidak dibenarkan.', label: 'Format fail pas pekerja tidak dibenarkan.' },
                    { type: 'fileMaxKb', value: 5120, message: 'Saiz fail pas pekerja melebihi had maksimum.', label: 'Saiz fail pas pekerja melebihi had maksimum.' },
                ],
            };

            function ensureFeedbackNode(target) {
                if (target.classList && target.classList.contains('upload-card')) {
                    let inlineNode = target.querySelector('.runtime-feedback');
                    if (!inlineNode) {
                        inlineNode = document.createElement('div');
                        inlineNode.className = 'invalid-feedback runtime-feedback';
                        inlineNode.style.display = 'none';
                        target.appendChild(inlineNode);
                    }
                    return inlineNode;
                }

                const next = target.nextElementSibling;
                if (next && next.classList && next.classList.contains('invalid-feedback')) {
                    return next;
                }

                const node = document.createElement('div');
                node.className = 'invalid-feedback';
                node.style.display = 'none';
                target.insertAdjacentElement('afterend', node);
                return node;
            }

            function markError(target, message) {
                target.classList.add('is-invalid');
                const feedback = ensureFeedbackNode(target);
                feedback.textContent = message;
                feedback.style.display = 'block';
            }

            function clearError(target) {
                target.classList.remove('is-invalid');

                if (target.classList && target.classList.contains('upload-card')) {
                    const inlineNode = target.querySelector('.runtime-feedback');
                    if (inlineNode) {
                        inlineNode.textContent = '';
                        inlineNode.style.display = 'none';
                    }
                    return;
                }

                const next = target.nextElementSibling;
                if (next && next.classList && next.classList.contains('invalid-feedback')) {
                    next.textContent = '';
                    next.style.display = 'none';
                }
            }

            function getPasswordChecks(value) {
                return {
                    minLength: value.length >= 12,
                    uppercase: /[A-Z]/.test(value),
                    lowercase: /[a-z]/.test(value),
                    number: /[0-9]/.test(value),
                    special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>/?]/.test(value),
                };
            }

            function renderPasswordStrength() {
                const value = passwordInput.value || '';
                const checks = getPasswordChecks(value);
                const passed = Object.values(checks).filter(Boolean).length;

                if (value.length === 0) {
                    passwordStrength.classList.remove('show');
                    return;
                }

                passwordStrength.classList.add('show');

                let color = '#ef4444';
                let label = 'Kekuatan: Lemah';
                let metaClass = 'weak';

                if (passed <= 2) {
                    color = '#ef4444';
                    label = 'Kekuatan: Lemah';
                    metaClass = 'weak';
                } else if (passed <= 3) {
                    color = '#eab308';
                    label = 'Kekuatan: Sederhana';
                    metaClass = 'medium';
                } else if (passed <= 4) {
                    color = '#2563eb';
                    label = 'Kekuatan: Kuat';
                    metaClass = 'strong';
                } else {
                    color = '#059669';
                    label = 'Kekuatan: Sangat Kuat';
                    metaClass = 'very-strong';
                }

                passwordStrengthMeta.textContent = label;
                passwordStrengthMeta.className = 'strength-meta ' + metaClass;

                const segments = passwordStrength.querySelectorAll('.strength-segment');
                segments.forEach(function (segment, index) {
                    segment.style.background = index < passed ? color : '#cbd5e1';
                });

                passwordChecks.querySelectorAll('.strength-check').forEach(function (item) {
                    const rule = item.getAttribute('data-rule');
                    const dot = item.querySelector('.strength-dot');
                    if (checks[rule]) {
                        item.classList.add('pass');
                        dot.textContent = '\u2713';
                    } else {
                        item.classList.remove('pass');
                        dot.textContent = '';
                    }
                });
            }

            function renderPasswordConfirmationHint() {
                const password = passwordInput.value || '';
                const confirmPassword = passwordConfirmationInput.value || '';

                if (!confirmPassword.length) {
                    passwordConfirmHint.className = 'confirm-hint';
                    passwordConfirmHint.textContent = '';
                    return;
                }

                if (password === confirmPassword) {
                    passwordConfirmHint.className = 'confirm-hint show pass';
                    passwordConfirmHint.textContent = 'Kata laluan sepadan';
                } else {
                    passwordConfirmHint.className = 'confirm-hint show fail';
                    passwordConfirmHint.textContent = 'Kata laluan tidak sepadan';
                }
            }

            function validateBeforeSubmit() {
                const result = ValidationService.validate(form, registerSchema, 'registerValidationSummary');
                return result.valid;
            }

            function validateLive(target, validatorFn, message) {
                const fieldName = target?.name || null;
                if (!fieldName || !registerSchema[fieldName]) {
                    if (validatorFn()) {
                        clearError(target);
                    } else {
                        markError(target, message);
                    }
                    return;
                }
                ValidationService.validate(form, { [fieldName]: registerSchema[fieldName] }, null, {
                    showToast: false,
                    scrollToError: false,
                });
            }

            function syncSource() {
                sourceField.value = kategoriPermohonan.value === 'agensi' ? 'AGENSI' : 'BTM';
            }

            async function loadBahagian(jabatanId, selectedId = null) {
                if (!jabatanId) {
                    selectedId = selectedId || bahagianUnitIdTemp.value || null;
                }

                selectBahagian.innerHTML = '<option value="">Memuatkan...</option>';

                const res = await fetch(`{{ route('register.bahagian-unit') }}?jabatan_id=${jabatanId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                const list = data.data || [];

                selectBahagian.innerHTML = '<option value="">-- Sila Pilih --</option>';
                list.forEach(function (item) {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.dataset.name = item.nama;
                    option.textContent = item.nama;
                    if (selectedId && String(selectedId) === String(item.id)) {
                        option.selected = true;
                    }
                    selectBahagian.appendChild(option);
                });

                const selectedBahagian = selectBahagian.options[selectBahagian.selectedIndex];
                bahagianUnitHidden.value = (selectedBahagian && selectedBahagian.dataset && selectedBahagian.dataset.name) || '';
                bahagianUnitIdTemp.value = selectBahagian.value || '';
            }

            async function loadJabatanOptions() {
                const selectedJabatanId = jabatanIdTemp.value || '';
                const selectedJabatanName = jabatanBahagianHidden.value || '';

                selectJabatan.innerHTML = '<option value="">Memuatkan...</option>';

                try {
                    const res = await fetch(jabatanApiUrl, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'include',
                    });

                    const data = await res.json();
                    const list = Array.isArray(data.data) ? data.data : [];

                    selectJabatan.innerHTML = '<option value="">-- Sila Pilih --</option>';
                    list.forEach(function (item) {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.dataset.name = item.nama;
                        option.textContent = item.nama;
                        if (selectedJabatanId && String(selectedJabatanId) === String(item.id)) {
                            option.selected = true;
                        }
                        selectJabatan.appendChild(option);
                    });

                    if (selectedJabatanId) {
                        const option = selectJabatan.options[selectJabatan.selectedIndex];
                        jabatanBahagianHidden.value = (option && option.dataset && option.dataset.name) || selectedJabatanName;
                        jabatanIdTemp.value = selectJabatan.value || selectedJabatanId;
                    }
                } catch (error) {
                    selectJabatan.innerHTML = '<option value="">-- Gagal memuatkan senarai --</option>';
                    console.error('Gagal memuatkan senarai jabatan:', error);
                }
            }

            selectJabatan.addEventListener('change', function () {
                const option = this.options[this.selectedIndex];
                jabatanBahagianHidden.value = (option && option.dataset && option.dataset.name) || '';
                jabatanIdTemp.value = this.value || '';
                loadBahagian(this.value);
            });

            selectBahagian.addEventListener('change', function () {
                const option = this.options[this.selectedIndex];
                bahagianUnitHidden.value = (option && option.dataset && option.dataset.name) || '';
                bahagianUnitIdTemp.value = this.value || '';
            });

            kategoriPermohonan.addEventListener('change', function () {
                validateLive(kategoriPermohonan, function () { return !!kategoriPermohonan.value; }, 'Kategori Permohonan diperlukan.');
                syncSource();
            });

            nameInput.addEventListener('input', function () {
                validateLive(nameInput, function () { return !!nameInput.value.trim(); }, 'Nama Penuh diperlukan.');
            });

            emailInput.addEventListener('input', function () {
                validateLive(emailInput, function () { return !!emailInput.value.trim(); }, 'Emel diperlukan.');
            });

            perananCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    validateLive(perananGroup, function () {
                        return form.querySelectorAll('input[name="peranan[]"]:checked').length > 0;
                    }, 'Sila pilih sekurang-kurangnya satu Peranan.');
                });
            });

            lampiranBorangInput.addEventListener('change', function () {
                validateLive(lampiranBorangInput.closest('.upload-card'), function () {
                    return lampiranBorangInput.files.length > 0;
                }, 'Lampiran Borang Permohonan diperlukan.');
            });

            lampiranKpInput.addEventListener('change', function () {
                validateLive(lampiranKpInput.closest('.upload-card'), function () {
                    return lampiranKpInput.files.length > 0;
                }, 'Lampiran Salinan Kad Pengenalan/Tentera diperlukan.');
            });

            lampiranPasInput.addEventListener('change', function () {
                validateLive(lampiranPasInput.closest('.upload-card'), function () {
                    return lampiranPasInput.files.length > 0;
                }, 'Lampiran Pas Pekerja diperlukan.');
            });

            form.addEventListener('submit', function (event) {
                const jab = selectJabatan.options[selectJabatan.selectedIndex];
                const bah = selectBahagian.options[selectBahagian.selectedIndex];
                jabatanBahagianHidden.value = (jab && jab.dataset && jab.dataset.name) || '';
                bahagianUnitHidden.value = (bah && bah.dataset && bah.dataset.name) || '';
                jabatanIdTemp.value = selectJabatan.value || '';
                bahagianUnitIdTemp.value = selectBahagian.value || '';
                syncSource();

                if (!validateBeforeSubmit()) {
                    event.preventDefault();
                }
            });

            loadJabatanOptions().then(function () {
                if (jabatanIdTemp.value) {
                    loadBahagian(jabatanIdTemp.value, bahagianUnitIdTemp.value);
                    const jab = selectJabatan.options[selectJabatan.selectedIndex];
                    jabatanBahagianHidden.value = (jab && jab.dataset && jab.dataset.name) || jabatanBahagianHidden.value;
                } else {
                    loadBahagian('', bahagianUnitIdTemp.value);
                }
            });

            passwordInput.addEventListener('input', function () {
                renderPasswordStrength();
                renderPasswordConfirmationHint();

                if (!passwordInput.value) {
                    clearError(passwordInput);
                    return;
                }

                const allChecksPassed = Object.values(getPasswordChecks(passwordInput.value)).every(Boolean);
                validateLive(passwordInput, function () { return allChecksPassed; }, 'Kata Laluan mesti minimum 12 aksara, huruf besar, huruf kecil, nombor dan aksara khas.');
            });

            passwordConfirmationInput.addEventListener('input', function () {
                renderPasswordConfirmationHint();

                if (!passwordConfirmationInput.value) {
                    clearError(passwordConfirmationInput);
                    return;
                }

                validateLive(passwordConfirmationInput, function () {
                    return passwordInput.value === passwordConfirmationInput.value;
                }, 'Taip Semula Kata Laluan mesti sepadan dengan Kata Laluan.');
            });

            syncSource();
            renderPasswordStrength();
            renderPasswordConfirmationHint();
            ValidationService.bindRealtime(form, registerSchema);
        })();
    </script>
</x-layouts.guest>
