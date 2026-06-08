<x-layouts.guest title="Log Masuk">

    <h2 class="login-card-title mb-6">Log Masuk Pengguna</h2>

    <form method="POST" action="/login" id="loginForm">
        @csrf

        <x-validation-summary id="loginValidationSummary" title="Sila lengkapkan maklumat yang diperlukan." />

        <div class="form-fields mb-6">

            <x-input
                label="No. Kad Pengenalan"
                name="ic_number"
                id="ic_number"
                required
                maxlength="12"
                minlength="12"
                pattern="[0-9]{12}"
                inputmode="numeric"
                autocomplete="username"
                placeholder="Contoh: 890101145678"
            />

            <x-input
                label="Kata Laluan"
                name="password"
                id="password"
                type="password"
                required
                autocomplete="current-password"
            />

            {{-- Daftar + Lupa Kata Laluan --}}
            <div class="forgot-row">
                <a href="{{ route('register') }}" class="form-link">Daftar Pengguna Baru</a>
                <a href="{{ route('password.reset.request') }}" class="form-link">Lupa Kata Laluan?</a>
            </div>

        </div>

        {{-- Buttons --}}
        <div class="btn-group gap-2">
            <x-button type="submit" variant="primary">
                LOG MASUK
            </x-button>
            <x-button type="button" variant="secondary" onclick="resetForm()">
                RESET
            </x-button>
        </div>

    </form>

    <script>
        function resetForm() {
            document.getElementById('ic_number').value = '';
            document.getElementById('password').value = '';
            document.getElementById('ic_number').focus();
        }

        ValidationService.mount('loginForm', {
            summaryId: 'loginValidationSummary',
            schema: {
                ic_number: [
                    { type: 'required', message: 'Nombor IC diperlukan.', label: 'Nombor IC diperlukan.' },
                    { type: 'ic12', message: 'Nombor IC mestilah 12 digit tanpa sempang.', label: 'Nombor IC mestilah 12 digit tanpa sempang.' },
                ],
                password: [
                    { type: 'required', message: 'Kata laluan diperlukan.', label: 'Kata laluan diperlukan.' },
                ],
            },
        });

        document.getElementById('ic_number').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>

</x-layouts.guest>
