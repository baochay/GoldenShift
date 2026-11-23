<!-- ================================================================
     MODAL ĐÁNH GIÁ 2 CHIỀU BẮT BUỘC (5 SAO TRỰC QUAN) - GOLDEN SHIFT
================================================================ -->
<div id="modalDanhGiaCaLam" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-modal="true" role="dialog" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.72); backdrop-filter: blur(4px); z-index: 100000; padding: 16px; overflow-y: auto; box-sizing: border-box; align-items: center; justify-content: center;">
    <div style="background: #ffffff; width: 100%; max-width: 520px; max-height: calc(100vh - 32px); border-radius: 18px; box-shadow: 0 25px 50px rgba(0,0,0,0.3); margin: auto; display: flex; flex-direction: column; overflow: hidden; animation: zoomIn 0.2s ease;">
        <!-- Header Modal (Cố định ở đỉnh, đã xóa nút đóng X) -->
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #fff;" id="modalReviewHeaderTitle">Đánh Giá Ca Làm Việc</h3>
                    <p style="margin: 2px 0 0; font-size: 12px; color: #fef3c7;" id="modalReviewHeaderSubtitle">Đánh giá 2 chiều minh bạch &amp; chuyên nghiệp</p>
                </div>
            </div>
            <span style="background: rgba(255, 255, 255, 0.22); color: #fff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px; white-space: nowrap;">
                <i class="fa-solid fa-lock"></i> Bắt buộc
            </span>
        </div>

        <!-- Form Đánh Giá (Flex container có thân cuộn và footer cố định) -->
        <form id="formSubmitReview" action="{{ route('danh_gia.store') }}" method="POST" style="margin: 0; display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; overflow: hidden;">
            @csrf
            <input type="hidden" name="ma_ca_lam" id="inputReviewMaCaLam" value="">
            <input type="hidden" name="ma_nguoi_duoc_danh_gia" id="inputReviewTargetUser" value="">
            <input type="hidden" name="so_sao" id="inputReviewSoSao" value="" required>
            <input type="hidden" name="tieu_chi_thai_do" id="inputReviewThaiDo" value="">
            <input type="hidden" name="tieu_chi_chuyen_can" id="inputReviewChuyenCan" value="">
            <input type="hidden" name="tieu_chi_hieu_suat" id="inputReviewHieuSuat" value="">

            <!-- Thân Form Cuộn Được (Scrollable Body) -->
            <div style="flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 18px 20px; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
                <!-- Thông báo bắt buộc đánh giá ngay sau khi Ra ca hoặc Duyệt chấm công -->
                <div id="modalReviewForcedAlert" style="background: #fef2f2; border: 1.5px solid #f87171; color: #991b1b; padding: 10px 14px; border-radius: 10px; margin-bottom: 14px; font-size: 13px; font-weight: 600; line-height: 1.4;">
                    <i class="fa-solid fa-circle-exclamation" style="color: #e11d48; margin-right: 6px;"></i>
                    <span><strong>Quy định bắt buộc:</strong> Vui lòng chọn từ 1-5 sao và nhập nội dung nhận xét để hoàn tất quy trình đánh giá ca làm việc!</span>
                </div>

                <!-- Thông báo lỗi validate inline -->
                <div id="modalReviewValidationError" style="display: {{ session('error') && (session('show_danh_gia_modal') || session('show_danh_gia_ung_vien_modal') || session('auto_open_review') || session('auto_open_review_manager')) ? 'block' : 'none' }}; background: #fff1f2; border: 1px solid #fda4af; color: #be123c; padding: 9px 12px; border-radius: 8px; margin-bottom: 14px; font-size: 12.5px; font-weight: 600;">
                    {{ session('error') }}
                </div>

                <!-- Card Tóm tắt thông tin đối tượng -->
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                    <div>
                        <div style="font-size: 11px; color: #92400e; font-weight: 700; text-transform: uppercase;" id="modalReviewTargetLabel">ĐỐI TƯỢNG ĐÁNH GIÁ</div>
                        <div style="font-size: 14.5px; font-weight: 800; color: #78350f; margin-top: 2px;" id="modalReviewTargetName">Tên đối tượng</div>
                        <div style="font-size: 12px; color: #b45309; margin-top: 2px;" id="modalReviewShiftName">Ca làm việc</div>
                    </div>
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #f59e0b; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; flex-shrink: 0;">
                        <i class="fa-solid fa-award"></i>
                    </div>
                </div>

                <!-- BỘ CHỌN SỐ SAO TỔNG QUAN (1 - 5 SAO - BẮT BUỘC CHỌN) -->
                <div style="text-align: center; margin-bottom: 18px;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                        Chất Lượng Tổng Thể <span style="color: #dc2626;">*</span>
                    </label>
                    <div id="starRatingContainer" style="display: inline-flex; gap: 8px; font-size: 28px; cursor: pointer;">
                        <i class="fa-solid fa-star gs-star" data-rating="1" style="color: #cbd5e1; transition: transform 0.15s, color 0.15s;"></i>
                        <i class="fa-solid fa-star gs-star" data-rating="2" style="color: #cbd5e1; transition: transform 0.15s, color 0.15s;"></i>
                        <i class="fa-solid fa-star gs-star" data-rating="3" style="color: #cbd5e1; transition: transform 0.15s, color 0.15s;"></i>
                        <i class="fa-solid fa-star gs-star" data-rating="4" style="color: #cbd5e1; transition: transform 0.15s, color 0.15s;"></i>
                        <i class="fa-solid fa-star gs-star" data-rating="5" style="color: #cbd5e1; transition: transform 0.15s, color 0.15s;"></i>
                    </div>
                    <div id="starRatingText" style="font-size: 12.5px; font-weight: 700; color: #64748b; margin-top: 4px;">
                        Vui lòng chọn số sao đánh giá (1 - 5 sao)
                    </div>
                </div>

                <!-- TIÊU CHÍ CHI TIẾT (CHO QUẢN LÝ ĐÁNH GIÁ ỨNG VIÊN) -->
                <div id="managerCriteriaSection" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; margin-bottom: 16px;">
                    <div style="font-size: 12px; font-weight: 800; color: #334155; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-sliders text-gold"></i> ĐÁNH GIÁ THEO TIÊU CHÍ NGHỀ NGHIỆP
                    </div>

                    <!-- Tiêu chí 1: Thái độ -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12.5px; font-weight: 600; color: #475569;">Thái độ &amp; Tác phong:</span>
                        <div class="sub-star-group" data-criterion="thai_do" style="font-size: 17px; cursor: pointer; color: #cbd5e1;">
                            <i class="fa-solid fa-star sub-star" data-val="1"></i>
                            <i class="fa-solid fa-star sub-star" data-val="2"></i>
                            <i class="fa-solid fa-star sub-star" data-val="3"></i>
                            <i class="fa-solid fa-star sub-star" data-val="4"></i>
                            <i class="fa-solid fa-star sub-star" data-val="5"></i>
                        </div>
                    </div>

                    <!-- Tiêu chí 2: Chuyên cần -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12.5px; font-weight: 600; color: #475569;">Chuyên cần &amp; Đúng giờ:</span>
                        <div class="sub-star-group" data-criterion="chuyen_can" style="font-size: 17px; cursor: pointer; color: #cbd5e1;">
                            <i class="fa-solid fa-star sub-star" data-val="1"></i>
                            <i class="fa-solid fa-star sub-star" data-val="2"></i>
                            <i class="fa-solid fa-star sub-star" data-val="3"></i>
                            <i class="fa-solid fa-star sub-star" data-val="4"></i>
                            <i class="fa-solid fa-star sub-star" data-val="5"></i>
                        </div>
                    </div>

                    <!-- Tiêu chí 3: Hiệu suất -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; font-weight: 600; color: #475569;">Hiệu suất &amp; Kỹ năng:</span>
                        <div class="sub-star-group" data-criterion="hieu_suat" style="font-size: 17px; cursor: pointer; color: #cbd5e1;">
                            <i class="fa-solid fa-star sub-star" data-val="1"></i>
                            <i class="fa-solid fa-star sub-star" data-val="2"></i>
                            <i class="fa-solid fa-star sub-star" data-val="3"></i>
                            <i class="fa-solid fa-star sub-star" data-val="4"></i>
                            <i class="fa-solid fa-star sub-star" data-val="5"></i>
                        </div>
                    </div>
                </div>

                <!-- Ô NHẬN XÉT -->
                <div style="margin-bottom: 4px;">
                    <label for="inputReviewComment" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                        Nội Dung Nhận Xét &amp; Phản Hồi <span style="color: #dc2626;">* (Bắt buộc)</span>
                    </label>
                    <textarea name="noi_dung_nhan_xet" id="inputReviewComment" rows="3" required minlength="5" placeholder="Chia sẻ cảm nhận chi tiết của bạn về ca làm việc, sự hỗ trợ hoặc thái độ làm việc (tối thiểu 5 ký tự)..." style="width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 12px; font-size: 13px; outline: none; font-family: inherit; line-height: 1.4; resize: vertical; min-height: 70px; max-height: 150px; box-sizing: border-box;"></textarea>
                </div>
            </div>

            <!-- Footer Nút Hành Động (Cố định ở đáy modal, không có nút Hủy/Bỏ qua) -->
            <div style="flex-shrink: 0; padding: 12px 20px 14px; background: #ffffff; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 10px; box-shadow: 0 -4px 12px rgba(0,0,0,0.03);">
                <button type="submit" id="btnReviewModalSubmit" class="btn btn-sm btn-gold" style="border-radius: 8px; padding: 8px 26px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-paper-plane"></i> Gửi Đánh Giá
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var starLabels = {
        1: '😡 Rất thất vọng (1 sao)',
        2: '🙁 Cần cải thiện (2 sao)',
        3: '😐 Tạm ổn (3 sao)',
        4: '🙂 Hài lòng (4 sao)',
        5: '🤩 Rất tuyệt vời (5 sao)'
    };

    var currentRating = 0;
    var touchedCriteria = {
        thai_do: false,
        chuyen_can: false,
        hieu_suat: false
    };
    var criterionInputIds = {
        thai_do: 'inputReviewThaiDo',
        chuyen_can: 'inputReviewChuyenCan',
        hieu_suat: 'inputReviewHieuSuat'
    };

    function showReviewError(msg) {
        var errBox = document.getElementById('modalReviewValidationError');
        if (!errBox) return;
        if (msg) {
            errBox.innerText = msg;
            errBox.style.display = 'block';
        } else {
            errBox.innerText = '';
            errBox.style.display = 'none';
        }
    }

    function paintMainStars(rating, commitValue) {
        var stars = document.querySelectorAll('#starRatingContainer .gs-star');
        stars.forEach(function(star) {
            var val = parseInt(star.getAttribute('data-rating'), 10);
            star.style.color = val <= rating ? '#f59e0b' : '#cbd5e1';
        });
        var textEl = document.getElementById('starRatingText');
        if (textEl) {
            if (rating >= 1 && rating <= 5) {
                textEl.innerText = starLabels[rating] || (rating + ' sao');
                textEl.style.color = '#d97706';
            } else {
                textEl.innerText = 'Vui lòng chọn số sao đánh giá (1 - 5 sao)';
                textEl.style.color = '#64748b';
            }
        }
        if (commitValue) {
            var inputSoSao = document.getElementById('inputReviewSoSao');
            if (inputSoSao) inputSoSao.value = rating > 0 ? String(rating) : '';
        }
    }

    function updateCriterionStars(criterion, val, commitValue) {
        var group = document.querySelector('.sub-star-group[data-criterion="' + criterion + '"]');
        if (group) {
            group.querySelectorAll('.sub-star').forEach(function(other) {
                var ov = parseInt(other.getAttribute('data-val'), 10);
                other.style.color = ov <= val ? '#f59e0b' : '#cbd5e1';
            });
        }
        if (commitValue) {
            var inputEl = document.getElementById(criterionInputIds[criterion]);
            if (inputEl) inputEl.value = val > 0 ? String(val) : '';
        }
    }

    function initModalEventListeners() {
        var stars = document.querySelectorAll('#starRatingContainer .gs-star');
        stars.forEach(function(star) {
            star.addEventListener('click', function() {
                currentRating = parseInt(this.getAttribute('data-rating'), 10);
                paintMainStars(currentRating, true);
                showReviewError('');

                ['thai_do', 'chuyen_can', 'hieu_suat'].forEach(function(c) {
                    if (!touchedCriteria[c]) {
                        updateCriterionStars(c, currentRating, true);
                    }
                });
            });
            star.addEventListener('mouseenter', function() {
                var val = parseInt(this.getAttribute('data-rating'), 10);
                paintMainStars(val, false);
            });
        });

        var container = document.getElementById('starRatingContainer');
        if (container) {
            container.addEventListener('mouseleave', function() {
                paintMainStars(currentRating, false);
            });
        }

        // Tiêu chí phụ
        document.querySelectorAll('.sub-star-group').forEach(function(group) {
            var criterion = group.getAttribute('data-criterion');
            var subStars = group.querySelectorAll('.sub-star');

            subStars.forEach(function(s) {
                s.addEventListener('click', function() {
                    var v = parseInt(this.getAttribute('data-val'), 10);
                    touchedCriteria[criterion] = true;
                    updateCriterionStars(criterion, v, true);
                });
            });
        });

        // Form submit validation: Bắt buộc số sao (1-5) & nhận xét (>= 5 ký tự)
        var form = document.getElementById('formSubmitReview');
        if (form && !form._boundReviewSubmit) {
            form._boundReviewSubmit = true;
            form.addEventListener('submit', function(e) {
                var starsVal = parseInt(document.getElementById('inputReviewSoSao').value, 10) || 0;
                var commentEl = document.getElementById('inputReviewComment');
                var comment = (commentEl ? commentEl.value : '').trim();

                if (starsVal < 1 || starsVal > 5) {
                    e.preventDefault();
                    showReviewError('Vui lòng chọn số sao đánh giá (từ 1 đến 5 sao)!');
                    return false;
                }

                if (comment.length < 5) {
                    e.preventDefault();
                    showReviewError('Vui lòng nhập nội dung nhận xét đánh giá (tối thiểu 5 ký tự)!');
                    if (commentEl) commentEl.focus();
                    return false;
                }

                showReviewError('');
            });
        }

        // Chặn đóng modal hoàn toàn khi click ngoài backdrop
        var modalEl = document.getElementById('modalDanhGiaCaLam');
        if (modalEl && !modalEl._boundBackdropClick) {
            modalEl._boundBackdropClick = true;
            modalEl.addEventListener('click', function(e) {
                if (e.target === modalEl) {
                    e.preventDefault();
                    e.stopPropagation();
                    showReviewError('Đánh giá ca làm việc là bắt buộc! Vui lòng chọn số sao và nhập nhận xét trước khi tiếp tục.');
                }
            });
        }

        // Chặn phím Escape hoàn toàn khi modal đang mở
        if (!window._boundReviewEscape) {
            window._boundReviewEscape = true;
            document.addEventListener('keydown', function(e) {
                var activeModal = document.getElementById('modalDanhGiaCaLam');
                if (e.key === 'Escape' && activeModal && activeModal.style.display === 'flex') {
                    e.preventDefault();
                    e.stopPropagation();
                    showReviewError('Đánh giá ca làm việc là bắt buộc! Vui lòng chọn số sao và nhập nhận xét trước khi tiếp tục.');
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModalEventListeners);
    } else {
        initModalEventListeners();
    }

    // Hàm mở modal toàn cục (luôn khóa đóng, bắt buộc đánh giá)
    window.openReviewModal = function(opts) {
        opts = opts || {};

        var elMaCaLam = document.getElementById('inputReviewMaCaLam');
        if (elMaCaLam) elMaCaLam.value = opts.maCaLam || '';

        var elTargetUser = document.getElementById('inputReviewTargetUser');
        if (elTargetUser) elTargetUser.value = opts.targetUserId || '';

        var elTargetName = document.getElementById('modalReviewTargetName');
        if (elTargetName) elTargetName.innerText = opts.targetName || 'Đối tác Golden Shift';

        var elShiftName = document.getElementById('modalReviewShiftName');
        if (elShiftName) elShiftName.innerText = opts.shiftName ? ('Ca: ' + opts.shiftName) : '';

        var isManager = !!opts.isManager;
        var managerSection = document.getElementById('managerCriteriaSection');

        if (isManager) {
            var titleEl = document.getElementById('modalReviewHeaderTitle');
            if (titleEl) titleEl.innerText = 'Đánh Giá Nhân Viên / Ứng Viên';
            var subEl = document.getElementById('modalReviewHeaderSubtitle');
            if (subEl) subEl.innerText = 'Chấm điểm tác phong, chuyên cần và hiệu suất hoàn thành ca';
            var lblEl = document.getElementById('modalReviewTargetLabel');
            if (lblEl) lblEl.innerText = 'ỨNG VIÊN LÀM VIỆC';
            if (managerSection) managerSection.style.display = 'block';
        } else {
            var titleEl = document.getElementById('modalReviewHeaderTitle');
            if (titleEl) titleEl.innerText = 'Đánh Giá Nhà Hàng & Ca Làm';
            var subEl = document.getElementById('modalReviewHeaderSubtitle');
            if (subEl) subEl.innerText = 'Chia sẻ trải nghiệm làm việc thực tế tại nhà hàng';
            var lblEl = document.getElementById('modalReviewTargetLabel');
            if (lblEl) lblEl.innerText = 'NHÀ HÀNG & QUẢN LÝ CA';
            if (managerSection) managerSection.style.display = 'none';
        }

        // Reset trạng thái sao về 0 để bắt buộc người dùng tự chọn 1-5 sao
        currentRating = 0;
        touchedCriteria = { thai_do: false, chuyen_can: false, hieu_suat: false };
        paintMainStars(0, true);
        ['thai_do', 'chuyen_can', 'hieu_suat'].forEach(function(c) {
            updateCriterionStars(c, 0, true);
        });

        var commentEl = document.getElementById('inputReviewComment');
        if (commentEl) commentEl.value = '';

        var modalEl = document.getElementById('modalDanhGiaCaLam');
        if (modalEl) {
            if (window.bootstrap && window.bootstrap.Modal) {
                try {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static', keyboard: false });
                } catch (err) {}
            }
            document.body.style.overflow = 'hidden';
            modalEl.style.display = 'flex';
        }
    };

    window.closeReviewModal = function() {
        showReviewError('Đánh giá ca làm việc là bắt buộc! Vui lòng chọn số sao và nhập nhận xét trước khi tiếp tục.');
        return false;
    };
})();
</script>

{{-- ================================================================
     TỰ ĐỘNG BẬT POPUP ĐÁNH GIÁ BẮT BUỘC (ỨNG VIÊN & QUẢN LÝ)
================================================================ --}}
@php
    $rvUser = session('user') ?? auth()->user();
    $rvUserRole = is_array($rvUser) ? ($rvUser['vai_tro'] ?? '') : ($rvUser->vai_tro ?? '');
    $rvUserId = is_array($rvUser) ? (int) ($rvUser['ma_nguoi_dung'] ?? 0) : (int) ($rvUser->ma_nguoi_dung ?? 0);

    $shouldOpenCandidateReview = $rvUserRole === 'ung_vien' && (session('show_danh_gia_modal') || session('auto_open_review'));
    $shouldOpenManagerReview = in_array($rvUserRole, ['quan_ly_nha_hang', 'quan_tri_vien'], true) && (session('show_danh_gia_ung_vien_modal') || session('auto_open_review_manager'));

    $rvMaCaLam = (int) (session('ma_ca_lam_danh_gia') ?? session('review_shift_id', 0));
    $rvTargetId = (int) session('review_target_id', 0);
    $rvTargetName = (string) session('review_target_name', $shouldOpenManagerReview ? 'Ứng viên' : 'Nhà hàng');
    $rvShiftName = (string) session('review_shift_name', 'Ca làm việc');

    if (($shouldOpenCandidateReview || $shouldOpenManagerReview) && $rvMaCaLam > 0 && $rvUserId > 0) {
        try {
            $alreadyReviewedQuery = \App\Models\DanhGiaCaLam::where('ma_ca_lam', $rvMaCaLam)
                ->where('ma_nguoi_danh_gia', $rvUserId);
            if ($shouldOpenManagerReview && $rvTargetId > 0) {
                $alreadyReviewedQuery->where('ma_nguoi_duoc_danh_gia', $rvTargetId);
            }
            if ($alreadyReviewedQuery->exists()) {
                session()->forget([
                    'show_danh_gia_modal',
                    'show_danh_gia_ung_vien_modal',
                    'ma_ca_lam_danh_gia',
                    'review_shift_id',
                    'review_target_id',
                    'review_target_name',
                    'review_shift_name',
                ]);
                $shouldOpenCandidateReview = false;
                $shouldOpenManagerReview = false;
            } else {
                $shiftModel = \App\Models\CaLamViec::with('nhaHang')->find($rvMaCaLam);
                if ($shiftModel) {
                    if ($rvShiftName === '' || $rvShiftName === 'Ca làm việc') {
                        $rvShiftName = $shiftModel->ten_vi_tri_tuyen_dung ?? 'Ca làm việc';
                    }
                    if ($shouldOpenCandidateReview && $shiftModel->nhaHang) {
                        $rvTargetName = $shiftModel->nhaHang->ten_nha_hang ?? $rvTargetName;
                        if ($rvTargetId <= 0) {
                            $rvTargetId = (int) ($shiftModel->nhaHang->ma_quan_ly ?? 1);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Bỏ qua trong môi trường test chưa khởi tạo bảng danh_gia_ca_lam
        }
    }
@endphp

@if ($shouldOpenCandidateReview || $shouldOpenManagerReview)
<script>
(function() {
    function runAutoOpenReviewModal() {
        if (window._autoReviewTriggered) return;
        window._autoReviewTriggered = true;

        var reviewOpts = {
            maCaLam: {{ $rvMaCaLam }},
            targetUserId: {{ $rvTargetId > 0 ? $rvTargetId : ($shouldOpenCandidateReview ? 1 : 0) }},
            targetName: {!! json_encode($rvTargetName) !!},
            shiftName: {!! json_encode($rvShiftName) !!},
            isManager: {{ $shouldOpenManagerReview ? 'true' : 'false' }},
            isForced: true
        };

        if (typeof window.openReviewModal === 'function') {
            window.openReviewModal(reviewOpts);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runAutoOpenReviewModal);
    } else {
        runAutoOpenReviewModal();
    }
})();
</script>
@endif
