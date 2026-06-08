<x-layouts.guest title="Pendaftaran Akaun">
    @php
        $activeTab = session('register_status') ? 'semakan' : 'permohonan';
        $status = session('register_status', []);
    @endphp

    <style>
        .reg-tabs { display: flex; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; margin-bottom: 1.15rem; box-shadow: inset 0 1px 0 rgba(255,255,255,0.6); }
        .reg-tab-btn { flex: 1; border: 0; background: #f8fafc; color: #334155; font-weight: 700; font-size: 0.88rem; padding: 0.95rem 0.95rem; cursor: pointer; transition: background 0.18s ease, color 0.18s ease; }
        .reg-tab-btn.active { background: #0f4c81; color: #fff; }
        .reg-panel { display: none; border: 1px solid #dbe5f0; border-radius: 14px; padding: 1.15rem; background: #ffffff; box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06); }
        .reg-panel.active { display: block; }
        .reg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; }
        .status-pill { display: inline-flex; align-items: center; padding: 0.32rem 0.8rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700; }
        .status-pill.pending { background: #fef3c7; color: #b45309; }
        .status-pill.diluluskan { background: #dcfce7; color: #166534; }
        .status-pill.ditolak { background: #fee2e2; color: #991b1b; }
        .status-card { border: 1px solid #dbe5f0; border-radius: 12px; padding: 1rem; margin-top: 1rem; background: linear-gradient(180deg, #ffffff, #f8fbff); }
        @media (max-width: 768px) { .reg-grid { grid-template-columns: 1fr; } }
    </style>

    <h2 class="login-card-title" style="margin-bottom:0.45rem;">Pendaftaran Pengguna</h2>
    <p style="font-size:0.83rem;color:#64748b;line-height:1.5;margin-bottom:1rem;">Halaman ini hanya untuk memilih jenis permohonan atau semakan. Borang penuh dibuka pada halaman baharu.</p>

    <div class="reg-tabs">
        <button type="button" class="reg-tab-btn {{ $activeTab === 'permohonan' ? 'active' : '' }}" data-tab="permohonan">PERMOHONAN BARU</button>
        <button type="button" class="reg-tab-btn {{ $activeTab === 'semakan' ? 'active' : '' }}" data-tab="semakan">SEMAKAN PERMOHONAN</button>
    </div>

    <div id="panel-permohonan" class="reg-panel {{ $activeTab === 'permohonan' ? 'active' : '' }}">
        <p style="font-size:0.84rem;color:#475569;margin-bottom:1rem;line-height:1.5;">
            Pilih jenis permohonan dan masukkan nombor kad pengenalan/no. tentera. Klik TERUSKAN untuk membuka Borang Pendaftaran Pengguna pada halaman baharu.
        </p>
        <form method="GET" action="{{ route('register.form') }}" id="startRegisterForm">
            <x-validation-summary id="startRegisterValidationSummary" title="Sila lengkapkan maklumat yang diperlukan." />
            <x-select
                label="Jenis Permohonan"
                name="jenis_permohonan"
                id="jenis_permohonan"
                required
                placeholder="--- Sila Pilih ---"
            >
                <option value="pendaftaran_online">Pendaftaran Online</option>
                <option value="pengaktifan_semula_id">Pengaktifan Semula ID (Pertukaran PTJ)</option>
                <option value="penukaran_peranan">Penukaran Peranan</option>
            </x-select>

            <x-input
                label="No. Kad Pengenalan / No. Tentera"
                name="identifier"
                id="identifier"
                required
                placeholder="Contoh: 890101145678 atau T123456"
            />
            <div class="btn-group" style="display:grid;grid-template-columns:1fr 1fr;gap:0.65rem;padding-top:0.8rem;">
                <x-button type="submit" variant="primary" full>
                    TERUSKAN
                </x-button>
                <x-button type="reset" variant="secondary" full>
                    SET SEMULA
                </x-button>
            </div>
        </form>
    </div>

    <div id="panel-semakan" class="reg-panel {{ $activeTab === 'semakan' ? 'active' : '' }}">
        <p style="font-size:0.84rem;color:#475569;margin-bottom:0.9rem;line-height:1.45;">Semakan hanya menggunakan nombor kad pengenalan.</p>
        <form method="POST" action="{{ route('register.check') }}" id="checkRegisterForm">
            @csrf
            <x-validation-summary id="checkRegisterValidationSummary" title="Sila lengkapkan maklumat yang diperlukan." />
            <x-input
                label="No. Kad Pengenalan"
                name="ic_number"
                id="semak_ic_number"
                required
                maxlength="12"
                minlength="12"
                pattern="[0-9]{12}"
                inputmode="numeric"
                placeholder="Masukkan nombor IC"
            />
            <div class="btn-group" style="padding-top:0.65rem;">
                <x-button type="submit" variant="primary" full>
                    SEMAK STATUS
                </x-button>
            </div>
        </form>

        @if(!empty($status))
            <div class="status-card">
                <div class="reg-grid">
                    <div>
                        <div style="font-size:0.76rem;color:#64748b;">No. Rujukan</div>
                        <div style="font-size:0.95rem;font-weight:700;color:#0f172a;">{{ $status['no_rujukan'] ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.76rem;color:#64748b;">Nama Pemohon</div>
                        <div style="font-size:0.95rem;font-weight:700;color:#0f172a;">{{ $status['name'] ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.76rem;color:#64748b;">Tarikh Permohonan</div>
                        <div style="font-size:0.95rem;font-weight:700;color:#0f172a;">{{ $status['tarikh_permohonan'] ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.76rem;color:#64748b;">Status</div>
                        <span class="status-pill {{ $status['status'] ?? 'pending' }}">{{ ucfirst($status['status'] ?? 'pending') }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        (function () {
            const buttons = document.querySelectorAll('.reg-tab-btn');
            const panelPermohonan = document.getElementById('panel-permohonan');
            const panelSemakan = document.getElementById('panel-semakan');

            function switchTab(tab) {
                buttons.forEach((b) => b.classList.toggle('active', b.dataset.tab === tab));
                panelPermohonan.classList.toggle('active', tab === 'permohonan');
                panelSemakan.classList.toggle('active', tab === 'semakan');
            }

            buttons.forEach((btn) => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

            const startMount = ValidationService.mount('startRegisterForm', {
                summaryId: 'startRegisterValidationSummary',
                schema: {
                    jenis_permohonan: [
                        { type: 'required', message: 'Jenis permohonan diperlukan.', label: 'Jenis permohonan diperlukan.' },
                    ],
                    identifier: [
                        { type: 'required', message: 'Nombor kad pengenalan/no. tentera diperlukan.', label: 'Nombor kad pengenalan/no. tentera diperlukan.' },
                        { type: 'pattern', value: '^(\\d{12}|[A-Za-z][A-Za-z0-9]{3,19})$', message: 'Format pengenalan tidak sah.', label: 'Format nombor kad pengenalan/no. tentera tidak sah.' },
                    ],
                },
            });

            const identifierInput = document.getElementById('identifier');
            if (identifierInput) {
                identifierInput.addEventListener('input', function () {
                    const value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                    this.value = value;
                });
            }

            ValidationService.mount('checkRegisterForm', {
                summaryId: 'checkRegisterValidationSummary',
                schema: {
                    ic_number: [
                        { type: 'required', message: 'Nombor IC diperlukan.', label: 'Nombor IC diperlukan.' },
                        { type: 'ic12', message: 'Nombor IC mestilah 12 digit tanpa sempang.', label: 'Nombor IC mestilah 12 digit tanpa sempang.' },
                    ],
                },
            });

            const checkInput = document.getElementById('semak_ic_number');
            if (checkInput) {
                checkInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '');
                });
            }
        })();
    </script>
</x-layouts.guest>
