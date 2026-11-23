@extends('layouts.app')

@section('title', 'Tìm Ca Làm Việc F&B - Tuyển Dụng Golden Shift')

@section('content')
@php
    $caLamList = $caLamList ?? [];
    $thuongHieuList = $thuongHieuList ?? [];
    $filters = $filters ?? [];
    $currentUser = session('user') ?? auth()->user();
    $isLoggedIn = !empty($currentUser);
    $hasActiveFilters = !empty($filters['tu_khoa']) || !empty($filters['thuong_hieu']) || !empty($filters['tinh_thanh_pho']) || !empty($filters['quan_huyen']) || !empty($filters['vi_tri']) || !empty($filters['muc_luong']) || !empty($filters['ngay_lam_viec']);
@endphp

<section class="page-header-section search-hero-banner">
    <div class="gs-container">
        <div class="breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <span>Tìm kiếm ca làm việc</span>
        </div>
        <div class="header-job-highlight">
            <span class="sub-badge"><i class="fa-solid fa-briefcase text-gold"></i> CỔNG TUYỂN DỤNG CA LÀM VIỆC F&B</span>
            <h1 class="page-title">Tìm Kiếm & Lọc Ca Làm Việc F&B</h1>
            <p class="page-subtitle">Khám phá hàng trăm ca làm linh hoạt tại các chuỗi nhà hàng ẩm thực hàng đầu Golden Gate & Golden Stars.</p>
        </div>

        <!-- 1. THANH TÌM KIẾM NỔI BẬT (TOP SEARCH BAR - CAREERVIET STYLE) -->
        <div class="careerviet-top-search-card glass-panel">
            <form action="{{ url('/ca-lam-viec') }}" method="GET" id="searchFilterForm">
                <div class="top-search-bar-row">
                    <!-- 1. Vị trí công việc F&B -->
                    <div class="search-field-select" style="flex: 1.35; border-left: none; padding-left: 4px;">
                        <i class="fa-solid fa-user-tie select-icon text-gold"></i>
                        <select name="vi_tri" id="selectViTri">
                            <option value="">Tất cả Vị trí F&B</option>
                            @php
                            $dsSearchViTri = [
                                'Nhân viên Phục vụ',
                                'Nhân viên Tiếp thực',
                                'Nhân viên Pha chế',
                                'Nhân viên Phụ bếp',
                                'Nhân viên Bếp'
                            ];
                            $filterViTri = $filters['vi_tri'] ?? '';
                            @endphp
                            @foreach ($dsSearchViTri as $vOption)
                                @php
                                    $isSelected = ($filterViTri === $vOption) || (!empty($filterViTri) && mb_stripos($vOption, $filterViTri) !== false);
                                @endphp
                                <option value="{{ $vOption }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $vOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Chuỗi Thương hiệu -->
                    <div class="search-field-select" style="flex: 1.35;">
                        <i class="fa-solid fa-utensils select-icon text-gold"></i>
                        <select name="thuong_hieu" id="selectThuongHieu">
                            <option value="">Tất cả thương hiệu</option>
                            @foreach ($thuongHieuList as $th)
                                @php
                                    $thArr = is_array($th) ? $th : (method_exists($th, 'toArray') ? $th->toArray() : (array)$th);
                                @endphp
                                <option value="{{ $thArr['ma_dinh_danh'] }}" {{ ($filters['thuong_hieu'] ?? '') === $thArr['ma_dinh_danh'] ? 'selected' : '' }}>
                                    {{ $thArr['ten_thuong_hieu'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Địa điểm (Tỉnh / Thành phố) -->
                    <div class="search-field-select" style="flex: 1.15;">
                        <i class="fa-solid fa-location-dot select-icon text-gold"></i>
                        <select name="tinh_thanh_pho" id="selectTinhThanh">
                            <option value="">Tất cả Địa điểm</option>
                            <option value="Hồ Chí Minh" {{ ($filters['tinh_thanh_pho'] ?? '') === 'Hồ Chí Minh' ? 'selected' : '' }}>TP. Hồ Chí Minh</option>
                            <option value="Hà Nội" {{ ($filters['tinh_thanh_pho'] ?? '') === 'Hà Nội' ? 'selected' : '' }}>Hà Nội</option>
                            <option value="Đà Nẵng" {{ ($filters['tinh_thanh_pho'] ?? '') === 'Đà Nẵng' ? 'selected' : '' }}>Đà Nẵng</option>
                            <option value="Bình Dương" {{ ($filters['tinh_thanh_pho'] ?? '') === 'Bình Dương' ? 'selected' : '' }}>Bình Dương</option>
                            <option value="Cần Thơ" {{ ($filters['tinh_thanh_pho'] ?? '') === 'Cần Thơ' ? 'selected' : '' }}>Cần Thơ</option>
                        </select>
                    </div>

                    <!-- 4. Nút Tìm kiếm nổi bật -->
                    <div class="search-field-btn" style="min-width: 140px;">
                        <button type="submit" class="btn btn-gold btn-search-hero" style="width: 100%; justify-content: center;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>Tìm Kiếm</span>
                        </button>
                    </div>
                </div>

                <!-- 2. BỘ LỌC ĐA TIÊU CHÍ (QUẬN/HUYỆN, LƯƠNG, NGÀY LÀM & XÓA BỘ LỌC) -->
                <div class="filter-pills-bar">
                    <div class="filter-pills-grid" style="grid-template-columns: 1.35fr 1.35fr 1.15fr 140px; gap: 12px; align-items: flex-end;">
                        <!-- Lọc Quận / Huyện -->
                        <div class="filter-pill-item">
                            <label><i class="fa-solid fa-map-pin text-gold"></i> Quận / Huyện:</label>
                            <select name="quan_huyen" onchange="document.getElementById('searchFilterForm').submit();">
                                <option value="">Tất cả quận/huyện</option>
                                <option value="Quận 1" {{ ($filters['quan_huyen'] ?? '') === 'Quận 1' ? 'selected' : '' }}>Quận 1</option>
                                <option value="Quận 3" {{ ($filters['quan_huyen'] ?? '') === 'Quận 3' ? 'selected' : '' }}>Quận 3</option>
                                <option value="Quận 7" {{ ($filters['quan_huyen'] ?? '') === 'Quận 7' ? 'selected' : '' }}>Quận 7</option>
                                <option value="Quận 10" {{ ($filters['quan_huyen'] ?? '') === 'Quận 10' ? 'selected' : '' }}>Quận 10</option>
                                <option value="Bình Thạnh" {{ ($filters['quan_huyen'] ?? '') === 'Bình Thạnh' ? 'selected' : '' }}>Bình Thạnh</option>
                                <option value="Hai Bà Trưng" {{ ($filters['quan_huyen'] ?? '') === 'Hai Bà Trưng' ? 'selected' : '' }}>Hai Bà Trưng (HN)</option>
                                <option value="Cầu Giấy" {{ ($filters['quan_huyen'] ?? '') === 'Cầu Giấy' ? 'selected' : '' }}>Cầu Giấy (HN)</option>
                            </select>
                        </div>

                        <!-- Lọc Mức thù lao / giờ -->
                        <div class="filter-pill-item">
                            <label><i class="fa-solid fa-money-bill-wave text-gold"></i> Mức lương / giờ:</label>
                            <select name="muc_luong" onchange="document.getElementById('searchFilterForm').submit();">
                                <option value="">Tất cả mức lương</option>
                                <option value="duoi_25" {{ ($filters['muc_luong'] ?? '') === 'duoi_25' ? 'selected' : '' }}>Dưới 25.000 VNĐ/giờ</option>
                                <option value="25_35" {{ ($filters['muc_luong'] ?? '') === '25_35' ? 'selected' : '' }}>25.000 - 35.000 VNĐ/giờ</option>
                                <option value="tren_35" {{ ($filters['muc_luong'] ?? '') === 'tren_35' ? 'selected' : '' }}>Trên 35.000 VNĐ/giờ</option>
                            </select>
                        </div>

                        <!-- Lọc Ngày làm việc cụ thể -->
                        <div class="filter-pill-item">
                            <label><i class="fa-solid fa-calendar-day text-gold"></i> Ngày làm việc:</label>
                            <input type="date" name="ngay_lam_viec" value="{{ $filters['ngay_lam_viec'] ?? '' }}" onchange="document.getElementById('searchFilterForm').submit();">
                        </div>

                        <!-- Nút Xóa bộ lọc -->
                        <div class="filter-pill-item filter-reset-wrap" style="padding-top: 0;">
                            <a href="{{ url('/ca-lam-viec') }}" class="btn btn-outline btn-reset-filter" style="width: 100%; justify-content: center; height: 38px; display: inline-flex; align-items: center; border-radius: var(--radius-sm);" title="Xóa tất cả tiêu chí lọc để xem toàn bộ ca làm">
                                <i class="fa-solid fa-rotate-right"></i> Xóa Bộ Lọc
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="gs-container">
        <!-- HEADER KẾT QUẢ TÌM KIẾM -->
        <div class="results-meta-bar">
            <div class="results-count-text">
                <i class="fa-solid fa-layer-group text-gold"></i>
                <span>Tìm thấy <strong class="text-gold font-bold">{{ method_exists($caLamList, 'total') ? $caLamList->total() : count($caLamList) }}</strong> ca làm việc đang mở tuyển phù hợp với tiêu chí</span>
            </div>

            @if ($hasActiveFilters)
                <div class="active-filter-tags">
                    <span class="active-tag-label">Đang lọc:</span>
                    @if (!empty($filters['tu_khoa']))
                        <span class="filter-badge-tag">Từ khóa: "{{ $filters['tu_khoa'] }}"</span>
                    @endif
                    @if (!empty($filters['tinh_thanh_pho']))
                        <span class="filter-badge-tag">{{ $filters['tinh_thanh_pho'] }}</span>
                    @endif
                    @if (!empty($filters['quan_huyen']))
                        <span class="filter-badge-tag">{{ $filters['quan_huyen'] }}</span>
                    @endif
                    @if (!empty($filters['vi_tri']))
                        <span class="filter-badge-tag">Vị trí: {{ $filters['vi_tri'] }}</span>
                    @endif
                    @if (!empty($filters['thuong_hieu']))
                        <span class="filter-badge-tag">Thương hiệu: {{ $filters['thuong_hieu'] }}</span>
                    @endif
                    @if (!empty($filters['muc_luong']))
                        <span class="filter-badge-tag">
                            {{ $filters['muc_luong'] === 'duoi_25' ? '< 25.000 VNĐ/giờ' : ($filters['muc_luong'] === '25_35' ? '25.000 - 35.000 VNĐ/giờ' : '> 35.000 VNĐ/giờ') }}
                        </span>
                    @endif
                    @if (!empty($filters['ngay_lam_viec']))
                        <span class="filter-badge-tag">Ngày: {{ date('d/m/Y', strtotime($filters['ngay_lam_viec'])) }}</span>
                    @endif
                    <a href="{{ url('/ca-lam-viec') }}" class="link-clear-all" title="Xóa tất cả"><i class="fa-solid fa-circle-xmark"></i> Xóa tất cả</a>
                </div>
            @endif
        </div>

        <!-- 3. DANH SÁCH CA LÀM VIỆC DẠNG THẺ (CARD LAYOUT - CAREERVIET STYLE) -->
        @if (empty($caLamList) || count($caLamList) === 0)
            <div class="empty-state-box glass-panel">
                <div class="empty-state-icon-wrap">
                    <i class="fa-solid fa-clipboard-question"></i>
                </div>
                <h3>Không tìm thấy ca làm việc phù hợp</h3>
                <p>Không có ca làm nào thỏa mãn toàn bộ tiêu chí lọc hiện tại. Vui lòng xóa bớt bộ lọc hoặc chọn ngày khác để tiếp tục tìm kiếm.</p>
                <a href="{{ url('/ca-lam-viec') }}" class="btn btn-gold">
                    <i class="fa-solid fa-rotate-right"></i> Xem Tất Cả Ca Làm Việc
                </a>
            </div>
        @else
            <div class="careerviet-shifts-grid">
                @foreach ($caLamList as $ca)
                    @php
                        $caArr = is_array($ca) ? $ca : (method_exists($ca, 'toArray') ? $ca->toArray() : (array)$ca);
                        $soLuongTuyen = (int)($caArr['so_luong_tuyen'] ?? 1);
                        $soLuongDaTuyen = (int)($caArr['so_luong_da_tuyen'] ?? 0);
                        $conLai = max(0, $soLuongTuyen - $soLuongDaTuyen);
                        $isFull = in_array($caArr['trang_thai_ca'] ?? '', ['da_du_nguoi', 'hoan_thanh']) || ($soLuongTuyen > 0 && $soLuongDaTuyen >= $soLuongTuyen);
                        $tenNhaHang = $caArr['ten_nha_hang'] ?? ($ca->nhaHang->ten_nha_hang ?? 'Golden Hotpot');
                        $tenThuongHieu = $caArr['ten_thuong_hieu'] ?? ($ca->nhaHang->thuongHieu->ten_thuong_hieu ?? 'Golden Stars');
                        $bieuTuong = $caArr['bieu_tuong'] ?? ($ca->nhaHang->thuongHieu->bieu_tuong ?? 'fa-utensils');
                        $brandLogoUrl = \App\Models\ThuongHieu::resolveLogoUrl($bieuTuong);
                        $brandFaIcon = \App\Models\ThuongHieu::resolveFallbackIcon($bieuTuong);
                        $quanHuyen = $caArr['quan_huyen'] ?? ($ca->nhaHang->quan_huyen ?? 'Quận 1');
                        $tinhThanh = $caArr['tinh_thanh_pho'] ?? ($ca->nhaHang->tinh_thanh_pho ?? 'TP.HCM');
                        $diaChi = $caArr['dia_chi_chi_tiet'] ?? ($ca->nhaHang->dia_chi_chi_tiet ?? '');
                    @endphp
                    <div class="careerviet-job-card glass-panel">
                        <div class="card-brand-wage-header">
                            <div class="brand-badge-box">
                                <div class="brand-avatar-mini" style="overflow: hidden;">
                                    @if ($brandLogoUrl)
                                        <img src="{{ $brandLogoUrl }}" alt="{{ $tenThuongHieu }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
                                    @else
                                        <i class="fa-solid {{ $brandFaIcon }}"></i>
                                    @endif
                                </div>
                                <div class="brand-meta-names">
                                    <span class="brand-title-text">{{ $tenThuongHieu }}</span>
                                    <span class="branch-locality"><i class="fa-solid fa-location-dot"></i> {{ $quanHuyen }}, {{ $tinhThanh }}</span>
                                </div>
                            </div>
                            @php
                                $holidayShift = \App\Helpers\HolidayHelper::isVietnameseHoliday($caArr['ngay_lam_viec']);
                            @endphp
                            <div class="careerviet-wage-badge" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap; justify-content: flex-end;">
                                <span class="wage-amount">{{ number_format($caArr['muc_luong_theo_gio'], 0, ',', '.') }} VNĐ</span>
                                <span class="wage-unit">/ giờ</span>
                                @if (!empty($holidayShift))
                                    <span class="badge-holiday-x3" style="background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%); color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.25); display: inline-flex; align-items: center; gap: 3px;" title="{{ $holidayShift }}">
                                        🔥 Lương Ngày Lễ (x3)
                                    </span>
                                @endif
                            </div>
                        </div>

                        <h3 class="job-card-title">
                            <a href="{{ url('/ca-lam-viec/chi-tiet?id=' . $caArr['ma_ca_lam']) }}" title="Xem chi tiết ca làm">
                                {{ $caArr['ten_vi_tri_tuyen_dung'] }}
                            </a>
                        </h3>

                        <div class="job-meta-details-list">
                            <div class="meta-detail-item">
                                <i class="fa-solid fa-store text-gold"></i>
                                <span class="meta-label">Nhà hàng:</span>
                                <strong class="meta-val">{{ $tenNhaHang }}</strong>
                            </div>
                            <div class="meta-detail-item">
                                <i class="fa-solid fa-map-location-dot text-gold"></i>
                                <span class="meta-label">Địa chỉ:</span>
                                <span class="meta-val">{{ $diaChi }}</span>
                            </div>
                            <div class="meta-detail-item-grid">
                                <div class="meta-sub-pill">
                                    <i class="fa-solid fa-calendar-day text-gold"></i>
                                    <span>{{ date('d/m/Y', strtotime($caArr['ngay_lam_viec'])) }}</span>
                                </div>
                                <div class="meta-sub-pill">
                                    <i class="fa-regular fa-clock text-gold"></i>
                                    <span>{{ substr($caArr['gio_bat_dau'], 0, 5) }} - {{ substr($caArr['gio_ket_thuc'], 0, 5) }}</span>
                                </div>
                                <div class="meta-sub-pill spot-status {{ $isFull ? 'full' : 'available' }}">
                                    <i class="fa-solid {{ $isFull ? 'fa-lock' : 'fa-users' }}"></i>
                                    <span>{{ $isFull ? 'Đã đủ số lượng' : ('Còn ' . $conLai . '/' . $soLuongTuyen . ' chỗ') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="job-perks-pills">
                            <span class="perk-pill"><i class="fa-solid fa-bowl-rice"></i> Bao cơm ca</span>
                            <span class="perk-pill"><i class="fa-solid fa-square-parking"></i> Giữ xe</span>
                            <span class="perk-pill"><i class="fa-solid fa-hand-holding-dollar"></i> Tip ca</span>
                        </div>

                        <div class="card-action-buttons-group">
                            <a href="{{ url('/ca-lam-viec/chi-tiet?id=' . $caArr['ma_ca_lam']) }}" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-circle-info"></i> Chi Tiết
                            </a>
                            @if ($isFull)
                                <button type="button" class="btn btn-sm" disabled style="background: #e2e8f0; color: #64748b; border: 1px solid #cbd5e1; cursor: not-allowed;">
                                    <i class="fa-solid fa-lock"></i> Đã Đủ Số Lượng
                                </button>
                            @else
                                <button type="button" class="btn btn-gold btn-sm btn-quick-apply" 
                                        data-id="{{ $caArr['ma_ca_lam'] }}"
                                        data-title="{{ $caArr['ten_vi_tri_tuyen_dung'] }}"
                                        data-brand="{{ $tenThuongHieu }}"
                                        data-store="{{ $tenNhaHang }}"
                                        data-wage="{{ number_format($caArr['muc_luong_theo_gio'], 0, ',', '.') }} VNĐ/giờ"
                                        data-date="{{ date('d/m/Y', strtotime($caArr['ngay_lam_viec'])) }}"
                                        data-time="{{ substr($caArr['gio_bat_dau'], 0, 5) }} - {{ substr($caArr['gio_ket_thuc'], 0, 5) }}">
                                    <i class="fa-solid fa-bolt"></i> Ứng Tuyển Ngay
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- PHÂN TRANG BOOTSTRAP 5 --}}
        @if (method_exists($caLamList, 'links') && $caLamList->lastPage() > 1)
            <div class="pagination-wrapper">
                <nav aria-label="Phân trang ca làm việc">
                    <ul class="pagination">
                        {{-- Nút Previous --}}
                        @if ($caLamList->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fa-solid fa-chevron-left"></i></span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $caLamList->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a>
                            </li>
                        @endif

                        {{-- Các số trang --}}
                        @foreach ($caLamList->getUrlRange(max(1, $caLamList->currentPage() - 2), min($caLamList->lastPage(), $caLamList->currentPage() + 2)) as $page => $url)
                            <li class="page-item {{ $page == $caLamList->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        {{-- Nút Next --}}
                        @if ($caLamList->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $caLamList->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fa-solid fa-chevron-right"></i></span>
                            </li>
                        @endif
                    </ul>
                </nav>
                <p class="pagination-info">
                    Hiển thị trang {{ $caLamList->currentPage() }} / {{ $caLamList->lastPage() }} &mdash; Tổng {{ $caLamList->total() }} ca làm việc
                </p>
            </div>
        @endif
    </div>
</section>

<!-- MODAL ỨNG TUYỂN NHANH -->
<div id="quickApplyModal" class="modal-overlay" style="display: none;">
    <div class="modal-box glass-panel">
        <button type="button" class="btn-close-modal" id="btnCloseQuickApply">&times;</button>
        
        <div class="modal-header-block">
            <span class="sub-badge"><i class="fa-solid fa-bolt text-gold"></i> ỨNG TUYỂN NHANH CA LÀM</span>
            <h3 id="modalShiftTitle" class="modal-job-heading">--</h3>
            <p id="modalShiftSub" class="modal-job-sub">--</p>
        </div>

        <div class="modal-shift-summary-pill">
            <div class="summary-pill-item">
                <i class="fa-solid fa-money-bill-wave text-gold"></i>
                <span id="modalShiftWage">--</span>
            </div>
            <div class="summary-pill-item">
                <i class="fa-solid fa-calendar-day text-gold"></i>
                <span id="modalShiftDateTime">--</span>
            </div>
        </div>

        <form id="formQuickApply" class="modal-inner-form">
            @csrf
            <input type="hidden" name="ma_ca_lam" id="modalShiftId" value="">

            <div class="form-group">
                <label for="qaHoTen">Họ và tên ứng viên <span class="req">*</span></label>
                <input type="text" id="qaHoTen" name="ho_ten" required 
                       value="{{ is_array($currentUser) ? ($currentUser['ho_ten'] ?? '') : ($currentUser->ho_ten ?? '') }}" 
                       placeholder="Nhập họ và tên đầy đủ của bạn">
            </div>

            <div class="form-group">
                <label for="qaSoDienThoai">Số điện thoại / Zalo liên hệ <span class="req">*</span></label>
                <input type="tel" id="qaSoDienThoai" name="so_dien_thoai" required 
                       value="{{ is_array($currentUser) ? ($currentUser['so_dien_thoai'] ?? '') : ($currentUser->so_dien_thoai ?? '') }}" 
                       placeholder="Nhập 10 chữ số điện thoại (ví dụ: 0912345678)">
            </div>

            <div class="form-group">
                <label for="qaLoiNhan">Kinh nghiệm F&B & Lời nhắn</label>
                <textarea id="qaLoiNhan" name="loi_nhan" rows="3" 
                          placeholder="Ví dụ: Đã có kinh nghiệm làm phục vụ 6 tháng, cam kết đi làm đúng giờ và hoàn thành tốt ca làm..."></textarea>
            </div>

            <div id="qaAlertBox" class="alert-box" style="display: none; margin-bottom: 1rem;"></div>

            <div class="modal-form-actions">
                <button type="button" id="btnCancelQuickApply" class="btn btn-outline">Hủy Bỏ</button>
                <button type="submit" id="btnSubmitQuickApply" class="btn btn-gold">
                    <i class="fa-solid fa-paper-plane"></i> Gửi Hồ Sơ Ứng Tuyển
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('quickApplyModal');
    const btnClose = document.getElementById('btnCloseQuickApply');
    const btnCancel = document.getElementById('btnCancelQuickApply');
    const form = document.getElementById('formQuickApply');
    const alertBox = document.getElementById('qaAlertBox');
    const btnSubmit = document.getElementById('btnSubmitQuickApply');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.btn-quick-apply').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalShiftId').value = this.dataset.id;
            document.getElementById('modalShiftTitle').innerText = this.dataset.title;
            document.getElementById('modalShiftSub').innerText = this.dataset.brand + ' - ' + this.dataset.store;
            document.getElementById('modalShiftWage').innerText = this.dataset.wage;
            document.getElementById('modalShiftDateTime').innerText = this.dataset.date + ' (' + this.dataset.time + ')';

            alertBox.style.display = 'none';
            alertBox.className = 'alert-box';
            modal.style.display = 'flex';
        });
    });

    function closeModal() {
        modal.style.display = 'none';
    }

    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            alertBox.style.display = 'none';
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi hồ sơ...';

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
                btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Gửi Hồ Sơ Ứng Tuyển';

                alertBox.style.display = 'block';
                if (data.success) {
                    alertBox.className = 'alert-box success';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
                    setTimeout(() => {
                        closeModal();
                        window.location.reload();
                    }, 1800);
                } else {
                    alertBox.className = 'alert-box error';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + data.message;
                }
            })
            .catch(err => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Gửi Hồ Sơ Ứng Tuyển';
                alertBox.style.display = 'block';
                alertBox.className = 'alert-box error';
                alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Lỗi kết nối mạng, vui lòng thử lại.';
            });
        });
    }
});
</script>
@endsection
