@extends('layouts.app')

@section('title', 'Quản Lý Chấm Công & Phê Duyệt Lương Ca - Golden Shift')

@section('content')
<section class="page-header-section">
    <div class="gs-container">
        <div class="breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ url('/quan-ly/ca-lam-viec') }}">Quản lý ca làm</a>
            <span>/</span>
            <span>Chấm công &amp; Phê duyệt giờ làm</span>
        </div>
        <div class="header-job-highlight">
            <span class="sub-badge"><i class="fa-solid fa-user-clock text-gold"></i> PHÂN HỆ QUẢN LÝ NHÀ HÀNG</span>
            <h1 class="page-title">Xét Duyệt Chấm Công &amp; Tính Lương Ca Làm</h1>
            <p class="page-subtitle">Kiểm tra thời gian vào/ra ca thực tế của nhân sự, điều chỉnh giờ công và phê duyệt tích lũy lương vào Sổ Thu Nhập.</p>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="gs-container">
        <!-- FLASH ALERTS -->
        @if (session('success'))
            <div class="alert-box success" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="alert-box warning" style="margin-bottom: 1.5rem; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 1rem 1.25rem; border-radius: 10px; display: flex; align-items: center; gap: 0.75rem; font-weight: 600;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem; color: #d97706;"></i> {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-box error" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <!-- METRIC SUMMARY CARDS (3 CỘT NGANG) -->
        <div class="dashboard-stats-grid">
            <!-- Thẻ 1: Ca chờ duyệt -->
            <div class="dash-stat-card glass-panel" style="border-left: 4px solid #f59e0b !important;">
                <div class="stat-icon-wrap orange">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <span class="stat-title">CA CHỜ XÁC NHẬN</span>
                    <div class="stat-value" style="color: {{ $totalPending > 0 ? '#d97706' : '#1e293b' }} !important;">
                        {{ $totalPending }} <span style="font-size: 14px; font-weight: 600; color: #64748b;">yêu cầu</span>
                    </div>
                    <span class="stat-sub"><i class="fa-solid fa-circle-info text-gold"></i> Tự động duyệt sau 60 phút</span>
                </div>
            </div>

            <!-- Thẻ 2: Ca đã duyệt -->
            <div class="dash-stat-card glass-panel" style="border-left: 4px solid #10b981 !important;">
                <div class="stat-icon-wrap green">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <div>
                    <span class="stat-title">CA ĐÃ PHÊ DUYỆT</span>
                    <div class="stat-value" style="color: #15803d !important;">
                        {{ $totalConfirmed }} <span style="font-size: 14px; font-weight: 600; color: #64748b;">ca</span>
                    </div>
                </div>
            </div>

            <!-- Thẻ 3: Tổng giờ làm -->
            <div class="dash-stat-card glass-panel" style="border-left: 4px solid #0284c7 !important;">
                <div class="stat-icon-wrap blue">
                    <i class="fa-solid fa-business-time"></i>
                </div>
                <div>
                    <span class="stat-title">TỔNG GIỜ CÔNG ĐÃ DUYỆT</span>
                    <div class="stat-value" style="color: #0284c7 !important;">
                        {{ number_format($totalHours, 1) }} <span style="font-size: 14px; font-weight: 600; color: #64748b;">giờ</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABS BỘ LỌC TRẠNG THÁI CHẤM CÔNG -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;">
            <a href="{{ url('/quan-ly/cham-cong?tab=cho_xac_nhan') }}" 
               class="btn {{ $tab === 'cho_xac_nhan' ? 'btn-gold' : 'btn-outline' }} btn-sm"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 7px 18px; border-radius: 999px; font-weight: 700;">
                <i class="fa-solid fa-clock"></i> Chờ Xác Nhận
                @if ($totalPending > 0)
                    <span style="background: {{ $tab === 'cho_xac_nhan' ? '#78350f' : '#dc2626' }}; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 999px; font-weight: 800;">
                        {{ $totalPending }}
                    </span>
                @endif
            </a>

            <a href="{{ url('/quan-ly/cham-cong?tab=da_xac_nhan') }}" 
               class="btn {{ $tab === 'da_xac_nhan' ? 'btn-gold' : 'btn-outline' }} btn-sm"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 7px 18px; border-radius: 999px; font-weight: 700;">
                <i class="fa-solid fa-check"></i> Đã Xác Nhận ({{ $totalConfirmed }})
            </a>

            <a href="{{ url('/quan-ly/cham-cong?tab=tat_ca') }}" 
               class="btn {{ $tab === 'tat_ca' ? 'btn-gold' : 'btn-outline' }} btn-sm"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 7px 18px; border-radius: 999px; font-weight: 700;">
                <i class="fa-solid fa-list"></i> Tất Cả Lượt Chấm Công
            </a>
        </div>

        <!-- BẢNG DANH SÁCH CHẤM CÔNG (ĐÚNG 5 CỘT - KHÔNG TRÀN, KHÔNG THANH CUỘN NGANG) -->
        <div class="dashboard-table-card glass-panel" style="background: #ffffff; border-radius: 16px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
            <div class="card-table-header" style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #ffffff;">
                <div>
                    <h3 class="table-card-title" style="margin: 0; font-size: 18px; font-weight: 800; color: var(--text-heading); display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-clipboard-user text-gold"></i> Bảng Danh Sách Phê Duyệt Chấm Công
                    </h3>
                    <p class="table-card-sub" style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted);">
                        {{ $tab === 'cho_xac_nhan' ? 'Các ca làm việc đang chờ Quản lý đối soát và phê duyệt công (Tự động duyệt sau 60 phút)' : ($tab === 'da_xac_nhan' ? 'Lịch sử các ca đã duyệt hoàn tất' : 'Toàn bộ danh sách lượt chấm công') }}
                    </p>
                </div>
                <div style="font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-rotate text-gold"></i> Cập nhật trực tiếp
                </div>
            </div>

            @if ($chamCongList->isEmpty())
                <div class="empty-state-box" style="padding: 50px 20px; text-align: center;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: #f8fafc; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 14px;">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #475569; margin-bottom: 6px;">Không có lượt chấm công nào trong danh mục này</h3>
                    <p style="font-size: 13px; color: #94a3b8; margin-bottom: 20px;">Khi ứng viên bấm "Vào ca" và "Ra ca" trên đơn ứng tuyển, dữ liệu chấm công sẽ tự động hiển thị tại đây.</p>
                    <a href="{{ url('/quan-ly/ca-lam-viec') }}" class="btn btn-gold btn-sm" style="border-radius: 999px; padding: 8px 22px;">Quản Lý Ca Làm Việc</a>
                </div>
            @else
                <div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table class="gs-table" style="width: 100%; border-collapse: collapse; table-layout: auto;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="width: 100px; text-align: center; white-space: nowrap; padding: 14px 12px; font-size: 13px; font-weight: 700; color: #475569;">MÃ CC</th>
                                <th style="padding: 14px 18px; font-size: 13px; font-weight: 700; color: #475569;">THÔNG TIN NHÂN VIÊN</th>
                                <th style="padding: 14px 18px; font-size: 13px; font-weight: 700; color: #475569;">THÔNG TIN CA LÀM</th>
                                <th style="width: 150px; text-align: center; white-space: nowrap; padding: 14px 12px; font-size: 13px; font-weight: 700; color: #475569;">TRẠNG THÁI</th>
                                <th style="width: 150px; text-align: center; white-space: nowrap; padding: 14px 18px; font-size: 13px; font-weight: 700; color: #475569;">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chamCongList as $item)
                                @php
                                    $userUngVien = $item->hoSoUngTuyen ? $item->hoSoUngTuyen->nguoiDung : ($item->nguoiDung ?? null);
                                    $shift = $item->caLamViec;
                                    $nhaHang = $shift ? $shift->nhaHang : null;
                                    $mucLuong = (float)($shift ? $shift->muc_luong_theo_gio : 0);
                                    $soGio = (float)$item->so_gio_lam_thuc_te;
                                    $thanhTien = round($soGio * $mucLuong);
                                    $statusCC = $item->trang_thai_cham_cong;
                                    $hoTen = $userUngVien ? $userUngVien->ho_ten : 'Ứng viên';
                                    $firstChar = mb_strtoupper(mb_substr($hoTen, 0, 1));

                                    // Kiểm tra thời gian ra ca đã quá 60 phút chưa
                                    $isPast60Min = false;
                                    if ($item->thoi_gian_ra_thuc_te) {
                                        $isPast60Min = \Carbon\Carbon::parse($item->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->addMinutes(60)->isPast();
                                    }
                                    $vaoVn = $item->thoi_gian_vao_thuc_te
                                        ? \Carbon\Carbon::parse($item->thoi_gian_vao_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')
                                        : null;
                                    $raVn = $item->thoi_gian_ra_thuc_te
                                        ? \Carbon\Carbon::parse($item->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')
                                        : null;
                                    $isLocked = in_array($statusCC, ['da_xac_nhan', 'tu_dong_duyet']) || ($statusCC === 'cho_xac_nhan' && $isPast60Min);
                                @endphp
                                <tr id="row-cc-{{ $item->ma_cham_cong }}" style="transition: background 0.2s;">
                                    <!-- Cột 1: Mã CC -->
                                    <td style="text-align: center; white-space: nowrap; padding: 14px 12px;">
                                        <span class="badge-code" style="font-weight: 700; font-size: 13px;">#CC - {{ $item->ma_cham_cong }}</span>
                                    </td>

                                    <!-- Cột 2: Thông tin Nhân viên (Avatar, Tên, SĐT, Email, Mã #UV-...) -->
                                    <td style="padding: 14px 18px;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; font-weight: 800; font-size: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1.5px solid #fcd34d; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                                                {{ $firstChar }}
                                            </div>
                                            <div style="min-width: 0;">
                                                <div style="font-weight: 700; color: #1e293b; font-size: 14px; line-height: 1.3;">
                                                    {{ $hoTen }}
                                                </div>
                                                <div style="font-size: 12px; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 8px;">
                                                    <span><i class="fa-solid fa-phone text-gold"></i> {{ $userUngVien ? $userUngVien->so_dien_thoai : '—' }}</span>
                                                    @if ($userUngVien && $userUngVien->ma_nguoi_dung)
                                                        <span style="background: #f1f5f9; color: #475569; font-size: 11px; padding: 1px 6px; border-radius: 4px; border: 1px solid #e2e8f0; font-weight: 600;">#UV - {{ $userUngVien->ma_nguoi_dung }}</span>
                                                    @endif
                                                </div>
                                                @if ($userUngVien && $userUngVien->email)
                                                    <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                                                        <i class="fa-regular fa-envelope"></i> {{ $userUngVien->email }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Cột 3: Thông tin Ca làm (Vị trí, Ngày làm, Khung giờ, Đơn giá 32.000 đ/h) -->
                                    <td style="padding: 14px 18px;">
                                        <div>
                                            <div style="font-weight: 700; color: #0284c7; font-size: 14px; line-height: 1.3;">
                                                {{ $shift ? $shift->ten_vi_tri_tuyen_dung : 'Ca làm việc' }}
                                            </div>
                                            <div style="font-size: 12px; color: #475569; margin-top: 4px;">
                                                <i class="fa-solid fa-calendar-day text-gold"></i> {{ $shift && $shift->ngay_lam_viec ? date('d/m/Y', strtotime($shift->ngay_lam_viec)) : '' }}
                                            </div>
                                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                                <i class="fa-regular fa-clock text-gold"></i> {{ $shift ? substr($shift->gio_bat_dau, 0, 5) . ' - ' . substr($shift->gio_ket_thuc, 0, 5) : '' }}
                                                <span style="color: #cbd5e1; margin: 0 4px;">•</span>
                                                <span style="font-weight: 700; color: #b45309;">{{ number_format($mucLuong, 0, ',', '.') }} đ/h</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Cột 4: Trạng thái (Chờ duyệt, Đã duyệt, Tự động duyệt) -->
                                    <td style="text-align: center; white-space: nowrap; padding: 14px 12px;">
                                        @if ($statusCC === 'tu_dong_duyet')
                                            <span class="badge-status recruit nowrap" style="background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; font-size: 12px; padding: 5px 12px; font-weight: 700; border-radius: 999px;">
                                                <i class="fa-solid fa-circle-check"></i> Đã duyệt (Tự động)
                                            </span>
                                            <div style="font-size: 11px; color: #0284c7; margin-top: 3px; font-weight: 600;">
                                                <i class="fa-solid fa-robot"></i> Hệ thống tự duyệt (&gt;60p)
                                            </div>
                                        @elseif ($statusCC === 'da_xac_nhan')
                                            <span class="badge-status recruit nowrap" style="background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; font-size: 12px; padding: 5px 12px; font-weight: 700; border-radius: 999px;">
                                                <i class="fa-solid fa-circle-check"></i> Đã duyệt
                                            </span>
                                            @php
                                                $tenQuanLy = ($shift && $shift->nhaHang && $shift->nhaHang->quanLy)
                                                    ? $shift->nhaHang->quanLy->ho_ten
                                                    : (auth()->user()->ho_ten ?? ($item->nguoiXacNhan->ho_ten ?? 'Quản lý'));
                                            @endphp
                                            <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                                {{ $tenQuanLy }}
                                            </div>
                                        @elseif ($statusCC === 'cho_xac_nhan')
                                            @if ($isPast60Min)
                                                <span class="badge-status nowrap" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 12px; padding: 5px 12px; font-weight: 700; border-radius: 999px;">
                                                    <i class="fa-solid fa-robot"></i> Tự động duyệt
                                                </span>
                                            @else
                                                <span class="badge-status nowrap" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 12px; padding: 5px 12px; font-weight: 700; border-radius: 999px;">
                                                    <i class="fa-solid fa-hourglass-half"></i> Chờ duyệt
                                                </span>
                                            @endif
                                        @elseif ($statusCC === 'can_kiem_tra')
                                            <span class="badge-status inactive nowrap" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-size: 12px; padding: 5px 12px; font-weight: 700; border-radius: 999px;" title="{{ $item->ghi_chu_cham_cong }}">
                                                <i class="fa-solid fa-clock-rotate-left"></i> Yêu cầu KT
                                            </span>
                                        @else
                                            <span class="badge-status nowrap" style="font-size: 12px; padding: 5px 12px;">
                                                {{ $statusCC }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Cột 5: Thao Tác (Nút màu xanh "Xác nhận" hoặc "Xem chi tiết") -->
                                    <td style="text-align: center; white-space: nowrap; padding: 14px 18px;">
                                        @php
                                            $thoiDiemKetThuc = $raVn ?? ($shift && $shift->ngay_lam_viec ? \Carbon\Carbon::parse($shift->ngay_lam_viec . ' ' . ($shift->gio_ket_thuc ?: '23:59:59'), 'Asia/Ho_Chi_Minh') : null);
                                            $quaHan7Ngay = $thoiDiemKetThuc ? $thoiDiemKetThuc->copy()->addDays(7)->isPast() : false;
                                        @endphp

                                        @if ($statusCC === 'cho_xac_nhan' && !$isPast60Min)
                                            <!-- Nút màu xanh "Xác nhận" mở Popup Phê duyệt -->
                                            <button type="button" 
                                                    class="btn-open-confirm-modal"
                                                    data-id="{{ $item->ma_cham_cong }}"
                                                    data-code="#CC - {{ $item->ma_cham_cong }}"
                                                    data-candidate="{{ $hoTen }}"
                                                    data-uv-code="{{ $userUngVien && $userUngVien->ma_nguoi_dung ? '#UV - ' . $userUngVien->ma_nguoi_dung : '' }}"
                                                    data-shift="{{ $shift ? $shift->ten_vi_tri_tuyen_dung : '' }}"
                                                    data-date="{{ $shift && $shift->ngay_lam_viec ? date('d/m/Y', strtotime($shift->ngay_lam_viec)) : '' }}"
                                                    data-shift-time="{{ $shift ? substr($shift->gio_bat_dau, 0, 5) . ' - ' . substr($shift->gio_ket_thuc, 0, 5) : '' }}"
                                                    data-wage="{{ $mucLuong }}"
                                                    data-in="{{ $vaoVn ? $vaoVn->format('Y-m-d\TH:i') : '' }}"
                                                    data-out="{{ $raVn ? $raVn->format('Y-m-d\TH:i') : '' }}"
                                                    data-hours="{{ $soGio }}"
                                                    data-note="{{ $item->ghi_chu_cham_cong }}"
                                                    data-status="{{ $statusCC }}"
                                                    data-readonly="0"
                                                    data-expired="{{ $quaHan7Ngay ? '1' : '0' }}"
                                                    data-ma-ca="{{ $shift ? $shift->ma_ca_lam : 0 }}"
                                                    data-ma-uv="{{ $userUngVien ? $userUngVien->ma_nguoi_dung : 0 }}"
                                                    data-punctual="{{ $item->trang_thai_cham_cong === 'di_muon' || ($vaoVn && $shift && $shift->gio_bat_dau && $vaoVn->format('H:i:s') > $shift->gio_bat_dau) ? 'di_muon' : 'dung_gio' }}"
                                                    style="background: #10b981; color: #fff; border: none; border-radius: 999px; padding: 7px 18px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(16,185,129,0.3); transition: all 0.2s;"
                                                    title="Mở phiếu duyệt chấm công">
                                                <i class="fa-solid fa-check"></i> Xác nhận
                                            </button>
                                        @else
                                            <!-- Nút "Xem chi tiết" mở Popup chỉ đọc -->
                                            <button type="button" 
                                                    class="btn-open-confirm-modal"
                                                    data-id="{{ $item->ma_cham_cong }}"
                                                    data-code="#CC - {{ $item->ma_cham_cong }}"
                                                    data-candidate="{{ $hoTen }}"
                                                    data-uv-code="{{ $userUngVien && $userUngVien->ma_nguoi_dung ? '#UV - ' . $userUngVien->ma_nguoi_dung : '' }}"
                                                    data-shift="{{ $shift ? $shift->ten_vi_tri_tuyen_dung : '' }}"
                                                    data-date="{{ $shift && $shift->ngay_lam_viec ? date('d/m/Y', strtotime($shift->ngay_lam_viec)) : '' }}"
                                                    data-shift-time="{{ $shift ? substr($shift->gio_bat_dau, 0, 5) . ' - ' . substr($shift->gio_ket_thuc, 0, 5) : '' }}"
                                                    data-wage="{{ $mucLuong }}"
                                                    data-in="{{ $vaoVn ? $vaoVn->format('Y-m-d\TH:i') : '' }}"
                                                    data-out="{{ $raVn ? $raVn->format('Y-m-d\TH:i') : '' }}"
                                                    data-hours="{{ $soGio }}"
                                                    data-note="{{ $item->ghi_chu_cham_cong }}"
                                                    data-status="{{ $statusCC }}"
                                                    data-readonly="1"
                                                    data-expired="{{ $quaHan7Ngay ? '1' : '0' }}"
                                                    data-ma-ca="{{ $shift ? $shift->ma_ca_lam : 0 }}"
                                                    data-ma-uv="{{ $userUngVien ? $userUngVien->ma_nguoi_dung : 0 }}"
                                                    data-punctual="{{ $item->trang_thai_cham_cong === 'di_muon' || ($vaoVn && $shift && $shift->gio_bat_dau && $vaoVn->format('H:i:s') > $shift->gio_bat_dau) ? 'di_muon' : 'dung_gio' }}"
                                                    style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 999px; padding: 6px 16px; font-weight: 700; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;"
                                                    title="Xem chi tiết phiếu chấm công (Chỉ đọc)">
                                                <i class="fa-solid fa-eye text-gold"></i> Xem chi tiết
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (method_exists($chamCongList, 'hasPages') && $chamCongList->hasPages())
                    <div class="pagination-wrapper">
                        {{ $chamCongList->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>

<!-- ===================================================================
     MODAL POPUP: "PHIẾU DUYỆT CHẤM CÔNG"
     Căn giữa màn hình, đầy đủ thông tin thời gian thực tế, tính lương real-time
=================================================================== -->
<div id="modalConfirmChamCong" class="gs-custom-modal-overlay" style="display: none;">
    <div class="gs-custom-modal-card">
        
        <!-- MODAL HEADER -->
        <div class="gs-custom-modal-header" style="background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: #1e293b; font-weight: 800; font-size: 19px;">Phiếu duyệt chấm công</h3>
                    <div style="margin-top: 3px; display: flex; align-items: center; gap: 8px;">
                        <span id="modalCcCode" class="badge-code" style="font-weight: 700; font-size: 12px;">#CC - 4</span>
                        <span id="modalStatusBadge"></span>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close-custom-modal" id="btnCloseConfirmModal" style="background: none; border: none; font-size: 24px; line-height: 1; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <form action="{{ route('quan_ly.cham_cong.confirm') }}" method="POST" id="formConfirmChamCong" style="display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; overflow: hidden; margin: 0;">
            @csrf
            <input type="hidden" name="ma_cham_cong" id="modalMaChamCong" value="">

            <!-- MODAL SCROLLABLE BODY (LĂN CHUỘT MƯỢT MÀ) -->
            <div class="gs-custom-modal-body" style="flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 20px 24px; overscroll-behavior: contain;">

                <!-- THÔNG BÁO CHẾ ĐỘ (PENDING HOẶC READONLY) -->
                <div id="modalNoticePending" style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #92400e; margin-bottom: 16px; line-height: 1.5;">
                <i class="fa-solid fa-clock text-gold"></i> <strong>Lưu ý:</strong> Ca làm đang chờ duyệt. Quản lý có thể điều chỉnh thời gian thực tế hoặc bổ sung thưởng. Nếu sau <strong>60 phút</strong> từ khi ra ca không duyệt, hệ thống sẽ tự động duyệt ca làm.
            </div>

            <div id="modalNoticeReadonly" style="display: none; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #0369a1; margin-bottom: 16px; line-height: 1.5;">
                <i class="fa-solid fa-lock"></i> <strong>Chế độ chỉ đọc:</strong> Ca làm việc này đã được <span id="modalReadonlyType" style="font-weight: 700;">phê duyệt</span>. Dữ liệu thời gian và tiền lương đã chốt cứng, không thể chỉnh sửa.
            </div>

            <!-- TÓM TẮT THÔNG TIN NHÂN VIÊN & CA LÀM -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 10px;">
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">Nhân viên:</div>
                        <div style="font-size: 15px; font-weight: 800; color: #1e293b; margin-top: 2px;">
                            <span id="modalCandidateName">--</span> 
                            <span id="modalUvCode" style="font-size: 11px; font-weight: 600; color: #475569; background: #fff; padding: 2px 6px; border-radius: 4px; border: 1px solid #cbd5e1; margin-left: 4px;"></span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">Đơn giá giờ:</div>
                        <div style="font-size: 15px; font-weight: 800; color: #b45309; margin-top: 2px;">
                            <span id="modalShiftWage">--</span> VNĐ/giờ
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px; color: #475569;">
                    <div><i class="fa-solid fa-briefcase text-gold"></i> Vị trí: <strong id="modalShiftTitle" style="color: #0284c7;">--</strong></div>
                    <div><i class="fa-solid fa-calendar-day text-gold"></i> Ngày làm: <strong id="modalShiftDate">--</strong></div>
                    <div><i class="fa-regular fa-clock text-gold"></i> Khung ca: <strong id="modalShiftTime">--</strong></div>
                </div>
            </div>

            <!-- PHẦN 1: THỜI GIAN THỰC TẾ (GIỜ VÀO & GIỜ RA) -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 700; color: #1e293b; font-size: 13px; margin-bottom: 8px;">
                    <i class="fa-regular fa-clock text-gold"></i> Thời Gian Thực Tế (Check-in &amp; Check-out):
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <!-- Giờ vào -->
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="font-size: 12px; font-weight: 700; color: #059669;">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i> Giờ vào (Check-in)
                            </span>
                            <span id="modalPunctualBadge"></span>
                        </div>
                        <input type="datetime-local" name="thoi_gian_vao_thuc_te" id="modalThoiGianVao"
                               style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 8px; font-size: 13px; font-family: inherit; font-weight: 600; color: #1e293b; background: #fff; box-sizing: border-box; outline: none;">
                    </div>

                    <!-- Giờ ra -->
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="font-size: 12px; font-weight: 700; color: #dc2626;">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Giờ ra (Check-out)
                            </span>
                        </div>
                        <input type="datetime-local" name="thoi_gian_ra_thuc_te" id="modalThoiGianRa"
                               style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 8px; font-size: 13px; font-family: inherit; font-weight: 600; color: #1e293b; background: #fff; box-sizing: border-box; outline: none;">
                    </div>
                </div>
            </div>

            <!-- PHẦN 2: TỔNG GIỜ THỰC TẾ & BỔ SUNG THƯỞNG -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                <!-- Tổng giờ thực tế -->
                <div>
                    <label for="modalSoGioLam" style="display: block; font-weight: 700; color: #1e293b; margin-bottom: 6px; font-size: 13px;">
                        Tổng Giờ Thực Tế: <span style="color: #dc2626;">*</span>
                    </label>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <input type="number" step="0.05" min="0.1" max="24" name="so_gio_lam_thuc_te" id="modalSoGioLam" required
                               style="flex: 1; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-family: inherit; font-size: 16px; font-weight: 800; color: #0284c7; outline: none; box-sizing: border-box;">
                        <span style="font-weight: 700; color: #64748b; font-size: 14px;">giờ</span>
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        Tự động tính từ Giờ vào &rarr; Giờ ra
                    </div>
                </div>

                <!-- Bổ sung thưởng -->
                <div>
                    <label for="modalSoTienThuong" style="display: block; font-weight: 700; color: #1e293b; margin-bottom: 6px; font-size: 13px;">
                        Bổ Sung Thưởng (VNĐ):
                    </label>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <input type="number" step="1000" min="0" name="so_tien_thuong" id="modalSoTienThuong" value="0"
                               style="flex: 1; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-family: inherit; font-size: 16px; font-weight: 800; color: #16a34a; outline: none; box-sizing: border-box;" placeholder="0">
                        <span style="font-weight: 700; color: #64748b; font-size: 14px;">VNĐ</span>
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        Thưởng nóng, tip, chuyên cần
                    </div>
                </div>
            </div>

            <!-- PHẦN 3: TỔNG LƯƠNG CA LÀM (THÀNH TIỀN) - TÍNH REAL-TIME -->
            <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 14px 18px; margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #475569; margin-bottom: 6px;">
                    <span>Lương theo giờ (<span id="modalEstHours" style="font-weight: 700;">0</span> giờ &times; <span id="modalEstRate">0</span> đ):</span>
                    <span id="modalEstSalary" style="font-weight: 700; color: #1e293b;">0 VNĐ</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #16a34a; margin-bottom: 8px;">
                    <span>Tiền thưởng bổ sung:</span>
                    <span id="modalEstBonus" style="font-weight: 700;">+0 VNĐ</span>
                </div>
                <div style="border-top: 1px dashed #fcd34d; padding-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 14px; color: #92400e; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px;">
                        Tổng Lương Ca Làm (Thành Tiền):
                    </span>
                    <span id="modalTotalPay" style="font-size: 20px; font-weight: 900; color: #15803d; letter-spacing: -0.3px;">
                        0 VNĐ
                    </span>
                </div>
            </div>

            <!-- GHI CHÚ CỦA QUẢN LÝ -->
            <div style="margin-bottom: 12px;">
                <label for="modalGhiChu" style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 13px;">
                    Ghi Chú Của Quản Lý (Tùy chọn):
                </label>
                <textarea name="ghi_chu_cham_cong" id="modalGhiChu" rows="2"
                          style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-family: inherit; font-size: 13px; resize: vertical; box-sizing: border-box; outline: none;"
                          placeholder="Ví dụ: Làm thêm dọn bàn, nhân sự tích cực..."></textarea>
            </div>

            </div> <!-- KẾT THÚC .gs-custom-modal-body -->

            <!-- MODAL ACTIONS (CỐ ĐỊNH Ở ĐÁY POPUP - LUÔN HIỂN THỊ NÚT XÁC NHẬN) -->
            <div class="gs-custom-modal-footer" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div style="display: flex; gap: 8px;">
                    <button type="button" id="btnModalReportCandidate" class="btn btn-sm" style="display: none; border-radius: 999px; padding: 7px 14px; font-weight: 700; font-size: 12.5px; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;">
                        <i class="fa-solid fa-flag"></i> Báo Cáo Vi Phạm
                    </button>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" id="btnCancelConfirmModal" class="btn btn-outline btn-sm" style="border-radius: 999px; padding: 8px 22px; font-weight: 700;">
                        Đóng
                    </button>
                    <button type="submit" id="btnSubmitConfirmModal" style="background: #10b981; color: #fff; border: none; border-radius: 999px; padding: 8px 26px; font-weight: 700; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(16,185,129,0.3); transition: all 0.2s;">
                        <i class="fa-solid fa-check"></i> Xác nhận &amp; Phê Duyệt
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal            = document.getElementById('modalConfirmChamCong');
    const btnClose         = document.getElementById('btnCloseConfirmModal');
    const btnCancel        = document.getElementById('btnCancelConfirmModal');
    const btnSubmit        = document.getElementById('btnSubmitConfirmModal');

    const inputMaCC        = document.getElementById('modalMaChamCong');
    const textCcCode       = document.getElementById('modalCcCode');
    const textCand         = document.getElementById('modalCandidateName');
    const textUvCode       = document.getElementById('modalUvCode');
    const textTitle        = document.getElementById('modalShiftTitle');
    const textDate         = document.getElementById('modalShiftDate');
    const textShiftTime    = document.getElementById('modalShiftTime');
    const textWage         = document.getElementById('modalShiftWage');
    const badgeStatus      = document.getElementById('modalStatusBadge');
    const badgePunctual    = document.getElementById('modalPunctualBadge');

    const inputVao         = document.getElementById('modalThoiGianVao');
    const inputRa          = document.getElementById('modalThoiGianRa');
    const inputHours       = document.getElementById('modalSoGioLam');
    const inputBonus       = document.getElementById('modalSoTienThuong');
    const textGhiChu       = document.getElementById('modalGhiChu');

    const textEstHours     = document.getElementById('modalEstHours');
    const textEstRate      = document.getElementById('modalEstRate');
    const textEstSal       = document.getElementById('modalEstSalary');
    const textEstBonus     = document.getElementById('modalEstBonus');
    const textTotalPay     = document.getElementById('modalTotalPay');

    const noticePending    = document.getElementById('modalNoticePending');
    const noticeReadonly   = document.getElementById('modalNoticeReadonly');
    const textReadonlyType = document.getElementById('modalReadonlyType');

    let currentWage = 0;

    // Tính toán Thành tiền = (Giờ * Đơn giá) + Thưởng
    function updateSalary() {
        const h = Math.max(0, parseFloat(inputHours.value) || 0);
        const bonus = Math.max(0, parseFloat(inputBonus.value) || 0);
        const wagePay = Math.round(h * currentWage);
        const total = wagePay + bonus;

        if (textEstHours) textEstHours.innerText = h;
        if (textEstRate)  textEstRate.innerText  = new Intl.NumberFormat('vi-VN').format(currentWage);
        if (textEstSal)   textEstSal.innerText   = new Intl.NumberFormat('vi-VN').format(wagePay) + ' VNĐ';
        if (textEstBonus) textEstBonus.innerText = '+' + new Intl.NumberFormat('vi-VN').format(bonus) + ' VNĐ';
        if (textTotalPay) textTotalPay.innerText = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
    }

    // Tự động tính số giờ khi thay đổi Giờ vào / Giờ ra
    function recalculateHoursFromTimes() {
        if (!inputVao.value || !inputRa.value) return;

        const dVao = new Date(inputVao.value);
        const dRa  = new Date(inputRa.value);

        if (dRa > dVao) {
            const diffMs = dRa - dVao;
            const diffMins = Math.round(diffMs / 60000);
            const calculatedHours = (diffMins / 60).toFixed(2);
            inputHours.value = calculatedHours;
            updateSalary();
        }
    }

    if (inputVao)   inputVao.addEventListener('change', recalculateHoursFromTimes);
    if (inputRa)    inputRa.addEventListener('change', recalculateHoursFromTimes);
    if (inputHours) inputHours.addEventListener('input', updateSalary);
    if (inputBonus) inputBonus.addEventListener('input', updateSalary);

    // Xử lý mở Modal khi nhấn "Xác nhận" hoặc "Xem chi tiết"
    document.querySelectorAll('.btn-open-confirm-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const ds = this.dataset;

            inputMaCC.value      = ds.id;
            textCcCode.innerText = ds.code;
            textCand.innerText   = ds.candidate;
            textUvCode.innerText = ds.uvCode || '';
            textTitle.innerText  = ds.shift;
            textDate.innerText   = ds.date;
            textShiftTime.innerText = ds.shiftTime;

            currentWage = parseFloat(ds.wage) || 0;
            textWage.innerText = new Intl.NumberFormat('vi-VN').format(currentWage);

            inputVao.value   = ds.in || '';
            inputRa.value    = ds.out || '';
            inputHours.value = ds.hours || '0';
            inputBonus.value = '0';
            textGhiChu.value = ds.note || '';

            // Badge đúng giờ / đi muộn
            if (ds.punctual === 'di_muon') {
                badgePunctual.innerHTML = '<span class="badge-status inactive nowrap" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;font-size:11px;padding:2px 7px;">Đi muộn</span>';
            } else {
                badgePunctual.innerHTML = '<span class="badge-status recruit nowrap" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:2px 7px;">Đúng giờ</span>';
            }

            // Trạng thái badge modal header
            if (ds.status === 'tu_dong_duyet') {
                badgeStatus.innerHTML = '<span class="badge-status nowrap" style="background:#e0f2fe;color:#0284c7;border:1px solid #bae6fd;font-size:11px;padding:3px 9px;"><i class="fa-solid fa-robot"></i> Tự động duyệt</span>';
            } else if (ds.status === 'da_xac_nhan') {
                badgeStatus.innerHTML = '<span class="badge-status recruit nowrap" style="background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;font-size:11px;padding:3px 9px;"><i class="fa-solid fa-circle-check"></i> Đã duyệt</span>';
            } else {
                badgeStatus.innerHTML = '<span class="badge-status nowrap" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:11px;padding:3px 9px;"><i class="fa-solid fa-hourglass-half"></i> Chờ duyệt</span>';
            }

            // Chế độ CHỈ ĐỌC (ReadOnly) khi đã duyệt hoặc tự động duyệt
            const isReadonly = ds.readonly === '1' || ds.status === 'da_xac_nhan' || ds.status === 'tu_dong_duyet';

            const btnReportCandidate = document.getElementById('btnModalReportCandidate');

            if (isReadonly) {
                inputVao.disabled   = true;
                inputRa.disabled    = true;
                inputHours.disabled = true;
                inputBonus.disabled = true;
                textGhiChu.disabled = true;

                btnSubmit.style.display = 'none';
                noticePending.style.display = 'none';
                noticeReadonly.style.display = 'block';

                textReadonlyType.innerText = ds.status === 'tu_dong_duyet' ? 'hệ thống Tự động duyệt (sau 60 phút)' : 'Quản lý phê duyệt';

                // Chỉ hiển thị nút báo cáo vi phạm (không còn nút đánh giá lại ứng viên ở phiếu đã duyệt)
                if (btnReportCandidate) {
                    if (ds.expired === '1') {
                        btnReportCandidate.style.display = 'none';
                    } else {
                        btnReportCandidate.style.display = 'inline-flex';
                        btnReportCandidate.onclick = function() {
                            closeModal();
                            if (typeof openReportModal === 'function') {
                                openReportModal({
                                    maCaLam: ds.maCa,
                                    targetUserId: ds.maUv,
                                    targetName: ds.candidate,
                                    shiftName: ds.shift,
                                    isManager: true
                                });
                            }
                        };
                    }
                }
            } else {
                inputVao.disabled   = false;
                inputRa.disabled    = false;
                inputHours.disabled = false;
                inputBonus.disabled = false;
                textGhiChu.disabled = false;

                btnSubmit.style.display = 'inline-flex';
                noticePending.style.display = 'block';
                noticeReadonly.style.display = 'none';

                if (btnReportCandidate) btnReportCandidate.style.display = 'none';
            }

            updateSalary();
            document.body.style.overflow = 'hidden';
            modal.style.display = 'flex';
        });
    });

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    if (btnClose)  btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });
});
</script>
@endsection
