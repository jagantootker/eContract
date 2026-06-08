@extends('components.layouts.app')

@section('title', 'Tukar Kata Laluan')
@section('breadcrumb', 'Tukar Kata Laluan')

@section('content')
<div style="max-width:620px;margin:0 auto;">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="page-title">Tukar Kata Laluan</div>
                <div class="page-subtitle">Kemaskini kata laluan akaun anda dengan standard keselamatan semasa.</div>
            </div>
        </div>

        <div class="card-body">
            <div class="section-hdr">Maklumat Keselamatan Akaun</div>

            <form method="POST" action="{{ route('change-password.update') }}" class="change-password-form" id="changePasswordForm">
            @csrf

            <x-validation-summary id="changePasswordValidationSummary" title="Sila lengkapkan maklumat yang diperlukan." />

                <x-input
                    label="{{ ($forcePasswordChange ?? false) ? 'Kata Laluan Sementara' : 'Kata Laluan Semasa' }}"
                    name="current_password"
                    id="current_password"
                    type="password"
                    required
                />

                <x-input
                    label="Kata Laluan Baru"
                    name="new_password"
                    id="new_password"
                    type="password"
                    required
                    oninput="checkStrength(this.value)"
                />

                <div class="strength-block">
                    <div class="strength-track">
                        <div id="strength-bar" class="strength-fill"></div>
                    </div>
                    <p id="strength-label" class="strength-label"></p>
                </div>

                <div class="strength-checks">
                    <div class="req-item" id="req-length">x Minimum 12 aksara</div>
                    <div class="req-item" id="req-upper">x Huruf besar (A-Z)</div>
                    <div class="req-item" id="req-lower">x Huruf kecil (a-z)</div>
                    <div class="req-item" id="req-number">x Nombor (0-9)</div>
                    <div class="req-item" id="req-special">x Aksara khas (@$!%*?&)</div>
                </div>

                <x-input
                    label="Taip Semula Kata Laluan Baru"
                    name="new_password_confirmation"
                    id="new_password_confirmation"
                    type="password"
                    required
                />

                <x-button type="submit" variant="primary" full>
                    KEMASKINI KATA LALUAN
                </x-button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .change-password-form { margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem; }
    .strength-block { display: flex; flex-direction: column; gap: 0.35rem; margin-top: -0.15rem; }
    .strength-track { height: 5px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
    .strength-fill { height: 100%; width: 0%; border-radius: 999px; transition: all 0.3s; }
    .strength-label { font-size: 0.75rem; color: #9ca3af; margin: 0; min-height: 1rem; }
    .strength-checks { display: grid; grid-template-columns: 1fr; gap: 0.35rem; padding-top: 0.1rem; }
    .req-item { display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; color: #9ca3af; transition: color .2s; line-height: 1.3; }
    .req-item.met { color:#16a34a; }

    @media (max-width: 680px) {
        .req-item { font-size:.72rem; }
        .strength-label { font-size: 0.72rem; }
    }
</style>
@endpush

@push('scripts')
<script>
    ValidationService.mount('changePasswordForm', {
        summaryId: 'changePasswordValidationSummary',
        schema: {
            current_password: [
                { type: 'required', message: 'Kata laluan semasa diperlukan.', label: 'Kata laluan semasa diperlukan.' },
            ],
            new_password: [
                { type: 'required', message: 'Kata laluan baharu diperlukan.', label: 'Kata laluan baharu diperlukan.' },
                { type: 'minLength', value: 12, message: 'Kata laluan mestilah sekurang-kurangnya 12 aksara.', label: 'Kata laluan baharu mesti sekurang-kurangnya 12 aksara.' },
                { type: 'pattern', value: '(?=.*[A-Z])', message: 'Kata laluan mesti mengandungi huruf besar.', label: 'Kata laluan baharu mesti mengandungi huruf besar.' },
                { type: 'pattern', value: '(?=.*[a-z])', message: 'Kata laluan mesti mengandungi huruf kecil.', label: 'Kata laluan baharu mesti mengandungi huruf kecil.' },
                { type: 'pattern', value: '(?=.*[0-9])', message: 'Kata laluan mesti mengandungi nombor.', label: 'Kata laluan baharu mesti mengandungi nombor.' },
                { type: 'pattern', value: '(?=.*[@$!%*?&])', message: 'Kata laluan mesti mengandungi aksara khas.', label: 'Kata laluan baharu mesti mengandungi aksara khas.' },
            ],
            new_password_confirmation: [
                { type: 'required', message: 'Pengesahan kata laluan baharu diperlukan.', label: 'Pengesahan kata laluan baharu diperlukan.' },
                { type: 'sameAs', value: 'new_password', message: 'Pengesahan kata laluan baharu tidak sepadan.', label: 'Pengesahan kata laluan baharu tidak sepadan.' },
            ],
        },
    });

    function checkStrength(val) {
        const checks = {
            length:  val.length >= 12,
            upper:   /[A-Z]/.test(val),
            lower:   /[a-z]/.test(val),
            number:  /[0-9]/.test(val),
            special: /[@$!%*?&]/.test(val),
        };

        Object.entries(checks).forEach(([key, met]) => {
            const el = document.getElementById('req-' + key);
            if (el) {
                el.classList.toggle('met', met);
                el.textContent = (met ? 'OK ' : 'x ') + el.textContent.slice(2);
            }
        });

        const score = Object.values(checks).filter(Boolean).length;
        const bar   = document.getElementById('strength-bar');
        const label = document.getElementById('strength-label');

        const levels = [
            { pct: '0%',   color: '#e5e7eb', text: '' },
            { pct: '25%',  color: '#ef4444', text: 'Sangat lemah' },
            { pct: '50%',  color: '#f97316', text: 'Lemah' },
            { pct: '75%',  color: '#eab308', text: 'Sederhana' },
            { pct: '90%',  color: '#22c55e', text: 'Kuat' },
            { pct: '100%', color: '#16a34a', text: 'Sangat kuat' },
        ];

        const level = levels[score] || levels[0];
        bar.style.width       = level.pct;
        bar.style.background  = level.color;
        label.textContent     = level.text;
        label.style.color     = level.color;
    }
</script>
@endpush
