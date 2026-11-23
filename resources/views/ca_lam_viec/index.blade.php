@extends('layouts.app')

@section('title', 'Quản Lý Ca Làm Việc Nhà Hàng - Golden Shift')

@section('content')
@php
    $caLamList = $caLamList ?? [];
    $thang = $thang ?? request('thang');
    $nam = $nam ?? request('nam');
    $trangThai = $trangThai ?? request('trang_thai', request('trang_thai_ca', ''));
    $flashSuccess = session('success');
    $flashError = session('error');
    $flashWarning = session('warning');
    $currentYear = (int)date('Y');
    $todayDate = date('Y-m-d');
@endphp

<section class="page-header-section">
    <div class="gs-container">
        <div class="breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ url('/dashboard') }}">Bảng quản trị</a>
            <span>/</span>
            <span>Quản lý ca làm việc nhà hàng</span>
        </div>
        <div class="header-job-highlight">
            <span class="sub-badge"><i class="fa-solid fa-store text-gold"></i> PHÂN HỆ QUẢN LÝ NHÀ HÀNG</span>
            <h1 class="page-title">Danh Sách Ca Làm Việc Của Nhà Hàng</h1>
            <p class="page-subtitle">Quản lý lịch xếp ca, đăng tuyển ca mới trực tiếp qua Popup Modal, theo dõi và phê duyệt ứng viên theo từng ca.</p>
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

        @if (!empty($flashWarning))
            <div class="alert-box warning" style="margin-bottom: 1.5rem; background: #fefce8; border: 1px solid #fde047; color: #854d0e; padding: 1rem 1.25rem; border-radius: 10px; display: flex; align-items: center; gap: 0.75rem; font-weight: 500;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem; color: #d97706;"></i> {{ $flashWarning }}
            </div>
        @endif

        @if (session('holiday_notice'))
            <div class="alert-box holiday-alert" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #fff1f2 0%, #fffbeb 100%); border: 1px solid #f87171; color: #991b1b; padding: 1rem 1.25rem; border-radius: 10px; display: flex; align-items: center; gap: 0.75rem; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.15);">
                <i class="fa-solid fa-fire text-gold" style="font-size: 1.35rem; color: #ef4444;"></i> {{ session('holiday_notice') }}
            </div>
        @endif

        @if (!empty($flashError))
            <div class="alert-box error" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $flashError }}
            </div>
        @endif

        <!-- TOAST NOTIFICATION CONTAINER -->
        <div id="gsToastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 10px; max-width: 380px; width: 100%;"></div>

        <!-- BAR THAO TÁC TẠO MỚI & TỔNG QUAN -->
        <div class="admin-approval-summary glass-panel" style="margin-bottom: 1.5rem;">
            <div class="summary-left">
                <div class="approval-icon-box">
                    <i class="fa-solid fa-calendar-plus"></i>
                </div>
                <div>
                    <h3>Tổng Ca Làm Việc: <strong>{{ method_exists($caLamList, 'total') ? $caLamList->total() : count($caLamList) }} ca</strong></h3>
                    <p>Đăng ca làm việc mới nhanh chóng qua Popup để thu hút ứng viên F&B ứng tuyển.</p>
                </div>
            </div>
            <div class="summary-right">
                <!-- NÚT MỞ POPUP TẠO CA LÀM MỚI -->
                <button type="button" class="btn btn-gold" id="btnOpenCreateShiftModal" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-plus"></i> Đăng Ca Làm Việc Mới
                </button>
            </div>
        </div>

        <!-- BỘ LỌC THEO THÁNG & NĂM DÀNH CHO QUẢN LÝ -->
        <div class="glass-panel" style="padding: 1.25rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; background: #fff;">
            <form action="{{ url('/quan-ly/ca-lam-viec') }}" method="GET" id="formFilterMonthYear" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
                @csrf
                <div style="flex: 1; min-width: 180px;">
                    <label for="filterThang" style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 4px;">
                        <i class="fa-solid fa-calendar-days text-gold"></i> Lọc Theo Tháng:
                    </label>
                    <select name="thang" id="filterThang" class="gs-select" style="width: 100%; padding: 0.55rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 500;">
                        <option value="">-- Tất cả các tháng --</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ ((int)$thang === $m) ? 'selected' : '' }}>
                                Tháng {{ $m }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div style="flex: 1; min-width: 160px;">
                    <label for="filterNam" style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 4px;">
                        <i class="fa-solid fa-calendar text-gold"></i> Năm:
                    </label>
                    <select name="nam" id="filterNam" class="gs-select" style="width: 100%; padding: 0.55rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 500;">
                        <option value="">-- Tất cả các năm --</option>
                        @for ($y = $currentYear - 3; $y <= $currentYear; $y++)
                            <option value="{{ $y }}" {{ (((int)$nam === $y) || ($nam === null && $thang !== null && $y === $currentYear)) ? 'selected' : '' }}>
                                Năm {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div style="flex: 1; min-width: 190px;">
                    <label for="filterTrangThai" style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 4px;">
                        <i class="fa-solid fa-signal text-gold"></i> Trạng Thái Ca Làm:
                    </label>
                    <select name="trang_thai" id="filterTrangThai" class="gs-select" style="width: 100%; padding: 0.55rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 500;">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="dang_tuyen" {{ (request('trang_thai') == 'dang_tuyen' || (empty(request('trang_thai')) && ($trangThai ?? '') == 'dang_tuyen')) ? 'selected' : '' }}>Đang tuyển</option>
                        <option value="da_du_nguoi" {{ (request('trang_thai') == 'da_du_nguoi' || (empty(request('trang_thai')) && ($trangThai ?? '') == 'da_du_nguoi')) ? 'selected' : '' }}>Đã đủ người</option>
                        <option value="hoan_thanh" {{ request('trang_thai') == 'hoan_thanh' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="da_huy" {{ (request('trang_thai') == 'da_huy' || (empty(request('trang_thai')) && ($trangThai ?? '') == 'da_huy')) ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-gold" style="padding: 0.55rem 1.25rem;">
                        <i class="fa-solid fa-filter"></i> Lọc Ca Làm
                    </button>
                    @if ($thang !== null || $nam !== null || !empty($trangThai))
                        <a href="{{ url('/quan-ly/ca-lam-viec') }}" class="btn btn-outline" style="padding: 0.55rem 1rem;" title="Xem tất cả ca làm">
                            <i class="fa-solid fa-rotate-right"></i> Tất Cả
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH CA LÀM VIỆC -->
        <div class="dashboard-table-card glass-panel">
            <div class="card-table-header">
                <div>
                    <h3 class="table-card-title"><i class="fa-solid fa-list text-gold"></i> Danh Sách Ca Làm Việc</h3>
                    <p class="table-card-sub">
                        {{ ($thang !== null) ? "Danh sách ca làm việc trong Tháng {$thang}" . ($nam ? "/{$nam}" : '') : 'Tất cả ca làm việc được sắp xếp mới nhất đến cũ nhất' }}
                    </p>
                </div>
            </div>

            @if ((method_exists($caLamList, 'isEmpty') && $caLamList->isEmpty()) || (!method_exists($caLamList, 'isEmpty') && (empty($caLamList) || count($caLamList) === 0)))
                <div class="empty-state-box">
                    <i class="fa-solid fa-folder-open empty-icon"></i>
                    <h3>Chưa có ca làm việc nào phù hợp</h3>
                    <p>Không có ca làm việc nào trong tháng/năm đã chọn hoặc chưa có ca nào được tạo.</p>
                    <button type="button" class="btn btn-gold btn-trigger-create-modal">
                        <i class="fa-solid fa-plus"></i> Tạo Ca Làm Mới
                    </button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="gs-table">
                        <thead>
                            <tr>
                                <th style="width: 95px; white-space: nowrap;">Mã Ca</th>
                                <th>Vị Trí Tuyển Dụng</th>
                                <th style="width: 120px; white-space: nowrap;">Ngày Làm Việc</th>
                                <th style="width: 130px; white-space: nowrap;">Khung Giờ (24h)</th>
                                <th style="width: 110px; white-space: nowrap;">Mức Lương/Giờ</th>
                                <th style="width: 130px; white-space: nowrap;">Chỉ Tiêu Tuyển</th>
                                <th style="width: 135px; white-space: nowrap;">Trạng Thái</th>
                                <th class="text-center" style="width: 220px; white-space: nowrap;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($caLamList as $item)
                                @php
                                    $itemArr = is_array($item) ? $item : (method_exists($item, 'toArray') ? $item->toArray() : (array)$item);
                                    $maCaLam = (int)($itemArr['ma_ca_lam'] ?? 0);
                                    $soLuongTuyen = (int)($itemArr['so_luong_tuyen'] ?? 1);
                                    $soLuongDaTuyen = (int)($itemArr['so_luong_da_tuyen'] ?? 0);
                                    $soLuongChoDuyet = (int)($itemArr['so_luong_cho_duyet'] ?? 0);
                                    $tenNhaHang = $itemArr['ten_nha_hang'] ?? ($item->nhaHang->ten_nha_hang ?? 'Golden Hotpot');
                                    $ngayLamViec = $itemArr['ngay_lam_viec'];
                                    $trangThaiCa = $itemArr['trang_thai_ca'] ?? 'dang_tuyen';
                                    $lyDoHuy = $itemArr['ly_do_huy'] ?? '';

                                    // =========================================================================
                                    // RÀNG BUỘC CHÍNH XÁC TRẠNG THÁI VÀ QUYỀN CHỈNH SỬA:
                                    // 1. Ca bị xóa/hủy: Hiển thị "Đã hủy", không thể chỉnh sửa, không thể hủy tiếp
                                    // 2. Ca sau ngày có ca làm (ngay_lam_viec < todayDate): Tự động khóa "Đã hoàn thành",
                                    //    KHÔNG THỂ CHỈNH SỬA DÙ CÓ ĐỦ SỐ LƯỢNG HAY KHÔNG (kể cả 2/4 hay 1/3)
                                    // 3. Ca trước hoặc trong ngày (ngay_lam_viec >= todayDate):
                                    //    - Nếu so_luong_da_tuyen >= so_luong_tuyen: "Đã đủ số lượng"
                                    //    - Ngược lại: "Đang tuyển"
                                    //    - Được phép chỉnh sửa
                                    // =========================================================================
                                    if ($trangThaiCa === 'da_huy') {
                                        $statusType = 'da_huy';
                                        $statusLabel = 'Đã hủy';
                                        $statusClass = 'badge-status inactive nowrap';
                                        $statusStyle = 'background:#fee2e2; color:#991b1b; border-color:#fca5a5;';
                                        $statusIcon = 'fa-solid fa-ban';
                                        $canEdit = false;
                                        $canCancel = false;
                                    } elseif ($ngayLamViec < $todayDate || $trangThaiCa === 'hoan_thanh') {
                                        $statusType = 'hoan_thanh';
                                        $statusLabel = 'Đã hoàn thành';
                                        $statusClass = 'badge-status inactive nowrap';
                                        $statusStyle = 'background:#f1f5f9; color:#475569; border-color:#cbd5e1;';
                                        $statusIcon = 'fa-solid fa-check-double';
                                        $canEdit = false;
                                        $canCancel = false;
                                    } elseif ($soLuongTuyen > 0 && $soLuongDaTuyen >= $soLuongTuyen) {
                                        $statusType = 'da_du_nguoi';
                                        $statusLabel = 'Đã đủ số lượng';
                                        $statusClass = 'badge-status nowrap';
                                        $statusStyle = 'background:#e0f2fe; color:#0369a1; border-color:#bae6fd;';
                                        $statusIcon = 'fa-solid fa-lock';
                                        $canEdit = true;
                                        $canCancel = true;
                                    } else {
                                        $statusType = 'dang_tuyen';
                                        $statusLabel = 'Đang tuyển';
                                        $statusClass = 'badge-status recruit nowrap';
                                        $statusStyle = '';
                                        $statusIcon = 'fa-solid fa-circle-check';
                                        $canEdit = true;
                                        $canCancel = true;
                                    }

                                    $isFullQuota = ($soLuongTuyen > 0 && $soLuongDaTuyen >= $soLuongTuyen);
                                @endphp
                                <tr id="shift-row-{{ $maCaLam }}">
                                    <td style="white-space: nowrap;">
                                        <span class="badge-code">#CA-{{ $maCaLam }}</span>
                                    </td>
                                    <td>
                                        <div class="job-title-col">
                                            <div class="job-name">{{ $itemArr['ten_vi_tri_tuyen_dung'] }}</div>
                                            <div class="job-sub"><i class="fa-solid fa-store text-gold"></i> {{ $tenNhaHang }}</div>
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <div class="cell-nowrap">
                                            <i class="fa-solid fa-calendar-day text-gold"></i> {{ date('d/m/Y', strtotime($itemArr['ngay_lam_viec'])) }}
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <div class="cell-nowrap font-medium">
                                            <i class="fa-regular fa-clock text-gold"></i> {{ substr($itemArr['gio_bat_dau'], 0, 5) }} - {{ substr($itemArr['gio_ket_thuc'], 0, 5) }}
                                        </div>
                                    </td>
                                    @php
                                        $holidayShiftName = \App\Helpers\HolidayHelper::isVietnameseHoliday($itemArr['ngay_lam_viec']);
                                    @endphp
                                    <td style="white-space: nowrap;">
                                        <div class="cell-nowrap wage-highlight" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <span>{{ number_format($itemArr['muc_luong_theo_gio'], 0, ',', '.') }} VNĐ/giờ</span>
                                            @if (!empty($holidayShiftName))
                                                <span class="badge-holiday-x3" style="background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%); color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.25); display: inline-flex; align-items: center; gap: 3px;" title="{{ $holidayShiftName }}">
                                                    🔥 Lương Ngày Lễ (x3)
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <span class="tag-badge nowrap" id="quota-badge-{{ $maCaLam }}" style="{{ $isFullQuota ? 'background:#fef3c7; color:#92400e; font-weight:700;' : '' }}">
                                            Đã tuyển: <span class="hired-num">{{ $soLuongDaTuyen }}</span>/<span class="total-num">{{ $soLuongTuyen }}</span>
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;" id="status-cell-{{ $maCaLam }}" data-date="{{ $ngayLamViec }}">
                                        <span class="{{ $statusClass }}" style="{{ $statusStyle }}">
                                            <i class="{{ $statusIcon }}"></i> {{ $statusLabel }}
                                        </span>
                                        @if ($statusType === 'da_huy' && !empty($lyDoHuy))
                                            <div style="font-size: 0.72rem; color: #dc2626; margin-top: 3px; max-width: 135px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="Lý do hủy: {{ $lyDoHuy }}">
                                                <i class="fa-solid fa-circle-info"></i> {{ $lyDoHuy }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <div class="table-action-btns nowrap" style="display: flex; gap: 0.35rem; align-items: center; justify-content: center;">
                                            <!-- NÚT ỨNG VIÊN KÈM BADGE ĐẾM SỐ LƯỢNG CHỜ DUYỆT -->
                                            <button type="button" 
                                                    class="btn btn-gold btn-sm btn-open-applicant-modal"
                                                    data-ca-id="{{ $maCaLam }}"
                                                    data-ca-ten="{{ $itemArr['ten_vi_tri_tuyen_dung'] }}"
                                                    data-ca-nha-hang="{{ $tenNhaHang }}"
                                                    data-ca-ngay="{{ date('d/m/Y', strtotime($itemArr['ngay_lam_viec'])) }}"
                                                    data-ca-gio="{{ substr($itemArr['gio_bat_dau'], 0, 5) }} - {{ substr($itemArr['gio_ket_thuc'], 0, 5) }}"
                                                    data-ca-luong="{{ number_format($itemArr['muc_luong_theo_gio'], 0, ',', '.') }} VNĐ/giờ"
                                                    data-ca-tuyen="{{ $soLuongTuyen }}"
                                                    data-ca-da-tuyen="{{ $soLuongDaTuyen }}"
                                                    title="Xem và duyệt ứng viên ca này">
                                                <i class="fa-solid fa-users"></i> Ứng viên
                                                <span class="applicant-badge-pill {{ $soLuongChoDuyet > 0 ? 'has-pending' : 'is-zero' }}" id="badge-pending-{{ $maCaLam }}">
                                                    {{ $soLuongChoDuyet }}
                                                </span>
                                            </button>

                                            <!-- NÚT SỬA: CHỈ CHO PHÉP SỬA TRƯỚC VÀ TRONG NGÀY CÓ CA LÀM, SAU NGÀY ĐÓ SẼ BỊ KHÓA -->
                                            @if ($canEdit)
                                                <button type="button" 
                                                        class="btn btn-outline btn-sm btn-open-edit-shift-modal"
                                                        id="btn-edit-shift-{{ $maCaLam }}"
                                                        data-id="{{ $maCaLam }}"
                                                        data-position="{{ $itemArr['ten_vi_tri_tuyen_dung'] }}"
                                                        data-date="{{ $itemArr['ngay_lam_viec'] }}"
                                                        data-start="{{ substr($itemArr['gio_bat_dau'], 0, 5) }}"
                                                        data-end="{{ substr($itemArr['gio_ket_thuc'], 0, 5) }}"
                                                        data-wage="{{ (int)($holidayShiftName ? round($itemArr['muc_luong_theo_gio'] / 3) : $itemArr['muc_luong_theo_gio']) }}"
                                                        data-is-holiday="{{ !empty($holidayShiftName) ? '1' : '0' }}"
                                                        data-holiday-name="{{ $holidayShiftName ?? '' }}"
                                                        data-quantity="{{ $soLuongTuyen }}"
                                                        data-requirements="{{ $itemArr['yeu_cau_cong_viec'] ?? '' }}"
                                                        data-benefits="{{ $itemArr['quyen_loi_ca_lam'] ?? '' }}"
                                                        data-applicants="{{ (int)($itemArr['tong_so_ung_vien'] ?? ($item->tong_so_ung_vien ?? 0)) }}"
                                                        title="Chỉnh sửa ca làm này trong Popup">
                                                    <i class="fa-solid fa-pen-to-square"></i> Sửa
                                                </button>
                                            @else
                                                <button type="button" 
                                                        class="btn btn-outline btn-sm" 
                                                        id="btn-edit-shift-{{ $maCaLam }}"
                                                        disabled 
                                                        style="opacity: 0.45; cursor: not-allowed; background: #f8fafc; color: #94a3b8; border-color: #e2e8f0;" 
                                                        title="{{ $statusType === 'da_huy' ? 'Ca đã bị hủy, không thể chỉnh sửa' : 'Ca đã hoàn thành (quá ngày), không thể chỉnh sửa' }}">
                                                    <i class="fa-solid fa-lock"></i> Sửa
                                                </button>
                                            @endif

                                            <!-- NÚT HỦY / XÓA CA LÀM (MỞ MODAL NHẬP LÝ DO HỦY CA) -->
                                            @if ($canCancel)
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm btn-open-cancel-shift-modal"
                                                        id="btn-cancel-shift-{{ $maCaLam }}"
                                                        data-id="{{ $maCaLam }}"
                                                        data-title="{{ $itemArr['ten_vi_tri_tuyen_dung'] }}"
                                                        data-date="{{ date('d/m/Y', strtotime($ngayLamViec)) }}"
                                                        data-time="{{ substr($itemArr['gio_bat_dau'], 0, 5) }} - {{ substr($itemArr['gio_ket_thuc'], 0, 5) }}"
                                                        title="Hủy ca làm việc này">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            @else
                                                <button type="button" 
                                                        class="btn btn-outline btn-sm" 
                                                        id="btn-cancel-shift-{{ $maCaLam }}"
                                                        disabled 
                                                        style="opacity: 0.35; cursor: not-allowed; border-color: #e2e8f0; color: #94a3b8;" 
                                                        title="{{ $statusType === 'da_huy' ? 'Ca đã bị hủy' : 'Ca đã hoàn thành' }}">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- PHÂN TRANG BOOTSTRAP 5 --}}
        @if (method_exists($caLamList, 'links') && $caLamList->lastPage() > 1)
            <div class="pagination-wrapper">
                <nav aria-label="Phân trang ca làm việc quản lý">
                    <ul class="pagination">
                        @if ($caLamList->onFirstPage())
                            <li class="page-item disabled"><span class="page-link"><i class="fa-solid fa-chevron-left"></i></span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $caLamList->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a></li>
                        @endif
                        @foreach ($caLamList->getUrlRange(max(1, $caLamList->currentPage() - 2), min($caLamList->lastPage(), $caLamList->currentPage() + 2)) as $page => $url)
                            <li class="page-item {{ $page == $caLamList->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach
                        @if ($caLamList->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $caLamList->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link"><i class="fa-solid fa-chevron-right"></i></span></li>
                        @endif
                    </ul>
                </nav>
                <p class="pagination-info">
                    Trang {{ $caLamList->currentPage() }} / {{ $caLamList->lastPage() }} &mdash; Tổng {{ $caLamList->total() }} ca làm việc
                </p>
            </div>
        @endif
    </div>
</section>

<!-- ========================================================================= -->
<!-- MODAL POPUP: TẠO MỚI / CHỈNH SỬA CA LÀM VIỆC (LOẠI BỎ HOÀN TOÀN ma_dinh_danh_ca) -->
<!-- ========================================================================= -->
<div id="modalShiftFormPopup" class="gs-custom-modal-overlay" style="display: none; z-index: 10000;">
    <div class="gs-custom-modal-card" style="max-width: 680px;">
        <div class="gs-custom-modal-header">
            <div class="header-content-left">
                <span class="header-badge-tag"><i class="fa-solid fa-calendar-plus text-gold"></i> BIỂU MẪU CA LÀM VIỆC</span>
                <h2 id="modalShiftFormHeading" class="modal-main-heading">Đăng Tuyển Ca Làm Việc Mới (Popup)</h2>
                <p style="margin: 0; font-size: 0.85rem; color: #64748b;">Nhập đầy đủ thông tin vị trí, thời gian và chỉ tiêu tuyển dụng cho nhà hàng.</p>
            </div>
            <button type="button" class="btn-close-custom-modal" id="btnCloseShiftFormModal" aria-label="Đóng">&times;</button>
        </div>

        <form id="formSaveShiftAjax" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            @csrf
            <div class="gs-custom-modal-body" style="padding: 1.5rem 1.75rem; overflow-y: auto; max-height: calc(85vh - 140px);">
                <input type="hidden" name="ma_ca_lam" id="sfMaCaLam" value="0">
                <input type="hidden" name="is_ajax" value="1">

                <div id="sfAlertBox" class="alert-box error" style="display: none; margin-bottom: 1.25rem;"></div>

                <!-- KHUNG CẢNH BÁO CA ĐÃ CÓ ỨNG VIÊN ĐĂNG KÝ (RÀNG BUỘC CÁCH 1) -->
                <div id="sfLockedShiftWarning" class="alert alert-warning" style="display: none; background: #fefce8; border: 1px solid #fde047; color: #854d0e; padding: 0.85rem 1.15rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; line-height: 1.5;">
                    <div style="display: flex; gap: 0.65rem; align-items: flex-start;">
                        <span style="font-size: 1.25rem; line-height: 1; flex-shrink: 0;">⚠️</span>
                        <div>
                            <strong>Ca làm này đã có ứng viên đăng ký.</strong> Bạn chỉ có thể sửa số lượng tuyển, yêu cầu và quyền lợi. Nếu muốn thay đổi Ngày/Giờ/Lương, vui lòng Hủy ca và tạo ca mới.
                        </div>
                    </div>
                </div>

                <!-- 1. VỊ TRÍ TUYỂN DỤNG F&B -->
                <div style="margin-bottom: 1rem;">
                    <label for="sfTenViTri" style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 0.88rem;">
                        Vị trí tuyển dụng F&B <span style="color: #dc2626;">*</span>
                    </label>
                    <select name="ten_vi_tri_tuyen_dung" id="sfTenViTri" required
                            style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.9rem; background: #fff;">
                        <option value="">-- Chọn vị trí công việc --</option>
                        <option value="Nhân viên Phục vụ">Nhân viên Phục vụ</option>
                        <option value="Nhân viên Tiếp thực">Nhân viên Tiếp thực</option>
                        <option value="Nhân viên Pha chế">Nhân viên Pha chế</option>
                        <option value="Nhân viên Phụ bếp">Nhân viên Phụ bếp</option>
                        <option value="Nhân viên Bếp">Nhân viên Bếp</option>
                    </select>
                </div>

                <!-- 2. NGÀY LÀM VIỆC & MỨC LƯƠNG & SỐ LƯỢNG -->
                <div class="form-grid-3" style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label for="sfNgayLam" style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 0.88rem;">
                            Ngày làm việc <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="date" name="ngay_lam_viec" id="sfNgayLam" required min="{{ $todayDate }}"
                               style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.9rem;">
                        <div id="sfHolidayNameNotice" style="display: none; color: #b45309; font-size: 0.78rem; font-weight: 600; margin-top: 5px;">
                            <i class="fa-solid fa-cake-candles text-gold"></i> <span id="sfHolidayNameText"></span>
                        </div>
                    </div>

                    <div>
                        <label for="sfMucLuong" style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 0.88rem;">
                            Lương/Giờ (VNĐ) <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="number" name="muc_luong_theo_gio" id="sfMucLuong" required min="10000" step="1000" value="25000"
                               style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.9rem;">
                        <div id="sfHolidayWageNotice" style="display: none; color: #dc2626; font-size: 0.8rem; font-weight: 600; margin-top: 5px; line-height: 1.35;">
                            🔥 Ngày lễ: Mức lương nhập vào sẽ được tự động x3 khi lưu ca!
                        </div>
                    </div>

                    <div>
                        <label for="sfSoLuong" style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 0.88rem;">
                            Chỉ tiêu tuyển <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="number" name="so_luong_tuyen" id="sfSoLuong" required min="1" max="50" value="1"
                               style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.9rem;">
                    </div>
                </div>

                <!-- 3. KHUNG GIỜ LÀM VIỆC (24H CHUẨN) -->
                <div style="background: #fffdf5; border: 1px solid #fde68a; padding: 1rem; border-radius: 12px; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.65rem;">
                        <span style="font-weight: 700; color: #b45309; font-size: 0.88rem;">
                            <i class="fa-solid fa-clock text-gold"></i> Khung giờ làm việc (Định dạng 24 Giờ chuẩn) <span style="color: #dc2626;">*</span>
                        </span>
                        <span id="sfShiftDurationBadge" style="font-size: 0.8rem; font-weight: 700; background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 20px; border: 1px solid #fde68a;">
                            <i class="fa-solid fa-hourglass-half"></i> 5.0 giờ (17:30 - 22:30)
                        </span>
                    </div>

                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 0.85rem;">
                        <button type="button" class="btn-quick-shift" data-start="08:00" data-end="14:00" title="Ca sáng 6 tiếng">
                            🌅 Ca Sáng (08:00 - 14:00)
                        </button>
                        <button type="button" class="btn-quick-shift" data-start="11:00" data-end="15:00" title="Ca trưa 4 tiếng">
                            ☀️ Ca Trưa (11:00 - 15:00)
                        </button>
                        <button type="button" class="btn-quick-shift active-preset" data-start="17:30" data-end="22:30" title="Ca tối F&B 5 tiếng">
                            🌆 Ca Tối (17:30 - 22:30)
                        </button>
                        <button type="button" class="btn-quick-shift" data-start="14:00" data-end="22:00" title="Ca chiều 8 tiếng">
                            🌇 Ca Chiều (14:00 - 22:00)
                        </button>
                        <button type="button" class="btn-quick-shift" data-start="08:00" data-end="17:00" title="Ca full ngày 9 tiếng">
                            🏢 Full Ca (08:00 - 17:00)
                        </button>
                    </div>

                    <div class="form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label for="sfGioBatDau" style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px; font-size: 0.84rem;">
                                Giờ bắt đầu (24h) <span style="color: #dc2626;">*</span>
                            </label>
                            <select name="gio_bat_dau" id="sfGioBatDau" required
                                    style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.9rem; background: #fff; font-weight: 600; color: #1e293b;">
                                @for ($h = 0; $h < 24; $h++)
                                    @foreach ([0, 15, 30, 45] as $m)
                                        @php $t = sprintf('%02d:%02d', $h, $m); @endphp
                                        <option value="{{ $t }}" {{ $t === '17:30' ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label for="sfGioKetThuc" style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px; font-size: 0.84rem;">
                                Giờ kết thúc (24h) <span style="color: #dc2626;">*</span>
                            </label>
                            <select name="gio_ket_thuc" id="sfGioKetThuc" required
                                    style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.9rem; background: #fff; font-weight: 600; color: #1e293b;">
                                @for ($h = 0; $h < 24; $h++)
                                    @foreach ([0, 15, 30, 45] as $m)
                                        @php $t = sprintf('%02d:%02d', $h, $m); @endphp
                                        <option value="{{ $t }}" {{ $t === '22:30' ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 4. YÊU CẦU CÔNG VIỆC -->
                <div style="margin-bottom: 1rem;">
                    <label for="sfYeuCau" style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 0.88rem;">
                        Yêu cầu công việc
                    </label>
                    <textarea name="yeu_cau_cong_viec" id="sfYeuCau" rows="2"
                              placeholder="Ví dụ: Tác phong nhanh nhẹn, trung thực, ưu tiên ứng viên có kinh nghiệm phục vụ nhà hàng..."
                              style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.9rem; resize: vertical;"></textarea>
                </div>

                <!-- 5. QUYỀN LỢI CA LÀM -->
                <div style="margin-bottom: 0.5rem;">
                    <label for="sfQuyenLoi" style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 0.88rem;">
                        Quyền lợi ca làm việc
                    </label>
                    <textarea name="quyen_loi_ca_lam" id="sfQuyenLoi" rows="2"
                              placeholder="Ví dụ: Bao cơm ca, miễn phí giữ xe, hỗ trợ tip chia đều theo ca làm..."
                              style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.9rem; resize: vertical;"></textarea>
                </div>
            </div>

            <div class="gs-custom-modal-footer" style="padding: 1rem 1.75rem;">
                <button type="button" class="btn btn-outline btn-sm" id="btnCancelShiftFormModal">Hủy Bỏ</button>
                <button type="submit" class="btn btn-gold btn-sm" id="btnSubmitSaveShift" style="padding: 0.5rem 1.5rem; font-weight: 700;">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Ca Làm Việc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL POPUP DUYỆT ỨNG VIÊN THEO CA (AJAX) -->
<!-- ========================================================================= -->
<div id="modalShiftApplicants" class="gs-custom-modal-overlay" style="display: none; z-index: 10000;">
    <div class="gs-custom-modal-card">
        <div class="gs-custom-modal-header">
            <div class="header-content-left">
                <span class="header-badge-tag"><i class="fa-solid fa-users-viewfinder text-gold"></i> XÉT DUYỆT ỨNG VIÊN THEO CA</span>
                <h2 id="modalShiftTitle" class="modal-main-heading">--</h2>
                <div class="modal-shift-info-tags">
                    <span class="info-tag"><i class="fa-solid fa-store text-gold"></i> <strong id="modalShiftStore">--</strong></span>
                    <span class="info-tag"><i class="fa-solid fa-calendar-day text-gold"></i> <span id="modalShiftDate">--</span></span>
                    <span class="info-tag"><i class="fa-regular fa-clock text-gold"></i> <span id="modalShiftTime">--</span></span>
                    <span class="info-tag"><i class="fa-solid fa-money-bill-wave text-gold"></i> <span id="modalShiftWage">--</span></span>
                    <span class="info-tag quota-tag" id="modalShiftQuotaTag"><i class="fa-solid fa-user-check text-gold"></i> Chỉ tiêu: <strong id="modalShiftQuota">0/0</strong></span>
                </div>
            </div>
            <button type="button" class="btn-close-custom-modal" id="btnCloseApplicantModal" aria-label="Đóng">&times;</button>
        </div>

        <div class="gs-custom-modal-body">
            <div id="modalLoadingState" style="display: none; text-align: center; padding: 3rem 1rem;">
                <div class="gs-spinner"></div>
                <p style="margin-top: 1rem; color: #64748b; font-weight: 500;">Đang tải danh sách hồ sơ ứng viên theo ca làm...</p>
            </div>

            <div id="modalEmptyState" style="display: none; text-align: center; padding: 3rem 1rem;">
                <div style="width: 64px; height: 64px; margin: 0 auto 1rem; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <h3 style="color: #1e293b; font-size: 1.15rem; margin-bottom: 0.5rem;">Chưa có ứng viên nào ứng tuyển ca làm này</h3>
                <p style="color: #64748b; font-size: 0.9rem; max-width: 420px; margin: 0 auto;">Ca làm đang được mở tuyển. Khi có ứng viên nộp hồ sơ, danh sách sẽ hiển thị tại đây để bạn xét duyệt.</p>
            </div>

            <div id="modalApplicantListContainer" class="applicant-cards-list"></div>
        </div>

        <div class="gs-custom-modal-footer">
            <div style="font-size: 0.85rem; color: #64748b;">
                <i class="fa-solid fa-circle-info text-gold"></i> Thao tác duyệt hoặc từ chối sẽ cập nhật chỉ tiêu và trạng thái ca làm ngay lập tức.
            </div>
            <button type="button" class="btn btn-outline btn-sm" id="btnDoneApplicantModal">Đóng Popup</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL PHỤ: FORM NHẬP LÝ DO TỪ CHỐI ỨNG VIÊN -->
<!-- ========================================================================= -->
<div id="modalRejectReasonPrompt" class="gs-custom-modal-overlay gs-sub-modal-overlay" style="display: none; z-index: 100000;">
    <div class="gs-custom-modal-card gs-sub-modal-card">
        <div class="gs-sub-modal-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: #991b1b; font-size: 1.15rem;">Xác Nhận Từ Chối Ứng Viên</h3>
                    <p style="margin: 2px 0 0 0; color: #64748b; font-size: 0.85rem;">Ứng viên: <strong id="rejectPromptCandidateName" style="color: #1e293b;">--</strong></p>
                </div>
            </div>
            <button type="button" class="btn-close-custom-modal" id="btnCloseRejectPrompt">&times;</button>
        </div>

        <form id="formRejectCandidateAjax" style="padding: 1.25rem 1.5rem;">
            @csrf
            <input type="hidden" id="rejectPromptMaUngTuyen" value="">
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="rejectPromptReasonText" style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px; font-size: 0.9rem;">
                    Lý do từ chối ứng viên <span style="color: #dc2626;">* (Bắt buộc)</span>:
                </label>
                <textarea id="rejectPromptReasonText" rows="4" required
                          style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem; font-family: inherit; font-size: 0.9rem; resize: vertical; outline: none;"
                          placeholder="Ví dụ: Ca làm đã đủ ứng viên phù hợp, kinh nghiệm chưa đáp ứng yêu cầu phục vụ ca tối..."></textarea>
                <div id="rejectPromptErrorMsg" style="display: none; color: #dc2626; font-size: 0.8rem; margin-top: 4px; font-weight: 500;">
                    <i class="fa-solid fa-circle-exclamation"></i> Vui lòng nhập lý do từ chối trước khi xác nhận.
                </div>
                <small style="color: #64748b; font-size: 0.8rem; display: block; margin-top: 4px;">
                    <i class="fa-solid fa-circle-info text-gold"></i> Lý do này sẽ lưu vào hệ thống và hiển thị minh bạch cho ứng viên.
                </small>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.25rem;">
                <button type="button" id="btnCancelRejectPrompt" class="btn btn-outline btn-sm">Hủy Bỏ</button>
                <button type="submit" id="btnConfirmRejectAjax" class="btn btn-sm" style="background: #dc2626; color: #fff; border: 1px solid #dc2626; font-weight: 600;">
                    <i class="fa-solid fa-check"></i> Xác Nhận Từ Chối
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL POPUP: XÁC NHẬN HỦY / XÓA CA LÀM VIỆC (BẮT BUỘC NHẬP LÝ DO HỦY) -->
<!-- ========================================================================= -->
<div id="modalCancelShiftPopup" class="gs-custom-modal-overlay" style="display: none; z-index: 100000;">
    <div class="gs-custom-modal-card" style="max-width: 520px;">
        <div class="gs-custom-modal-header" style="background: #fff; border-bottom: 1px solid #fee2e2;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: #991b1b; font-size: 1.15rem;">Hủy / Xóa Ca Làm Việc</h3>
                    <p style="margin: 2px 0 0 0; color: #64748b; font-size: 0.82rem;">Ca: <strong id="cancelShiftJobTitle" style="color: #1e293b;">--</strong></p>
                </div>
            </div>
            <button type="button" class="btn-close-custom-modal" id="btnCloseCancelShiftModal">&times;</button>
        </div>

        <form id="formCancelShiftAjax" style="padding: 1.25rem 1.5rem;">
            @csrf
            <input type="hidden" name="ma_ca_lam" id="cancelShiftId" value="">
            <input type="hidden" name="is_ajax" value="1">

            <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: #9a3412;">
                <i class="fa-solid fa-circle-info"></i> Ca sau khi hủy sẽ <strong>không hiển thị với ứng viên/khách</strong> nhưng vẫn được lưu trong trang quản lý với trạng thái <strong>"Đã hủy"</strong> và không thể chỉnh sửa.
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="cancelShiftReason" style="display: block; font-weight: 700; color: #334155; margin-bottom: 6px; font-size: 0.88rem;">
                    Lý do hủy / xóa ca làm việc <span style="color: #dc2626;">* (Bắt buộc)</span>:
                </label>
                <textarea name="ly_do_huy" id="cancelShiftReason" rows="3" required
                          style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.85rem; font-family: inherit; font-size: 0.9rem; resize: vertical;"
                          placeholder="Ví dụ: Nhà hàng thay đổi lịch hoạt động, chi nhánh đã sắp xếp đủ nhân sự cơ hữu..."></textarea>
                <div id="cancelShiftErrorMsg" style="display: none; color: #dc2626; font-size: 0.8rem; margin-top: 4px; font-weight: 500;">
                    <i class="fa-solid fa-circle-exclamation"></i> Vui lòng nhập lý do hủy ca trước khi xác nhận.
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" id="btnDismissCancelShiftModal" class="btn btn-outline btn-sm">Quay Lại</button>
                <button type="submit" id="btnConfirmCancelShift" class="btn btn-sm" style="background: #dc2626; color: #fff; border: 1px solid #dc2626; font-weight: 600; padding: 0.5rem 1.25rem;">
                    <i class="fa-solid fa-ban"></i> Xác Nhận Hủy Ca
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('styles')
<style>
/* 1. BADGE SỐ LƯỢNG ỨNG VIÊN & NÚT CHỌN NHANH CA 24H */
.btn-quick-shift {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 5px 11px;
    border-radius: 7px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-quick-shift:hover {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
}

.btn-quick-shift.active-preset {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-color: #d97706;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
}

.applicant-badge-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1;
    margin-left: 5px;
    transition: all 0.2s ease;
}

.applicant-badge-pill.has-pending {
    background: #dc2626;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
    animation: pulseBadge 2s infinite ease-in-out;
}

.applicant-badge-pill.is-zero {
    background: rgba(0, 0, 0, 0.12);
    color: #475569;
}

@keyframes pulseBadge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.12); }
}

/* 2. MODAL OVERLAY & CARD */
.gs-custom-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    animation: fadeInModal 0.25s ease-out;
}

@keyframes fadeInModal {
    from { opacity: 0; }
    to { opacity: 1; }
}

.gs-custom-modal-card {
    background: #ffffff;
    border-radius: 18px;
    width: 100%;
    max-width: 820px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    animation: slideUpModal 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUpModal {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.gs-sub-modal-card {
    max-width: 520px;
}

.gs-custom-modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1.5rem 1.75rem 1.25rem;
    background: linear-gradient(135deg, #fffdf5 0%, #ffffff 100%);
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}

.gs-sub-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: #fff;
    border-bottom: 1px solid #fee2e2;
}

.header-badge-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #b45309;
    background: #fef3c7;
    padding: 3px 10px;
    border-radius: 20px;
    margin-bottom: 6px;
}

.modal-main-heading {
    margin: 0 0 0.5rem 0;
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
}

.modal-shift-info-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.modal-shift-info-tags .info-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.82rem;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 4px 10px;
    border-radius: 8px;
    font-weight: 500;
}

.modal-shift-info-tags .quota-tag {
    background: #fffbeb;
    border-color: #fde68a;
    color: #92400e;
}

.btn-close-custom-modal {
    background: transparent;
    border: none;
    font-size: 1.6rem;
    line-height: 1;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 8px;
    transition: all 0.2s;
}

.btn-close-custom-modal:hover {
    color: #0f172a;
    background: #f1f5f9;
}

.gs-custom-modal-body {
    padding: 1.5rem 1.75rem;
    overflow-y: auto;
    flex: 1;
    background: #f8fafc;
}

.applicant-cards-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.applicant-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
}

.applicant-card-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
}

.applicant-card-item.status-approved {
    border-left: 4px solid #10b981;
}

.applicant-card-item.status-rejected {
    border-left: 4px solid #ef4444;
    opacity: 0.85;
}

.applicant-card-item.status-pending {
    border-left: 4px solid #f59e0b;
}

.applicant-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}

.applicant-profile-meta {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.applicant-avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #b45309;
    font-weight: 800;
    font-size: 1.15rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(180, 83, 9, 0.15);
}

.applicant-name-wrap h4 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
}

.applicant-contacts {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 3px;
    font-size: 0.82rem;
    color: #64748b;
}

.applicant-contacts a {
    color: #0284c7;
    text-decoration: none;
    font-weight: 600;
}

.applicant-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
}

.applicant-status-badge.pending {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.applicant-status-badge.approved {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.applicant-status-badge.rejected {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.applicant-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .applicant-detail-grid {
        grid-template-columns: 1fr;
    }
}

.applicant-box-snippet {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 10px;
    padding: 0.85rem 1rem;
    font-size: 0.85rem;
}

.applicant-box-snippet strong {
    display: block;
    margin-bottom: 4px;
    color: #334155;
    font-size: 0.82rem;
}

.applicant-box-snippet p {
    margin: 0;
    color: #64748b;
    line-height: 1.45;
}

.applicant-box-snippet.reject-box {
    grid-column: 1 / -1;
    background: #fff5f5;
    border-color: #fecaca;
}

.applicant-box-snippet.reject-box strong {
    color: #b91c1c;
}

.applicant-box-snippet.reject-box p {
    color: #dc2626;
    font-weight: 500;
}

.applicant-action-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f1f5f9;
}

.applicant-applied-time {
    font-size: 0.8rem;
    color: #94a3b8;
}

.applicant-buttons-group {
    display: flex;
    gap: 0.5rem;
}

.btn-approve-ajax {
    background: #10b981;
    color: #ffffff;
    border: 1px solid #10b981;
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.btn-approve-ajax:hover {
    background: #059669;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
}

.btn-reject-prompt-open {
    background: #ffffff;
    color: #dc2626;
    border: 1px solid #fca5a5;
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.btn-reject-prompt-open:hover {
    background: #fef2f2;
    border-color: #dc2626;
}

.gs-custom-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.75rem;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
}

.gs-spinner {
    width: 36px;
    height: 36px;
    border: 3px solid #fde68a;
    border-top-color: #d97706;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.gs-toast-item {
    padding: 12px 16px;
    border-radius: 10px;
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #10b981;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    animation: slideInToast 0.3s ease-out;
}

.gs-toast-item.toast-error {
    border-left-color: #ef4444;
}

@keyframes slideInToast {
    from { opacity: 0; transform: translateX(50px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const todayDate = '{{ $todayDate }}';

    // =========================================================================
    // 1. MODAL POPUP FORM TẠO / SỬA CA LÀM VIỆC
    // =========================================================================
    const modalShiftForm = document.getElementById('modalShiftFormPopup');
    const btnOpenCreate = document.getElementById('btnOpenCreateShiftModal');
    const btnCloseShiftForm = document.getElementById('btnCloseShiftFormModal');
    const btnCancelShiftForm = document.getElementById('btnCancelShiftFormModal');
    const formShift = document.getElementById('formSaveShiftAjax');
    const sfHeading = document.getElementById('modalShiftFormHeading');
    const sfAlert = document.getElementById('sfAlertBox');
    const btnSubmitShift = document.getElementById('btnSubmitSaveShift');

    const sfMaCa = document.getElementById('sfMaCaLam');
    const sfViTri = document.getElementById('sfTenViTri');
    const sfNgay = document.getElementById('sfNgayLam');
    const sfLuong = document.getElementById('sfMucLuong');
    const sfSoLuong = document.getElementById('sfSoLuong');
    const sfBatDau = document.getElementById('sfGioBatDau');
    const sfKetThuc = document.getElementById('sfGioKetThuc');
    const sfYeuCau = document.getElementById('sfYeuCau');
    const sfQuyenLoi = document.getElementById('sfQuyenLoi');

    function updateShiftDurationBadge() {
        const badge = document.getElementById('sfShiftDurationBadge');
        if (!badge || !sfBatDau || !sfKetThuc) return;
        
        const startVal = sfBatDau.value;
        const endVal = sfKetThuc.value;
        if (!startVal || !endVal) return;
        
        const [startH, startM] = startVal.split(':').map(Number);
        const [endH, endM] = endVal.split(':').map(Number);
        
        let startMin = startH * 60 + startM;
        let endMin = endH * 60 + endM;
        
        if (endMin < startMin) {
            endMin += 24 * 60;
        }
        
        const diffMinutes = endMin - startMin;
        const diffHours = (diffMinutes / 60).toFixed(1);
        
        if (diffMinutes <= 0) {
            badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;"></i> Giờ kết thúc không hợp lệ';
            badge.style.background = '#fee2e2';
            badge.style.color = '#991b1b';
            badge.style.borderColor = '#fca5a5';
        } else {
            badge.innerHTML = `<i class="fa-solid fa-hourglass-half"></i> ${diffHours} giờ (${startVal} - ${endVal})`;
            badge.style.background = '#fef3c7';
            badge.style.color = '#92400e';
            badge.style.borderColor = '#fde68a';
        }
    }

    function updateQuickPresetState() {
        const curStart = sfBatDau ? sfBatDau.value : '';
        const curEnd = sfKetThuc ? sfKetThuc.value : '';
        document.querySelectorAll('.btn-quick-shift').forEach(btn => {
            if (btn.dataset.start === curStart && btn.dataset.end === curEnd) {
                btn.classList.add('active-preset');
            } else {
                btn.classList.remove('active-preset');
            }
        });
    }

    if (sfBatDau) {
        sfBatDau.addEventListener('change', function() {
            updateShiftDurationBadge();
            updateQuickPresetState();
        });
    }

    if (sfKetThuc) {
        sfKetThuc.addEventListener('change', function() {
            updateShiftDurationBadge();
            updateQuickPresetState();
        });
    }

    document.querySelectorAll('.btn-quick-shift').forEach(btn => {
        btn.addEventListener('click', function() {
            const start = this.dataset.start;
            const end = this.dataset.end;
            if (sfBatDau && sfKetThuc) {
                sfBatDau.value = start;
                sfKetThuc.value = end;
                updateShiftDurationBadge();
                updateQuickPresetState();
            }
        });
    });

    const sfLockedWarning = document.getElementById('sfLockedShiftWarning');
    const quickShiftBtns = document.querySelectorAll('.btn-quick-shift');

    function setShiftFieldsLockedState(isLocked) {
        if (sfLockedWarning) {
            sfLockedWarning.style.display = isLocked ? 'block' : 'none';
        }

        // 1. Vị trí tuyển dụng (select)
        sfViTri.disabled = isLocked;
        sfViTri.style.backgroundColor = isLocked ? '#f1f5f9' : '#fff';
        sfViTri.style.cursor = isLocked ? 'not-allowed' : '';

        // 2. Ngày làm việc (input date)
        sfNgay.readOnly = isLocked;
        sfNgay.style.backgroundColor = isLocked ? '#f1f5f9' : '#fff';
        sfNgay.style.cursor = isLocked ? 'not-allowed' : '';

        // 3. Giờ bắt đầu & Giờ kết thúc (select)
        sfBatDau.disabled = isLocked;
        sfBatDau.style.backgroundColor = isLocked ? '#f1f5f9' : '#fff';
        sfBatDau.style.cursor = isLocked ? 'not-allowed' : '';

        sfKetThuc.disabled = isLocked;
        sfKetThuc.style.backgroundColor = isLocked ? '#f1f5f9' : '#fff';
        sfKetThuc.style.cursor = isLocked ? 'not-allowed' : '';

        // 4. Mức lương theo giờ (input number)
        sfLuong.readOnly = isLocked;
        sfLuong.style.backgroundColor = isLocked ? '#f1f5f9' : '#fff';
        sfLuong.style.cursor = isLocked ? 'not-allowed' : '';

        // Khóa các nút chọn giờ nhanh
        quickShiftBtns.forEach(btn => {
            btn.disabled = isLocked;
            btn.style.opacity = isLocked ? '0.45' : '1';
            btn.style.cursor = isLocked ? 'not-allowed' : 'pointer';
        });

        // Cho phép nhập dữ liệu bình thường ở các ô: Số lượng cần tuyển, Yêu cầu, Quyền lợi
        sfSoLuong.readOnly = false;
        sfSoLuong.disabled = false;
        sfSoLuong.style.backgroundColor = '#fff';
        sfSoLuong.style.cursor = '';

        sfYeuCau.readOnly = false;
        sfYeuCau.disabled = false;
        sfYeuCau.style.backgroundColor = '#fff';
        sfYeuCau.style.cursor = '';

        sfQuyenLoi.readOnly = false;
        sfQuyenLoi.disabled = false;
        sfQuyenLoi.style.backgroundColor = '#fff';
        sfQuyenLoi.style.cursor = '';
    }

    // -------------------------------------------------------------------------
    // RÀNG BUỘC TỰ ĐỘNG X3 LƯƠNG NGÀY LỄ VIỆT NAM (HolidayHelper JS Bridge)
    // -------------------------------------------------------------------------
    const VIETNAMESE_HOLIDAYS = {!! \App\Helpers\HolidayHelper::getHolidaysJson() !!};
    const sfHolidayWageNotice = document.getElementById('sfHolidayWageNotice');
    const sfHolidayNameNotice = document.getElementById('sfHolidayNameNotice');
    const sfHolidayNameText = document.getElementById('sfHolidayNameText');

    function checkVietnameseHoliday(dateStr) {
        if (!dateStr) return null;
        const parts = dateStr.split('-');
        if (parts.length !== 3) return null;
        const monthDay = parts[1] + '-' + parts[2];
        if (VIETNAMESE_HOLIDAYS.solar && VIETNAMESE_HOLIDAYS.solar[monthDay]) {
            return VIETNAMESE_HOLIDAYS.solar[monthDay];
        }
        if (VIETNAMESE_HOLIDAYS.lunar && VIETNAMESE_HOLIDAYS.lunar[dateStr]) {
            return VIETNAMESE_HOLIDAYS.lunar[dateStr];
        }
        return null;
    }

    function updateHolidayWageNotice() {
        if (!sfNgay || !sfHolidayWageNotice) return;
        const selectedDate = sfNgay.value;
        const holidayName = checkVietnameseHoliday(selectedDate);
        if (holidayName) {
            sfHolidayWageNotice.style.display = 'block';
            if (sfHolidayNameNotice && sfHolidayNameText) {
                sfHolidayNameText.innerText = holidayName;
                sfHolidayNameNotice.style.display = 'block';
            }
        } else {
            sfHolidayWageNotice.style.display = 'none';
            if (sfHolidayNameNotice) {
                sfHolidayNameNotice.style.display = 'none';
            }
        }
    }

    if (sfNgay) {
        sfNgay.addEventListener('change', updateHolidayWageNotice);
        sfNgay.addEventListener('input', updateHolidayWageNotice);
    }

    function openCreateShiftModal() {
        sfHeading.innerText = 'Đăng Tuyển Ca Làm Việc Mới (Popup)';
        sfMaCa.value = '0';
        sfViTri.value = '';
        sfNgay.value = todayDate;
        sfNgay.min = todayDate;
        sfLuong.value = '25000';
        sfSoLuong.value = '1';
        sfBatDau.value = '17:30';
        sfKetThuc.value = '22:30';
        sfYeuCau.value = '';
        sfQuyenLoi.value = '';
        sfAlert.style.display = 'none';

        // Mở khóa toàn bộ các trường cho ca tạo mới
        setShiftFieldsLockedState(false);

        updateHolidayWageNotice();
        updateShiftDurationBadge();
        updateQuickPresetState();
        modalShiftForm.style.display = 'flex';
        setTimeout(() => sfViTri.focus(), 150);
    }

    if (btnOpenCreate) {
        btnOpenCreate.addEventListener('click', openCreateShiftModal);
    }

    document.querySelectorAll('.btn-trigger-create-modal').forEach(btn => {
        btn.addEventListener('click', openCreateShiftModal);
    });

    document.querySelectorAll('.btn-open-edit-shift-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            // Kiểm tra ngày làm việc: chỉ cho phép sửa trước và trong ngày có ca làm
            if (d.date < todayDate) {
                showToast('Chỉ cho phép sửa ca làm trước và trong ngày có ca làm đó. Sau ngày đó ca đã tự động khóa thành "Đã hoàn thành"!', 'error');
                return;
            }

            const applicantsCount = parseInt(d.applicants || '0', 10);
            const hasApplicants = applicantsCount > 0;

            sfHeading.innerText = 'Chỉnh Sửa Ca Làm Việc #' + d.id;
            sfMaCa.value = d.id;
            sfViTri.value = d.position || '';
            sfNgay.value = d.date || '';
            sfNgay.min = todayDate;
            sfLuong.value = d.wage || '25000';
            sfSoLuong.value = d.quantity || '1';
            
            const startClean = (d.start || '17:30').substring(0, 5);
            const endClean = (d.end || '22:30').substring(0, 5);
            sfBatDau.value = startClean;
            sfKetThuc.value = endClean;
            
            sfYeuCau.value = d.requirements || '';
            sfQuyenLoi.value = d.benefits || '';
            sfAlert.style.display = 'none';

            // Ràng buộc Cách 1: Khóa trường nếu ca đã có ứng viên đăng ký
            setShiftFieldsLockedState(hasApplicants);

            updateHolidayWageNotice();
            updateShiftDurationBadge();
            updateQuickPresetState();
            modalShiftForm.style.display = 'flex';
        });
    });

    function closeShiftFormModal() {
        modalShiftForm.style.display = 'none';
        if (sfHolidayWageNotice) sfHolidayWageNotice.style.display = 'none';
        if (sfHolidayNameNotice) sfHolidayNameNotice.style.display = 'none';
    }

    if (btnCloseShiftForm) btnCloseShiftForm.addEventListener('click', closeShiftFormModal);
    if (btnCancelShiftForm) btnCancelShiftForm.addEventListener('click', closeShiftFormModal);

    if (formShift) {
        formShift.addEventListener('submit', function(e) {
            e.preventDefault();
            sfAlert.style.display = 'none';

            // Chỉ kiểm tra ràng buộc thời gian quá khứ nếu các trường chưa bị khóa
            const isEditingLocked = sfViTri.disabled;
            if (!isEditingLocked) {
                const startDateTime = new Date(sfNgay.value + 'T' + sfBatDau.value + ':00');
                if (startDateTime <= new Date()) {
                    sfAlert.innerText = 'Không thể tạo hoặc sửa ca làm việc có thời gian bắt đầu trong quá khứ hoặc trước thời điểm hiện tại!';
                    sfAlert.style.display = 'block';
                    sfBatDau.focus();
                    return;
                }

                if (sfBatDau.value === sfKetThuc.value) {
                    sfAlert.innerText = 'Giờ bắt đầu và giờ kết thúc không được trùng nhau.';
                    sfAlert.style.display = 'block';
                    sfKetThuc.focus();
                    return;
                }
            }

            const origHtml = btnSubmitShift.innerHTML;
            btnSubmitShift.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu ca...';
            btnSubmitShift.disabled = true;

            const formData = new FormData(formShift);
            // Đảm bảo các trường bị disabled vẫn gửi đúng giá trị lên server
            if (sfViTri.disabled) formData.append('ten_vi_tri_tuyen_dung', sfViTri.value);
            if (sfBatDau.disabled) formData.append('gio_bat_dau', sfBatDau.value);
            if (sfKetThuc.disabled) formData.append('gio_ket_thuc', sfKetThuc.value);
            if (sfLuong.readOnly) formData.append('muc_luong_theo_gio', sfLuong.value);
            if (sfNgay.readOnly) formData.append('ngay_lam_viec', sfNgay.value);

            fetch('{{ url("/quan-ly/ca-lam-viec/luu") }}', {
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
                btnSubmitShift.innerHTML = origHtml;
                btnSubmitShift.disabled = false;

                if (!data.success) {
                    sfAlert.innerText = data.message || 'Lỗi khi lưu ca làm việc.';
                    sfAlert.style.display = 'block';
                    return;
                }

                closeShiftFormModal();
                showToast(data.message || 'Đã lưu ca làm việc thành công!', data.warning ? 'warning' : 'success');

                setTimeout(() => {
                    window.location.reload();
                }, data.warning ? 1800 : 800);
            })
            .catch(err => {
                btnSubmitShift.innerHTML = origHtml;
                btnSubmitShift.disabled = false;
                sfAlert.innerText = 'Lỗi kết nối máy chủ hoặc phiên đăng nhập đã hết hạn.';
                sfAlert.style.display = 'block';
                console.error(err);
            });
        });
    }

    // =========================================================================
    // 2. MODAL POPUP HỦY / XÓA CA LÀM VIỆC (BẮT BUỘC NHẬP LÝ DO HỦY)
    // =========================================================================
    const modalCancelShift = document.getElementById('modalCancelShiftPopup');
    const btnCloseCancelShift = document.getElementById('btnCloseCancelShiftModal');
    const btnDismissCancelShift = document.getElementById('btnDismissCancelShiftModal');
    const formCancelShift = document.getElementById('formCancelShiftAjax');
    const inputCancelShiftId = document.getElementById('cancelShiftId');
    const inputCancelShiftReason = document.getElementById('cancelShiftReason');
    const textCancelJobTitle = document.getElementById('cancelShiftJobTitle');
    const cancelShiftErrorMsg = document.getElementById('cancelShiftErrorMsg');
    const btnConfirmCancel = document.getElementById('btnConfirmCancelShift');

    document.querySelectorAll('.btn-open-cancel-shift-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const shiftId = this.dataset.id;
            const title = this.dataset.title;
            const dateStr = this.dataset.date;
            const timeStr = this.dataset.time;

            inputCancelShiftId.value = shiftId;
            textCancelJobTitle.innerText = `${title} (${dateStr} • ${timeStr})`;
            inputCancelShiftReason.value = '';
            cancelShiftErrorMsg.style.display = 'none';

            modalCancelShift.style.display = 'flex';
            setTimeout(() => inputCancelShiftReason.focus(), 150);
        });
    });

    function closeCancelShiftModal() {
        if (modalCancelShift) modalCancelShift.style.display = 'none';
    }

    if (btnCloseCancelShift) btnCloseCancelShift.addEventListener('click', closeCancelShiftModal);
    if (btnDismissCancelShift) btnDismissCancelShift.addEventListener('click', closeCancelShiftModal);

    if (formCancelShift) {
        formCancelShift.addEventListener('submit', function(e) {
            e.preventDefault();
            const shiftId = inputCancelShiftId.value;
            const reason = inputCancelShiftReason.value.trim();

            if (!reason) {
                cancelShiftErrorMsg.style.display = 'block';
                inputCancelShiftReason.focus();
                return;
            }

            const origHtml = btnConfirmCancel.innerHTML;
            btnConfirmCancel.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang hủy ca...';
            btnConfirmCancel.disabled = true;

            const formData = new FormData();
            formData.append('ma_ca_lam', shiftId);
            formData.append('ly_do_huy', reason);
            formData.append('is_ajax', '1');

            fetch('{{ url("/quan-ly/ca-lam-viec/xoa") }}', {
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
                btnConfirmCancel.innerHTML = origHtml;
                btnConfirmCancel.disabled = false;

                if (!data.success) {
                    showToast(data.message || 'Không thể hủy ca làm việc.', 'error');
                    return;
                }

                closeCancelShiftModal();
                showToast(data.message || 'Đã hủy ca làm việc thành công!', 'success');

                // Cập nhật giao diện trực tiếp của dòng ca
                const row = document.getElementById('shift-row-' + shiftId);
                if (row) {
                    const statusCell = document.getElementById('status-cell-' + shiftId);
                    if (statusCell) {
                        statusCell.innerHTML = `
                            <span class="badge-status inactive nowrap" style="background:#fee2e2; color:#991b1b; border-color:#fca5a5;">
                                <i class="fa-solid fa-ban"></i> Đã hủy
                            </span>
                            <div style="font-size: 0.72rem; color: #dc2626; margin-top: 3px; max-width: 135px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="Lý do hủy: ${escapeHtml(reason)}">
                                <i class="fa-solid fa-circle-info"></i> ${escapeHtml(reason)}
                            </div>
                        `;
                    }
                    const editBtn = document.getElementById('btn-edit-shift-' + shiftId);
                    if (editBtn) {
                        editBtn.disabled = true;
                        editBtn.style.opacity = '0.45';
                        editBtn.style.cursor = 'not-allowed';
                        editBtn.style.background = '#f8fafc';
                        editBtn.style.color = '#94a3b8';
                        editBtn.style.borderColor = '#e2e8f0';
                        editBtn.title = 'Ca đã bị hủy, không thể chỉnh sửa';
                        editBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Sửa';
                    }
                    const cancelBtn = document.getElementById('btn-cancel-shift-' + shiftId);
                    if (cancelBtn) {
                        cancelBtn.disabled = true;
                        cancelBtn.style.opacity = '0.35';
                        cancelBtn.style.cursor = 'not-allowed';
                        cancelBtn.style.borderColor = '#e2e8f0';
                        cancelBtn.style.color = '#94a3b8';
                        cancelBtn.title = 'Ca đã bị hủy';
                        cancelBtn.innerHTML = '<i class="fa-solid fa-ban"></i>';
                    }
                }

                setTimeout(() => window.location.reload(), 800);
            })
            .catch(err => {
                btnConfirmCancel.innerHTML = origHtml;
                btnConfirmCancel.disabled = false;
                showToast('Lỗi khi gửi yêu cầu hủy ca.', 'error');
                console.error(err);
            });
        });
    }

    // =========================================================================
    // 3. MODAL POPUP DUYỆT ỨNG VIÊN THEO CA (AJAX)
    // =========================================================================
    const modalApplicants = document.getElementById('modalShiftApplicants');
    const btnCloseModal = document.getElementById('btnCloseApplicantModal');
    const btnDoneModal = document.getElementById('btnDoneApplicantModal');
    const loadingState = document.getElementById('modalLoadingState');
    const emptyState = document.getElementById('modalEmptyState');
    const listContainer = document.getElementById('modalApplicantListContainer');

    const elShiftTitle = document.getElementById('modalShiftTitle');
    const elShiftStore = document.getElementById('modalShiftStore');
    const elShiftDate = document.getElementById('modalShiftDate');
    const elShiftTime = document.getElementById('modalShiftTime');
    const elShiftWage = document.getElementById('modalShiftWage');
    const elShiftQuota = document.getElementById('modalShiftQuota');

    const modalReject = document.getElementById('modalRejectReasonPrompt');
    const btnCloseReject = document.getElementById('btnCloseRejectPrompt');
    const btnCancelReject = document.getElementById('btnCancelRejectPrompt');
    const formReject = document.getElementById('formRejectCandidateAjax');
    const inputRejectMaUngTuyen = document.getElementById('rejectPromptMaUngTuyen');
    const inputRejectReason = document.getElementById('rejectPromptReasonText');
    const elRejectCandName = document.getElementById('rejectPromptCandidateName');
    const elRejectErrorMsg = document.getElementById('rejectPromptErrorMsg');
    const btnConfirmReject = document.getElementById('btnConfirmRejectAjax');

    let currentActiveShiftId = null;

    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('gsToastContainer');
        if (!toastContainer) return;
        const toast = document.createElement('div');
        toast.className = 'gs-toast-item ' + (type === 'error' ? 'toast-error' : (type === 'warning' ? 'toast-warning' : ''));
        const icon = type === 'error' 
            ? '<i class="fa-solid fa-circle-exclamation" style="color:#ef4444;font-size:1.15rem;"></i>' 
            : (type === 'warning' 
                ? '<i class="fa-solid fa-triangle-exclamation" style="color:#d97706;font-size:1.15rem;"></i>' 
                : '<i class="fa-solid fa-circle-check" style="color:#10b981;font-size:1.15rem;"></i>');
        toast.innerHTML = icon + '<span>' + message + '</span>';
        if (type === 'warning') {
            toast.style.borderLeft = '4px solid #f59e0b';
            toast.style.background = '#fffbeb';
            toast.style.color = '#92400e';
        }
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s, transform 0.3s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(50px)';
            setTimeout(() => toast.remove(), 300);
        }, type === 'warning' ? 5500 : 3500);
    }

    document.querySelectorAll('.btn-open-applicant-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const shiftId = this.dataset.caId;
            currentActiveShiftId = shiftId;

            elShiftTitle.innerText = this.dataset.caTen || 'Ca làm việc';
            elShiftStore.innerText = this.dataset.caNhaHang || '';
            elShiftDate.innerText = this.dataset.caNgay || '';
            elShiftTime.innerText = this.dataset.caGio || '';
            elShiftWage.innerText = this.dataset.caLuong || '';
            elShiftQuota.innerText = (this.dataset.caDaTuyen || '0') + '/' + (this.dataset.caTuyen || '1');

            listContainer.innerHTML = '';
            emptyState.style.display = 'none';
            loadingState.style.display = 'block';
            modalApplicants.style.display = 'flex';

            fetchShiftApplicants(shiftId);
        });
    });

    function fetchShiftApplicants(shiftId) {
        fetch('{{ url("/quan-ly/ung-tuyen/theo-ca") }}?ma_ca_lam=' + encodeURIComponent(shiftId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loadingState.style.display = 'none';
            if (!data.success) {
                showToast(data.message || 'Không thể tải danh sách ứng viên.', 'error');
                return;
            }

            const ca = data.ca_lam;
            if (ca) {
                elShiftTitle.innerText = ca.ten_vi_tri_tuyen_dung;
                elShiftStore.innerText = ca.ten_nha_hang;
                elShiftDate.innerText = ca.ngay_lam_viec;
                elShiftTime.innerText = ca.gio_bat_dau + ' - ' + ca.gio_ket_thuc;
                elShiftWage.innerText = ca.muc_luong_theo_gio;
                elShiftQuota.innerText = ca.so_luong_da_tuyen + '/' + ca.so_luong_tuyen;
            }

            updateTableRowCounters(shiftId, data.so_luong_cho_duyet, ca ? ca.so_luong_da_tuyen : null, ca ? ca.so_luong_tuyen : null, ca ? ca.ngay_lam_viec_raw : null);

            const applicants = data.ung_vien_list || [];
            if (applicants.length === 0) {
                emptyState.style.display = 'block';
                listContainer.innerHTML = '';
            } else {
                emptyState.style.display = 'none';
                renderApplicantCards(applicants, shiftId);
            }
        })
        .catch(err => {
            loadingState.style.display = 'none';
            showToast('Lỗi mạng hoặc máy chủ không phản hồi.', 'error');
            console.error(err);
        });
    }

    function renderApplicantCards(applicants, shiftId) {
        let html = '';
        applicants.forEach(app => {
            const id = app.ma_ung_tuyen;
            const name = escapeHtml(app.ho_ten || 'Ứng viên');
            const phone = escapeHtml(app.so_dien_thoai || 'Chưa cập nhật');
            const email = escapeHtml(app.email || '');
            const exp = escapeHtml(app.kinh_nghiem_lam_viec || 'Chưa cập nhật kinh nghiệm chi tiết.');
            const msg = escapeHtml(app.loi_nhan_ung_vien || 'Không có lời nhắn bổ sung.');
            const rejectNote = escapeHtml(app.ghi_chu_nha_tuyen_dung || app.ly_do_tu_choi || '');
            const status = app.trang_thai_ung_tuyen || 'cho_duyet';
            const initial = name.trim().charAt(0).toUpperCase();
            const appliedDate = app.ngay_nop_don ? formatDateTime(app.ngay_nop_don) : '';

            let statusClass = 'status-pending';
            let badgeHtml = '<span class="applicant-status-badge pending"><i class="fa-solid fa-hourglass-half"></i> Chờ duyệt</span>';
            
            if (status === 'da_nhan' || status === 'da_duyet') {
                statusClass = 'status-approved';
                badgeHtml = '<span class="applicant-status-badge approved"><i class="fa-solid fa-circle-check"></i> Đã duyệt nhận ca</span>';
            } else if (status === 'tu_choi') {
                statusClass = 'status-rejected';
                badgeHtml = '<span class="applicant-status-badge rejected"><i class="fa-solid fa-circle-xmark"></i> Đã từ chối</span>';
            }

            html += `
            <div class="applicant-card-item ${statusClass}" id="applicant-card-${id}">
                <div class="applicant-header-row">
                    <div class="applicant-profile-meta">
                        <div class="applicant-avatar-circle">${initial}</div>
                        <div class="applicant-name-wrap">
                            <h4>${name}</h4>
                            <div class="applicant-contacts">
                                <span><i class="fa-solid fa-phone text-gold"></i> <a href="tel:${phone}">${phone}</a></span>
                                ${email ? `<span><i class="fa-solid fa-envelope text-gold"></i> <a href="mailto:${email}">${email}</a></span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div id="applicant-badge-container-${id}">
                        ${badgeHtml}
                    </div>
                </div>

                <div class="applicant-detail-grid">
                    <div class="applicant-box-snippet exp-box">
                        <strong><i class="fa-solid fa-briefcase text-gold"></i> Kinh Nghiệm Làm Việc F&B:</strong>
                        <p>${exp}</p>
                    </div>
                    <div class="applicant-box-snippet msg-box">
                        <strong><i class="fa-solid fa-comment-dots" style="color:#3b82f6;"></i> Lời Nhắn Ứng Tuyển:</strong>
                        <p>${msg}</p>
                    </div>
                    ${(status === 'tu_choi' && rejectNote) ? `
                    <div class="applicant-box-snippet reject-box" id="applicant-reject-box-${id}">
                        <strong><i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i> Lý Do Từ Chối:</strong>
                        <p>${rejectNote}</p>
                    </div>` : `<div class="applicant-box-snippet reject-box" id="applicant-reject-box-${id}" style="display:none;"></div>`}
                </div>

                <div class="applicant-action-row" id="applicant-actions-${id}">
                    <div class="applicant-applied-time">
                        <i class="fa-regular fa-clock"></i> Nộp lúc: ${appliedDate}
                    </div>
                    <div class="applicant-buttons-group" id="applicant-btns-wrap-${id}">
                        ${status === 'cho_duyet' ? `
                            <button type="button" class="btn-approve-ajax" data-id="${id}" data-name="${name}">
                                <i class="fa-solid fa-check"></i> Duyệt Nhận Ca
                            </button>
                            <button type="button" class="btn-reject-prompt-open" data-id="${id}" data-name="${name}">
                                <i class="fa-solid fa-xmark"></i> Từ Chối
                            </button>
                        ` : `
                            <span style="font-size:0.85rem;color:#94a3b8;font-weight:600;"><i class="fa-solid fa-lock"></i> Đã xử lý xét duyệt</span>
                        `}
                    </div>
                </div>
            </div>`;
        });

        listContainer.innerHTML = html;
        bindApplicantCardEvents(shiftId);
    }

    function bindApplicantCardEvents(shiftId) {
        listContainer.querySelectorAll('.btn-approve-ajax').forEach(btn => {
            btn.addEventListener('click', function() {
                const appId = this.dataset.id;
                const candName = this.dataset.name;
                
                if (!confirm(`Bạn có chắc chắn muốn PHÊ DUYỆT ứng viên [${candName}] vào ca làm việc này?`)) {
                    return;
                }

                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang duyệt...';
                this.disabled = true;

                const formData = new FormData();
                formData.append('ma_ung_tuyen', appId);
                formData.append('hanh_dong', 'duyet');
                formData.append('is_ajax', '1');

                fetch('{{ url("/quan-ly/ung-tuyen/duyet") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(res => res.json())
                .then(resData => {
                    if (!resData.success) {
                        this.innerHTML = originalHtml;
                        this.disabled = false;
                        showToast(resData.message || 'Không thể phê duyệt ứng viên.', 'error');
                        return;
                    }

                    showToast(`Đã PHÊ DUYỆT thành công ứng viên [${candName}]!`, 'success');

                    const card = document.getElementById('applicant-card-' + appId);
                    if (card) {
                        card.className = 'applicant-card-item status-approved';
                        document.getElementById('applicant-badge-container-' + appId).innerHTML = 
                            '<span class="applicant-status-badge approved"><i class="fa-solid fa-circle-check"></i> Đã duyệt nhận ca</span>';
                        document.getElementById('applicant-btns-wrap-' + appId).innerHTML = 
                            '<span style="font-size:0.85rem;color:#10b981;font-weight:700;"><i class="fa-solid fa-circle-check"></i> Đã được chọn vào ca</span>';
                    }

                    const d = resData.data;
                    if (d) {
                        elShiftQuota.innerText = d.so_luong_da_tuyen + '/' + d.so_luong_tuyen;
                        updateTableRowCounters(d.ma_ca_lam || shiftId, d.so_luong_cho_duyet, d.so_luong_da_tuyen, d.so_luong_tuyen, null);
                    }
                })
                .catch(err => {
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                    showToast('Lỗi khi gửi yêu cầu duyệt.', 'error');
                    console.error(err);
                });
            });
        });

        listContainer.querySelectorAll('.btn-reject-prompt-open').forEach(btn => {
            btn.addEventListener('click', function() {
                inputRejectMaUngTuyen.value = this.dataset.id;
                elRejectCandName.innerText = this.dataset.name;
                inputRejectReason.value = '';
                elRejectErrorMsg.style.display = 'none';
                modalReject.style.display = 'flex';
                setTimeout(() => inputRejectReason.focus(), 150);
            });
        });
    }

    if (formReject) {
        formReject.addEventListener('submit', function(e) {
            e.preventDefault();
            const appId = inputRejectMaUngTuyen.value;
            const reason = inputRejectReason.value.trim();

            if (!reason) {
                elRejectErrorMsg.style.display = 'block';
                inputRejectReason.focus();
                return;
            }

            const originalBtnHtml = btnConfirmReject.innerHTML;
            btnConfirmReject.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
            btnConfirmReject.disabled = true;

            const formData = new FormData();
            formData.append('ma_ung_tuyen', appId);
            formData.append('hanh_dong', 'tu_choi');
            formData.append('ly_do_tu_choi', reason);
            formData.append('is_ajax', '1');

            fetch('{{ url("/quan-ly/ung-tuyen/duyet") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(resData => {
                btnConfirmReject.innerHTML = originalBtnHtml;
                btnConfirmReject.disabled = false;

                if (!resData.success) {
                    showToast(resData.message || 'Không thể từ chối ứng viên.', 'error');
                    return;
                }

                modalReject.style.display = 'none';
                showToast('Đã TỪ CHỐI hồ sơ ứng viên và lưu lý do thành công.', 'success');

                const card = document.getElementById('applicant-card-' + appId);
                if (card) {
                    card.className = 'applicant-card-item status-rejected';
                    document.getElementById('applicant-badge-container-' + appId).innerHTML = 
                        '<span class="applicant-status-badge rejected"><i class="fa-solid fa-circle-xmark"></i> Đã từ chối</span>';
                    document.getElementById('applicant-btns-wrap-' + appId).innerHTML = 
                        '<span style="font-size:0.85rem;color:#ef4444;font-weight:700;"><i class="fa-solid fa-ban"></i> Đã từ chối</span>';
                    
                    const rejectBox = document.getElementById('applicant-reject-box-' + appId);
                    if (rejectBox) {
                        rejectBox.innerHTML = `<strong><i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i> Lý Do Từ Chối:</strong><p>${escapeHtml(reason)}</p>`;
                        rejectBox.style.display = 'block';
                    }
                }

                const d = resData.data;
                if (d) {
                    updateTableRowCounters(d.ma_ca_lam || currentActiveShiftId, d.so_luong_cho_duyet, d.so_luong_da_tuyen, d.so_luong_tuyen, null);
                }
            })
            .catch(err => {
                btnConfirmReject.innerHTML = originalBtnHtml;
                btnConfirmReject.disabled = false;
                showToast('Lỗi khi gửi yêu cầu từ chối.', 'error');
                console.error(err);
            });
        });
    }

    function updateTableRowCounters(shiftId, pendingCount, hiredCount, totalCount, shiftDateRaw) {
        if (!shiftId) return;

        const badge = document.getElementById('badge-pending-' + shiftId);
        if (badge && pendingCount !== null && pendingCount !== undefined) {
            badge.innerText = pendingCount;
            if (pendingCount > 0) {
                badge.className = 'applicant-badge-pill has-pending';
            } else {
                badge.className = 'applicant-badge-pill is-zero';
            }
        }

        const quotaBadge = document.getElementById('quota-badge-' + shiftId);
        if (quotaBadge && hiredCount !== null && totalCount !== null) {
            const hiredNum = quotaBadge.querySelector('.hired-num');
            const totalNum = quotaBadge.querySelector('.total-num');
            if (hiredNum) hiredNum.innerText = hiredCount;
            if (totalNum) totalNum.innerText = totalCount;

            if (hiredCount >= totalCount) {
                quotaBadge.style.background = '#fef3c7';
                quotaBadge.style.color = '#92400e';
                quotaBadge.style.fontWeight = '700';
            } else {
                quotaBadge.style.background = '';
                quotaBadge.style.color = '';
                quotaBadge.style.fontWeight = '';
            }
        }

        // Cập nhật cell trạng thái theo ngày và chỉ tiêu chuẩn xác
        const statusCell = document.getElementById('status-cell-' + shiftId);
        if (statusCell && hiredCount !== null && totalCount !== null) {
            const shiftDate = shiftDateRaw || statusCell.dataset.date;
            if (shiftDate && shiftDate < todayDate) {
                // Sau ngày có ca làm đó: tự động khóa thành "Đã hoàn thành"
                statusCell.innerHTML = '<span class="badge-status inactive nowrap" style="background:#f1f5f9; color:#475569; border-color:#cbd5e1;"><i class="fa-solid fa-check-double"></i> Đã hoàn thành</span>';
            } else if (hiredCount >= totalCount) {
                statusCell.innerHTML = '<span class="badge-status nowrap" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;"><i class="fa-solid fa-lock"></i> Đã đủ số lượng</span>';
            } else {
                statusCell.innerHTML = '<span class="badge-status recruit nowrap"><i class="fa-solid fa-circle-check"></i> Đang tuyển</span>';
            }
        }
    }

    function closeApplicantModal() {
        modalApplicants.style.display = 'none';
        currentActiveShiftId = null;
    }

    function closeRejectPrompt() {
        modalReject.style.display = 'none';
    }

    if (btnCloseModal) btnCloseModal.addEventListener('click', closeApplicantModal);
    if (btnDoneModal) btnDoneModal.addEventListener('click', closeApplicantModal);
    if (btnCloseReject) btnCloseReject.addEventListener('click', closeRejectPrompt);
    if (btnCancelReject) btnCancelReject.addEventListener('click', closeRejectPrompt);

    [modalShiftForm, modalApplicants, modalReject, modalCancelShift].forEach(m => {
        if (m) {
            m.addEventListener('click', function(e) {
                if (e.target === m) {
                    if (m === modalReject) closeRejectPrompt();
                    else if (m === modalShiftForm) closeShiftFormModal();
                    else if (m === modalCancelShift) closeCancelShiftModal();
                    else closeApplicantModal();
                }
            });
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    }

    function formatDateTime(dateStr) {
        try {
            const d = new Date(dateStr.replace(/-/g, '/'));
            if (isNaN(d.getTime())) return dateStr;
            const pad = n => n < 10 ? '0' + n : n;
            return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        } catch(e) {
            return dateStr;
        }
    }
});
</script>

{{-- ================================================================
     MANAGER REAL-TIME POLLING (mỗi 4 giây)
     - Tự động cập nhật badge "số đơn chờ duyệt" trên mỗi ca làm.
     - Hiện toast khi có đơn ứng tuyển mới vào.
================================================================ --}}
<script>
(function() {
    'use strict';

    const MANAGER_POLL_MS = 4000;  // 4 giây / lần

    // Thu thập tất cả mã ca trên trang
    function getAllShiftIds() {
        const badges = document.querySelectorAll('[id^="badge-pending-"]');
        return Array.from(badges).map(el => parseInt(el.id.replace('badge-pending-', ''))).filter(n => !isNaN(n) && n > 0);
    }

    // Trạng thái trước đó: { maCaLam: count }
    const _prevCounts = {};
    let _firstPoll    = true;

    function showManagerToast(msg) {
        let container = document.getElementById('gsGlobalToastContainer') || document.getElementById('gsToastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.style.cssText = 'background:#78350f;color:#fff;border-left:4px solid #f59e0b;border-radius:10px;padding:12px 16px;font-size:0.875rem;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);opacity:0;transition:opacity .3s;margin-bottom:8px;';
        toast.innerHTML = '<i class="fa-solid fa-user-plus" style="font-size:1rem;flex-shrink:0;color:#fbbf24;"></i><span>' + msg + '</span>';
        container.appendChild(toast);
        requestAnimationFrame(() => { toast.style.opacity = '1'; });
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 6000);
    }

    function pollShiftPending(shiftId) {
        fetch('/api/applications/pending?ma_ca_lam=' + shiftId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data || !data.success) return;
            const count   = parseInt(data.count) || 0;
            const prevCount = _prevCounts[shiftId] ?? -1;

            // Cập nhật badge
            const badgeEl = document.getElementById('badge-pending-' + shiftId);
            if (badgeEl) {
                badgeEl.textContent = count;
                if (count > 0) {
                    badgeEl.classList.add('has-pending');
                    badgeEl.classList.remove('is-zero');
                } else {
                    badgeEl.classList.remove('has-pending');
                    badgeEl.classList.add('is-zero');
                }
            }

            // Nếu có đơn mới vào (không phải lần poll đầu tiên)
            if (!_firstPoll && prevCount >= 0 && count > prevCount) {
                const diff = count - prevCount;
                showManagerToast('📋 Ca #CA-' + shiftId + ': Có <strong>' + diff + '</strong> đơn ứng tuyển mới! Đang chờ duyệt.');
            }

            _prevCounts[shiftId] = count;
        })
        .catch(() => {});
    }

    function pollAll() {
        const ids = getAllShiftIds();
        ids.forEach(id => pollShiftPending(id));
        if (_firstPoll) _firstPoll = false;
    }

    // Chỉ chạy khi có ca làm trên trang
    if (getAllShiftIds().length > 0) {
        pollAll();
        setInterval(pollAll, MANAGER_POLL_MS);
    }
})();
</script>
@endsection
