@extends('layouts.app')

@section('title', ($shift->ten_vi_tri_tuyen_dung ?? 'Chi Tiết Ca Làm') . ' - ' . ($shift->nhaHang->ten_nha_hang ?? 'Golden Shift'))

@section('content')
@php
    $nhaHang      = $shift->nhaHang;
    $thuongHieu   = $nhaHang->thuongHieu ?? null;
    $tenNhaHang   = $nhaHang->ten_nha_hang ?? 'Golden Hotpot';
    $tenThuongHieu= $thuongHieu->ten_thuong_hieu ?? 'Golden Stars';
    $diaChiChiTiet= $nhaHang->dia_chi_chi_tiet ?? '';
    $quanHuyen    = $nhaHang->quan_huyen ?? '';
    $tinhThanh    = $nhaHang->tinh_thanh_pho ?? 'Hồ Chí Minh';
    $soDienThoai  = $nhaHang->so_dien_thoai_lien_he ?? '1900 8899';
    $currentUser  = $user;
    $hoTen        = is_array($currentUser) ? ($currentUser['ho_ten'] ?? '') : ($currentUser->ho_ten ?? '');
    $soDt         = is_array($currentUser) ? ($currentUser['so_dien_thoai'] ?? '') : ($currentUser->so_dien_thoai ?? '');
@endphp

{{-- FLASH ALERT (redirect với error) --}}
@if (session('error'))
    <div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:1rem 1.5rem; margin-bottom:1rem; border-radius:10px; max-width:900px; margin-left:auto; margin-right:auto; margin-top:1rem;">
        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
    </div>
@endif

<section class="page-header-section">
    <div class="gs-container">
        <div class="breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ url('/ca-lam-viec') }}">Ca làm việc</a>
            <span>/</span>
            <span>Chi tiết ca làm việc</span>
        </div>
        <div class="header-job-highlight">
            <span class="sub-badge"><i class="fa-solid fa-star text-gold"></i> {{ $tenThuongHieu }}</span>
            <h1 class="page-title">{{ $shift->ten_vi_tri_tuyen_dung }}</h1>
            <p class="page-subtitle">
                <i class="fa-solid fa-location-dot"></i>
                {{ $tenNhaHang }}@if($diaChiChiTiet) - {{ $diaChiChiTiet }}@endif
            </p>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="gs-container">
        <div class="detail-layout-grid">

            {{-- CỘT TRÁI: THÔNG TIN CHI TIẾT CA LÀM --}}
            <div class="detail-main-column">
                <div class="detail-card glass-panel">
                    <h2 class="card-section-title">
                        <i class="fa-solid fa-clipboard-list text-gold"></i> Thông Tin Tổng Quan Ca Làm Việc
                    </h2>

                    <div class="summary-metrics-grid">
                        @php
                            $holidayShiftDetail = \App\Helpers\HolidayHelper::isVietnameseHoliday($shift->ngay_lam_viec);
                        @endphp
                        <div class="metric-box">
                            <span class="metric-label">Mức thù lao theo giờ</span>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span class="metric-value text-gold">{{ number_format($shift->muc_luong_theo_gio, 0, ',', '.') }} VNĐ/giờ</span>
                                @if (!empty($holidayShiftDetail))
                                    <span class="badge-holiday-x3" style="background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%); color: #ffffff; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.25); display: inline-flex; align-items: center; gap: 3px;" title="{{ $holidayShiftDetail }}">
                                        🔥 Lương Ngày Lễ (x3)
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="metric-box">
                            <span class="metric-label">Ngày làm việc</span>
                            <span class="metric-value">{{ date('d/m/Y', strtotime($shift->ngay_lam_viec)) }}</span>
                        </div>
                        <div class="metric-box">
                            <span class="metric-label">Khung giờ ca (24h)</span>
                            <span class="metric-value">{{ substr($shift->gio_bat_dau, 0, 5) }} - {{ substr($shift->gio_ket_thuc, 0, 5) }}</span>
                        </div>
                        <div class="metric-box">
                            <span class="metric-label">Chỉ tiêu tuyển dụng</span>
                            <span class="metric-value">{{ $soLuongTuyen }} bạn (Đã nhận {{ $soLuongDaTuyen }})</span>
                        </div>
                    </div>

                    <div class="detail-text-block">
                        <h3><i class="fa-solid fa-file-lines text-gold"></i> Mô tả &amp; Yêu cầu công việc</h3>
                        <p>{!! nl2br(e($shift->yeu_cau_cong_viec ?: 'Tác phong nhanh nhẹn, trung thực, mặc đồng phục đúng quy định nhà hàng.')) !!}</p>
                    </div>

                    <div class="detail-text-block">
                        <h3><i class="fa-solid fa-gift text-gold"></i> Quyền lợi ca làm việc</h3>
                        <p>{!! nl2br(e($shift->quyen_loi_ca_lam ?: 'Hỗ trợ cơm ca đầy đủ, tiền tip chia theo ca, giữ xe miễn phí tại toà nhà.')) !!}</p>
                    </div>

                    <div class="detail-text-block">
                        <h3><i class="fa-solid fa-circle-info text-gold"></i> Quy trình nhận ca &amp; Chấm công</h3>
                        <ul>
                            <li>Có mặt trước ca làm việc 15 phút để quản lý nhà hàng phổ biến vị trí và kiểm tra đồng phục.</li>
                            <li>Quét mã QR tại quầy thu ngân lúc bắt đầu và kết thúc ca làm để ghi nhận giờ công chuẩn xác.</li>
                            <li>Lương ca làm sẽ tự động được cộng vào <strong>Sổ thu nhập</strong> của bạn ngay khi kết thúc ca.</li>
                        </ul>
                    </div>
                </div>

                {{-- CHI NHÁNH NHÀ HÀNG & ĐÁNH GIÁ UY TÍN (GIAI ĐOẠN 5) --}}
                @php
                    $diemNhaHang = $diemNhaHang ?? \App\Models\DanhGiaCaLam::tinhDiemTrungBinhNhaHang($shift->ma_nha_hang);
                    $danhGiaMoiNhat = $danhGiaMoiNhat ?? collect();
                    $nhTongLuot = (int) ($diemNhaHang['tong_luot'] ?? 0);
                @endphp
                <div class="detail-card glass-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 10px;">
                        <h2 class="card-section-title" style="margin: 0;"><i class="fa-solid fa-store text-gold"></i> Địa Điểm Làm Việc &amp; Uy Tín</h2>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if ($nhTongLuot > 0)
                                <span style="background: #fffbeb; border: 1px solid #fde68a; color: #b45309; padding: 4px 12px; border-radius: 999px; font-weight: 800; font-size: 13px; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fa-solid fa-star text-gold"></i> {{ number_format($diemNhaHang['diem_tb'] ?? 0, 1) }} / 5.0 ({{ $nhTongLuot }} đánh giá)
                                </span>
                            @else
                                <span style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #64748b; padding: 4px 12px; border-radius: 999px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fa-regular fa-star" style="color: #94a3b8;"></i> 0 (Chưa có đánh giá)
                                </span>
                            @endif
                            @php
                                $isCaHoanThanh = ($shift->trang_thai_ca === 'hoan_thanh');
                                $thoiDiemKetThuc = $shift->ngay_lam_viec ? \Carbon\Carbon::parse($shift->ngay_lam_viec . ' ' . ($shift->gio_ket_thuc ?: '23:59:59')) : null;
                                $isExpired7Days = $thoiDiemKetThuc ? $thoiDiemKetThuc->copy()->addDays(7)->isPast() : false;
                                $canReportCa = $currentUser && $isCaHoanThanh && !$isExpired7Days;
                            @endphp
                            @if ($canReportCa)
                                <button type="button" 
                                        onclick="openReportModal({ maCaLam: {{ $shift->ma_ca_lam }}, targetUserId: {{ $nhaHang->ma_quan_ly ?? 1 }}, targetName: '{{ addslashes($tenNhaHang) }}', shiftName: '{{ addslashes($shift->ten_vi_tri_tuyen_dung) }}', isManager: false })"
                                        style="background: transparent; border: 1px solid #fca5a5; color: #dc2626; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
                                        title="Báo cáo vi phạm hoặc khiếu nại ca làm việc này">
                                    <i class="fa-solid fa-flag"></i> Báo cáo
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="branch-info-box" style="margin-bottom: 1.25rem;">
                        <p><strong>Chi nhánh:</strong> {{ $tenNhaHang }}</p>
                        <p><strong>Địa chỉ:</strong> {{ $diaChiChiTiet }}{{ $quanHuyen ? ', ' . $quanHuyen : '' }}, {{ $tinhThanh }}</p>
                        <p><strong>Điện thoại liên hệ:</strong> {{ $soDienThoai }}</p>
                    </div>

                    {{-- Nhận xét gần đây của ứng viên --}}
                    @if ($danhGiaMoiNhat->isNotEmpty())
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                            <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">
                                <i class="fa-solid fa-comments text-gold"></i> Nhận xét từ ứng viên đã làm việc:
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                @foreach ($danhGiaMoiNhat as $rv)
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                            <span style="font-weight: 700; font-size: 12.5px; color: #334155;">{{ $rv->nguoiDanhGia ? $rv->nguoiDanhGia->ho_ten : 'Ứng viên' }}</span>
                                            <div style="color: #f59e0b; font-size: 11px;">
                                                @for ($s = 1; $s <= 5; $s++)
                                                    <i class="fa-solid fa-star" style="color: {{ $s <= $rv->so_sao ? '#f59e0b' : '#cbd5e1' }};"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <div style="font-size: 12.5px; color: #64748b; line-height: 1.4;">
                                            {{ $rv->noi_dung_nhan_xet ?: 'Ứng viên chấm ' . $rv->so_sao . ' sao.' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if (method_exists($danhGiaMoiNhat, 'hasPages') && $danhGiaMoiNhat->hasPages())
                                <div class="pagination-wrapper" style="margin-top: 1rem; margin-bottom: 0;">
                                    {{ $danhGiaMoiNhat->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- CỘT PHẢI: FORM ỨNG TUYỂN TRỰC TIẾP --}}
            <div class="detail-sidebar-column">
                <div class="apply-sidebar-card glass-panel sticky-box">
                    <div class="sidebar-header">
                        @if ($isFull)
                            <span class="badge-status" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;">
                                <i class="fa-solid fa-lock"></i> Đã đủ số lượng
                            </span>
                            <h3>Ca Làm Đã Đủ Người</h3>
                            <p>Ca làm việc này đã nhận đủ số lượng ứng viên cần tuyển.</p>
                        @elseif ($daUngTuyen)
                            <span class="badge-status recruit">
                                <i class="fa-solid fa-clock-rotate-left"></i> Đã ứng tuyển
                            </span>
                            <h3>Bạn Đã Nộp Đơn</h3>
                            <p>Hồ sơ của bạn đã được ghi nhận. Vui lòng chờ quản lý nhà hàng xét duyệt.</p>
                        @else
                            <span class="badge-status recruit">
                                <i class="fa-solid fa-circle-check"></i> Đang nhận hồ sơ
                            </span>
                            <h3>Ứng Tuyển Ca Làm Này</h3>
                            <p>Điền thông tin để đăng ký nhận ca làm việc ngay lập tức.</p>
                        @endif
                    </div>

                    @if ($isFull)
                        <div class="alert-box info" style="margin: 1.5rem 0;">
                            <i class="fa-solid fa-circle-info"></i> Ca làm việc này hiện đã đạt chỉ tiêu số lượng ứng viên được duyệt. Vui lòng tham khảo các ca làm khác.
                        </div>
                        <a href="{{ url('/ca-lam-viec') }}" class="btn btn-gold btn-block" style="text-align: center;">
                            <i class="fa-solid fa-magnifying-glass"></i> Tìm Ca Làm Việc Khác
                        </a>
                    @elseif ($daUngTuyen)
                        <div class="alert-box success" style="margin: 1.5rem 0;">
                            <i class="fa-solid fa-circle-check"></i> Bạn đã ứng tuyển ca này. Theo dõi kết quả tại trang <a href="{{ url('/ung-tuyen/da-nop') }}" style="color:#065f46; font-weight:700;">Ca Làm Đã Ứng Tuyển</a>.
                        </div>
                        <a href="{{ url('/ung-tuyen/da-nop') }}" class="btn btn-gold btn-block" style="text-align: center;">
                            <i class="fa-solid fa-list-check"></i> Xem Trạng Thái Hồ Sơ
                        </a>
                    @else
                        <form id="formNopDonUngTuyen" class="modal-form">
                            @csrf
                            <input type="hidden" name="ma_ca_lam" value="{{ $shift->ma_ca_lam }}">

                            <div class="form-group">
                                <label for="detailHoTen">Họ và tên ứng viên <span class="req">*</span></label>
                                <input type="text" id="detailHoTen" name="ho_ten" required
                                       value="{{ $hoTen }}"
                                       placeholder="Họ và tên của bạn">
                            </div>

                            <div class="form-group">
                                <label for="detailSoDienThoai">Số điện thoại / Zalo <span class="req">*</span></label>
                                <input type="tel" id="detailSoDienThoai" name="so_dien_thoai" required
                                       value="{{ $soDt }}"
                                       placeholder="Số điện thoại liên hệ">
                            </div>

                            <div class="form-group">
                                <label for="detailLoiNhan">Kinh nghiệm &amp; Lời nhắn</label>
                                <textarea id="detailLoiNhan" name="loi_nhan" rows="3"
                                          placeholder="Ví dụ: Đã có kinh nghiệm làm phục vụ 6 tháng, cam kết đi làm đúng giờ..."></textarea>
                            </div>

                            <div id="applyAlert" class="alert-box" style="display: none; margin-bottom: 1rem;"></div>

                            <button type="submit" id="btnNopDon" class="btn btn-gold btn-block">
                                <i class="fa-solid fa-paper-plane"></i> Gửi thông tin ứng tuyển
                            </button>
                        </form>
                    @endif

                    <div class="security-guarantee">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Hệ thống tự động kiểm tra trùng ca &amp; bảo mật thông tin ứng viên.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formNopDonUngTuyen');
    const alertBox = document.getElementById('applyAlert');
    const btnSubmit = document.getElementById('btnNopDon');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                   || '{{ csrf_token() }}';

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            alertBox.style.display = 'none';
            alertBox.className = 'alert-box';
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';

            const formData = new FormData(form);

            fetch('{{ url("/ung-tuyen/nop-don") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Gửi thông tin ứng tuyển';

                alertBox.style.display = 'block';
                if (data.success) {
                    alertBox.className = 'alert-box success';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
                    // Ẩn form, hiện thông báo thành công
                    setTimeout(() => {
                        form.style.display = 'none';
                        const sidebar = form.closest('.apply-sidebar-card');
                        if (sidebar) {
                            const header = sidebar.querySelector('.sidebar-header');
                            if (header) {
                                header.innerHTML = `
                                    <span class="badge-status recruit">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Đã ứng tuyển
                                    </span>
                                    <h3>Hồ Sơ Đã Được Gửi!</h3>
                                    <p>Vui lòng chờ quản lý nhà hàng xét duyệt.</p>
                                `;
                            }
                        }
                    }, 800);
                } else {
                    alertBox.className = 'alert-box error';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + data.message;
                }
            })
            .catch(err => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Gửi thông tin ứng tuyển';
                alertBox.style.display = 'block';
                alertBox.className = 'alert-box error';
                alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Đã xảy ra lỗi kết nối, vui lòng thử lại.';
                console.error(err);
            });
        });
    }
});
</script>
@endsection
