<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .swal2-container { z-index: 10050 !important; }
    .swal2-popup.ek-swal-popup {
        border-radius: 22px !important;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.24) !important;
        padding: 1.35rem 1.4rem 1.1rem !important;
    }
    .swal2-popup.ek-swal-popup.swal2-toast {
        border-radius: 18px !important;
        padding: 0.95rem 1rem !important;
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.18) !important;
    }
    .swal2-title.ek-swal-title {
        color: #0f172a !important;
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        font-size: 1.15rem !important;
    }
    .swal2-html-container.ek-swal-text {
        color: #475569 !important;
        font-size: 0.94rem !important;
        line-height: 1.55 !important;
    }
    .swal2-icon.ek-swal-icon {
        margin: 0.7rem auto 0.95rem !important;
        border-width: 0.2rem !important;
    }
    .swal2-actions.ek-swal-actions {
        gap: 0.65rem !important;
        margin-top: 1.1rem !important;
    }
    .swal2-confirm.ek-swal-confirm,
    .swal2-cancel.ek-swal-cancel {
        min-width: 124px !important;
        border-radius: 9999px !important;
        padding: 0.72rem 1.05rem !important;
        font-size: 0.84rem !important;
        font-weight: 700 !important;
        box-shadow: none !important;
    }
    .swal2-confirm.ek-swal-confirm {
        background: var(--blue, #2563eb) !important;
        border: 1px solid var(--blue, #2563eb) !important;
    }
    .swal2-confirm.ek-swal-confirm:hover {
        background: var(--blue-hover, #1d4ed8) !important;
        border-color: var(--blue-hover, #1d4ed8) !important;
    }
    .swal2-cancel.ek-swal-cancel {
        background: #e5e7eb !important;
        color: #334155 !important;
        border: 1px solid #d1d5db !important;
    }
    .swal2-cancel.ek-swal-cancel:hover {
        background: #dbe1e8 !important;
        color: #0f172a !important;
    }
    .swal2-timer-progress-bar {
        background: rgba(37, 99, 235, 0.35) !important;
    }
    .toast-container.bootstrap-toast-container {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 10040;
        width: min(92vw, 420px);
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        pointer-events: none;
    }
    .toast.ek-bootstrap-toast {
        display: block;
        width: 100%;
        max-width: 100%;
        pointer-events: auto;
        background: #ffffff;
        border: 1px solid #dbe3ef;
        border-left: 4px solid #2563eb;
        border-radius: 0.5rem;
        box-shadow: 0 0.75rem 1.75rem rgba(15, 23, 42, 0.14);
        opacity: 0;
        transform: translateY(-8px);
        transition: opacity 0.18s ease, transform 0.18s ease;
        overflow: hidden;
    }
    .toast.ek-bootstrap-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
    .toast.ek-bootstrap-toast.hide {
        opacity: 0;
        transform: translateY(-8px);
    }
    .ek-bootstrap-toast .toast-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.68rem 0.78rem;
        color: #334155;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.82rem;
        font-weight: 800;
    }
    .ek-bootstrap-toast .toast-body {
        padding: 0.78rem;
        color: #475569;
        font-size: 0.84rem;
        line-height: 1.45;
    }
    .ek-bootstrap-toast .btn-close {
        margin-left: auto;
        width: 1.5rem;
        height: 1.5rem;
        border: 0;
        border-radius: 0.35rem;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        font-size: 1.05rem;
        line-height: 1;
    }
    .ek-bootstrap-toast .btn-close:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .ek-toast-dot {
        width: 0.62rem;
        height: 0.62rem;
        border-radius: 999px;
        background: currentColor;
        flex: 0 0 auto;
    }
    .ek-bootstrap-toast.toast-success { border-left-color: #16a34a; }
    .ek-bootstrap-toast.toast-success .toast-header { color: #15803d; background: #f0fdf4; }
    .ek-bootstrap-toast.toast-error { border-left-color: #dc2626; }
    .ek-bootstrap-toast.toast-error .toast-header { color: #b91c1c; background: #fef2f2; }
    .ek-bootstrap-toast.toast-warning { border-left-color: #f59e0b; }
    .ek-bootstrap-toast.toast-warning .toast-header { color: #b45309; background: #fffbeb; }
    .ek-bootstrap-toast.toast-info { border-left-color: #2563eb; }
    .ek-bootstrap-toast.toast-info .toast-header { color: #1d4ed8; background: #eff6ff; }
    @keyframes ekSwalIn {
        from { opacity: 0; transform: translateY(12px) scale(0.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes ekSwalOut {
        from { opacity: 1; transform: translateY(0) scale(1); }
        to { opacity: 0; transform: translateY(8px) scale(0.98); }
    }
    .swal2-show.ek-swal-show { animation: ekSwalIn 0.18s ease-out both !important; }
    .swal2-hide.ek-swal-hide { animation: ekSwalOut 0.16s ease-in both !important; }
</style>

<script>
(function () {
    const knownTypes = ['success', 'error', 'warning', 'info', 'question'];

    function isType(value) {
        return knownTypes.includes(String(value || '').toLowerCase());
    }

    function normaliseToastArgs(messageOrType, typeOrTitle, titleOrMessage) {
        if (isType(messageOrType) && !isType(typeOrTitle) && typeof titleOrMessage !== 'undefined') {
            return {
                message: String(titleOrMessage ?? ''),
                type: String(messageOrType).toLowerCase(),
                title: String(typeOrTitle ?? '')
            };
        }

        return {
            message: String(messageOrType ?? ''),
            type: isType(typeOrTitle) ? String(typeOrTitle).toLowerCase() : 'info',
            title: String(titleOrMessage ?? '')
        };
    }

    function baseOptions() {
        return {
            allowOutsideClick: true,
            allowEscapeKey: true,
            backdrop: true,
            buttonsStyling: false,
            heightAuto: false,
            customClass: {
                popup: 'ek-swal-popup',
                title: 'ek-swal-title',
                htmlContainer: 'ek-swal-text',
                icon: 'ek-swal-icon',
                actions: 'ek-swal-actions',
                confirmButton: 'ek-swal-confirm',
                cancelButton: 'ek-swal-cancel'
            },
            showClass: {
                popup: 'ek-swal-show'
            },
            hideClass: {
                popup: 'ek-swal-hide'
            }
        };
    }

    function fire(options = {}) {
        if (typeof Swal === 'undefined') {
            return Promise.resolve({ isConfirmed: false, isDismissed: true });
        }

        return Swal.fire(Object.assign(baseOptions(), options));
    }

    function confirm(options = {}) {
        const icon = options.icon || 'warning';
        const title = options.title || 'Adakah anda pasti?';
        const text = options.text || 'Tindakan ini tidak boleh dibatalkan.';
        const confirmText = options.confirmText || 'Ya';
        const cancelText = options.cancelText || 'Batal';

        return fire({
            icon,
            title,
            text,
            showCancelButton: true,
            focusCancel: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            allowEnterKey: true,
            allowOutsideClick: false,
        }).then(function (result) {
            return Boolean(result.isConfirmed);
        });
    }

    function confirmDelete(options = {}) {
        return confirm({
            icon: 'warning',
            title: options.title || 'Padam Rekod?',
            text: options.text || 'Data ini akan dipadam secara kekal.',
            confirmText: options.confirmText || 'Ya, Padam',
            cancelText: options.cancelText || 'Batal'
        });
    }

    function confirmApprove(options = {}) {
        return confirm({
            icon: 'success',
            title: options.title || 'Lulus Permohonan?',
            text: options.text || 'Pengguna akan diberikan akses ke sistem.',
            confirmText: options.confirmText || 'Ya, Luluskan',
            cancelText: options.cancelText || 'Batal'
        });
    }

    function confirmReject(options = {}) {
        return confirm({
            icon: 'error',
            title: options.title || 'Tolak Permohonan?',
            text: options.text || 'Permohonan ini akan ditolak.',
            confirmText: options.confirmText || 'Ya, Tolak',
            cancelText: options.cancelText || 'Batal'
        });
    }

    function confirmBlock(options = {}) {
        return confirm({
            icon: 'warning',
            title: options.title || 'Sekat Pengguna?',
            text: options.text || 'Pengguna tidak akan dapat mengakses sistem.',
            confirmText: options.confirmText || 'Ya, Sekat',
            cancelText: options.cancelText || 'Batal'
        });
    }

    function confirmLogout(options = {}) {
        return confirm({
            icon: 'question',
            title: options.title || 'Log Keluar?',
            text: options.text || 'Anda akan keluar daripada sistem.',
            confirmText: options.confirmText || 'Ya, Log Keluar',
            cancelText: options.cancelText || 'Batal'
        });
    }

    function confirmSave(options = {}) {
        return confirm({
            icon: 'info',
            title: options.title || 'Simpan Maklumat?',
            text: options.text || 'Pastikan maklumat yang dimasukkan adalah betul.',
            confirmText: options.confirmText || 'Ya, Simpan',
            cancelText: options.cancelText || 'Batal'
        });
    }

    function showMessage(type, title, message, options = {}) {
        if (typeof Swal === 'undefined') return Promise.resolve();

        return fire({
            icon: type,
            title: title,
            text: String(message ?? ''),
            confirmButtonText: options.confirmButtonText || 'OK',
            showConfirmButton: true,
            timer: options.timer,
            timerProgressBar: Boolean(options.timer),
            allowOutsideClick: true,
            showCancelButton: false,
            width: options.width || 420,
            padding: options.padding || '1.4rem 1.5rem 1.2rem'
        });
    }

    function ensureToastContainer() {
        let container = document.getElementById('bootstrapToastContainer');
        if (container) return container;

        container = document.createElement('div');
        container.id = 'bootstrapToastContainer';
        container.className = 'toast-container bootstrap-toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
        });
    }

    function showToast(messageOrType, typeOrTitle, titleOrMessage) {
        const payload = normaliseToastArgs(messageOrType, typeOrTitle, titleOrMessage);
        const titles = {
            success: 'Berjaya',
            error: 'Ralat',
            warning: 'Amaran',
            info: 'Maklumat'
        };
        const container = ensureToastContainer();
        const toast = document.createElement('div');
        const safeType = ['success', 'error', 'warning', 'info'].includes(payload.type) ? payload.type : 'info';
        const title = payload.title || titles[safeType] || 'Maklumat';

        toast.className = 'toast ek-bootstrap-toast toast-' + safeType;
        toast.setAttribute('role', safeType === 'error' ? 'alert' : 'status');
        toast.setAttribute('aria-live', safeType === 'error' ? 'assertive' : 'polite');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML =
            '<div class="toast-header">' +
                '<span class="ek-toast-dot" aria-hidden="true"></span>' +
                '<strong class="me-auto">' + escapeHtml(title) + '</strong>' +
                '<button type="button" class="btn-close" aria-label="Tutup">&times;</button>' +
            '</div>' +
            '<div class="toast-body">' + escapeHtml(payload.message) + '</div>';

        const close = function () {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(function () { toast.remove(); }, 190);
        };

        toast.querySelector('.btn-close').addEventListener('click', close);
        container.appendChild(toast);
        setTimeout(function () { toast.classList.add('show'); }, 10);
        setTimeout(close, safeType === 'success' ? 3200 : 4600);

        return Promise.resolve(toast);
    }

    function showSuccess(message, title = 'Berjaya', options = {}) {
        return showToast(message, 'success', title);
    }

    function showError(message, title = 'Ralat', options = {}) {
        return showToast(message, 'error', title);
    }

    function showWarning(message, title = 'Amaran', options = {}) {
        return showToast(message, 'warning', title);
    }

    function showInfo(message, title = 'Maklumat', options = {}) {
        return showToast(message, 'info', title);
    }

    function showValidationToast(errors, fallbackMessage) {
        const fallback = fallbackMessage || 'Sila lengkapkan maklumat yang diperlukan.';
        if (!errors || typeof errors !== 'object') {
            return showWarning(fallback, 'Maklumat Tidak Lengkap');
        }

        const firstKey = Object.keys(errors)[0];
        const firstValue = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey];
        const message = String(firstValue || fallback);
        const isRequired = /required|wajib|diperlukan/i.test(message);
        return showToast(message, isRequired ? 'warning' : 'error', isRequired ? 'Maklumat Tidak Lengkap' : 'Semakan Data Gagal');
    }

    const service = {
        confirm,
        confirmDelete,
        confirmApprove,
        confirmReject,
        confirmBlock,
        confirmLogout,
        confirmSave,
        showToast,
        showSuccess,
        showError,
        showWarning,
        showInfo,
        showValidationToast
    };

    window.SweetAlertService = service;
    window.ConfirmationService = service;
    window.showConfirmDialog = confirm;
    window.themedConfirm = confirm;
    window.showToast = showToast;
    window.showValidationToast = showValidationToast;

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showSuccess(@json(session('success')), 'Berjaya');
        @endif
        @if(session('error'))
            showError(@json(session('error')), 'Ralat');
        @endif
        @if(session('warning'))
            showWarning(@json(session('warning')), 'Amaran');
        @endif
        @if(session('info'))
            showInfo(@json(session('info')), 'Maklumat');
        @endif
        @if($errors->any())
            showWarning(@json($errors->first()), 'Maklumat Tidak Lengkap');
        @endif
    });

})();
</script>
