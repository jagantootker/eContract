<div class="toast-container" id="toastContainer"></div>

<script>
    (function () {
        function toastIcon(type) {
            switch (type) {
                case 'success':
                    return '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3.25-3.25a1 1 0 011.414-1.414L8.75 11.836l6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/>';
                case 'error':
                    return '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.536-10.95a1 1 0 00-1.414-1.414L10 7.758 7.879 5.636A1 1 0 106.464 7.05L8.586 9.172l-2.122 2.121a1 1 0 001.415 1.415L10 10.586l2.121 2.122a1 1 0 001.415-1.415l-2.122-2.121 2.122-2.122z" clip-rule="evenodd"/>';
                case 'warning':
                    return '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.518 11.59c.75 1.334-.213 2.986-1.743 2.986H3.482c-1.53 0-2.493-1.652-1.743-2.986L8.257 3.1zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-2a1 1 0 01-1-1V7a1 1 0 112 0v3a1 1 0 01-1 1z" clip-rule="evenodd"/>';
                default:
                    return '<path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-7-4a1 1 0 10-2 0v4a1 1 0 00.293.707l2 2a1 1 0 001.414-1.414L11 9.586V6z" clip-rule="evenodd"/>';
            }
        }

        function showToast(message, type = 'success', title = '') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const allowed = ['success', 'error', 'warning', 'info'];
            const safeType = allowed.includes(type) ? type : 'info';
            const toast = document.createElement('div');
            toast.className = 'toast toast-' + safeType;
            const heading = title ? '<div class="toast-title">' + title + '</div>' : '';
            const body = '<div class="toast-message">' + String(message ?? '') + '</div>';
            toast.innerHTML = '<svg class="toast-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20">' + toastIcon(safeType) + '</svg><div class="toast-content">' + heading + body + '</div><button type="button" class="toast-close" aria-label="Tutup">&times;</button>';

            const closeBtn = toast.querySelector('.toast-close');
            const close = function () {
                toast.classList.add('toast-out');
                setTimeout(function () { toast.remove(); }, 180);
            };
            closeBtn.addEventListener('click', close);
            container.appendChild(toast);
            setTimeout(function () { toast.classList.add('toast-show'); }, 10);
            setTimeout(function () {
                if (!toast.isConnected) return;
                toast.classList.add('toast-out');
                setTimeout(function () { toast.remove(); }, 180);
            }, safeType === 'success' ? 3200 : 4600);
        }

        function showValidationToast(errors, fallbackMessage) {
            const fallback = fallbackMessage || 'Sila lengkapkan maklumat yang diperlukan.';
            if (!errors || typeof errors !== 'object') {
                showToast(fallback, 'warning', 'Maklumat tidak lengkap');
                return;
            }
            const firstKey = Object.keys(errors)[0];
            const firstValue = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey];
            const msg = firstValue || fallback;
            const isRequired = /required|wajib|diperlukan/i.test(String(msg));
            showToast(msg, isRequired ? 'warning' : 'error', isRequired ? 'Maklumat tidak lengkap' : 'Semakan data gagal');
        }

        window.showToast = showToast;
        window.showValidationToast = showValidationToast;

        // Fire session flash messages as toasts on page load
        @if(session('success'))
        document.addEventListener('DOMContentLoaded', function () {
            showToast(@json(session('success')), 'success', 'Berjaya');
        });
        @endif
        @if(session('error'))
        document.addEventListener('DOMContentLoaded', function () {
            showToast(@json(session('error')), 'error', 'Ralat');
        });
        @endif
        @if(session('warning'))
        document.addEventListener('DOMContentLoaded', function () {
            showToast(@json(session('warning')), 'warning', 'Amaran');
        });
        @endif
    })();
</script>
