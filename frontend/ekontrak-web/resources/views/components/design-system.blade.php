<style>
:root {
    --ds-space-4: 1rem;
    --ds-space-6: 1.5rem;
    --ds-radius-lg: 10px;
    --ds-radius-xl: 14px;
    --ds-blue: #1d4ed8;
    --ds-blue-hover: #1e40af;
    --ds-green: #15803d;
    --ds-red: #b91c1c;
    --ds-amber: #b45309;
    --ds-gray: #475569;
    --ds-border: #dbe3ef;
    --ds-focus: #93c5fd;
}

.mb-4 { margin-bottom: var(--ds-space-4); }
.mb-6 { margin-bottom: var(--ds-space-6); }
.p-6 { padding: var(--ds-space-6); }
.gap-2 { gap: .5rem; }
.rounded-lg { border-radius: var(--ds-radius-lg); }
.rounded-xl { border-radius: var(--ds-radius-xl); }
.shadow-sm { box-shadow: 0 2px 8px rgba(15,23,42,.08); }
.shadow-md { box-shadow: 0 8px 24px rgba(15,23,42,.11); }
.shadow-lg { box-shadow: 0 14px 34px rgba(15,23,42,.16); }

.ds-label { display:block; margin-bottom:.38rem; font-size:.82rem; color:#334155; font-weight:700; }
.ds-required { color:#dc2626; font-weight:800; }
.ds-input-wrap { position: relative; }
.ds-input-wrap.has-error .ds-input { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.12); }
.ds-input-wrap.has-success .ds-input { border-color:#16a34a; box-shadow:0 0 0 3px rgba(34,197,94,.12); }
.ds-input-icon { position:absolute; left:.75rem; top:50%; transform:translateY(-50%); color:#64748b; display:inline-flex; }
.ds-input {
    width:100%; height:44px; border:1px solid var(--ds-border); border-radius:var(--ds-radius-lg);
    padding:.6rem .75rem; font-size:.9rem; color:#0f172a; background:#fff;
}
.ds-input:focus { outline: none; border-color: var(--ds-blue); box-shadow: 0 0 0 3px rgba(59,130,246,.2); }
.ds-input-wrap .ds-input { padding-left: 2.35rem; }
.ds-input-wrap.has-status .ds-input { padding-right: 2.35rem; }
.ds-input-status {
    position:absolute;
    right:.75rem;
    top:50%;
    transform:translateY(-50%);
    width:20px;
    height:20px;
    border-radius:999px;
    display:none;
    align-items:center;
    justify-content:center;
    font-size:.72rem;
    font-weight:800;
    line-height:1;
    pointer-events:none;
}
.ds-input-status.show { display:inline-flex; }
.ds-input-wrap.has-error .ds-input-status { display:inline-flex; color:#dc2626; background:#fee2e2; }
.ds-input-wrap.has-success .ds-input-status { display:inline-flex; color:#16a34a; background:#dcfce7; }
.ds-textarea { height:auto; min-height:104px; }
.ds-date-hint { margin-top:.28rem; font-size:.72rem; color:#64748b; }
.ds-error { margin-top:.34rem; color:#b91c1c; font-size:.78rem; display:flex; align-items:center; gap:.35rem; line-height:1.35; }
.ds-success { color:#15803d; }
.ds-error-icon,
.ds-success-icon { width:16px; height:16px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.68rem; flex-shrink:0; }
.ds-error-icon { background:#fee2e2; color:#dc2626; }
.ds-success-icon { background:#dcfce7; color:#16a34a; }

.ds-btn {
    min-height:42px; border-radius: var(--ds-radius-lg); border:1px solid transparent;
    display:inline-flex; align-items:center; justify-content:center; gap:.45rem; padding:.6rem .95rem;
    font-size:.84rem; font-weight:700; cursor:pointer; transition:all .2s ease; text-decoration:none;
}
.ds-btn[disabled] { opacity:.55; cursor:not-allowed; }
.ds-btn-full { width:100%; }
.ds-btn-primary { background:var(--ds-blue); color:#fff; }
.ds-btn-primary:hover { background:var(--ds-blue-hover); }
.ds-btn-secondary { background:#fff; color:#334155; border-color:#cbd5e1; }
.ds-btn-success { background:var(--ds-green); color:#fff; }
.ds-btn-danger { background:var(--ds-red); color:#fff; }
.ds-btn-warning { background:var(--ds-amber); color:#fff; }
.ds-btn-spinner { width:13px; height:13px; border:2px solid transparent; border-top-color:currentColor; border-radius:999px; display:none; animation: ds-spin .8s linear infinite; }
.ds-btn[data-loading="1"] .ds-btn-spinner { display:inline-flex; }
.ds-btn[data-loading="1"] .ds-btn-label { opacity:.8; }
@keyframes ds-spin { to { transform: rotate(360deg); } }

.ds-table-wrap { border:1px solid #d7e1ee; border-radius:16px; overflow:auto; background:#fff; box-shadow:0 6px 18px rgba(15,23,42,.06); }
.ds-table-shell { border:1px solid #d7e1ee; border-radius:16px; overflow:hidden; background:#fff; box-shadow:0 6px 18px rgba(15,23,42,.06); }
.ds-table-shell-body { overflow:auto; }
.ds-table-shell-footer { background:linear-gradient(180deg,#fbfdff 0%,#f8fbff 100%); border-top:1px solid #e2e8f0; padding:.75rem 1rem; }
.ds-table { width:100%; border-collapse:separate; border-spacing:0; min-width:760px; }
.ds-table th { background:linear-gradient(180deg,#f8fbff 0%,#f4f8fc 100%); color:#334155; font-size:.74rem; font-weight:800; text-align:left; padding:1rem 1rem .95rem; border-bottom:1px solid #d7e1ee; letter-spacing:.04em; white-space:nowrap; }
.ds-table td { padding:.95rem 1rem; border-bottom:1px solid #e7edf5; font-size:.84rem; color:#334155; line-height:1.55; vertical-align:top; }
.ds-table tbody tr:nth-child(even) { background:#fcfdff; }
.ds-table tbody tr:hover { background:#f6faff; }
.ds-table tbody tr:last-child td { border-bottom:none; }
.ds-table-sticky th { position: sticky; top: 0; z-index: 2; }
.ds-skeleton { width:100%; height:14px; border-radius:8px; background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size:200% 100%; animation:ds-shimmer 1.4s infinite; }
.ds-empty-cell { text-align:center; padding:2.35rem 1rem !important; }
.ds-empty-state { display:inline-flex; align-items:center; gap:.45rem; color:#64748b; font-size:.85rem; }
@keyframes ds-shimmer { 0% {background-position:200% 0} 100% {background-position:-200% 0} }

.ds-pagination-wrap { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; margin-top:0; padding:0; }
.ds-pagination-meta { font-size:.78rem; color:#475569; display:flex; align-items:center; gap:.4rem; }
.ds-per-page { height:34px; border:1px solid #cbd5e1; border-radius:8px; padding:0 .5rem; background:#fff; }
.ds-pagination-btns { display:flex; align-items:center; gap:.3rem; }
.ds-page-btn { min-width:34px; height:34px; border:1px solid #cbd5e1; background:#fff; border-radius:9px; font-size:.76rem; cursor:pointer; box-shadow:0 1px 2px rgba(15,23,42,.04); }
.ds-page-btn.active { background:#dbeafe; border-color:#93c5fd; color:#1d4ed8; font-weight:700; box-shadow:0 3px 8px rgba(59,130,246,.14); }
.ds-page-btn:disabled { opacity:.5; cursor:not-allowed; }

.ds-filter { margin-bottom:1rem; }
.ds-filter-grid { display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:.7rem; background:#f8fafc; border:1px solid #dce5f1; border-radius:12px; padding:1rem; }
.ds-filter-grid > * { grid-column: span 12; }
@media (min-width: 768px) {
    .ds-filter-grid .ds-col-3 { grid-column: span 3; }
    .ds-filter-grid .ds-col-2 { grid-column: span 2; }
    .ds-filter-grid .ds-col-4 { grid-column: span 4; }
}

.ds-confirm-backdrop { position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,.45); align-items:center; justify-content:center; }
.ds-confirm-card { width:min(92vw,430px); border-radius:14px; background:#fff; padding:1.15rem; box-shadow:0 14px 34px rgba(15,23,42,.24); }
.ds-confirm-title { font-size:1rem; color:#0f172a; font-weight:800; margin-bottom:.45rem; }
.ds-confirm-message { color:#475569; font-size:.85rem; margin-bottom:.9rem; }
.ds-confirm-actions { display:flex; justify-content:flex-end; gap:.5rem; }

.ds-validation-summary {
    border: 1px solid #fecaca;
    background: #fef2f2;
    border-radius: 10px;
    padding: 0.75rem 0.85rem;
    margin-bottom: 1rem;
}
.ds-validation-summary-title { color:#991b1b; font-size:.82rem; font-weight:800; margin-bottom:.35rem; }
.ds-validation-summary-list { margin:0; padding-left:1rem; color:#991b1b; font-size:.78rem; }
.ds-validation-summary-list li { margin:.2rem 0; }
.ds-validation-summary-list a { color:#991b1b; text-decoration:underline; cursor:pointer; }

.is-invalid,
.is-valid,
.form-control.is-invalid,
.form-control.is-valid,
.search-input.is-invalid,
.search-input.is-valid,
.filter-input.is-invalid,
.filter-input.is-valid,
.filter-select.is-invalid,
.filter-select.is-valid {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important;
}
.is-valid,
.form-control.is-valid,
.search-input.is-valid,
.filter-input.is-valid,
.filter-select.is-valid {
    border-color: #16a34a !important;
    box-shadow: 0 0 0 3px rgba(34,197,94,.12) !important;
}
</style>

<script>
(function(){
    const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const IC_RE = /^\d{12}$/;
    const PHONE_MY_RE = /^(?:\+?6?01\d{8,9}|0\d{8,10}|03\d{7,8}|0[4-9]\d{7,8})$/;

    function resolveField(form, fieldName) {
        return form.querySelector(`[name="${fieldName}"]`) || document.getElementById(fieldName) || null;
    }

    function resolveErrorNode(form, fieldName, el) {
        const byId = document.getElementById('err_' + fieldName) || document.getElementById('err' + fieldName.charAt(0).toUpperCase() + fieldName.slice(1));
        if (byId) return byId;

        const wrapper = el?.closest('.form-group,.ds-field,.upload-card') || el?.parentElement;
        if (!wrapper) return null;

        let node = wrapper.querySelector('.invalid-note,.invalid-feedback,.ds-error.runtime,.ds-status-message.runtime');
        if (node) return node;

        node = document.createElement('div');
        node.className = 'invalid-note ds-error ds-status-message runtime';
        node.setAttribute('role', 'alert');
        node.setAttribute('aria-live', 'polite');
        wrapper.appendChild(node);
        return node;
    }

    function applyFieldStatus(form, fieldName, state, message) {
        const el = resolveField(form, fieldName);
        if (!el) return null;

        const wrapper = el.closest('.form-group,.ds-field,.upload-card') || el.parentElement;
        const node = resolveErrorNode(form, fieldName, el);
        const icon = wrapper ? wrapper.querySelector('.ds-input-status') : null;

        el.classList.remove('is-invalid', 'is-valid');
        el.removeAttribute('aria-invalid');

        if (wrapper) {
            wrapper.classList.remove('has-error', 'has-success', 'has-status');
        }

        if (node) {
            node.classList.remove('ds-error', 'ds-success');
            node.innerHTML = '';
        }

        if (icon) {
            icon.classList.remove('show');
            icon.textContent = '';
        }

        if (state === 'clear') {
            return el;
        }

        const isSuccess = state === 'success';

        el.classList.add(isSuccess ? 'is-valid' : 'is-invalid');
        if (!isSuccess) {
            el.setAttribute('aria-invalid', 'true');
        }

        if (wrapper) {
            wrapper.classList.add(isSuccess ? 'has-success' : 'has-error', 'has-status');
        }

        if (icon) {
            icon.classList.add('show');
            icon.textContent = isSuccess ? '✓' : '!';
        }

        if (node) {
            node.classList.add(isSuccess ? 'ds-success' : 'ds-error');
            node.innerHTML = isSuccess
                ? `<span class="ds-success-icon" aria-hidden="true">✓</span><span>${message || 'Maklumat sah.'}</span>`
                : `<span class="ds-error-icon" aria-hidden="true">!</span><span>${message || 'Maklumat tidak sah.'}</span>`;
        }

        return el;
    }

    function setFieldError(form, fieldName, message) {
        return applyFieldStatus(form, fieldName, 'error', message);
    }

    function setFieldSuccess(form, fieldName, message) {
        return applyFieldStatus(form, fieldName, 'success', message);
    }

    function clearFieldError(form, fieldName) {
        return applyFieldStatus(form, fieldName, 'clear');
    }

    function clearErrors(form) {
        form.querySelectorAll('.is-invalid,.is-valid').forEach((el) => {
            el.classList.remove('is-invalid', 'is-valid');
            el.removeAttribute('aria-invalid');
        });
        form.querySelectorAll('.ds-input-wrap').forEach((wrap) => {
            wrap.classList.remove('has-error', 'has-success', 'has-status');
            const statusIcon = wrap.querySelector('.ds-input-status');
            if (statusIcon) {
                statusIcon.classList.remove('show');
                statusIcon.textContent = '';
            }
        });
        form.querySelectorAll('.invalid-note,.invalid-feedback,.ds-error.runtime,.ds-status-message.runtime').forEach((node) => {
            if (node.classList.contains('ds-error') || node.classList.contains('ds-status-message') || node.classList.contains('invalid-note') || node.classList.contains('runtime')) {
                node.classList.remove('ds-error', 'ds-success');
                node.innerHTML = '';
            }
        });
    }

    function renderSummary(form, summaryId, items) {
        if (!summaryId) return;
        const panel = document.getElementById(summaryId);
        if (!panel) return;

        const title = panel.querySelector('.ds-validation-summary-title');
        const list = panel.querySelector('.ds-validation-summary-list');
        if (!title || !list) return;

        if (!items.length) {
            panel.style.display = 'none';
            list.innerHTML = '';
            return;
        }

        title.textContent = `Terdapat ${items.length} maklumat yang perlu dilengkapkan.`;
        list.innerHTML = items.map((item, idx) => `<li><a href="#" data-idx="${idx}">${item.label}</a></li>`).join('');
        panel.style.display = 'block';
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });

        list.querySelectorAll('a[data-idx]').forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const item = items[Number(link.getAttribute('data-idx'))];
                if (!item?.el) return;
                item.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof item.el.focus === 'function') item.el.focus();
            });
        });
    }

    function checkRule(value, rule, form) {
        const val = String(value ?? '').trim();
        switch (rule.type) {
            case 'required':
                if (rule.el && (rule.el.type === 'checkbox' || rule.el.type === 'radio')) {
                    const group = form.querySelectorAll(`[name="${rule.el.name}"]`);
                    return Array.from(group).some((item) => item.checked);
                }
                return !!val;
            case 'minLength':
                return val.length >= (rule.value || 0);
            case 'maxLength':
                return val.length <= (rule.value || Number.MAX_SAFE_INTEGER);
            case 'email':
                return !val || EMAIL_RE.test(val);
            case 'phoneMY':
                return !val || PHONE_MY_RE.test(val.replace(/[\s-]/g, ''));
            case 'ic12':
                return IC_RE.test(val);
            case 'numberPositive':
                return val !== '' && !Number.isNaN(Number(val)) && Number(val) > 0;
            case 'numberMin':
                return val !== '' && !Number.isNaN(Number(val)) && Number(val) >= Number(rule.value || 0);
            case 'pattern':
                return !val || new RegExp(rule.value).test(val);
            case 'sameAs': {
                const other = resolveField(form, rule.value);
                return (other ? String(other.value || '') : '') === String(value || '');
            }
            case 'dateOrder': {
                const start = resolveField(form, rule.startField)?.value;
                const end = resolveField(form, rule.endField)?.value;
                if (!start || !end) return true;
                return rule.allowEqual === false ? (new Date(end) > new Date(start)) : (new Date(end) >= new Date(start));
            }
            case 'fileRequired':
                return !!(rule.el?.files?.length);
            case 'fileType': {
                const file = rule.el?.files?.[0];
                if (!file) return true;
                const exts = (rule.value || []).map((x) => String(x).toLowerCase());
                const ext = (file.name.split('.').pop() || '').toLowerCase();
                return exts.includes(ext);
            }
            case 'fileMaxKb': {
                const file = rule.el?.files?.[0];
                if (!file) return true;
                return file.size <= (Number(rule.value || 0) * 1024);
            }
            default:
                return true;
        }
    }

    function validateWithSchema(form, schema, summaryId, options = {}) {
        const showToastOnError = options.showToast !== false;
        const scrollToError = options.scrollToError !== false;

        clearErrors(form);
        renderSummary(form, summaryId, []);

        const allErrors = [];

        Object.entries(schema || {}).forEach(([field, rules]) => {
            const el = resolveField(form, field);
            const value = el?.value ?? '';
            const shouldShowSuccess = Array.isArray(rules) && rules.some((rule) => rule.type === 'required');

            for (const rule of rules) {
                const pass = checkRule(value, { ...rule, el }, form);
                if (!pass) {
                    const msg = rule.message || 'Nilai tidak sah.';
                    const errEl = setFieldError(form, field, msg);
                    allErrors.push({ field, message: msg, label: rule.label || field, el: errEl || el });
                    break;
                }
            }

            if (!allErrors.some((item) => item.field === field)) {
                if (shouldShowSuccess) {
                    setFieldSuccess(form, field, 'Maklumat sah.');
                } else {
                    clearFieldError(form, field);
                }
            }
        });

        renderSummary(form, summaryId, allErrors);

        if (allErrors.length) {
            const first = allErrors[0]?.el;
            if (first && scrollToError) {
                first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof first.focus === 'function') first.focus();
            }
            if (showToastOnError && window.showToast) {
                window.showToast('Sila lengkapkan semua maklumat yang diperlukan.', 'warning', 'Maklumat tidak lengkap');
            }
        }

        return { valid: allErrors.length === 0, errors: allErrors };
    }

    function bindRealtime(form, schema) {
        Object.keys(schema || {}).forEach((field) => {
            const el = resolveField(form, field);
            if (!el) return;
            const run = () => {
                const result = validateWithSchema(form, { [field]: schema[field] }, null, {
                    showToast: false,
                    scrollToError: false,
                });
                if (result.valid) {
                    const rules = schema[field] || [];
                    if (Array.isArray(rules) && rules.some((rule) => rule.type === 'required')) {
                        setFieldSuccess(form, field, 'Maklumat sah.');
                    } else {
                        clearFieldError(form, field);
                    }
                }
            };
            el.addEventListener('blur', run);
            el.addEventListener('change', run);
            el.addEventListener('input', run);
        });
    }

    function mountForm(formId, config = {}) {
        const form = document.getElementById(formId);
        if (!form) return null;

        const schema = config.schema || {};
        const summaryId = config.summaryId || null;
        bindRealtime(form, schema);

        form.addEventListener('submit', function(event) {
            const result = validateWithSchema(form, schema, summaryId, {
                showToast: true,
                scrollToError: true,
            });

            if (!result.valid) {
                event.preventDefault();
                return;
            }

            if (typeof config.beforeSubmit === 'function') {
                const proceed = config.beforeSubmit(event, form);
                if (proceed === false) {
                    event.preventDefault();
                    return;
                }
            }
        });

        return {
            form,
            validate: (options = {}) => validateWithSchema(form, schema, summaryId, options),
            clear: () => clearErrors(form),
        };
    }

    function ensureRequiredMarkers(root = document) {
        const requiredFields = root.querySelectorAll('input[required], select[required], textarea[required]');

        requiredFields.forEach((field) => {
            const id = field.id;
            let label = id ? root.querySelector(`label[for="${id}"]`) : null;

            if (!label) {
                const wrapper = field.closest('.form-group,.ds-field');
                label = wrapper ? wrapper.querySelector('label') : null;
            }

            if (!label) return;
            if (label.querySelector('.ds-required')) return;

            const mark = document.createElement('span');
            mark.className = 'ds-required';
            mark.setAttribute('aria-hidden', 'true');
            mark.textContent = '*';
            label.appendChild(document.createTextNode(' '));
            label.appendChild(mark);
        });
    }

    window.EKValidation = {
        validateWithSchema,
        bindRealtime,
        mountForm,
        clearFieldError,
        setFieldError,
        clearErrors,
        patterns: {
            email: EMAIL_RE,
            ic: IC_RE,
            phoneMY: PHONE_MY_RE,
        }
    };

    window.ValidationService = {
        mount: mountForm,
        validate: validateWithSchema,
        bindRealtime,
        clearErrors,
        setFieldError,
        clearFieldError,
        ensureRequiredMarkers,
        patterns: {
            email: EMAIL_RE,
            ic: IC_RE,
            phoneMY: PHONE_MY_RE,
        },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ensureRequiredMarkers(document));
    } else {
        ensureRequiredMarkers(document);
    }

    const requiredObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType !== 1) return;
                ensureRequiredMarkers(node);
            });
        });
    });
    requiredObserver.observe(document.body, { childList: true, subtree: true });

    window.dsValidateRequired = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return true;
        const invalid = Array.from(form.querySelectorAll('[required]')).filter((el) => {
            if (el.type === 'checkbox' || el.type === 'radio') {
                const group = form.querySelectorAll(`[name="${el.name}"]`);
                return !Array.from(group).some((item) => item.checked);
            }
            return !String(el.value || '').trim();
        });
        if (!invalid.length) return true;

        invalid.forEach((el) => {
            el.classList.add('is-invalid');
            el.setAttribute('aria-invalid', 'true');
        });

        const first = invalid[0];
        first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        first.focus();
        if (window.showToast) {
            window.showToast('Sila lengkapkan semua maklumat yang diperlukan.', 'warning', 'Maklumat tidak lengkap');
        }
        return false;
    };
})();
</script>
