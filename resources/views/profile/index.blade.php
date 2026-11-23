@extends('layouts.app')

@section('title', 'Hồ Sơ Cá Nhân - Golden Shift')

@push('styles')
<style>
    /* Căn chỉnh chính xác nút con mắt trong Tab Đổi mật khẩu */
    .input-with-icon.password-toggle-box {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
    }
    .input-with-icon.password-toggle-box > i:first-child {
        position: absolute !important;
        left: 14px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: #94a3b8 !important;
        font-size: 14px !important;
        pointer-events: none !important;
        z-index: 2 !important;
    }
    .input-with-icon.password-toggle-box .form-control {
        width: 100% !important;
        height: 44px !important;
        padding-left: 40px !important;
        padding-right: 48px !important;
        box-sizing: border-box !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        color: #1e293b !important;
        background: #ffffff !important;
        transition: all 0.15s ease !important;
    }
    .input-with-icon.password-toggle-box .form-control:focus {
        border-color: #f59e0b !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15) !important;
    }
    .btn-toggle-eye {
        position: absolute !important;
        right: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
        border: none !important;
        outline: none !important;
        color: #94a3b8 !important;
        cursor: pointer !important;
        font-size: 15px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 5 !important;
        border-radius: 6px !important;
        transition: color 0.15s ease, background 0.15s ease !important;
    }
    .btn-toggle-eye:hover {
        color: #d97706 !important;
        background: rgba(245, 158, 11, 0.08) !important;
    }
</style>
@endpush

@section('content')
@php
    $role = $user->vai_tro ?? 'ung_vien';
    $roleLabels = [
        'quan_tri_vien'   => ['label' => 'Quản Trị Viên', 'badge' => 'role-quan_tri_vien', 'icon' => 'fa-shield-halved'],
        'quan_ly_nha_hang' => ['label' => 'Quản Lý Nhà Hàng', 'badge' => 'role-pill-gold', 'icon' => 'fa-user-tie'],
        'ung_vien'        => ['label' => 'Ứng Viên Tuyển Dụng', 'badge' => 'role-ung_vien', 'icon' => 'fa-user'],
    ];
    $currentRoleInfo = $roleLabels[$role] ?? ['label' => 'Người Dùng', 'badge' => 'role-ung_vien', 'icon' => 'fa-user'];
    $avatarPath = $user->anh_dai_dien;
    $hasAvatar = !empty($avatarPath) && file_exists(public_path($avatarPath));
    $activeTab = request('tab') ?: (request()->has('reviews_page') ? 'reviews' : session('active_tab', 'profile'));
    $tongLuotDanhGia = (int) ($diemThongKe['tong_luot'] ?? 0);
    $diemTbHienThi = $tongLuotDanhGia > 0 ? (float) ($diemThongKe['diem_tb'] ?? 0.0) : 0.0;
@endphp

<section class="section-profile">
    <div class="gs-container">
        <!-- BREADCRUMB -->
        <nav class="profile-breadcrumb" aria-label="breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house text-gold"></i> Trang Chủ</a>
            <i class="fa-solid fa-chevron-right separator"></i>
            <span class="current">Hồ Sơ Cá Nhân</span>
        </nav>

        <!-- PAGE HEADER -->
        <div class="profile-page-header">
            <div class="header-icon-box">
                <i class="fa-solid fa-id-card-clip"></i>
            </div>
            <div>
                <h1 class="page-title">Hồ Sơ Cá Nhân &amp; Tài Khoản</h1>
                <p class="page-subtitle">Quản lý thông tin định danh, hồ sơ nghề nghiệp và bảo mật tài khoản trên hệ thống Golden Shift.</p>
            </div>
        </div>

        {{-- THÔNG BÁO FLASH MESSAGE --}}
        @if (session('success'))
            <div class="alert-box success" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 1.25rem;"></i>
                <div>
                    <strong>Thành công!</strong> {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert-box error" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 1.25rem;"></i>
                <div>
                    <strong>Lỗi!</strong> {{ session('error') }}
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-box error" style="margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; font-weight: 700;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Vui lòng kiểm tra lại thông tin:
                </div>
                <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.9rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- MAIN PROFILE GRID (2 CỘT) -->
        <div class="profile-grid">
            <!-- CỘT TRÁI: SIDEBAR SUMMARY CARD -->
            <div class="profile-sidebar-col">
                <div class="profile-card glass-panel profile-summary-card">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar-circle" id="avatarCirclePreview">
                            <img src="{{ $hasAvatar ? asset($avatarPath) : 'https://ui-avatars.com/api/?name=' . urlencode($user->ho_ten) . '&background=fef3c7&color=b45309&size=200&font-size=0.45&bold=true' }}" 
                                 alt="{{ $user->ho_ten }}" 
                                 id="avatarPreviewImg"
                                 class="avatar-image">
                        </div>
                        <button type="button" class="btn-avatar-camera" id="btnUploadTrigger" title="Nhấp để tải lên ảnh đại diện mới">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                    </div>

                    <h2 class="profile-name" id="sidebarDisplayName">{{ $user->ho_ten }}</h2>
                    
                    <div class="profile-role-pill {{ $currentRoleInfo['badge'] }}">
                        <i class="fa-solid {{ $currentRoleInfo['icon'] }}"></i>
                        <span>{{ $currentRoleInfo['label'] }}</span>
                    </div>

                    <div class="profile-meta-list">
                        <div class="meta-item">
                            <i class="fa-solid fa-envelope text-gold"></i>
                            <span class="meta-text" title="{{ $user->email }}">{{ $user->email }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-solid fa-phone text-gold"></i>
                            <span class="meta-text" id="sidebarDisplayPhone">{{ $user->so_dien_thoai ?: 'Chưa cập nhật SĐT' }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-solid fa-circle-check text-success"></i>
                            <span class="meta-text">Tài khoản: <strong class="text-success">Đang hoạt động</strong></span>
                        </div>
                        <div class="meta-item">
                            <i class="fa-regular fa-calendar-days text-gold"></i>
                            <span class="meta-text">Tham gia: {{ $user->ngay_tao ? date('d/m/Y', strtotime($user->ngay_tao)) : 'Hệ thống' }}</span>
                        </div>
                    </div>

                    @if ($role === 'quan_ly_nha_hang' && $nhaHang)
                        <div class="sidebar-branch-box">
                            <div class="branch-label"><i class="fa-solid fa-store text-gold"></i> Chi nhánh quản lý:</div>
                            <div class="branch-name">{{ $nhaHang->ten_nha_hang }}</div>
                            <div class="branch-brand">{{ $nhaHang->thuongHieu->ten_thuong_hieu ?? 'Golden Stars' }}</div>
                        </div>
                    @elseif ($role === 'ung_vien')
                        <div class="sidebar-branch-box">
                            <div class="branch-label"><i class="fa-solid fa-briefcase text-gold"></i> Ca làm đã nộp:</div>
                            <a href="{{ url('/ung-vien/ca-lam-da-ung-tuyen') }}" class="btn btn-outline btn-sm btn-block" style="margin-top: 6px; justify-content: center;">
                                <i class="fa-solid fa-list-check"></i> Xem Lịch Sử Ứng Tuyển
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- CỘT PHẢI: TABS & FORM NỘI DUNG CHÍNH -->
            <div class="profile-main-col">
                <div class="profile-card glass-panel profile-tabs-card">
                    <!-- TAB NAVIGATION -->
                    <div class="profile-tabs-header">
                        <button type="button" class="tab-nav-btn {{ $activeTab === 'profile' ? 'active' : '' }}" data-target="#tabProfile">
                            <i class="fa-solid fa-user-pen"></i>
                            <span>Thông Tin Cá Nhân</span>
                        </button>
                        <button type="button" class="tab-nav-btn {{ $activeTab === 'password' ? 'active' : '' }}" data-target="#tabPassword">
                            <i class="fa-solid fa-key"></i>
                            <span>Đổi Mật Khẩu</span>
                        </button>
                        <button type="button" class="tab-nav-btn {{ $activeTab === 'reviews' ? 'active' : '' }}" data-target="#tabReviews">
                            <i class="fa-solid fa-star text-gold"></i>
                            <span>Đánh Giá &amp; Phản Hồi</span>
                            @if ($tongLuotDanhGia > 0)
                                <span style="background: #f59e0b; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 999px; margin-left: 4px; font-weight: 800;">
                                    {{ number_format($diemTbHienThi, 1) }} ⭐
                                </span>
                            @else
                                <span style="background: #94a3b8; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 999px; margin-left: 4px; font-weight: 700;">
                                    0 ⭐
                                </span>
                            @endif
                        </button>
                    </div>

                    <div class="profile-tabs-content">
                        <!-- ============================================== -->
                        <!-- TAB 1: THÔNG TIN CÁ NHÂN -->
                        <!-- ============================================== -->
                        <div class="tab-pane {{ $activeTab === 'profile' ? 'active' : '' }}" id="tabProfile">
                            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="formUpdateProfile">
                                @csrf

                                <!-- Ẩn input file, kích hoạt qua nút camera hoặc nút chọn ảnh -->
                                <input type="file" name="anh_dai_dien" id="inputAvatarFile" accept="image/png, image/jpeg, image/jpg, image/webp" style="display: none;">

                                <div class="form-section-title">
                                    <i class="fa-solid fa-user-gear text-gold"></i>
                                    <span>Thông Tin Định Danh Chung</span>
                                </div>

                                <!-- KHỐI UPLOAD ẢNH NHANH TRONG TAB -->
                                <div class="avatar-upload-row">
                                    <div class="avatar-upload-preview">
                                        <img src="{{ $hasAvatar ? asset($avatarPath) : 'https://ui-avatars.com/api/?name=' . urlencode($user->ho_ten) . '&background=fef3c7&color=b45309&size=120&bold=true' }}" 
                                             alt="Avatar Preview" 
                                             id="tabAvatarPreviewImg">
                                    </div>
                                    <div class="avatar-upload-info">
                                        <div class="upload-title">Ảnh đại diện tài khoản</div>
                                        <div class="upload-desc">Định dạng JPG, PNG hoặc WEBP. Dung lượng tối đa 2MB. Ảnh sẽ được hiển thị trên thanh điều hướng và hồ sơ.</div>
                                        <button type="button" class="btn btn-outline btn-sm" id="btnSelectAvatarTab">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i> Chọn Ảnh Đại Diện Mới
                                        </button>
                                        <span id="selectedFileName" class="selected-file-text"></span>
                                    </div>
                                </div>

                                <div class="form-grid-2">
                                    <!-- HỌ VÀ TÊN (KHÔNG CHỨA CHỮ SỐ) -->
                                    <div class="form-group">
                                        <label for="inputHoTen" class="form-label">
                                            Họ và Tên <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-with-icon">
                                            <i class="fa-solid fa-user"></i>
                                            <input type="text" 
                                                   name="ho_ten" 
                                                   id="inputHoTen" 
                                                   class="form-control @error('ho_ten') is-invalid @enderror" 
                                                   value="{{ old('ho_ten', $user->ho_ten) }}" 
                                                   required 
                                                   maxlength="100"
                                                   pattern="^[^\d]+$"
                                                   title="Họ và tên chỉ bao gồm chữ cái và khoảng trắng, không chứa chữ số"
                                                   placeholder="Nhập họ và tên đầy đủ (ví dụ: Nguyễn Văn A)">
                                        </div>
                                        <small class="form-hint">Yêu cầu chỉ chứa chữ cái và khoảng trắng, không chứa chữ số.</small>
                                        @error('ho_ten')
                                            <div class="field-error-text">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- SỐ ĐIỆN THOẠI (10 CHỮ SỐ BẮT ĐẦU BẰNG 0) -->
                                    <div class="form-group">
                                        <label for="inputSoDienThoai" class="form-label">
                                            Số Điện Thoại <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-with-icon">
                                            <i class="fa-solid fa-phone"></i>
                                            <input type="tel" 
                                                   name="so_dien_thoai" 
                                                   id="inputSoDienThoai" 
                                                   class="form-control @error('so_dien_thoai') is-invalid @enderror" 
                                                   value="{{ old('so_dien_thoai', $user->so_dien_thoai) }}" 
                                                   required 
                                                   maxlength="10"
                                                   pattern="^0[0-9]{9}$"
                                                   title="Số điện thoại gồm 10 chữ số bắt đầu bằng số 0"
                                                   placeholder="Ví dụ: 0912345678">
                                        </div>
                                        <small class="form-hint">Đúng 10 chữ số bắt đầu bằng số 0 (liên hệ tuyển dụng &amp; xác nhận ca).</small>
                                        @error('so_dien_thoai')
                                            <div class="field-error-text">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-grid-2">
                                    <!-- EMAIL (CHO PHÉP ỨNG VIÊN CHỈNH SỬA) -->
                                    <div class="form-group">
                                        <label for="inputEmail" class="form-label">
                                            Email Đăng Nhập
                                            @if ($role === 'ung_vien')
                                                <span class="text-danger">*</span>
                                            @else
                                                <span class="text-muted">(Chỉ đọc)</span>
                                            @endif
                                        </label>
                                        @if ($role === 'ung_vien')
                                            <div class="input-with-icon">
                                                <i class="fa-solid fa-envelope"></i>
                                                <input type="email" 
                                                       name="email"
                                                       id="inputEmail" 
                                                       class="form-control @error('email') is-invalid @enderror" 
                                                       value="{{ old('email', $user->email) }}" 
                                                       required 
                                                       maxlength="150"
                                                       placeholder="Nhập email nhận việc">
                                            </div>
                                            <small class="form-hint">Email nhận việc &amp; thông báo tuyển dụng (cho phép ứng viên cập nhật).</small>
                                            @error('email')
                                                <div class="field-error-text">{{ $message }}</div>
                                            @enderror
                                        @else
                                            <div class="input-with-icon readonly-input">
                                                <i class="fa-solid fa-lock"></i>
                                                <input type="email" 
                                                       id="inputEmail" 
                                                       class="form-control" 
                                                       value="{{ $user->email }}" 
                                                       readonly 
                                                       disabled
                                                       title="Email dùng làm tên đăng nhập và không thể tự thay đổi">
                                            </div>
                                            <small class="form-hint">Tài khoản định danh hệ thống, không thể chỉnh sửa trực tiếp.</small>
                                        @endif
                                    </div>

                                    <!-- VAI TRÒ HỆ THỐNG (READONLY) -->
                                    <div class="form-group">
                                        <label class="form-label">Vai Trò Tài Khoản</label>
                                        <div class="role-display-box">
                                            <span class="role-pill-gold">
                                                <i class="fa-solid {{ $currentRoleInfo['icon'] }}"></i> {{ $currentRoleInfo['label'] }}
                                            </span>
                                        </div>
                                        <small class="form-hint">Quyền hạn truy cập và tác vụ được phân công trong hệ thống.</small>
                                    </div>
                                </div>

                                <!-- ========================================== -->
                                <!-- RIÊNG CHO QUẢN LÝ NHÀ HÀNG (quan_ly_nha_hang) -->
                                <!-- ========================================== -->
                                @if ($role === 'quan_ly_nha_hang')
                                    <div class="role-specific-block">
                                        <div class="form-section-title">
                                            <i class="fa-solid fa-store text-gold"></i>
                                            <span>Thông Tin Chi Nhánh Trực Thuộc Quản Lý</span>
                                        </div>

                                        @if ($nhaHang)
                                            <div class="branch-detail-card">
                                                <div class="branch-detail-grid">
                                                    <div class="detail-item">
                                                        <span class="detail-label"><i class="fa-solid fa-shop text-gold"></i> Tên Nhà Hàng:</span>
                                                        <strong class="detail-value">{{ $nhaHang->ten_nha_hang }}</strong>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label"><i class="fa-solid fa-layer-group text-gold"></i> Thuộc Thương Hiệu:</span>
                                                        <strong class="detail-value text-gold">{{ $nhaHang->thuongHieu->ten_thuong_hieu ?? 'Golden Stars Group' }}</strong>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label"><i class="fa-solid fa-location-dot text-gold"></i> Địa Chỉ Chi Nhánh:</span>
                                                        <span class="detail-value">{{ $nhaHang->dia_chi_chi_tiet ?: 'Đang cập nhật' }}</span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label"><i class="fa-solid fa-map text-gold"></i> Khu Vực:</span>
                                                        <span class="detail-value">{{ $nhaHang->quan_huyen ?: 'Quận 1' }}, {{ $nhaHang->tinh_thanh_pho ?: 'TP. Hồ Chí Minh' }}</span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label"><i class="fa-solid fa-phone text-gold"></i> Hotline Chi Nhánh:</span>
                                                        <span class="detail-value">{{ $nhaHang->so_dien_thoai ?: '1900 8899' }}</span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label"><i class="fa-solid fa-circle-check text-success"></i> Trạng Thái Hoạt Động:</span>
                                                        <span class="badge-status recruit">Đang mở đợt tuyển</span>
                                                    </div>
                                                </div>
                                                <div class="branch-note">
                                                    <i class="fa-solid fa-circle-info text-gold"></i>
                                                    <span>Chi nhánh làm việc do Quản trị viên phân bổ. Nếu cần thuyên chuyển hoặc thay đổi chi nhánh phụ trách, vui lòng liên hệ Ban Điều Hành Golden Shift.</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert-box" style="display: flex; align-items: center; gap: 0.5rem;">
                                                <i class="fa-solid fa-circle-info text-gold"></i>
                                                <span>Bạn hiện chưa được gán chi nhánh cố định. Vui lòng liên hệ Admin để cập nhật nhà hàng trực thuộc.</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- ========================================== -->
                                <!-- RIÊNG CHO ỨNG VIÊN (ung_vien) -->
                                <!-- ========================================== -->
                                @if ($role === 'ung_vien')
                                    <div class="role-specific-block">
                                        <div class="form-section-title">
                                            <i class="fa-solid fa-briefcase text-gold"></i>
                                            <span>Hồ Sơ Nghề Nghiệp &amp; Kinh Nghiệm F&amp;B</span>
                                        </div>

                                        <div class="form-grid-2">
                                            <!-- NGÀY SINH -->
                                            <div class="form-group">
                                                <label for="inputNgaySinh" class="form-label">Ngày Sinh <span class="text-danger">*</span></label>
                                                <div class="input-with-icon">
                                                    <i class="fa-solid fa-cake-candles"></i>
                                                    <input type="date" 
                                                           name="ngay_sinh" 
                                                           id="inputNgaySinh" 
                                                           class="form-control @error('ngay_sinh') is-invalid @enderror"
                                                           value="{{ old('ngay_sinh', $hoSo->ngay_sinh ? date('Y-m-d', strtotime($hoSo->ngay_sinh)) : '') }}"
                                                           max="{{ now()->subYears(16)->format('Y-m-d') }}"
                                                           required>
                                                </div>
                                                <small class="form-hint">Ứng viên phải từ 16 tuổi trở lên mới đủ điều kiện làm việc.</small>
                                                @error('ngay_sinh')
                                                    <div class="field-error-text">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- GIỚI TÍNH (CHỈ NAM HOẶC NỮ) -->
                                            <div class="form-group">
                                                <label for="selectGioiTinh" class="form-label">Giới Tính <span class="text-danger">*</span></label>
                                                <div class="input-with-icon">
                                                    <i class="fa-solid fa-venus-mars"></i>
                                                    <select name="gioi_tinh" id="selectGioiTinh" class="form-control @error('gioi_tinh') is-invalid @enderror" required>
                                                        <option value="" disabled hidden {{ empty(old('gioi_tinh', $hoSo->gioi_tinh)) ? 'selected' : '' }}>-- Chọn giới tính --</option>
                                                        <option value="nam" {{ old('gioi_tinh', $hoSo->gioi_tinh) === 'nam' ? 'selected' : '' }}>Nam</option>
                                                        <option value="nu" {{ old('gioi_tinh', $hoSo->gioi_tinh) === 'nu' ? 'selected' : '' }}>Nữ</option>
                                                    </select>
                                                </div>
                                                <small class="form-hint">Bắt buộc chọn 1 trong 2 tùy chọn: Nam hoặc Nữ.</small>
                                                @error('gioi_tinh')
                                                    <div class="field-error-text">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-grid-2">
                                            <!-- CĂN CƯỚC CÔNG DÂN (CCCD) -->
                                            <div class="form-group">
                                                <label for="inputSoCccd" class="form-label">
                                                    Số Căn Cước Công Dân (CCCD) <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-with-icon">
                                                    <i class="fa-solid fa-id-card"></i>
                                                    <input type="text" 
                                                           name="so_cccd" 
                                                           id="inputSoCccd" 
                                                           class="form-control @error('so_cccd') is-invalid @enderror" 
                                                           value="{{ old('so_cccd', $hoSo->so_cccd) }}" 
                                                           required 
                                                           maxlength="12"
                                                           pattern="^0[0-9]{11}$"
                                                           title="Số CCCD phải bao gồm 12 chữ số và bắt đầu bằng số 0"
                                                           placeholder="Ví dụ: 079123456789">
                                                </div>
                                                <small class="form-hint">Số CCCD phải bao gồm đúng 12 chữ số và bắt đầu bằng số 0.</small>
                                                @error('so_cccd')
                                                    <div class="field-error-text">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- GIỚI THIỆU BẢN THÂN -->
                                        <div class="form-group">
                                            <label for="inputGioiThieu" class="form-label">
                                                Giới Thiệu Bản Thân &amp; Mục Tiêu Ứng Tuyển
                                            </label>
                                            <div class="input-with-icon textarea-icon">
                                                <i class="fa-solid fa-comment-dots"></i>
                                                <textarea name="gioi_thieu_ban_than" 
                                                          id="inputGioiThieu" 
                                                          rows="3" 
                                                          class="form-control" 
                                                          placeholder="Chia sẻ ngắn gọn về bản thân, thời gian rảnh có thể nhận ca, tinh thần trách nhiệm trong công việc...">{{ old('gioi_thieu_ban_than', $hoSo->gioi_thieu_ban_than) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- ========================================== -->
                                <!-- RIÊNG CHO QUẢN TRỊ VIÊN (quan_tri_vien) -->
                                <!-- ========================================== -->
                                @if ($role === 'quan_tri_vien')
                                    <div class="role-specific-block">
                                        <div class="form-section-title">
                                            <i class="fa-solid fa-shield-halved text-gold"></i>
                                            <span>Đặc Quyền Quản Trị Hệ Thống</span>
                                        </div>

                                        <div class="admin-privilege-card">
                                            <div class="privilege-item">
                                                <i class="fa-solid fa-users-gear text-gold"></i>
                                                <div>
                                                    <strong>Quản lý Người dùng &amp; Xét duyệt:</strong>
                                                    <p>Toàn quyền duyệt, từ chối hoặc khóa tài khoản Quản lý chi nhánh nhà hàng trên toàn hệ thống.</p>
                                                </div>
                                            </div>
                                            <div class="privilege-item">
                                                <i class="fa-solid fa-utensils text-gold"></i>
                                                <div>
                                                    <strong>Quản lý Thương hiệu &amp; Chi nhánh:</strong>
                                                    <p>Thêm mới, cập nhật và quản lý chuỗi các nhà hàng F&amp;B thuộc hệ sinh thái Golden Stars.</p>
                                                </div>
                                            </div>
                                            <div class="privilege-item">
                                                <i class="fa-solid fa-chart-line text-gold"></i>
                                                <div>
                                                    <strong>Báo cáo &amp; Giám sát Ca làm:</strong>
                                                    <p>Theo dõi tiến độ đăng ca, tỷ lệ nhận ca của ứng viên và dữ liệu tuyển dụng tổng quan.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-actions-bar">
                                    <button type="submit" class="btn btn-gold btn-lg">
                                        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Hồ Sơ
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- ============================================== -->
                        <!-- TAB 2: ĐỔI MẬT KHẨU -->
                        <!-- ============================================== -->
                        <div class="tab-pane {{ $activeTab === 'password' ? 'active' : '' }}" id="tabPassword">
                            <form action="{{ route('profile.change_password') }}" method="POST" id="formChangePassword">
                                @csrf

                                <div class="form-section-title">
                                    <i class="fa-solid fa-shield-halved text-gold"></i>
                                    <span>Bảo Mật &amp; Đổi Mật Khẩu Đăng Nhập</span>
                                </div>

                                <p class="section-desc-text">
                                    Để bảo mật tài khoản Golden Shift của bạn, hãy sử dụng mật khẩu mới mạnh: tối thiểu 8 ký tự, bao gồm chữ hoa (A-Z), chữ thường (a-z), chữ số (0-9) và ký tự đặc biệt (!@#$...).
                                </p>

                                <!-- MẬT KHẨU HIỆN TẠI -->
                                <div class="form-group" style="max-width: 520px;">
                                    <label for="inputCurrentPassword" class="form-label">
                                        Mật Khẩu Hiện Tại <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-with-icon password-toggle-box">
                                        <i class="fa-solid fa-key"></i>
                                        <input type="password" 
                                               name="mat_khau_hien_tai" 
                                               id="inputCurrentPassword" 
                                               class="form-control @error('mat_khau_hien_tai') is-invalid @enderror" 
                                               required 
                                               placeholder="Nhập mật khẩu hiện tại của bạn">
                                        <button type="button" class="btn-toggle-eye" data-target="inputCurrentPassword" title="Ẩn/Hiện mật khẩu">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('mat_khau_hien_tai')
                                        <div class="field-error-text">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- MẬT KHẨU MỚI -->
                                <div class="form-group" style="max-width: 520px;">
                                    <label for="inputNewPassword" class="form-label">
                                        Mật Khẩu Mới <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-with-icon password-toggle-box">
                                        <i class="fa-solid fa-lock"></i>
                                        <input type="password" 
                                               name="mat_khau_moi" 
                                               id="inputNewPassword" 
                                               class="form-control @error('mat_khau_moi') is-invalid @enderror" 
                                               required 
                                               minlength="8"
                                               placeholder="Nhập mật khẩu mới (tối thiểu 8 ký tự)">
                                        <button type="button" class="btn-toggle-eye" data-target="inputNewPassword" title="Ẩn/Hiện mật khẩu">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="form-hint">Mật khẩu mới tối thiểu 8 ký tự, gồm chữ hoa, chữ thường, chữ số và ký tự đặc biệt.</small>
                                    @error('mat_khau_moi')
                                        <div class="field-error-text">{{ $message }}</div>
                                    @enderror

                                    <!-- Checklist tiêu chí mật khẩu mạnh -->
                                    <div class="pwd-checklist" id="pwdChecklistProfile" style="{{ old('mat_khau_moi') || $errors->has('mat_khau_moi') ? 'display:block;' : 'display:none;' }}; margin-top: 10px; background: rgba(245, 158, 11, 0.08); padding: 12px 14px; border-radius: 8px; border: 1px dashed rgba(245, 158, 11, 0.3);">
                                        <div class="pwd-checklist-title" style="font-size: 0.85rem; font-weight: 600; color: #b45309; margin-bottom: 6px;">
                                            <i class="fa-solid fa-shield-halved text-gold"></i> Tiêu chí mật khẩu mới an toàn:
                                        </div>
                                        <ul class="pwd-criteria" style="list-style: none; padding-left: 0; font-size: 0.82rem; color: #64748b; margin: 0; display: flex; flex-direction: column; gap: 4px;">
                                            <li class="criteria-item" id="prof-len"><i class="fa-regular fa-circle"></i> Tối thiểu 8 ký tự</li>
                                            <li class="criteria-item" id="prof-upper"><i class="fa-regular fa-circle"></i> Có chữ hoa (A-Z)</li>
                                            <li class="criteria-item" id="prof-lower"><i class="fa-regular fa-circle"></i> Có chữ thường (a-z)</li>
                                            <li class="criteria-item" id="prof-digit"><i class="fa-regular fa-circle"></i> Có chữ số (0-9)</li>
                                            <li class="criteria-item" id="prof-special"><i class="fa-regular fa-circle"></i> Có ký tự đặc biệt (!@#$...)</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- XÁC NHẬN MẬT KHẨU MỚI -->
                                <div class="form-group" style="max-width: 520px;">
                                    <label for="inputConfirmPassword" class="form-label">
                                        Xác Nhận Mật Khẩu Mới <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-with-icon password-toggle-box">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <input type="password" 
                                               name="xac_nhan_mat_khau" 
                                               id="inputConfirmPassword" 
                                               class="form-control @error('xac_nhan_mat_khau') is-invalid @enderror" 
                                               required 
                                               minlength="8"
                                               placeholder="Nhập lại mật khẩu mới">
                                        <button type="button" class="btn-toggle-eye" data-target="inputConfirmPassword" title="Ẩn/Hiện mật khẩu">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('xac_nhan_mat_khau')
                                        <div class="field-error-text">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if (session('otp_change_pwd_sent'))
                                <div class="alert-box warning" style="margin-top: 1.25rem; margin-bottom: 1.25rem; background: #fffdf5; border: 1.5px solid #f59e0b; border-radius: 10px; padding: 14px 18px;">
                                    <div style="font-weight: 700; color: #b45309; margin-bottom: 4px;">
                                        <i class="fa-solid fa-envelope-open-text"></i> Mã OTP đã gửi tới email {{ $user->email }}
                                    </div>
                                    <div style="font-size: 13px; color: #475569;">
                                        Vui lòng nhập mã OTP gồm 6 chữ số để xác nhận đúng chủ tài khoản trước khi hoàn tất đổi mật khẩu.
                                    </div>
                                </div>

                                <div class="form-group" style="max-width: 520px;">
                                    <label for="inputOtpChangePwd" class="form-label" style="font-weight: 700; color: #b45309;">
                                        Mã Xác Thực OTP (6 số) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-shield-halved text-gold"></i>
                                        <input type="text" name="otp" id="inputOtpChangePwd" required maxlength="6" pattern="[0-9]{6}" class="form-control @error('otp') is-invalid @enderror" placeholder="Nhập 6 chữ số OTP từ email..." style="font-size: 1.1rem; font-weight: 700; letter-spacing: 4px;" autofocus>
                                    </div>
                                    @error('otp')
                                        <div class="field-error-text">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif

                                <div class="form-actions-bar" style="margin-top: 2rem;">
                                    <button type="submit" class="btn btn-gold btn-lg">
                                        <i class="fa-solid fa-shield-halved"></i> Cập Nhật Mật Khẩu Mới
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- ============================================== -->
                        <!-- TAB 3: ĐÁNH GIÁ & PHẢN HỒI (GIAI ĐOẠN 5) -->
                        <!-- ============================================== -->
                        <div class="tab-pane {{ $activeTab === 'reviews' ? 'active' : '' }}" id="tabReviews">
                            <div class="form-section-title" style="margin-bottom: 20px;">
                                <i class="fa-solid fa-comments text-gold"></i>
                                <span>Lịch Sử Đánh Giá &amp; Phản Hồi Chất Lượng</span>
                            </div>

                            <!-- CARD TỔNG QUAN ĐIỂM SỐ -->
                            <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a; border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.08);">
                                <div style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
                                    <!-- Điểm số lớn -->
                                    <div style="text-align: center; min-width: 140px; border-right: 1px solid rgba(217, 119, 6, 0.2); padding-right: 24px;">
                                        <div style="font-size: 48px; font-weight: 900; color: {{ $tongLuotDanhGia > 0 ? '#b45309' : '#94a3b8' }}; line-height: 1;">
                                            {{ $tongLuotDanhGia > 0 ? number_format($diemTbHienThi, 1) : '0' }}
                                        </div>
                                        <div style="color: #f59e0b; font-size: 18px; margin: 6px 0;">
                                            @php $starCount = $tongLuotDanhGia > 0 ? (int) round($diemTbHienThi) : 0; @endphp
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fa-solid fa-star" style="color: {{ $i <= $starCount ? '#f59e0b' : '#cbd5e1' }};"></i>
                                            @endfor
                                        </div>
                                        <div style="font-size: 12.5px; color: {{ $tongLuotDanhGia > 0 ? '#78350f' : '#64748b' }}; font-weight: 600;">
                                            {{ $tongLuotDanhGia > 0 ? ($tongLuotDanhGia . ' lượt đánh giá') : '0 (Chưa có đánh giá)' }}
                                        </div>
                                    </div>

                                    <!-- Tiêu chí chi tiết -->
                                    <div style="flex: 1; min-width: 250px;">
                                        @if ($role === 'ung_vien')
                                            @php
                                                $thaiDoTb = $tongLuotDanhGia > 0 ? (float) ($diemThongKe['thai_do_tb'] ?? 0.0) : 0.0;
                                                $chuyenCanTb = $tongLuotDanhGia > 0 ? (float) ($diemThongKe['chuyen_can_tb'] ?? 0.0) : 0.0;
                                                $hieuSuatTb = $tongLuotDanhGia > 0 ? (float) ($diemThongKe['hieu_suat_tb'] ?? 0.0) : 0.0;
                                            @endphp
                                            <div style="font-size: 13px; font-weight: 700; color: #78350f; margin-bottom: 10px;">
                                                Chỉ số năng lực chuyên môn được đánh giá bởi Quản lý:
                                            </div>
                                            <!-- Tiêu chí 1: Thái độ -->
                                            <div style="margin-bottom: 8px;">
                                                <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 3px;">
                                                    <span>Thái độ &amp; Tác phong phục vụ:</span>
                                                    <span style="color: #b45309; font-weight: 700;">{{ number_format($thaiDoTb, 1) }} / 5.0</span>
                                                </div>
                                                <div style="height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                                                    <div style="width: {{ ($thaiDoTb / 5) * 100 }}%; height: 100%; background: #f59e0b; border-radius: 999px;"></div>
                                                </div>
                                            </div>
                                            <!-- Tiêu chí 2: Chuyên cần -->
                                            <div style="margin-bottom: 8px;">
                                                <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 3px;">
                                                    <span>Chuyên cần &amp; Đúng giờ:</span>
                                                    <span style="color: #b45309; font-weight: 700;">{{ number_format($chuyenCanTb, 1) }} / 5.0</span>
                                                </div>
                                                <div style="height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                                                    <div style="width: {{ ($chuyenCanTb / 5) * 100 }}%; height: 100%; background: #10b981; border-radius: 999px;"></div>
                                                </div>
                                            </div>
                                            <!-- Tiêu chí 3: Hiệu suất -->
                                            <div>
                                                <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 3px;">
                                                    <span>Hiệu suất &amp; Kỹ năng nghiệp vụ:</span>
                                                    <span style="color: #b45309; font-weight: 700;">{{ number_format($hieuSuatTb, 1) }} / 5.0</span>
                                                </div>
                                                <div style="height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                                                    <div style="width: {{ ($hieuSuatTb / 5) * 100 }}%; height: 100%; background: #3b82f6; border-radius: 999px;"></div>
                                                </div>
                                            </div>
                                        @else
                                            <div style="font-size: 13px; font-weight: 700; color: #78350f; margin-bottom: 6px;">
                                                Mức độ hài lòng của Nhân viên &amp; Ứng viên về Nhà hàng:
                                            </div>
                                            <p style="font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.5;">
                                                Điểm số uy tín được tính toán dựa trên các phản hồi trung thực của ứng viên sau khi hoàn thành ca làm việc tại chi nhánh {{ $nhaHang->ten_nha_hang ?? 'nhà hàng' }}.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- DANH SÁCH CÁC NHẬN XÉT CŨ -->
                            <div style="margin-top: 10px;">
                                <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
                                    <span>Tất Cả Nhận Xét ({{ method_exists($danhGiaList, 'total') ? $danhGiaList->total() : $danhGiaList->count() }})</span>
                                    <span style="font-size: 12px; color: #94a3b8; font-weight: normal;">Hiển thị mới nhất trước</span>
                                </div>

                                @if ($danhGiaList->isEmpty())
                                    <div style="padding: 40px 20px; text-align: center; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                                        <div style="width: 50px; height: 50px; border-radius: 50%; background: #fff; color: #f59e0b; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                                            <i class="fa-regular fa-star"></i>
                                        </div>
                                        <div style="font-size: 14px; font-weight: 700; color: #475569; margin-bottom: 4px;">Chưa có nhận xét nào</div>
                                        <div style="font-size: 12.5px; color: #94a3b8;">
                                            Sau khi các ca làm việc hoàn thành và duyệt chấm công, các nhận xét và phản hồi 2 chiều sẽ tự động xuất hiện tại đây.
                                        </div>
                                    </div>
                                @else
                                    <div style="display: flex; flex-direction: column; gap: 14px;">
                                        @foreach ($danhGiaList as $dg)
                                            @php
                                                $nguoiGui = $dg->nguoiDanhGia;
                                                $ca = $dg->caLamViec;
                                            @endphp
                                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#f59e0b'" onmouseout="this.style.borderColor='#e2e8f0'">
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                                    <div style="display: flex; gap: 12px; align-items: center;">
                                                        <div style="width: 38px; height: 38px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">
                                                            {{ $nguoiGui ? mb_substr($nguoiGui->ho_ten, 0, 1) : 'G' }}
                                                        </div>
                                                        <div>
                                                            <div style="font-weight: 700; font-size: 14px; color: #1e293b;">
                                                                {{ $nguoiGui ? $nguoiGui->ho_ten : 'Đối tác' }}
                                                            </div>
                                                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                                                {{ $nguoiGui && $nguoiGui->vai_tro === 'quan_ly_nha_hang' ? 'Quản lý nhà hàng' : 'Ứng viên nhận ca' }}
                                                                @if ($ca)
                                                                    • <span style="color: #0284c7; font-weight: 600;">Ca: {{ $ca->ten_vi_tri_tuyen_dung }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Số sao & thời gian -->
                                                    <div style="text-align: right;">
                                                        <div style="color: #f59e0b; font-size: 13px;">
                                                            @for ($s = 1; $s <= 5; $s++)
                                                                <i class="fa-solid fa-star" style="color: {{ $s <= $dg->so_sao ? '#f59e0b' : '#cbd5e1' }};"></i>
                                                            @endfor
                                                        </div>
                                                        <div style="font-size: 11px; color: #94a3b8; margin-top: 3px;">
                                                            {{ $dg->ngay_danh_gia ? $dg->ngay_danh_gia->diffForHumans() : '' }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Nội dung nhận xét -->
                                                <div style="font-size: 13.5px; color: #334155; line-height: 1.5; background: #f8fafc; padding: 10px 14px; border-radius: 8px;">
                                                    {{ $dg->noi_dung_nhan_xet ?: 'Người dùng không để lại lời nhận xét bằng chữ.' }}
                                                </div>

                                                <!-- Các tiêu chí phụ nếu có -->
                                                @if ($dg->loai_danh_gia === 'nha_hang_danh_gia_ung_vien')
                                                    <div style="display: flex; gap: 14px; margin-top: 10px; font-size: 11.5px; color: #64748b; flex-wrap: wrap;">
                                                        <span>Thái độ: <strong style="color: #b45309;">{{ $dg->tieu_chi_thai_do }}/5⭐</strong></span>
                                                        <span>Chuyên cần: <strong style="color: #10b981;">{{ $dg->tieu_chi_chuyen_can }}/5⭐</strong></span>
                                                        <span>Hiệu suất: <strong style="color: #3b82f6;">{{ $dg->tieu_chi_hieu_suat }}/5⭐</strong></span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    @if (method_exists($danhGiaList, 'hasPages') && $danhGiaList->hasPages())
                                        <div class="pagination-wrapper">
                                            {{ $danhGiaList->links('pagination::bootstrap-5') }}
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Quản lý chuyển Tab (Thông tin cá nhân & Đổi mật khẩu)
    const tabButtons = document.querySelectorAll('.tab-nav-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            
            // Xóa active trên tất cả tabs
            tabButtons.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));

            // Kích hoạt tab được chọn
            this.classList.add('active');
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.classList.add('active');
            }

            // Cập nhật hash trên URL (nếu muốn)
            if (targetId === '#tabPassword') {
                history.replaceState(null, null, '#password');
            } else if (targetId === '#tabReviews') {
                history.replaceState(null, null, '#reviews');
            } else {
                history.replaceState(null, null, '#profile');
            }
        });
    });

    // Mở đúng tab nếu có hash trên URL
    if (window.location.hash === '#password') {
        const passBtn = document.querySelector('[data-target="#tabPassword"]');
        if (passBtn) passBtn.click();
    } else if (window.location.hash === '#reviews') {
        const revBtn = document.querySelector('[data-target="#tabReviews"]');
        if (revBtn) revBtn.click();
    }

    // 2. Xem trước (Preview) ảnh đại diện Avatar tròn
    const inputAvatarFile = document.getElementById('inputAvatarFile');
    const btnUploadTrigger = document.getElementById('btnUploadTrigger');
    const btnSelectAvatarTab = document.getElementById('btnSelectAvatarTab');
    const avatarPreviewImg = document.getElementById('avatarPreviewImg');
    const tabAvatarPreviewImg = document.getElementById('tabAvatarPreviewImg');
    const selectedFileName = document.getElementById('selectedFileName');

    function triggerFileInput() {
        if (inputAvatarFile) inputAvatarFile.click();
    }

    if (btnUploadTrigger) btnUploadTrigger.addEventListener('click', triggerFileInput);
    if (btnSelectAvatarTab) btnSelectAvatarTab.addEventListener('click', triggerFileInput);

    if (inputAvatarFile) {
        inputAvatarFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Kiểm tra định dạng
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Vui lòng chọn tệp hình ảnh hợp lệ (JPG, PNG, WEBP).');
                inputAvatarFile.value = '';
                return;
            }

            // Kiểm tra dung lượng (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Dung lượng ảnh vượt quá 2MB. Vui lòng chọn ảnh có kích thước nhỏ hơn.');
                inputAvatarFile.value = '';
                return;
            }

            // Hiển thị tên file đã chọn
            if (selectedFileName) {
                selectedFileName.textContent = 'Đã chọn: ' + file.name;
                selectedFileName.style.display = 'inline-block';
            }

            // Đọc file và cập nhật preview tức thời
            const reader = new FileReader();
            reader.onload = function(event) {
                const imgDataUrl = event.target.result;
                if (avatarPreviewImg) avatarPreviewImg.src = imgDataUrl;
                if (tabAvatarPreviewImg) tabAvatarPreviewImg.src = imgDataUrl;
            };
            reader.readAsDataURL(file);
        });
    }

    // 3. Xem / Ẩn mật khẩu (Toggle Password Visibility)
    const eyeButtons = document.querySelectorAll('.btn-toggle-eye');
    eyeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (!targetInput) return;

            const isPassword = targetInput.type === 'password';
            targetInput.type = isPassword ? 'text' : 'password';

            const icon = this.querySelector('i');
            if (icon) {
                if (isPassword) {
                    icon.className = 'fa-solid fa-eye-slash';
                } else {
                    icon.className = 'fa-regular fa-eye';
                }
            }
        });
    });

    // 4. Validate họ tên và số điện thoại theo thời gian thực (Frontend Feedback)
    const inputHoTen = document.getElementById('inputHoTen');
    if (inputHoTen) {
        inputHoTen.addEventListener('input', function() {
            // Thay thế ngay nếu người dùng nhập số
            this.value = this.value.replace(/[0-9]/g, '');
            const sidebarName = document.getElementById('sidebarDisplayName');
            if (sidebarName && this.value.trim().length > 0) {
                sidebarName.textContent = this.value;
            }
        });
    }

    const inputPhone = document.getElementById('inputSoDienThoai');
    if (inputPhone) {
        inputPhone.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
            const sidebarPhone = document.getElementById('sidebarDisplayPhone');
            if (sidebarPhone && this.value.length > 0) {
                sidebarPhone.textContent = this.value;
            }
        });
    }

    // 5. Kiểm tra tiêu chí Mật khẩu mới trong Tab Đổi mật khẩu theo thời gian thực
    const newPwdInput = document.getElementById('inputNewPassword');
    const profChecklist = document.getElementById('pwdChecklistProfile');
    if (newPwdInput && profChecklist) {
        function checkProfCriteria(val) {
            return {
                len: val.length >= 8,
                upper: /[A-Z]/.test(val),
                lower: /[a-z]/.test(val),
                digit: /[0-9]/.test(val),
                special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(val)
            };
        }
        function updateProfUI(c) {
            ['len', 'upper', 'lower', 'digit', 'special'].forEach(k => {
                const el = document.getElementById('prof-' + k);
                if (!el) return;
                const icon = el.querySelector('i');
                if (c[k]) {
                    el.style.color = '#16a34a';
                    el.style.fontWeight = '600';
                    if (icon) {
                        icon.className = 'fa-solid fa-circle-check';
                        icon.style.color = '#16a34a';
                    }
                } else {
                    el.style.color = '#64748b';
                    el.style.fontWeight = 'normal';
                    if (icon) {
                        icon.className = 'fa-regular fa-circle';
                        icon.style.color = '#94a3b8';
                    }
                }
            });
        }
        newPwdInput.addEventListener('focus', () => { profChecklist.style.display = 'block'; });
        newPwdInput.addEventListener('input', () => {
            profChecklist.style.display = 'block';
            updateProfUI(checkProfCriteria(newPwdInput.value));
        });
        newPwdInput.addEventListener('blur', () => {
            if (newPwdInput.value.length === 0) {
                profChecklist.style.display = 'none';
            }
        });
    }
});
</script>
@endsection
