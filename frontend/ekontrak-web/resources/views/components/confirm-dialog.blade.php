<div id="dsConfirmDialog" class="ds-confirm-backdrop" style="display:none;" aria-hidden="true">
    <div class="ds-confirm-card" role="dialog" aria-modal="true" aria-labelledby="dsConfirmTitle">
        <h3 id="dsConfirmTitle" class="ds-confirm-title">Adakah anda pasti?</h3>
        <p id="dsConfirmMessage" class="ds-confirm-message">Tindakan ini tidak boleh dibatalkan.</p>
        <div class="ds-confirm-actions">
            <button type="button" class="ds-btn ds-btn-secondary" id="dsConfirmNo">Tidak</button>
            <button type="button" class="ds-btn ds-btn-danger" id="dsConfirmYes">Ya</button>
        </div>
    </div>
</div>

<script>
(function(){
    const backdrop = document.getElementById('dsConfirmDialog');
    if (!backdrop) return;
    const title = document.getElementById('dsConfirmTitle');
    const message = document.getElementById('dsConfirmMessage');
    const noBtn = document.getElementById('dsConfirmNo');
    const yesBtn = document.getElementById('dsConfirmYes');
    let resolver = null;

    function close(result) {
        backdrop.style.display = 'none';
        backdrop.setAttribute('aria-hidden', 'true');
        if (resolver) resolver(result);
        resolver = null;
    }

    noBtn.addEventListener('click', () => close(false));
    yesBtn.addEventListener('click', () => close(true));
    backdrop.addEventListener('click', (e) => { if (e.target === backdrop) close(false); });

    window.showConfirmDialog = function(opts) {
        const config = opts || {};
        title.textContent = config.title || 'Adakah anda pasti?';
        message.textContent = config.message || 'Tindakan ini tidak boleh dibatalkan.';
        yesBtn.textContent = config.confirmText || 'Ya';
        noBtn.textContent = config.cancelText || 'Tidak';
        backdrop.style.display = 'flex';
        backdrop.setAttribute('aria-hidden', 'false');
        yesBtn.focus();
        return new Promise((resolve) => { resolver = resolve; });
    };
})();
</script>
