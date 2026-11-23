<style>
    #modalQrChamCong.gs-qr-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(5px);
        z-index: 10050;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    #modalQrChamCong.gs-qr-modal-overlay.is-open {
        display: flex;
    }

    .gs-qr-modal-card {
        width: 100%;
        max-width: 500px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 28px 65px rgba(15, 23, 42, 0.38);
        overflow: hidden;
        border: 1px solid #e2e8f0;
        animation: gsQrModalSlide 0.22s ease-out;
    }

    @keyframes gsQrModalSlide {
        from { opacity: 0; transform: translateY(16px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .gs-qr-modal-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #f8fafc;
        padding: 1.1rem 1.35rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .gs-qr-modal-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-weight: 800;
        font-size: 0.98rem;
        color: #fbbf24;
    }

    .gs-qr-modal-close {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: rgba(255, 255, 255, 0.12);
        color: #e2e8f0;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s;
    }

    .gs-qr-modal-close:hover {
        background: rgba(255, 255, 255, 0.24);
        color: #ffffff;
    }

    .gs-qr-modal-body {
        padding: 1.25rem 1.35rem 1.35rem;
    }

    .gs-qr-request-card {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 14px;
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .gs-qr-action-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.7rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .gs-qr-action-badge.is-checkin {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .gs-qr-action-badge.is-checkout {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .gs-qr-stage {
        position: relative;
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 1.15rem;
        text-align: center;
        margin-bottom: 0.95rem;
        min-height: 244px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    #qrQrImageWrap {
        background: #ffffff;
        padding: 12px;
        border-radius: 14px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        border: 1px solid #e2e8f0;
        transition: filter 0.3s ease, opacity 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 214px;
        min-height: 214px;
    }

    #qrQrImageWrap.is-expired {
        filter: blur(8px);
        opacity: 0.35;
        pointer-events: none;
        user-select: none;
    }

    #qrExpiredOverlay {
        display: none;
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.74);
        backdrop-filter: blur(2px);
        color: #ffffff;
        padding: 1.4rem;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.85rem;
        z-index: 5;
        text-align: center;
    }

    #qrExpiredOverlay.is-visible {
        display: flex;
    }

    .gs-qr-expired-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(239, 68, 68, 0.22);
        border: 1px solid rgba(252, 165, 165, 0.6);
        color: #fecaca;
        padding: 0.45rem 0.95rem;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.9rem;
    }

    .btn-refresh-qr {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 0.68rem 1.25rem;
        font-weight: 800;
        font-size: 0.92rem;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(217, 119, 6, 0.4);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .btn-refresh-qr:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(217, 119, 6, 0.5);
    }

    .gs-qr-countdown-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.45rem;
        font-size: 0.86rem;
        font-weight: 700;
        color: #334155;
    }

    .gs-qr-countdown-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.22rem 0.65rem;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }

    .gs-qr-countdown-pill.is-warning {
        background: #fffbeb;
        color: #b45309;
    }

    .gs-qr-countdown-pill.is-danger {
        background: #fef2f2;
        color: #b91c1c;
    }

    #modalQrChamCong .progress {
        width: 100%;
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 0.9rem;
    }

    #modalQrChamCong .progress-bar {
        height: 100%;
        width: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #10b981 0%, #f59e0b 65%, #ef4444 100%);
        transition: width 0.95s linear, background-color 0.3s ease;
    }

    .gs-qr-token-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        color: #475569;
    }

    .gs-qr-token-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 700;
        color: #0f172a;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 245px;
    }

    .gs-qr-mini-btn {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        border-radius: 7px;
        padding: 0.32rem 0.65rem;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }

    .gs-qr-mini-btn:hover {
        border-color: #d97706;
        color: #b45309;
    }

    .gs-qr-status-banner {
        display: none;
        border-radius: 10px;
        padding: 0.65rem 0.85rem;
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 0.85rem;
    }

    .gs-qr-status-banner.is-error {
        display: block;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }

    .gs-qr-status-banner.is-success {
        display: block;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
</style>

<div id="modalQrChamCong" class="gs-qr-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="qrModalHeading">
    <div class="gs-qr-modal-card">
        <div class="gs-qr-modal-header">
            <div class="gs-qr-modal-title" id="qrModalHeading">
                <i class="fa-solid fa-qrcode"></i>
                <span>Yêu Cầu Chấm Công Từ Ứng Viên (Mã QR 30s)</span>
            </div>
            <button type="button" class="gs-qr-modal-close" id="btnCloseQrModal" aria-label="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="gs-qr-modal-body">
            <div id="qrCompletedBanner" class="gs-qr-status-banner"></div>
            <div id="qrModalError" class="gs-qr-status-banner"></div>

            <div class="gs-qr-request-card">
                <div style="min-width: 0;">
                    <div id="qrCandidateInfo" style="font-weight: 800; font-size: 0.96rem; color: #78350f;">
                        Ứng viên đang chờ quét mã
                    </div>
                    <div id="qrCandidateEmail" style="font-size: 0.8rem; color: #b45309; margin-top: 2px;"></div>
                    <div id="qrShiftInfo" style="font-size: 0.83rem; font-weight: 600; color: #334155; margin-top: 6px;"></div>
                </div>
                <span id="qrActionBadge" class="gs-qr-action-badge is-checkin">
                    <i class="fa-solid fa-right-to-bracket"></i> VÀO CA / CHECK-IN
                </span>
            </div>

            <div class="gs-qr-stage">
                <div id="qrQrImageWrap">
                    <div id="qrCanvasBox" style="width: 190px; height: 190px; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 0.85rem;">
                        Đang tạo mã QR...
                    </div>
                </div>

                <div id="qrExpiredOverlay">
                    <div class="gs-qr-expired-badge">
                        <i class="fa-solid fa-hourglass-end"></i>
                        <span>Mã QR đã hết hạn sau 30s</span>
                    </div>
                    <p style="margin: 0; font-size: 0.84rem; color: #e2e8f0; max-width: 300px; line-height: 1.45;">
                        Token bảo mật đã hết hiệu lực. Bấm nút bên dưới để sinh ngay mã QR 30 giây mới cho ứng viên quét.
                    </p>
                    <button type="button" id="btnRefreshQrModal" class="btn-refresh-qr">
                        <i class="fa-solid fa-rotate-right"></i> Làm mới mã QR
                    </button>
                </div>
            </div>

            <div class="gs-qr-countdown-row">
                <span>Thời gian hiệu lực còn lại:</span>
                <span id="qrCountdownPill" class="gs-qr-countdown-pill">
                    <i class="fa-regular fa-clock"></i>
                    <span id="qrCountdownText">30s</span>
                </span>
            </div>

            <div class="progress">
                <div id="qrProgressBar" class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="30" aria-valuenow="30"></div>
            </div>

            <div class="gs-qr-token-bar">
                <span style="white-space: nowrap;">Token:</span>
                <span id="qrTokenValue" class="gs-qr-token-code">---</span>
                <div style="display: flex; gap: 0.35rem;">
                    <button type="button" id="btnCopyQrToken" class="gs-qr-mini-btn" title="Sao chép mã token">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                    <button type="button" class="gs-qr-mini-btn btn-refresh-qr" style="padding: 0.32rem 0.65rem; font-size: 0.78rem; box-shadow: none;" title="Làm mới ngay">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>
<script>
(function () {
    const QR_PENDING_URL = {!! json_encode(route('quan_ly.cham_cong.qr_pending'), JSON_UNESCAPED_SLASHES) !!};
    const QR_DISMISS_URL = {!! json_encode(route('quan_ly.cham_cong.qr_dismiss'), JSON_UNESCAPED_SLASHES) !!};
    const QR_TOKEN_BASE_URL = {!! json_encode(url('/quan-ly/cham-cong/qr-token'), JSON_UNESCAPED_SLASHES) !!};
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const QR_POLL_MS = 2000;

    let qrCountdownInterval = null;
    let qrRemainingSeconds = 30;
    let qrTtlSeconds = 30;
    let activeRequestId = null;
    let activeCaLamId = null;
    let activeQrType = 'vao_ca';
    let currentQrToken = '';
    let dismissedRequestId = null;
    let handledCompletedIds = {};
    let autoCloseTimeout = null;

    function el(id) {
        return document.getElementById(id);
    }

    function stopQrCountdown() {
        if (qrCountdownInterval) {
            clearInterval(qrCountdownInterval);
            qrCountdownInterval = null;
        }
    }

    function showBanner(elementId, message, typeClass) {
        const box = el(elementId);
        if (!box) return;
        box.classList.remove('is-error', 'is-success');
        if (message) {
            box.textContent = message;
            box.classList.add(typeClass);
        } else {
            box.textContent = '';
        }
    }

    function setQrExpiredState(isExpired) {
        const wrap = el('qrQrImageWrap');
        const overlay = el('qrExpiredOverlay');
        if (wrap) {
            wrap.classList.toggle('is-expired', isExpired);
        }
        if (overlay) {
            overlay.classList.toggle('is-visible', isExpired);
        }
    }

    function updateCountdownUi(secondsLeft, ttl) {
        const countEl = el('qrCountdownText');
        const pillEl = el('qrCountdownPill');
        const barEl = el('qrProgressBar');
        const safeSeconds = Math.max(0, Number(secondsLeft) || 0);
        const safeTtl = Math.max(1, Number(ttl) || 30);
        const pct = Math.max(0, Math.min(100, (safeSeconds / safeTtl) * 100));

        if (countEl) {
            countEl.textContent = safeSeconds + 's';
        }

        if (pillEl) {
            pillEl.classList.remove('is-warning', 'is-danger');
            if (safeSeconds <= 5) {
                pillEl.classList.add('is-danger');
            } else if (safeSeconds <= 12) {
                pillEl.classList.add('is-warning');
            }
        }

        if (barEl) {
            barEl.style.width = pct + '%';
            barEl.setAttribute('aria-valuenow', String(safeSeconds));
        }
    }

    function startQrCountdown(remainingSeconds, ttlSeconds) {
        stopQrCountdown();
        qrTtlSeconds = Math.max(1, Number(ttlSeconds) || 30);
        qrRemainingSeconds = Math.max(0, Number(remainingSeconds) || 0);

        updateCountdownUi(qrRemainingSeconds, qrTtlSeconds);
        if (qrRemainingSeconds <= 0) {
            setQrExpiredState(true);
            return;
        }

        setQrExpiredState(false);
        qrCountdownInterval = setInterval(function () {
            qrRemainingSeconds = Math.max(0, qrRemainingSeconds - 1);
            updateCountdownUi(qrRemainingSeconds, qrTtlSeconds);

            if (qrRemainingSeconds <= 0) {
                stopQrCountdown();
                setQrExpiredState(true);
            }
        }, 1000);
    }

    function renderQrVisual(payloadText) {
        const canvasBox = el('qrCanvasBox');
        if (!canvasBox) return;
        canvasBox.innerHTML = '';

        if (typeof window.QRCode === 'function') {
            try {
                new window.QRCode(canvasBox, {
                    text: payloadText,
                    width: 190,
                    height: 190,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: window.QRCode.CorrectLevel ? window.QRCode.CorrectLevel.M : 1
                });
                return;
            } catch (e) {
                // Fallback to external image renderer below
            }
        }

        const img = document.createElement('img');
        img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=190x190&data=' + encodeURIComponent(payloadText);
        img.alt = 'Mã QR chấm công 30 giây';
        img.width = 190;
        img.height = 190;
        img.style.borderRadius = '8px';
        canvasBox.appendChild(img);
    }

    function renderPendingRequest(req) {
        const modal = el('modalQrChamCong');
        if (!modal) return;

        if (autoCloseTimeout) {
            clearTimeout(autoCloseTimeout);
            autoCloseTimeout = null;
        }

        showBanner('qrCompletedBanner', '', 'is-success');
        showBanner('qrModalError', '', 'is-error');

        activeRequestId = req.request_id || null;
        activeCaLamId = req.ma_ca_lam || null;
        activeQrType = req.loai_qr === 'ra_ca' ? 'ra_ca' : 'vao_ca';
        currentQrToken = req.qr_token || '';

        if (el('qrCandidateInfo')) {
            el('qrCandidateInfo').textContent = req.ten_ung_vien || 'Ứng viên';
        }
        if (el('qrCandidateEmail')) {
            el('qrCandidateEmail').textContent = req.email_ung_vien || '';
        }
        if (el('qrShiftInfo')) {
            const datePart = req.ngay_lam_viec ? (' | ' + req.ngay_lam_viec) : '';
            const timePart = req.khung_gio ? (' (' + req.khung_gio + ')') : '';
            el('qrShiftInfo').textContent = '#CA-' + req.ma_ca_lam + ' — ' + (req.ten_ca_lam || 'Ca làm việc') + datePart + timePart;
        }

        const badgeEl = el('qrActionBadge');
        if (badgeEl) {
            badgeEl.classList.remove('is-checkin', 'is-checkout');
            if (activeQrType === 'ra_ca') {
                badgeEl.classList.add('is-checkout');
                badgeEl.innerHTML = '<i class="fa-solid fa-right-from-bracket"></i> RA CA / CHECK-OUT';
            } else {
                badgeEl.classList.add('is-checkin');
                badgeEl.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> VÀO CA / CHECK-IN';
            }
        }

        if (el('qrTokenValue')) {
            el('qrTokenValue').textContent = currentQrToken || '---';
        }

        renderQrVisual(req.qr_payload || currentQrToken);
        modal.classList.add('is-open');

        if (req.is_expired || Number(req.remaining_seconds) <= 0) {
            startQrCountdown(0, req.ttl || 30);
        } else {
            startQrCountdown(req.remaining_seconds, req.ttl || 30);
        }
    }

    async function refreshActiveQrToken() {
        if (!activeCaLamId) return;

        showBanner('qrModalError', '', 'is-error');
        try {
            const response = await fetch(QR_TOKEN_BASE_URL + '/' + encodeURIComponent(activeCaLamId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    ma_ca_lam: activeCaLamId,
                    loai_qr: activeQrType
                })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Không thể làm mới mã QR.');
            }

            currentQrToken = data.qrToken || data.qr_token || '';
            if (el('qrTokenValue')) {
                el('qrTokenValue').textContent = currentQrToken;
            }
            renderQrVisual(data.qr_payload || currentQrToken);
            startQrCountdown(data.ttl || 30, data.ttl || 30);
        } catch (err) {
            showBanner('qrModalError', err.message || 'Lỗi kết nối khi làm mới mã QR.', 'is-error');
        }
    }

    window.closeQrChamCongModal = function (markDismissed) {
        const modal = el('modalQrChamCong');
        if (modal) {
            modal.classList.remove('is-open');
        }
        stopQrCountdown();

        if (markDismissed && activeRequestId) {
            dismissedRequestId = activeRequestId;
            fetch(QR_DISMISS_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ request_id: activeRequestId })
            }).catch(function () {});
        }
    };

    async function pollPendingQrRequest() {
        try {
            const response = await fetch(QR_PENDING_URL, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) return;
            const data = await response.json();
            if (!data || !data.success) return;

            if (data.completed_request && data.completed_request.request_id) {
                const done = data.completed_request;
                if (activeRequestId === done.request_id && !handledCompletedIds[done.request_id]) {
                    handledCompletedIds[done.request_id] = true;
                    stopQrCountdown();
                    setQrExpiredState(false);
                    const actionText = done.loai_qr === 'ra_ca' ? 'Ra ca' : 'Vào ca';
                    showBanner(
                        'qrCompletedBanner',
                        'Ứng viên [' + (done.ten_ung_vien || 'Ứng viên') + '] đã quét mã ' + actionText + ' thành công!',
                        'is-success'
                    );
                    autoCloseTimeout = setTimeout(function () {
                        window.closeQrChamCongModal(false);
                        activeRequestId = null;
                        currentQrToken = '';
                    }, 2000);
                }
                return;
            }

            if (data.has_pending && data.request) {
                const req = data.request;
                if (dismissedRequestId && dismissedRequestId === req.request_id) {
                    return;
                }

                const isNewRequestOrToken = req.request_id !== activeRequestId || req.qr_token !== currentQrToken;
                const modal = el('modalQrChamCong');
                const isModalClosed = modal && !modal.classList.contains('is-open');

                if (isNewRequestOrToken || isModalClosed) {
                    renderPendingRequest(req);
                } else if (req.is_expired && qrRemainingSeconds > 0) {
                    startQrCountdown(0, req.ttl || 30);
                }
            }
        } catch (err) {
            // Bỏ qua lỗi mạng tạm thời trong quá trình polling
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modal = el('modalQrChamCong');
        const closeBtn = el('btnCloseQrModal');

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                window.closeQrChamCongModal(true);
            });
        }

        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    window.closeQrChamCongModal(true);
                }
            });
        }

        document.querySelectorAll('#modalQrChamCong .btn-refresh-qr').forEach(function (btn) {
            btn.addEventListener('click', function () {
                refreshActiveQrToken();
            });
        });

        const copyBtn = el('btnCopyQrToken');
        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                if (!currentQrToken) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(currentQrToken).then(function () {
                        copyBtn.innerHTML = '<i class="fa-solid fa-check"></i> Đã chép';
                        setTimeout(function () {
                            copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i> Copy';
                        }, 1500);
                    });
                }
            });
        }

        pollPendingQrRequest();
        setInterval(pollPendingQrRequest, QR_POLL_MS);
    });
})();
</script>
