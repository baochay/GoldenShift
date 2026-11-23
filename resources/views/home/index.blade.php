@extends('layouts.app')

@section('title', 'Golden Stars Recruitment - Cổng Tuyển Dụng F&B Đẳng Cấp')

@section('content')
@php
$thuongHieuList = $thuongHieuList ?? [];
$caLamList = $caLamList ?? [];
$tongSoCa = $tongSoCa ?? 0;
$tongSoThuongHieu = $tongSoThuongHieu ?? 0;
$currentUser = session('user') ?? auth()->user();
$isLoggedIn = !empty($currentUser);
$userRole = is_array($currentUser) ? ($currentUser['vai_tro'] ?? '') : ($currentUser->vai_tro ?? '');
@endphp

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="gs-container">
        <div class="hero-grid">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fa-solid fa-clock-rotate-left text-gold"></i>
                    <span>HỆ THỐNG QUẢN LÝ CA & TUYỂN DỤNG GOLDEN SHIFT</span>
                </div>
                <h1 class="hero-title">
                    Xếp Ca Linh Hoạt, <br>
                    <span class="gradient-text">Kiến Tạo Sự Nghiệp F&B</span> Vững Bền
                </h1>
                <p class="hero-desc">
                    Hệ thống xếp ca làm việc thông minh và tuyển dụng nhân sự chuỗi nhà hàng cao cấp. Đăng ký nhận ca linh hoạt, nâng cao thu nhập.
                </p>

                <!-- Search Bar -->
                <form action="{{ url('/ca-lam-viec') }}" method="GET" class="hero-search-box">
                    <div class="search-select-group">
                        <i class="fa-solid fa-user-tie text-gold"></i>
                        <select name="vi_tri" aria-label="Chọn vị trí ứng tuyển">
                            <option value="">Tất cả vị trí F&B</option>
                            <option value="Nhân viên Phục vụ">Nhân viên Phục vụ</option>
                            <option value="Nhân viên Tiếp thực">Nhân viên Tiếp thực</option>
                            <option value="Nhân viên Pha chế">Nhân viên Pha chế</option>
                            <option value="Nhân viên Phụ bếp">Nhân viên Phụ bếp</option>
                            <option value="Nhân viên Bếp">Nhân viên Bếp</option>
                        </select>
                    </div>
                    <div class="search-divider"></div>
                    <div class="search-select-group">
                        <i class="fa-solid fa-utensils"></i>
                        <select name="thuong_hieu" aria-label="Chọn thương hiệu">
                            <option value="">Tất cả thương hiệu</option>
                            @foreach ($thuongHieuList as $th)
                            @php $thArr = is_array($th) ? $th : (method_exists($th, 'toArray') ? $th->toArray() : (array)$th); @endphp
                            <option value="{{ $thArr['ma_dinh_danh'] ?? '' }}">{{ $thArr['ten_thuong_hieu'] ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-gold">
                        <i class="fa-solid fa-arrow-right"></i> Tìm Ca Ngay
                    </button>
                </form>

                <!-- Quick Stats Pills -->
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">4+</span>
                        <span class="stat-label">Thương hiệu lớn</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-number">10+</span>
                        <span class="stat-label">Chi nhánh TP.HCM</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-number">30K - 45K</span>
                        <span class="stat-label">Thu nhập / giờ</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Bao cơm ca</span>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="visual-card-wrapper">
                    <div class="floating-badge badge-top">
                        <i class="fa-solid fa-circle-check text-green"></i>
                        <div>
                            <strong>Chấm công QR Code</strong>
                            <small>Tính giờ công tự động chính xác</small>
                        </div>
                    </div>

                    <div class="hero-main-card glass-panel">
                        <div class="card-header-bar">
                            <span class="dot red"></span>
                            <span class="dot yellow"></span>
                            <span class="dot green"></span>
                            <span class="bar-title">Hồ Sơ Tuyển Dụng Mới Nhất</span>
                        </div>
                        <div class="card-shift-preview">
                            <div class="shift-preview-item">
                                <div class="shift-icon-box hotpot">
                                    <i class="fa-solid fa-fire-alt"></i>
                                </div>
                                <div class="shift-info">
                                    <h4>Golden Hotpot - Vincom Q1</h4>
                                    <p>Phục vụ bàn (Ca tối 17:30 - 22:30)</p>
                                    <div class="wage-pill">32.000 VNĐ/giờ</div>
                                </div>
                                <span class="badge-status recruit">Đang tuyển</span>
                            </div>

                            <div class="shift-preview-item">
                                <div class="shift-icon-box bbq">
                                    <i class="fa-solid fa-bacon"></i>
                                </div>
                                <div class="shift-info">
                                    <h4>Golden BBQ - Landmark 81</h4>
                                    <p>Nướng bàn & Tiếp thực (Ca tối)</p>
                                    <div class="wage-pill">33.000 VNĐ/giờ</div>
                                </div>
                                <span class="badge-status recruit">Đang tuyển</span>
                            </div>

                            <div class="shift-preview-item">
                                <div class="shift-icon-box dimsum">
                                    <i class="fa-solid fa-utensils"></i>
                                </div>
                                <div class="shift-info">
                                    <h4>Golden Dimsum - Saigon Centre</h4>
                                    <p>Pha chế Trà cao cấp (Ca sáng)</p>
                                    <div class="wage-pill">36.000 VNĐ/giờ</div>
                                </div>
                                <span class="badge-status recruit">Đang tuyển</span>
                            </div>
                        </div>
                    </div>

                    <div class="floating-badge badge-bottom">
                        <i class="fa-solid fa-wallet text-gold"></i>
                        <div>
                            <strong>Thanh toán minh bạch</strong>
                            <small>Theo dõi sổ thu nhập từng ca</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CA LÀM VIỆC TUYỂN GẤP  --}}
<section class="section-shifts">
    <div class="gs-container">
        <div class="section-header-flex">
            <div>
                <span class="sub-badge"><i class="fa-solid fa-fire text-gold"></i> CƠ HỘI NÓNG TRONG NGÀY</span>
                <h2 class="section-title">Ca Làm Việc Cần Tuyển Gấp</h2>
            </div>
            <a href="{{ url('/ca-lam-viec') }}" class="view-all-link">
                Xem tất cả ca làm <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>

        <div class="shifts-grid">
            @foreach ($caLamList as $ca)
            @php
            $caArr = is_array($ca) ? $ca : (method_exists($ca, 'toArray') ? $ca->toArray() : (array)$ca);
            $soLuongTuyen = (int)($caArr['so_luong_tuyen'] ?? 1);
            $soLuongDaTuyen = (int)($caArr['so_luong_da_tuyen'] ?? 0);
            $conLai = max(0, $soLuongTuyen - $soLuongDaTuyen);
            $tenNhaHang = $caArr['ten_nha_hang'] ?? ($ca->nhaHang->ten_nha_hang ?? 'Golden Hotpot');
            $tenThuongHieu = $caArr['ten_thuong_hieu'] ?? ($ca->nhaHang->thuongHieu->ten_thuong_hieu ?? 'Golden Stars');
            $bieuTuong = $caArr['bieu_tuong'] ?? ($ca->nhaHang->thuongHieu->bieu_tuong ?? 'fa-utensils');
            $brandLogoUrl = \App\Models\ThuongHieu::resolveLogoUrl($bieuTuong);
            $brandFaIcon = \App\Models\ThuongHieu::resolveFallbackIcon($bieuTuong);
            $quanHuyen = $caArr['quan_huyen'] ?? ($ca->nhaHang->quan_huyen ?? 'Quận 1');
            @endphp
            <div class="shift-card glass-panel">
                <div class="shift-card-header">
                    <div class="shift-brand">
                        @if ($brandLogoUrl)
                            <img src="{{ $brandLogoUrl }}" alt="{{ $tenThuongHieu }}" style="width: 22px; height: 22px; border-radius: 6px; object-fit: cover;">
                        @else
                            <i class="fa-solid {{ $brandFaIcon }} brand-mini-icon"></i>
                        @endif
                        <span>{{ $tenThuongHieu }}</span>
                    </div>
                    @php
                        $holidayHomeShift = \App\Helpers\HolidayHelper::isVietnameseHoliday($caArr['ngay_lam_viec']);
                    @endphp
                    <div class="shift-wage-tag" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                        <span>{{ number_format($caArr['muc_luong_theo_gio'], 0, ',', '.') }} VNĐ<small>/giờ</small></span>
                        @if (!empty($holidayHomeShift))
                            <span class="badge-holiday-x3" style="background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%); color: #ffffff; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 6px; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.25); display: inline-flex; align-items: center; gap: 3px;" title="{{ $holidayHomeShift }}">
                                🔥 Lương Ngày Lễ (x3)
                            </span>
                        @endif
                    </div>
                </div>

                <h3 class="shift-job-title">
                    <a href="{{ route('ca-lam-viec.show', $caArr['ma_ca_lam']) }}">
                        {{ $caArr['ten_vi_tri_tuyen_dung'] }}
                    </a>
                </h3>

                <div class="shift-details">
                    <div class="detail-row">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>{{ $tenNhaHang }} ({{ $quanHuyen }})</span>
                    </div>
                    <div class="detail-row">
                        <i class="fa-solid fa-calendar-day"></i>
                        <span>{{ date('d/m/Y', strtotime($caArr['ngay_lam_viec'])) }}</span>
                    </div>
                    <div class="detail-row">
                        <i class="fa-solid fa-clock"></i>
                        <span>{{ substr($caArr['gio_bat_dau'], 0, 5) }} - {{ substr($caArr['gio_ket_thuc'], 0, 5) }}</span>
                    </div>
                    <div class="detail-row">
                        <i class="fa-solid fa-users"></i>
                        <span>Chỉ tiêu tuyển: <strong>{{ $conLai }}</strong> / {{ $soLuongTuyen }} vị trí</span>
                    </div>
                </div>

                <div class="shift-perks">
                    <span class="perk-badge"><i class="fa-solid fa-bowl-rice"></i> Cơm ca</span>
                    <span class="perk-badge"><i class="fa-solid fa-square-parking"></i> Giữ xe</span>
                    <span class="perk-badge"><i class="fa-solid fa-award"></i> Tip</span>
                </div>

                <div class="shift-card-actions">
                    <a href="{{ route('ca-lam-viec.show', $caArr['ma_ca_lam']) }}" class="btn btn-outline">
                        Chi Tiết
                    </a>
                    @if (!$isLoggedIn)
                        <a href="{{ route('ca-lam-viec.ung-tuyen-gap', $caArr['ma_ca_lam']) }}" class="btn btn-gold btn-ung-tuyen-gap" title="Ứng tuyển ca làm việc gấp này">
                            <i class="fa-solid fa-bolt"></i> Ứng tuyển ngay
                        </a>
                    @elseif ($userRole === 'ung_vien')
                        <a href="{{ route('ca-lam-viec.show', $caArr['ma_ca_lam']) }}" class="btn btn-gold btn-ung-tuyen-gap" title="Ứng tuyển ca làm việc gấp này">
                            <i class="fa-solid fa-bolt"></i> Ứng tuyển ngay
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- THƯƠNG HIỆU NỔI BẬT  -->
<section class="section-brands">
    <div class="gs-container">
        <div class="section-header text-center">
            <span class="sub-badge"><i class="fa-solid fa-layer-group"></i> HỆ SINH THÁI ẨM THỰC</span>
            <h2 class="section-title">Chuỗi Thương Hiệu Golden Shift</h2>
            <p class="section-desc">Khám phá các chuỗi nhà hàng đang mở đợt tuyển dụng nhân sự quy mô lớn với nhiều quyền lợi vượt trội.</p>
        </div>

        <div class="brands-grid">
            @foreach ($thuongHieuList as $th)
            @php
                $thArr = is_array($th) ? $th : (method_exists($th, 'toArray') ? $th->toArray() : (array)$th);
                $thIcon = $thArr['bieu_tuong'] ?? null;
                $thLogoUrl = \App\Models\ThuongHieu::resolveLogoUrl($thIcon);
                $thFaIcon = \App\Models\ThuongHieu::resolveFallbackIcon($thIcon);
            @endphp
            <div class="brand-card glass-panel">
                <div class="brand-card-top">
                    @if ($thLogoUrl)
                        <img src="{{ $thLogoUrl }}" alt="{{ $thArr['ten_thuong_hieu'] }}" style="width: 56px; height: 56px; border-radius: 12px; object-fit: cover; border: 1px solid #e2e8f0; background: #fff; flex-shrink: 0;">
                    @else
                        <div class="brand-icon-box">
                            <i class="fa-solid {{ $thFaIcon }}"></i>
                        </div>
                    @endif
                    <span class="brand-tag">{{ $thArr['loai_hinh_am_thuc'] ?? 'Ẩm thực' }}</span>
                </div>
                <h3 class="brand-name">{{ $thArr['ten_thuong_hieu'] }}</h3>
                <p class="brand-desc">{{ $thArr['mo_ta_ngan'] }}</p>

                <div class="brand-meta">
                    <div class="meta-item">
                        <i class="fa-solid fa-store"></i>
                        <span>{{ $thArr['so_nha_hang'] ?? 1 }} chi nhánh</span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-user-plus"></i>
                        <span class="text-gold">Đang mở tuyển</span>
                    </div>
                </div>

                <a href="{{ url('/ca-lam-viec?thuong_hieu=' . $thArr['ma_dinh_danh']) }}" class="btn btn-outline-gold btn-block">
                    Xem Ca Làm Việc <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- QUYỀN LỢI KHI ĐỒNG HÀNH CÙNG GOLDENSHIFT -->
<section class="section-perks">
    <div class="gs-container">
        <div class="section-header text-center">
            <span class="sub-badge"><i class="fa-solid fa-gem"></i> ĐÃI NGỘ XỨNG TẦM</span>
            <h2 class="section-title">Tại Sao Nên Chọn Golden Shift?</h2>
            <p class="section-desc">Chúng tôi mang lại môi trường làm việc chuyên nghiệp, đãi ngộ tốt và sự an tâm cao nhất cho nhân sự F&B.</p>
        </div>

        <div class="perks-grid">
            <div class="perk-box glass-panel">
                <div class="perk-icon-circle"><i class="fa-solid fa-money-check-dollar"></i></div>
                <h3>Lương Thưởng Minh Bạch</h3>
                <p>Mức thù lao hấp dẫn từ 30.000 - 45.000 VNĐ/giờ. Sổ thu nhập cập nhật minh bạch sau mỗi ca làm, thanh toán định kỳ chuẩn xác.</p>
            </div>
            <div class="perk-box glass-panel">
                <div class="perk-icon-circle"><i class="fa-solid fa-calendar-check"></i></div>
                <h3>Xếp Lịch Linh Hoạt</h3>
                <p>Tự do đăng ký ca sáng, trưa hoặc tối theo thời gian rảnh. Rất thích hợp cho sinh viên hoặc người muốn tăng thêm thu nhập.</p>
            </div>
            <div class="perk-box glass-panel">
                <div class="perk-icon-circle"><i class="fa-solid fa-qrcode"></i></div>
                <h3>Chấm Công QR Chuẩn Xác</h3>
                <p>Điểm danh vào/ra ca nhanh chóng qua mã QR tại nhà hàng, đảm bảo tính đủ từng phút làm việc mà không lo sai sót số giờ.</p>
            </div>
            <div class="perk-box glass-panel">
                <div class="perk-icon-circle"><i class="fa-solid fa-chart-line"></i></div>
                <h3>Lộ Trình Thăng Tiến Rõ Ràng</h3>
                <p>Được đào tạo nghiệp vụ nhà hàng chuẩn 5 sao. Cơ hội thăng tiến lên Trưởng ca, Giám sát và Quản lý chi nhánh trong hệ thống.</p>
            </div>
        </div>
    </div>
</section>
@endsection