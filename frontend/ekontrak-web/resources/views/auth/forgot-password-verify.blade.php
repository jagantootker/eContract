<x-layouts.guest title="Pengesahan Token">

    <h2 class="login-card-title mb-6">Pengesahan Token</h2>

    @if(!empty($debugToken))
        <div class="alert-box alert-box--warning mb-4" role="status" aria-live="polite">
            <strong>Token Ujian:</strong> {{ $debugToken }}
            <div>Sila gunakan token ini untuk pengesahan kerana pelayan emel tidak dapat dicapai.</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.reset.verify') }}" id="forgotPasswordVerifyForm">
        @csrf

        <x-validation-summary id="forgotPasswordVerifySummary" title="Sila lengkapkan maklumat yang diperlukan." />

        <div class="form-fields mb-6">
            <x-input
                label="Emel"
                name="email"
                id="email"
                type="email"
                required
                autocomplete="email"
                value="{{ $email }}"
                placeholder="nama@agensi.gov.my"
            />

            <x-input
                label="Token Pengesahan"
                name="token"
                id="token"
                required
                maxlength="6"
                inputmode="numeric"
                value="{{ $debugToken }}"
                placeholder="Contoh: 483921"
            />
        </div>

        <div class="btn-group gap-2">
            <x-button type="submit" variant="primary">
                SAHKAN TOKEN
            </x-button>
            <x-button type="button" variant="secondary" onclick="window.location='{{ route('password.reset.request') }}'">
                KEMBALI
            </x-button>
        </div>
    </form>

    <script>
        ValidationService.mount('forgotPasswordVerifyForm', {
            summaryId: 'forgotPasswordVerifySummary',
            schema: {
                email: [
                    { type: 'required', message: 'Emel berdaftar diperlukan.', label: 'Emel berdaftar diperlukan.' },
                    { type: 'email', message: 'Format emel tidak sah.', label: 'Format emel tidak sah.' },
                ],
                token: [
                    { type: 'required', message: 'Token pengesahan diperlukan.', label: 'Token pengesahan diperlukan.' },
                    { type: 'pattern', value: '^\\d{6}$', message: 'Token pengesahan mesti 6 digit.', label: 'Token pengesahan mesti 6 digit.' },
                ],
            },
        });

        document.getElementById('token').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    </script>

</x-layouts.guest>