@extends('layouts.app')

@section('title', 'Ca Làm Đã Ứng Tuyển - Golden Shift')

@section('content')
@php
    $appliedList = $appliedList ?? [];
    $flashSuccess = session('success');
    $flashError = session('error');
@endphp

<section class="page-header-section">
    <div class="gs-container">
        <div class="breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <span>Ca làm đã ứng tuyển</span>
        </div>
        <div class="header-job-highlight">
            <span class="sub-badge"><i class="fa-solid fa-user-check text-gold"></i> PHÂN HỆ ỨNG VIÊN</span>
            <h1 class="page-title">Danh Sách Ca Làm Việc Đã Ứng Tuyển</h1>
            <p class="page-subtitle">Theo dõi tiến độ xét duyệt và kết quả nhận ca làm việc của bạn tại chuỗi nhà hàng Golden Stars.</p>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="gs-container">
        <!-- FLASH ALERTS -->
        @if (!empty($flashSuccess))
            <div class="alert-box success" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i> {{ $flashSuccess }}
            </div>
        @endif

        @if (!empty($flashError))
            <div class="alert-box error" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $flashError }}
            </div>
        @endif

        <!-- METRIC SUMMARY -->
        <div class="admin-approval-summary glass-panel" style="margin-bottom: 2rem;">
            <div class="summary-left">
                <div class="approval-icon-box">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div>
                    <h3>Tổng Ca Đã Ứng Tuyển: <strong>{{ method_exists($appliedList, 'total') ? $appliedList->total() : count($appliedList) }} ca</strong></h3>
                    <p>Các ca làm đã được duyệt sẽ xuất hiện trong lịch làm việc chính thức của bạn.</p>
                </div>
            </div>
            <div class="summary-right">
                <a href="{{ url('/ca-lam-viec') }}" class="btn btn-gold">
                    <i class="fa-solid fa-plus"></i> Tìm Ca Làm Việc Mới
                </a>
            </div>
        </div>

        <!-- BẢNG DANH SÁCH CA ĐÃ NỘP -->
        <div class="dashboard-table-card glass-panel">
            <div class="card-table-header">
                <div>
                    <h3 class="table-card-title"><i class="fa-solid fa-clock-rotate-left text-gold"></i> Lịch Sử Đăng Ký Ca Làm</h3>
                    <p class="table-card-sub">Trạng thái phản hồi từ Quản lý nhà hàng</p>
                </div>
            </div>

            @if (empty($appliedList) || count($appliedList) === 0)
                <div class="empty-state-box">
                    <i class="fa-solid fa-file-circle-plus empty-icon"></i>
                    <h3>Bạn chưa ứng tuyển ca làm việc nào</h3>
                    <p>Hãy khám phá các ca làm việc hấp dẫn đang tuyển dụng và gửi đơn ứng tuyển ngay hôm nay.</p>
                    <a href="{{ url('/ca-lam-viec') }}" class="btn btn-gold">Tìm Ca Làm Việc</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="gs-table">
                        <thead>
                            <tr>
                                <th style="width: 95px; white-space: nowrap;">Mã Đơn</th>
                                <th>Thương Hiệu & Vị Trí</th>
                                <th>Chi Nhánh Làm Việc</th>
                                <th style="width: 140px; white-space: nowrap;">Ngày & Khung Giờ</th>
                                <th style="width: 110px; white-space: nowrap;">Lương/Giờ</th>
                                <th style="width: 130px; white-space: nowrap;">Thời Gian Nộp</th>
                                <th class="text-center" style="width: 140px; white-space: nowrap;">Trạng Thái Xét Duyệt</th>
                                <th class="text-center" style="width: 170px; white-space: nowrap;">Chấm Công (Vào/Ra Ca)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $today = \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d');
                                // Ưu tiên mã ứng tuyển đang trong ca từ Controller (trên toàn bộ dữ liệu)
                                $activeMaUngTuyen = (int) ($activeMaUngTuyen ?? 0);
                                if ($activeMaUngTuyen <= 0 && isset($appliedList)) {
                                    foreach ($appliedList as $_it) {
                                        $_cc = $_it->chamCong ?? null;
                                        if ($_cc && in_array($_cc->trang_thai_cham_cong, ['dung_gio', 'di_muon']) && empty($_cc->thoi_gian_ra_thuc_te)) {
                                            $activeMaUngTuyen = (int) ($_it->ma_ung_tuyen ?? 0);
                                            break;
                                        }
                                    }
                                }
                                $hasActiveShift = $activeMaUngTuyen > 0;
                            @endphp
                            @foreach ($appliedList as $item)
                                @php
                                    $itemArr = is_array($item) ? $item : (method_exists($item, 'toArray') ? $item->toArray() : (array)$item);
                                    $status = $itemArr['trang_thai_ung_tuyen'] ?? 'cho_duyet';
                                    $rejectReason = $itemArr['ghi_chu_nha_tuyen_dung'] ?? '';
                                    $tenViTri = $itemArr['ten_vi_tri_tuyen_dung'] ?? ($item->caLamViec->ten_vi_tri_tuyen_dung ?? '');
                                    $tenThuongHieu = $itemArr['ten_thuong_hieu'] ?? ($item->caLamViec->nhaHang->thuongHieu->ten_thuong_hieu ?? 'Golden Stars');
                                    $tenNhaHang = $itemArr['ten_nha_hang'] ?? ($item->caLamViec->nhaHang->ten_nha_hang ?? '');
                                    $diaChi = $itemArr['dia_chi_chi_tiet'] ?? ($item->caLamViec->nhaHang->dia_chi_chi_tiet ?? '');
                                    $ngayLam = $itemArr['ngay_lam_viec'] ?? ($item->caLamViec->ngay_lam_viec ?? '');
                                    $gioBatDau = $itemArr['gio_bat_dau'] ?? ($item->caLamViec->gio_bat_dau ?? '');
                                    $gioKetThuc = $itemArr['gio_ket_thuc'] ?? ($item->caLamViec->gio_ket_thuc ?? '');
                                    $luong = $itemArr['muc_luong_theo_gio'] ?? ($item->caLamViec->muc_luong_theo_gio ?? 0);
                                @endphp
                                <tr>
                                    <td style="white-space: nowrap;"><span class="badge-code">#UT-{{ $itemArr['ma_ung_tuyen'] }}</span></td>
                                    <td>
                                        <div style="font-weight: 700; color: #1e293b;">{{ $tenViTri }}</div>
                                        <span class="sub-badge" style="font-size: 0.75rem; padding: 2px 8px; margin-top: 4px; display: inline-block;">{{ $tenThuongHieu }}</span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #475569;">{{ $tenNhaHang }}</div>
                                        <div style="font-size: 0.82rem; color: #94a3b8;"><i class="fa-solid fa-location-dot text-gold"></i> {{ $diaChi }}</div>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <div style="font-weight: 600; color: #1e293b;"><i class="fa-solid fa-calendar-day text-gold"></i> {{ $ngayLam ? date('d/m/Y', strtotime($ngayLam)) : '' }}</div>
                                        <div style="font-size: 0.82rem; color: #64748b;"><i class="fa-regular fa-clock text-gold"></i> {{ substr($gioBatDau, 0, 5) }} - {{ substr($gioKetThuc, 0, 5) }}</div>
                                    </td>
                                    <td style="white-space: nowrap; font-weight: 700; color: #b45309;">
                                        {{ number_format($luong, 0, ',', '.') }} VNĐ/giờ
                                    </td>
                                    <td style="white-space: nowrap; font-size: 0.82rem; color: #64748b;">
                                        {{ !empty($itemArr['ngay_nop_don']) ? \Carbon\Carbon::parse($itemArr['ngay_nop_don'])->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : '' }}
                                    </td>
                                    <td class="text-center col-trang-thai-ung-tuyen" id="status-cell-{{ $itemArr['ma_ung_tuyen'] }}" data-ma-ung-tuyen="{{ $itemArr['ma_ung_tuyen'] }}" data-current-status="{{ $status }}" style="white-space: nowrap;">
                                        @php
                                        // Tính trạng thái hiển thị chuẩn xác (5 trạng thái)
                                        $displayStatus = method_exists($item, 'getDisplayStatus')
                                            ? $item->getDisplayStatus()
                                            : $status;
                                    @endphp
                                    @if ($displayStatus === 'da_huy')
                                        {{-- Ca làm bị hủy bởi quản lý --}}
                                        <span class="badge-status inactive nowrap"
                                              style="background:#fee2e2; color:#991b1b; border-color:#fca5a5;"
                                              title="Ca làm việc đã bị hủy bởi quản lý nhà hàng">
                                            <i class="fa-solid fa-ban"></i> Đã hủy
                                        </span>
                                        <div style="font-size: 0.73rem; color: #dc2626; margin-top: 3px;">
                                            <i class="fa-solid fa-circle-info"></i> Ca bị hủy bởi QL
                                        </div>
                                    @elseif ($displayStatus === 'hoan_thanh')
                                        {{-- Đã được duyệt + ca đã qua ngày = Hoàn thành --}}
                                        <span class="badge-status nowrap"
                                              style="background:#f0fdf4; color:#166534; border-color:#bbf7d0;">
                                            <i class="fa-solid fa-check-double"></i> Đã hoàn thành
                                        </span>
                                    @elseif ($displayStatus === 'da_nhan')
                                        {{-- Đã duyệt, ca chưa đến hoặc đang diễn ra --}}
                                        <span class="badge-status recruit nowrap"
                                              style="background:#d1fae5; color:#065f46; border-color:#a7f3d0;">
                                            <i class="fa-solid fa-circle-check"></i> Đã duyệt
                                        </span>
                                    @elseif ($displayStatus === 'tu_choi')
                                        {{-- Bị từ chối --}}
                                        <span class="badge-status inactive nowrap"
                                              style="background:#fee2e2; color:#991b1b; border-color:#fca5a5;"
                                              title="{{ $rejectReason ? 'Lý do: ' . $rejectReason : '' }}">
                                            <i class="fa-solid fa-circle-xmark"></i> Đã từ chối
                                        </span>
                                        @if ($rejectReason)
                                            <div style="font-size: 0.75rem; color: #dc2626; margin-top: 3px; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                 title="{{ $rejectReason }}">
                                                {{ $rejectReason }}
                                            </div>
                                        @endif
                                    @else
                                        {{-- Chờ duyệt (mặc định) --}}
                                        <span class="badge-status nowrap"
                                              style="background:#fef3c7; color:#92400e; border-color:#fde68a;">
                                            <i class="fa-solid fa-hourglass-half"></i> Chờ duyệt
                                        </span>
                                    @endif
                                    </td>
                                    @php
                                        $chamCong = $item->chamCong ?? null;
                                        $statusCC = $chamCong ? $chamCong->trang_thai_cham_cong : null;
                                        $shiftDate = !empty($ngayLam) ? date('Y-m-d', strtotime($ngayLam)) : null;
                                    @endphp
                                    <td class="text-center col-cham-cong" id="cham-cong-cell-{{ $itemArr['ma_ung_tuyen'] }}" data-cc-status="{{ $statusCC ?? '' }}" style="white-space: nowrap;">
                                        @if ($chamCong && in_array($statusCC, ['da_xac_nhan', 'tu_dong_duyet']))
                                            <div>
                                                <span class="badge-status recruit nowrap" style="background: #d1fae5; color: #065f46; border-color: #a7f3d0; font-size: 0.78rem;">
                                                    <i class="fa-solid fa-circle-check"></i> {{ $statusCC === 'tu_dong_duyet' ? 'Đã duyệt (Tự động)' : 'Đã duyệt công' }} ({{ $chamCong->so_gio_lam_thuc_te }}h)
                                                </span>
                                                @if ($statusCC === 'tu_dong_duyet')
                                                    <div style="font-size: 0.71rem; color: #0284c7; margin-top: 2px; font-weight: 600;">
                                                        <i class="fa-solid fa-robot"></i> Tự động duyệt sau 60 phút
                                                    </div>
                                                @endif
                                            </div>
                                            <div style="margin-top: 5px; display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                                @php
                                                    $currentUid = auth()->id() ?? (session('user')['ma_nguoi_dung'] ?? (session('user')->ma_nguoi_dung ?? 0));
                                                    $danhGiaCuaMinh = \App\Models\DanhGiaCaLam::where('ma_ca_lam', $itemArr['ma_ca_lam'])
                                                        ->where('ma_nguoi_danh_gia', $currentUid)
                                                        ->first();
                                                    $targetQlId = $item->caLamViec && $item->caLamViec->nhaHang ? ($item->caLamViec->nhaHang->ma_quan_ly ?? 1) : 1;

                                                    $thoiDiemKetThuc = null;
                                                    if ($chamCong && $chamCong->thoi_gian_ra_thuc_te) {
                                                        $thoiDiemKetThuc = \Carbon\Carbon::parse($chamCong->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh');
                                                    } elseif (!empty($ngayLam)) {
                                                        $thoiDiemKetThuc = \Carbon\Carbon::parse($ngayLam . ' 23:59:59', 'Asia/Ho_Chi_Minh');
                                                    }
                                                    $isExpired7Days = $thoiDiemKetThuc ? $thoiDiemKetThuc->copy()->addDays(7)->isPast() : false;
                                                @endphp

                                                @if ($danhGiaCuaMinh)
                                                    <span style="font-size: 0.72rem; color: #b45309; background: #fef3c7; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Bạn đã đánh giá: {{ $danhGiaCuaMinh->so_sao }} sao">
                                                        <i class="fa-solid fa-star text-gold"></i> Đã đánh giá ({{ $danhGiaCuaMinh->so_sao }}⭐)
                                                    </span>
                                                @elseif (!$isExpired7Days)
                                                    <button type="button" 
                                                            onclick="openReviewModal({ maCaLam: {{ $itemArr['ma_ca_lam'] }}, targetUserId: {{ $targetQlId }}, targetName: '{{ addslashes($tenNhaHang ?? 'Nhà hàng') }}', shiftName: '{{ addslashes($tenViTri ?? '') }}', isManager: false, isForced: true })"
                                                            class="btn btn-sm btn-gold"
                                                            style="padding: 2px 8px; font-size: 0.72rem; border-radius: 4px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;">
                                                        <i class="fa-solid fa-star"></i> Đánh giá
                                                    </button>
                                                @else
                                                    <span style="font-size: 0.72rem; color: #94a3b8; background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Đã quá thời hạn 7 ngày để đánh giá ca làm việc này">
                                                        <i class="fa-solid fa-clock-rotate-left"></i> Hết hạn đánh giá
                                                    </span>
                                                @endif

                                                @if (!$isExpired7Days)
                                                    <button type="button"
                                                            onclick="openReportModal({ maCaLam: {{ $itemArr['ma_ca_lam'] }}, targetUserId: {{ $targetQlId }}, targetName: '{{ addslashes($tenNhaHang ?? 'Nhà hàng') }}', shiftName: '{{ addslashes($tenViTri ?? '') }}', isManager: false })"
                                                            style="background: transparent; border: 1px solid #fca5a5; color: #dc2626; font-size: 0.72rem; border-radius: 4px; cursor: pointer; padding: 2px 6px; display: inline-flex; align-items: center; gap: 3px;"
                                                            title="Báo cáo vi phạm hoặc khiếu nại ca làm này">
                                                        <i class="fa-solid fa-flag"></i> Báo cáo
                                                    </button>
                                                @endif
                                            </div>
                                            <a href="{{ url('/ung-vien/so-thu-nhap') }}" style="font-size: 0.72rem; color: #b45309; text-decoration: underline; margin-top: 3px; display: inline-block;">
                                                <i class="fa-solid fa-wallet"></i> Xem Sổ thu nhập
                                            </a>
                                        @elseif ($chamCong && $statusCC === 'cho_xac_nhan')
                                            <div>
                                                <span class="badge-status nowrap" style="background: #fef3c7; color: #92400e; border-color: #fde68a; font-size: 0.78rem;">
                                                    <i class="fa-solid fa-hourglass-half"></i> Chờ QL duyệt ({{ $chamCong->so_gio_lam_thuc_te }}h)
                                                </span>
                                            </div>
                                            @if ($chamCong->thoi_gian_vao_thuc_te || $chamCong->thoi_gian_ra_thuc_te)
                                                <div style="font-size: 0.72rem; color: #64748b; margin-top: 3px;">
                                                    {{ $chamCong->thoi_gian_vao_thuc_te ? \Carbon\Carbon::parse($chamCong->thoi_gian_vao_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i') : '—' }}
                                                    -
                                                    {{ $chamCong->thoi_gian_ra_thuc_te ? \Carbon\Carbon::parse($chamCong->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : '—' }}
                                                </div>
                                            @endif
                                            <div style="margin-top: 5px; display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                                @php
                                                    $currentUid = auth()->id() ?? (session('user')['ma_nguoi_dung'] ?? (session('user')->ma_nguoi_dung ?? 0));
                                                    $danhGiaCuaMinh = \App\Models\DanhGiaCaLam::where('ma_ca_lam', $itemArr['ma_ca_lam'])
                                                        ->where('ma_nguoi_danh_gia', $currentUid)
                                                        ->first();
                                                    $targetQlId = $item->caLamViec && $item->caLamViec->nhaHang ? ($item->caLamViec->nhaHang->ma_quan_ly ?? 1) : 1;

                                                    $thoiDiemKetThuc = null;
                                                    if ($chamCong && $chamCong->thoi_gian_ra_thuc_te) {
                                                        $thoiDiemKetThuc = \Carbon\Carbon::parse($chamCong->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh');
                                                    } elseif (!empty($ngayLam)) {
                                                        $thoiDiemKetThuc = \Carbon\Carbon::parse($ngayLam . ' 23:59:59', 'Asia/Ho_Chi_Minh');
                                                    }
                                                    $isExpired7Days = $thoiDiemKetThuc ? $thoiDiemKetThuc->copy()->addDays(7)->isPast() : false;
                                                @endphp

                                                @if ($danhGiaCuaMinh)
                                                    <span style="font-size: 0.72rem; color: #b45309; background: #fef3c7; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Bạn đã đánh giá: {{ $danhGiaCuaMinh->so_sao }} sao">
                                                        <i class="fa-solid fa-star text-gold"></i> Đã đánh giá ({{ $danhGiaCuaMinh->so_sao }}⭐)
                                                    </span>
                                                @elseif (!$isExpired7Days)
                                                    <button type="button" 
                                                            onclick="openReviewModal({ maCaLam: {{ $itemArr['ma_ca_lam'] }}, targetUserId: {{ $targetQlId }}, targetName: '{{ addslashes($tenNhaHang ?? 'Nhà hàng') }}', shiftName: '{{ addslashes($tenViTri ?? '') }}', isManager: false, isForced: true })"
                                                            class="btn btn-sm btn-gold"
                                                            style="padding: 2px 8px; font-size: 0.72rem; border-radius: 4px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;">
                                                        <i class="fa-solid fa-star"></i> Đánh giá
                                                    </button>
                                                @else
                                                    <span style="font-size: 0.72rem; color: #94a3b8; background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;" title="Đã quá thời hạn 7 ngày để đánh giá ca làm việc này">
                                                        <i class="fa-solid fa-clock-rotate-left"></i> Hết hạn đánh giá
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif ($chamCong && in_array($statusCC, ['dung_gio', 'di_muon']) && empty($chamCong->thoi_gian_ra_thuc_te))
                                            <!-- ĐANG TRONG CA -> NÚT RA CA -->
                                            <div style="margin-bottom: 4px;">
                                                <span class="badge-status nowrap" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0; font-size: 0.72rem; padding: 2px 6px;">
                                                    <i class="fa-solid fa-person-digging"></i> Đang làm việc{{ $chamCong->thoi_gian_vao_thuc_te ? ' (Vào: ' . \Carbon\Carbon::parse($chamCong->thoi_gian_vao_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i') . ')' : '' }}
                                                </span>
                                            </div>
                                            <button type="button"
                                                    class="btn btn-sm btn-trigger-qr-checkout"
                                                    onclick="triggerQrAttendanceRequest({{ $itemArr['ma_ung_tuyen'] }}, 'ra_ca', '{{ addslashes($tenViTri) }}', '{{ addslashes($tenNhaHang) }}', {{ (int) ($itemArr['ma_ca_lam'] ?? 0) }})"
                                                    style="background: #dc2626; color: #fff; border: 1px solid #dc2626; padding: 3px 10px; font-weight: 700; font-size: 0.8rem; border-radius: 6px; cursor: pointer;">
                                                <i class="fa-solid fa-stop"></i> Ra Ca
                                            </button>
                                        @elseif ($displayStatus === 'da_nhan')
                                            @if ($shiftDate && $shiftDate > $today)
                                                <!-- CHƯA TỚI NGÀY -->
                                                <span class="badge-status nowrap" style="background: #f1f5f9; color: #64748b; border-color: #cbd5e1; font-size: 0.78rem;" title="Chỉ được vào ca đúng ngày làm việc ({{ date('d/m/Y', strtotime($shiftDate)) }})">
                                                    <i class="fa-regular fa-calendar-clock"></i> Chưa tới ngày
                                                </span>
                                            @elseif ($shiftDate && $shiftDate < $today)
                                                <!-- QUÁ NGÀY LÀM VIỆC -->
                                                <span class="badge-status nowrap" style="background: #fee2e2; color: #991b1b; border-color: #fca5a5; font-size: 0.78rem;" title="Ca làm việc đã quá ngày">
                                                    <i class="fa-solid fa-calendar-xmark"></i> Quá ngày làm
                                                </span>
                                            @elseif ($hasActiveShift && (int)$activeMaUngTuyen !== (int)$itemArr['ma_ung_tuyen'])
                                                <!-- ĐANG BẬN CA KHÁC: RÀNG BUỘC KHÔNG THỂ VÀO 2 CA CÙNG LÚC -->
                                                <button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 4px 10px; font-weight: 600; font-size: 0.78rem; border-radius: 6px; cursor: not-allowed;" title="Bạn đang trong ca làm việc khác và chưa bấm Ra ca! Không thể vào 2 ca cùng một lúc.">
                                                    <i class="fa-solid fa-lock"></i> Đang bận ca khác
                                                </button>
                                            @else
                                                <!-- NÚT VÀO CA HỢP LỆ -> TỰ ĐỘNG POPUP QR 30S TRÊN MÀN HÌNH QUẢN LÝ -->
                                                <button type="button"
                                                        class="btn btn-sm btn-gold btn-trigger-qr-checkin"
                                                        onclick="triggerQrAttendanceRequest({{ $itemArr['ma_ung_tuyen'] }}, 'vao_ca', '{{ addslashes($tenViTri) }}', '{{ addslashes($tenNhaHang) }}', {{ (int) ($itemArr['ma_ca_lam'] ?? 0) }})"
                                                        style="padding: 4px 12px; font-weight: 700; font-size: 0.82rem; border-radius: 6px; cursor: pointer;">
                                                    <i class="fa-solid fa-play"></i> Vào Ca
                                                </button>
                                            @endif
                                        @elseif ($displayStatus === 'hoan_thanh')
                                            <!-- CA ĐÃ HOÀN THÀNH MÀ CHƯA CHẤM CÔNG -->
                                            <span class="badge-status nowrap" style="background: #f8fafc; color: #64748b; border-color: #e2e8f0; font-size: 0.78rem;" title="Ca làm việc đã hoàn thành/kết thúc">
                                                <i class="fa-solid fa-check"></i> Đã kết thúc
                                            </span>
                                        @else
                                            <span style="color: #94a3b8; font-size: 0.82rem;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (method_exists($appliedList, 'hasPages') && $appliedList->hasPages())
                    <div class="pagination-wrapper">
                        {{ $appliedList->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>

{{-- ================================================================
     ỨNG VIÊN REAL-TIME POLLING (mỗi 4 giây)
     Tự động cập nhật trạng thái đơn ứng tuyển khi Quản lý duyệt/từ chối.
================================================================ --}}
@push('scripts')
<script>
(function() {
    'use strict';

    const POLL_MS = 4000;

    function statusBadgeHtml(status, ghiChu) {
        if (status === 'da_huy') {
            return '<span class="badge-status inactive nowrap" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;"><i class="fa-solid fa-ban"></i> Đã hủy</span><div style="font-size:0.73rem;color:#dc2626;margin-top:3px;"><i class="fa-solid fa-circle-info"></i> Ca bị hủy bởi QL</div>';
        }
        if (status === 'hoan_thanh') {
            return '<span class="badge-status nowrap" style="background:#f0fdf4;color:#166534;border-color:#bbf7d0;"><i class="fa-solid fa-check-double"></i> Đã hoàn thành</span>';
        }
        if (status === 'da_nhan') {
            return '<span class="badge-status recruit nowrap" style="background:#d1fae5;color:#065f46;border-color:#a7f3d0;"><i class="fa-solid fa-circle-check"></i> Đã duyệt</span>';
        }
        if (status === 'tu_choi') {
            const esc = ghiChu ? ghiChu.replace(/"/g, '&quot;') : '';
            const reasonHtml = ghiChu ? `<div style="font-size:0.75rem;color:#dc2626;margin-top:3px;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${esc}">${esc}</div>` : '';
            return `<span class="badge-status inactive nowrap" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;${esc ? ' title=\"'+esc+'\"' : ''}"><i class="fa-solid fa-circle-xmark"></i> Đã từ chối</span>${reasonHtml}`;
        }
        return '<span class="badge-status nowrap" style="background:#fef3c7;color:#92400e;border-color:#fde68a;"><i class="fa-solid fa-hourglass-half"></i> Chờ duyệt</span>';
    }

    function showCandidateToast(msg, type) {
        let container = document.getElementById('gsGlobalToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'gsGlobalToastContainer';
            container.style.cssText = 'position:fixed;top:72px;right:20px;z-index:999999;display:flex;flex-direction:column;gap:8px;max-width:360px;width:100%;pointer-events:none;';
            document.body.appendChild(container);
        }
        const isSuccess = type === 'success';
        const toast = document.createElement('div');
        toast.style.cssText = 'background:' + (isSuccess ? '#065f46' : '#991b1b') + ';color:#fff;border-left:4px solid ' + (isSuccess ? '#10b981' : '#ef4444') + ';border-radius:10px;padding:12px 16px;font-size:0.875rem;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);pointer-events:auto;opacity:0;transition:opacity .3s;';
        toast.innerHTML = '<i class="fa-solid ' + (isSuccess ? 'fa-circle-check' : 'fa-circle-xmark') + '" style="font-size:1rem;flex-shrink:0;"></i><span>' + msg + '</span>';
        container.appendChild(toast);
        requestAnimationFrame(function() { toast.style.opacity = '1'; });
        setTimeout(function() { toast.style.opacity = '0'; setTimeout(function() { toast.remove(); }, 400); }, 6000);
    }

    // Xây dựng map trạng thái ban đầu CHÍNH XÁC từ cột Trạng Thái Xét Duyệt và Chấm Công
    const _prevMap = {};
    const _prevCcMap = {};
    let _isFirstPoll = true;

    document.querySelectorAll('.col-trang-thai-ung-tuyen').forEach(function(cell) {
        const id = parseInt(cell.getAttribute('data-ma-ung-tuyen'));
        const status = cell.getAttribute('data-current-status');
        if (id && status) {
            _prevMap[id] = status;
        }
    });

    document.querySelectorAll('.col-cham-cong').forEach(function(cell) {
        const idAttr = (cell.id || '').replace('cham-cong-cell-', '');
        const id = parseInt(idAttr, 10);
        const ccStatus = cell.getAttribute('data-cc-status') || '';
        if (id) {
            _prevCcMap[id] = ccStatus;
        }
    });

    function pollApplicationStatus() {
        fetch('/api/applications/status', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.ok ? r.json() : null; })
        .then(function(data) {
            if (!data || !data.success || !data.data) return;

            let shouldReloadForAttendance = false;

            data.data.forEach(function(app) {
                const id     = app.ma_ung_tuyen;
                const status = app.trang_thai_ung_tuyen;
                const ccStatus = app.trang_thai_cham_cong || '';
                const ghiChu = app.ghi_chu_nha_tuyen_dung || '';
                const prev   = _prevMap[id];
                const prevCc = _prevCcMap[id];

                // Theo dõi chuyển trạng thái chấm công (cho_xac_nhan -> da_xac_nhan / tu_dong_duyet)
                if (prevCc === 'cho_xac_nhan' && (ccStatus === 'da_xac_nhan' || ccStatus === 'tu_dong_duyet')) {
                    _prevCcMap[id] = ccStatus;
                    showCandidateToast('Ca làm #UT-' + id + ' đã được duyệt công!', 'success');
                    shouldReloadForAttendance = true;
                } else if (prevCc === undefined || _isFirstPoll) {
                    _prevCcMap[id] = ccStatus;
                } else {
                    _prevCcMap[id] = ccStatus;
                }

                // CHỈ cập nhật và bắn Toast khi trạng thái THỰC SỰ THAY ĐỔI và KHÔNG PHẢI lần poll đầu tiên
                if (!_isFirstPoll && prev !== undefined && prev !== status) {
                    // 1. Cập nhật cột Trạng Thái Xét Duyệt
                    const statusCell = document.getElementById('status-cell-' + id);
                    if (statusCell) {
                        statusCell.innerHTML = statusBadgeHtml(status, ghiChu);
                        statusCell.setAttribute('data-current-status', status);
                    }

                    // 2. Cập nhật cột Chấm Công tương ứng
                    const chamCongCell = document.getElementById('cham-cong-cell-' + id);
                    if (chamCongCell) {
                        if (status === 'da_nhan' || status === 'da_duyet') {
                            const todayStr = '{{ \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d') }}';
                            const shiftDate = app.ngay_lam_viec ? app.ngay_lam_viec.substring(0, 10) : '';
                            if (shiftDate && shiftDate > todayStr) {
                                chamCongCell.innerHTML = '<span class="badge-status nowrap" style="background: #f1f5f9; color: #64748b; border-color: #cbd5e1; font-size: 0.78rem;"><i class="fa-regular fa-calendar-clock"></i> Chưa tới ngày</span>';
                            } else if (shiftDate && shiftDate < todayStr) {
                                chamCongCell.innerHTML = '<span class="badge-status nowrap" style="background: #fee2e2; color: #991b1b; border-color: #fca5a5; font-size: 0.78rem;"><i class="fa-solid fa-calendar-xmark"></i> Quá ngày làm</span>';
                            } else {
                                const safePos = (app.ten_vi_tri_tuyen_dung || 'Ca làm việc').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                                const safeBranch = (app.ten_nha_hang || 'Nhà hàng').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                                const shiftIdVal = Number(app.ma_ca_lam || 0);
                                chamCongCell.innerHTML = `
                                    <button type="button" class="btn btn-sm btn-gold btn-trigger-qr-checkin" style="padding: 4px 12px; font-weight: 700; font-size: 0.82rem; border-radius: 6px; cursor: pointer;" onclick="triggerQrAttendanceRequest(${id}, 'vao_ca', '${safePos}', '${safeBranch}', ${shiftIdVal})">
                                        <i class="fa-solid fa-play"></i> Vào Ca
                                    </button>
                                `;
                            }
                        } else if (status === 'tu_choi' || status === 'da_huy') {
                            chamCongCell.innerHTML = '<span style="color: #94a3b8; font-size: 0.82rem;">—</span>';
                        }
                    }

                    // 3. Thông báo Toast
                    if (status === 'da_nhan' || status === 'da_duyet') {
                        showCandidateToast('🎉 Chúc mừng! Đơn #UT-' + id + ' đã được DUYỆT bởi Quản lý nhà hàng!', 'success');
                    } else if (status === 'tu_choi') {
                        showCandidateToast('❌ Đơn #UT-' + id + ' đã bị từ chối.' + (ghiChu ? ' Lý do: ' + ghiChu : ''), 'error');
                    }

                    _prevMap[id] = status;
                } else if (_isFirstPoll || prev === undefined) {
                    _prevMap[id] = status;
                }
            });

            _isFirstPoll = false;

            if (shouldReloadForAttendance) {
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(function() {});
    }

    // Chỉ chạy khi có đơn trong bảng
    if (Object.keys(_prevMap).length > 0) {
        setInterval(pollApplicationStatus, POLL_MS);
    }

    window._showCandidateQrToast = showCandidateToast;
})();
</script>

<div id="modalCandidateQrScan" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.74); backdrop-filter: blur(4px); z-index: 10050; align-items: center; justify-content: center; padding: 1rem;">
    <div style="width: 100%; max-width: 480px; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 55px rgba(15, 23, 42, 0.35); border: 1px solid #e2e8f0;">
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fbbf24; padding: 1.05rem 1.35rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 800; font-size: 1.02rem; display: flex; align-items: center; gap: 0.6rem;">
                <i class="fa-solid fa-qrcode"></i>
                <span id="candidateQrModalHeading">Quét Mã QR Chấm Công (30s)</span>
            </div>
            <button type="button" onclick="closeCandidateQrModal()" style="width: 32px; height: 32px; border-radius: 50%; border: none; background: rgba(255,255,255,0.14); color: #f8fafc; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="candidateQrScanForm" style="padding: 1.25rem 1.35rem; display: grid; gap: 0.85rem;">
            <input type="hidden" id="candidateQrShiftId" value="">
            <input type="hidden" id="candidateQrAction" value="vao_ca">
            <input type="hidden" id="candidateQrMaUngTuyen" value="">

            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 0.8rem 0.95rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.3rem;">
                    <span id="candidateQrShiftSummary" style="font-weight: 800; font-size: 0.92rem; color: #78350f;">Ca làm việc</span>
                    <span id="candidateQrActionBadge" style="font-size: 0.74rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 999px; background: #d1fae5; color: #065f46;">VÀO CA</span>
                </div>
                <div id="candidateQrBranchSummary" style="font-size: 0.82rem; color: #b45309;">Nhà hàng chi nhánh</div>
                <div style="font-size: 0.79rem; color: #475569; margin-top: 0.35rem; line-height: 1.4;">
                    Mã QR hiệu lực <strong>30 giây</strong> đã tự động bật trên màn hình <strong>Quản lý nhà hàng</strong>. Hãy quét mã tại quầy quản lý hoặc dán token để hoàn tất chấm công.
                </div>
            </div>

            <div id="candidateQrAlert" style="display: none; padding: 0.7rem 0.9rem; border-radius: 10px; font-size: 0.86rem; font-weight: 600;"></div>

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" id="btnToggleQrCamera" class="btn btn-outline btn-sm" style="border-color: #cbd5e1; color: #1e293b; font-weight: 700;">
                    <i class="fa-solid fa-camera"></i> Bật Camera Quét QR
                </button>
                <button type="button" id="btnResendQrRequest" class="btn btn-outline btn-sm" style="border-color: rgba(217, 119, 6, 0.45); color: #b45309; background: #fffbeb; font-weight: 700;">
                    <i class="fa-solid fa-rotate"></i> Gửi lại yêu cầu QR tới Quản lý
                </button>
            </div>

            <div id="candidateQrCameraWrap" style="display: none; background: #0f172a; border-radius: 12px; padding: 0.5rem; overflow: hidden;">
                <div id="candidateQrReaderBox" style="width: 100%; border-radius: 8px; overflow: hidden;"></div>
            </div>

            <div>
                <label for="candidateQrToken" style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Mã Token hoặc Link từ Mã QR trên màn hình Quản lý</label>
                <input type="text" id="candidateQrToken" required placeholder="Quét QR hoặc dán token / link QR từ màn hình Quản lý..."
                       style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 0.9rem; font-family: monospace; box-sizing: border-box;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.6rem; padding-top: 0.25rem;">
                <button type="button" onclick="closeCandidateQrModal()" class="btn btn-outline">Hủy</button>
                <button type="submit" id="btnSubmitCandidateQr" class="btn btn-gold" style="font-weight: 700;">
                    <i class="fa-solid fa-check"></i> Xác Nhận Chấm Công
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const QR_REQUEST_URL = {!! json_encode(route('cham_cong.qr_request'), JSON_UNESCAPED_SLASHES) !!};
    const QR_SCAN_URL = {!! json_encode(route('cham_cong.qr_scan'), JSON_UNESCAPED_SLASHES) !!};
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let html5QrScanner = null;
    let isCameraActive = false;
    let currentCtx = {
        maUngTuyen: 0,
        maCaLam: 0,
        loaiQr: 'vao_ca',
        tenViTri: '',
        tenNhaHang: ''
    };

    function showModalAlert(message, type) {
        const alertBox = document.getElementById('candidateQrAlert');
        if (!alertBox) return;
        if (!message) {
            alertBox.style.display = 'none';
            alertBox.textContent = '';
            return;
        }

        alertBox.style.display = 'block';
        if (type === 'success') {
            alertBox.style.background = '#ecfdf5';
            alertBox.style.border = '1px solid #a7f3d0';
            alertBox.style.color = '#065f46';
        } else if (type === 'info') {
            alertBox.style.background = '#eff6ff';
            alertBox.style.border = '1px solid #bfdbfe';
            alertBox.style.color = '#1e40af';
        } else {
            alertBox.style.background = '#fef2f2';
            alertBox.style.border = '1px solid #fecaca';
            alertBox.style.color = '#b91c1c';
        }
        alertBox.textContent = message;
    }

    function stopQrCamera() {
        const wrap = document.getElementById('candidateQrCameraWrap');
        const toggleBtn = document.getElementById('btnToggleQrCamera');
        if (html5QrScanner && isCameraActive) {
            html5QrScanner.stop().then(function () {
                html5QrScanner.clear();
            }).catch(function () {});
        }
        isCameraActive = false;
        if (wrap) wrap.style.display = 'none';
        if (toggleBtn) toggleBtn.innerHTML = '<i class="fa-solid fa-camera"></i> Bật Camera Quét QR';
    }

    function extractQrParams(rawInput) {
        const raw = String(rawInput || '').trim();
        if (!raw) return { token: '', caLamId: 0, loaiQr: '' };

        try {
            if (/^https?:\/\//i.test(raw) || raw.includes('qr_token=')) {
                const parsedUrl = new URL(raw, window.location.origin);
                return {
                    token: (parsedUrl.searchParams.get('qr_token') || parsedUrl.searchParams.get('token') || raw).trim(),
                    caLamId: Number(parsedUrl.searchParams.get('ma_ca_lam') || 0),
                    loaiQr: parsedUrl.searchParams.get('loai_qr') || ''
                };
            }
        } catch (e) {
            // Fall back to raw string token
        }

        return { token: raw, caLamId: 0, loaiQr: '' };
    }

    function loadHtml5QrLibrary() {
        return new Promise(function (resolve, reject) {
            if (typeof window.Html5Qrcode === 'function') {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
            script.async = true;
            script.onload = function () { resolve(); };
            script.onerror = function () { reject(new Error('Không thể tải thư viện camera QR.')); };
            document.head.appendChild(script);
        });
    }

    function openCandidateQrModalWithContext(ctx, serverMessage) {
        currentCtx = ctx;
        const modal = document.getElementById('modalCandidateQrScan');

        document.getElementById('candidateQrShiftId').value = String(ctx.maCaLam || '');
        document.getElementById('candidateQrAction').value = ctx.loaiQr || 'vao_ca';
        document.getElementById('candidateQrMaUngTuyen').value = String(ctx.maUngTuyen || '');
        document.getElementById('candidateQrToken').value = '';

        const shiftSummary = document.getElementById('candidateQrShiftSummary');
        if (shiftSummary) {
            shiftSummary.textContent = '#CA-' + ctx.maCaLam + ' — ' + (ctx.tenViTri || 'Ca làm việc');
        }
        const branchSummary = document.getElementById('candidateQrBranchSummary');
        if (branchSummary) {
            branchSummary.textContent = ctx.tenNhaHang || 'Nhà hàng chi nhánh';
        }
        const badgeEl = document.getElementById('candidateQrActionBadge');
        if (badgeEl) {
            if (ctx.loaiQr === 'ra_ca') {
                badgeEl.style.background = '#fee2e2';
                badgeEl.style.color = '#991b1b';
                badgeEl.textContent = 'RA CA';
            } else {
                badgeEl.style.background = '#d1fae5';
                badgeEl.style.color = '#065f46';
                badgeEl.textContent = 'VÀO CA';
            }
        }

        showModalAlert(serverMessage || '', 'info');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    window.closeCandidateQrModal = function () {
        stopQrCamera();
        const modal = document.getElementById('modalCandidateQrScan');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    window.triggerQrAttendanceRequest = async function (maUngTuyen, loaiQr, tenViTri, tenNhaHang, maCaLam) {
        const confirmMsg = loaiQr === 'ra_ca'
            ? 'Hệ thống sẽ hiển thị mã QR Ra Ca (hiệu lực 30 giây) trên màn hình Quản lý nhà hàng. Tiếp tục?'
            : 'Hệ thống sẽ hiển thị mã QR Vào Ca (hiệu lực 30 giây) trên màn hình Quản lý nhà hàng. Tiếp tục?';

        if (!window.confirm(confirmMsg)) {
            return;
        }

        try {
            const response = await fetch(QR_REQUEST_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    ma_ung_tuyen: maUngTuyen,
                    loai_qr: loaiQr
                })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                const errMsg = data.message || 'Không thể gửi yêu cầu tạo mã QR chấm công.';
                if (typeof window._showCandidateQrToast === 'function') {
                    window._showCandidateQrToast(errMsg, 'error');
                } else {
                    alert(errMsg);
                }
                return;
            }

            openCandidateQrModalWithContext({
                maUngTuyen: maUngTuyen,
                maCaLam: data.ma_ca_lam || maCaLam || 0,
                loaiQr: data.loai_qr || loaiQr || 'vao_ca',
                tenViTri: data.ten_ca_lam || tenViTri || 'Ca làm việc',
                tenNhaHang: data.ten_nha_hang || tenNhaHang || 'Nhà hàng'
            }, data.message);
        } catch (err) {
            const msg = 'Lỗi kết nối máy chủ khi gửi yêu cầu chấm công QR.';
            if (typeof window._showCandidateQrToast === 'function') {
                window._showCandidateQrToast(msg, 'error');
            } else {
                alert(msg);
            }
        }
    };

    async function submitQrScan(tokenRaw) {
        const alertSubmitBtn = document.getElementById('btnSubmitCandidateQr');
        const extracted = extractQrParams(tokenRaw);
        const shiftId = Number(document.getElementById('candidateQrShiftId').value || extracted.caLamId || 0);
        const actionType = document.getElementById('candidateQrAction').value || extracted.loaiQr || 'vao_ca';
        const tokenValue = extracted.token;

        if (!tokenValue) {
            showModalAlert('Vui lòng quét mã QR hoặc nhập token từ màn hình Quản lý.', 'error');
            return;
        }

        showModalAlert('', '');
        if (alertSubmitBtn) alertSubmitBtn.disabled = true;

        try {
            const response = await fetch(QR_SCAN_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    ma_ca_lam: shiftId,
                    loai_qr: actionType,
                    qr_token: tokenValue
                })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                showModalAlert(data.message || 'Mã QR không hợp lệ hoặc đã hết hạn.', 'error');
                return;
            }

            stopQrCamera();
            showModalAlert(data.message || 'Chấm công bằng mã QR thành công!', 'success');
            setTimeout(function () {
                window.location.reload();
            }, 850);
        } catch (err) {
            showModalAlert('Lỗi kết nối máy chủ khi xác nhận mã QR.', 'error');
        } finally {
            if (alertSubmitBtn) alertSubmitBtn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('candidateQrScanForm');
        const toggleBtn = document.getElementById('btnToggleQrCamera');
        const resendBtn = document.getElementById('btnResendQrRequest');

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                submitQrScan(document.getElementById('candidateQrToken').value);
            });
        }

        if (resendBtn) {
            resendBtn.addEventListener('click', async function () {
                if (!currentCtx.maUngTuyen) return;
                resendBtn.disabled = true;
                try {
                    const resp = await fetch(QR_REQUEST_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        body: JSON.stringify({
                            ma_ung_tuyen: currentCtx.maUngTuyen,
                            loai_qr: currentCtx.loaiQr
                        })
                    });
                    const data = await resp.json();
                    if (!resp.ok || !data.success) {
                        showModalAlert(data.message || 'Không thể gửi lại yêu cầu QR.', 'error');
                    } else {
                        showModalAlert('Đã gửi lại yêu cầu QR 30 giây mới tới màn hình Quản lý!', 'info');
                    }
                } catch (e) {
                    showModalAlert('Lỗi kết nối khi gửi lại yêu cầu QR.', 'error');
                } finally {
                    resendBtn.disabled = false;
                }
            });
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', async function () {
                const wrap = document.getElementById('candidateQrCameraWrap');
                if (isCameraActive) {
                    stopQrCamera();
                    return;
                }

                try {
                    await loadHtml5QrLibrary();
                    if (wrap) wrap.style.display = 'block';
                    html5QrScanner = new window.Html5Qrcode('candidateQrReaderBox');
                    await html5QrScanner.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 210, height: 210 } },
                        function (decodedText) {
                            const extracted = extractQrParams(decodedText);
                            document.getElementById('candidateQrToken').value = extracted.token;
                            stopQrCamera();
                            submitQrScan(decodedText);
                        }
                    );
                    isCameraActive = true;
                    toggleBtn.innerHTML = '<i class="fa-solid fa-video-slash"></i> Tắt Camera';
                } catch (err) {
                    if (wrap) wrap.style.display = 'none';
                    showModalAlert('Không thể mở camera trên thiết bị này. Bạn có thể dán token QR từ màn hình Quản lý.', 'error');
                }
            });
        }
    });
})();
</script>
@endpush
@endsection
