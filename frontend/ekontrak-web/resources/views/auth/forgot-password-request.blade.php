<x-layouts.guest title="Tukar Kata Laluan">

    <h2 class="login-card-title mb-6">Tukar Kata Laluan</h2>

    <form method="POST" action="{{ route('password.reset.send') }}" id="forgotPasswordRequestForm">
        @csrf

        <x-validation-summary id="forgotPasswordRequestSummary" title="Sila lengkapkan maklumat yang diperlukan." />

        <div class="form-fields mb-6">
            <x-input
                label="Emel Berdaftar"
                name="email"
                id="email"
                type="email"
                required
                autocomplete="email"
                placeholder="nama@agensi.gov.my"
            />
        </div>

        <div class="btn-group gap-2">
            <x-button type="submit" variant="primary">
                HANTAR TOKEN
            </x-button>
            <x-button type="button" variant="secondary" onclick="window.location='{{ route('login') }}'">
                KEMBALI
            </x-button>
        </div>
    </form>

    <script>
        ValidationService.mount('forgotPasswordRequestForm', {
            summaryId: 'forgotPasswordRequestSummary',
            schema: {
                email: [
                    { type: 'required', message: 'Emel berdaftar diperlukan.', label: 'Emel berdaftar diperlukan.' },
                    { type: 'email', message: 'Format emel tidak sah.', label: 'Format emel tidak sah.' },
                ],
            },
        });
    </script>

</x-layouts.guest>