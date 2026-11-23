<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Golden Shift - Quản Lý Ca Làm & Tuyển Dụng F&B')</title>
    <meta name="description" content="Hệ thống Golden Shift quản lý ca làm việc và tuyển dụng F&B chuỗi nhà hàng ẩm thực cao cấp. Nền tảng xếp ca linh hoạt và xét duyệt tài khoản quản lý chi nhánh.">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS (White & Gold Theme) -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ @filemtime(public_path('assets/css/style.css')) ?: time() }}">
    @yield('styles')
    @stack('styles')
</head>

<body class="theme-white-gold">
    @php
    $currentUser = session('user') ?? auth()->user();
    $isLoggedIn = !empty($currentUser);
    $userRole = is_array($currentUser) ? ($currentUser['vai_tro'] ?? '') : ($currentUser->vai_tro ?? '');
    $hoTen = is_array($currentUser) ? ($currentUser['ho_ten'] ?? 'Người dùng') : ($currentUser->ho_ten ?? 'Người dùng');
    $avatarUrl = is_array($currentUser) ? ($currentUser['anh_dai_dien'] ?? '') : ($currentUser->anh_dai_dien ?? '');

    // Tên nhà hàng & thương hiệu cho Quản lý
    $managerRestaurantName = is_array($currentUser) ? ($currentUser['ten_nha_hang'] ?? '') : ($currentUser->nhaHang->ten_nha_hang ?? '');
    $managerBrandName = is_array($currentUser) ? ($currentUser['ten_thuong_hieu'] ?? '') : ($currentUser->nhaHang->thuongHieu->ten_thuong_hieu ?? '');

    if ($isLoggedIn && $userRole === 'quan_ly_nha_hang' && (empty($managerRestaurantName) || empty($managerBrandName))) {
    $managerRestaurantName = 'Golden Hotpot - Chi nhánh Đồng Khởi';
    $managerBrandName = 'Golden Hotpot';
    }

    $managerBrandDisplay = trim((string) $managerBrandName);
    $managerBranchDisplay = trim((string) $managerRestaurantName);
    if ($managerBrandDisplay !== '' && $managerBranchDisplay !== '') {
        // Loại bỏ phần tên thương hiệu bị lặp ở đầu tên chi nhánh (hỗ trợ -, –, —, :, |)
        $brandPrefixPattern = '/^' . preg_quote($managerBrandDisplay, '/') . '(?:$|[\s\-–—:|]+)/iu';
        $managerBranchDisplay = trim((string) preg_replace($brandPrefixPattern, '', $managerBranchDisplay, 1));
    }
    $managerStoreFullTitle = $managerBrandDisplay !== '' && $managerBranchDisplay !== ''
        ? $managerBrandDisplay . ' - ' . $managerBranchDisplay
        : ($managerBrandDisplay !== '' ? $managerBrandDisplay : $managerBranchDisplay);
    @endphp

    <header class="gs-header">
        <div class="gs-container header-inner {{ $userRole === 'quan_ly_nha_hang' ? 'header-inner--manager' : '' }}">
            <!-- LOGO BRAND -->
            <a href="{{ url('/') }}" class="gs-brand" title="Về trang chủ Golden Shift">
                <div class="brand-logo-icon">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-title">GOLDEN SHIFT</span>
                    <span class="brand-subtitle">F&B RECRUITMENT & SHIFTS</span>
                </div>
            </a>

            <!-- NAVIGATION PHÂN THEO VAI TRÒ (Role-Based Navbar) -->
            <nav class="gs-nav {{ $userRole === 'quan_ly_nha_hang' ? 'gs-nav--manager' : '' }}">
                @if (!$isLoggedIn || $userRole === 'ung_vien')
                <!-- TRANG CHỦ DÀNH CHO KHÁCH VÀ ỨNG VIÊN -->
                <a href="{{ url('/') }}" class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Trang Chủ
                </a>
                @endif

                @if (!$isLoggedIn)
                <!-- KHÁCH VÃNG LAI (CHƯA ĐĂNG NHẬP) -->
                <a href="{{ url('/ca-lam-viec') }}" class="nav-item {{ request()->is('ca-lam-viec*') || request()->is('tim-kiem*') ? 'active' : '' }}">
                    <i class="fa-solid fa-magnifying-glass"></i> Tìm Ca Làm Việc
                </a>

                @elseif ($userRole === 'ung_vien')
                <!-- 1. DÀNH CHO ỨNG VIÊN -->
                <a href="{{ url('/ca-lam-viec') }}" class="nav-item {{ (request()->is('ca-lam-viec*') || request()->is('tim-kiem*')) && !request()->is('quan-ly/ca-lam-viec*') ? 'active' : '' }}">
                    <i class="fa-solid fa-magnifying-glass"></i> Tìm Ca Làm
                </a>
                <a href="{{ url('/ung-tuyen/da-nop') }}" class="nav-item {{ request()->is('ung-tuyen/da-nop*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-check"></i> Ca Ứng Tuyển
                </a>
                <a href="{{ url('/ung-vien/so-thu-nhap') }}" class="nav-item {{ request()->is('ung-vien/so-thu-nhap*') || request()->is('so-thu-nhap*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-bookmark"></i> Sổ Thu Nhập
                </a>

                @elseif ($userRole === 'quan_ly_nha_hang')
                <!-- 2. DÀNH CHO QUẢN LÝ NHÀ HÀNG (ĐÃ BỎ TRANG CHỦ THEO YÊU CẦU) -->
                <a href="{{ url('/quan-ly/ca-lam-viec') }}" class="nav-item {{ request()->is('quan-ly/ca-lam-viec*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i> Quản Lý Ca Làm
                </a>
                <a href="{{ url('/quan-ly/cham-cong') }}" class="nav-item {{ request()->is('quan-ly/cham-cong*') || request()->is('cham-cong-thu-nhap*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-clock"></i> Chấm Công & Thu Nhập
                </a>
                <a href="{{ route('quan_ly.thong_ke.index') }}" class="nav-item {{ request()->is('quan-ly/thong-ke*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-column"></i> Thống Kê
                </a>

                @elseif ($userRole === 'quan_tri_vien')
                <!-- 3. DÀNH CHO QUẢN TRỊ VIÊN (ADMIN) (ĐÃ BỎ TRANG CHỦ THEO YÊU CẦU) -->
                <a href="{{ route('admin.categories.index') }}" class="nav-item nav-item--admin {{ request()->is('admin/categories*') || request()->is('admin/thuong-hieu-nha-hang*') || request()->is('admin/nha-hang*') ? 'active' : '' }}">
                    <i class="fa-solid fa-store"></i> Thương Hiệu &amp; Nhà Hàng
                </a>
                <a href="{{ url('/admin/xet-duyet-quan-ly') }}" class="nav-item nav-item--admin {{ request()->is('admin/xet-duyet-quan-ly*') || request()->is('admin/tai-khoan*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear"></i> Tài Khoản
                </a>
                <a href="{{ route('admin.bao_cao.index') }}" class="nav-item nav-item--admin {{ request()->is('admin/bao-cao-vi-pham*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved"></i> Vi Phạm
                </a>
                <a href="{{ route('admin.thong_ke.index') }}" class="nav-item nav-item--admin {{ request()->is('admin/bao-cao-thong-ke*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i> Thống Kê
                </a>
                @endif
            </nav>

            <!-- AUTH & USER ACCOUNT ACTIONS -->
            <div class="header-actions">
                @if ($isLoggedIn)
                <div class="user-profile-menu">
                    <!-- BIỂU TƯỢNG CHUÔNG THÔNG BÁO (IN-APP NOTIFICATIONS) -->
                    <div class="notification-dropdown-wrapper" id="notificationDropdownWrapper">
                        <button type="button" class="btn-notification-bell" id="btnNotificationBell" title="Thông báo hệ thống" aria-label="Xem thông báo" aria-expanded="false">
                            <i class="fa-solid fa-bell"></i>
                            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                        </button>

                        <div class="notification-dropdown-menu" id="notificationDropdownMenu">
                            <div class="notification-dropdown-header">
                                <div class="notification-header-title">
                                    <i class="fa-solid fa-bell text-gold"></i>
                                    <span>Thông Báo</span>
                                    <span class="notification-unread-count" id="notificationUnreadCount"></span>
                                </div>
                                <button type="button" class="btn-mark-all-read" id="btnMarkAllRead" title="Đánh dấu tất cả là đã đọc">
                                    <i class="fa-solid fa-check-double"></i> Đã đọc tất cả
                                </button>
                            </div>
                            <div class="notification-dropdown-body" id="notificationDropdownBody">
                                <div class="notification-loading">
                                    <i class="fa-solid fa-spinner fa-spin text-gold"></i> Đang tải thông báo...
                                </div>
                            </div>
                            <div class="notification-dropdown-footer">
                                <span>Hệ thống thông báo nội bộ Golden Shift</span>
                            </div>
                        </div>
                    </div>

                    @if ($userRole === 'quan_ly_nha_hang')
                    <!-- THÔNG TIN QUẢN LÝ NHÀ HÀNG -->
                    <a href="{{ url('/thong-tin-ca-nhan') }}" class="manager-header-info-card" title="Xem Thông tin cá nhân & Quản lý chi nhánh">
                        <div class="avatar-circle-gold">
                            @if (!empty($avatarUrl) && file_exists(public_path($avatarUrl)))
                            <img src="{{ asset($avatarUrl) }}" alt="{{ $hoTen }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                            <i class="fa-solid fa-user-tie"></i>
                            @endif
                        </div>
                        <div class="manager-text-block">
                            <div class="manager-top-line">
                                <span class="manager-name font-bold" title="{{ $hoTen }}">{{ $hoTen }}</span>
                                <span class="role-pill-gold">Quản lý</span>
                            </div>
                            <div class="manager-bottom-line">
                                <i class="fa-solid fa-store text-gold"></i>
                                <span class="manager-store-detail" title="{{ $managerStoreFullTitle }}">
                                    @if ($managerBrandDisplay !== '' && $managerBranchDisplay !== '')
                                    <strong>{{ $managerBrandDisplay }}</strong> - {{ $managerBranchDisplay }}
                                    @elseif ($managerBrandDisplay !== '')
                                    <strong>{{ $managerBrandDisplay }}</strong>
                                    @else
                                    {{ $managerBranchDisplay }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </a>
                    <a href="{{ url('/thong-tin-ca-nhan') }}" class="btn btn-outline btn-sm" title="Thông tin cá nhân">
                        <i class="fa-solid fa-user-gear"></i> <span class="hide-mobile">Hồ sơ</span>
                    </a>
                    @elseif ($userRole === 'ung_vien')
                    <!-- THÔNG TIN ỨNG VIÊN -->
                    <a href="{{ url('/ho-so-ca-nhan') }}" class="user-badge-header" title="Xem Hồ sơ cá nhân">
                        <div class="avatar-circle-gold">
                            @if (!empty($avatarUrl) && file_exists(public_path($avatarUrl)))
                            <img src="{{ asset($avatarUrl) }}" alt="{{ $hoTen }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                            <i class="fa-solid fa-user"></i>
                            @endif
                        </div>
                        <div class="user-meta-info">
                            <span class="user-name-text">{{ $hoTen }}</span>
                            <span class="user-role-badge role-ung_vien">Ứng Viên</span>
                        </div>
                    </a>
                    <a href="{{ url('/ho-so-ca-nhan') }}" class="btn btn-outline btn-sm" title="Hồ sơ cá nhân">
                        <i class="fa-solid fa-id-badge"></i> <span class="hide-mobile">Hồ sơ</span>
                    </a>
                    @else
                    <!-- THÔNG TIN ADMIN -->
                    <a href="{{ url('/ho-so-ca-nhan') }}" class="user-badge-header" title="Hồ sơ Quản Trị Viên">
                        <div class="avatar-circle-gold">
                            @if (!empty($avatarUrl) && file_exists(public_path($avatarUrl)))
                            <img src="{{ asset($avatarUrl) }}" alt="{{ $hoTen }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                            <i class="fa-solid fa-shield-halved"></i>
                            @endif
                        </div>
                        <div class="user-meta-info">
                            <span class="user-name-text">{{ $hoTen }}</span>
                            <span class="user-role-badge role-quan_tri_vien">Quản Trị Viên</span>
                        </div>
                    </a>
                    <a href="{{ url('/ho-so-ca-nhan') }}" class="btn btn-outline btn-sm" title="Hồ sơ cá nhân">
                        <i class="fa-solid fa-user-gear"></i> <span class="hide-mobile">Hồ sơ</span>
                    </a>
                    @endif

                    <!-- NÚT ĐĂNG XUẤT -->
                    <a href="{{ url('/dang-xuat') }}" class="btn btn-outline-danger btn-sm" title="Đăng xuất khỏi hệ thống">
                        <i class="fa-solid fa-right-from-bracket"></i> <span class="hide-mobile">Đăng xuất</span>
                    </a>
                </div>
                @else
                <!-- CHƯA ĐĂNG NHẬP -->
                <div class="auth-btn-group">
                    <a href="{{ url('/dang-nhap') }}" class="btn btn-outline">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Đăng Nhập
                    </a>
                    <a href="{{ url('/dang-ky-ung-vien') }}" class="btn btn-gold">
                        <i class="fa-solid fa-user-plus"></i> Đăng Ký
                    </a>
                </div>
                @endif

                <!-- NÚT BẬT MENU MOBILE (HAMBURGER) -->
                <button type="button" class="btn-mobile-nav-toggle" id="btnMobileNavToggle" aria-label="Menu điều hướng" aria-expanded="false" title="Menu điều hướng">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <main class="gs-main">
        @yield('content')
    </main>

    <footer class="gs-footer">
        <div class="gs-container">
            <div class="footer-top">
                <div class="footer-col brand-col">
                    <div class="footer-logo">
                        <div class="brand-logo-icon mini">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <span>GOLDEN SHIFT</span>
                    </div>
                    <p class="footer-desc">
                        Hệ thống thông minh quản lý ca làm việc và tuyển dụng F&B chuỗi nhà hàng cao cấp. Kết nối cơ hội việc làm linh hoạt, xét duyệt quản lý chi nhánh minh bạch và quản lý nhân sự chuyên nghiệp.
                    </p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Tiktok"><i class="fa-brands fa-tiktok"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4 class="col-title">Chức Năng Hệ Thống</h4>
                    <ul class="footer-links">
                        <li><a href="{{ url('/ca-lam-viec') }}"><i class="fa-solid fa-chevron-right"></i> Tìm kiếm ca làm việc</a></li>
                        <li><a href="{{ url('/dang-ky-ung-vien') }}"><i class="fa-solid fa-chevron-right"></i> Đăng ký tài khoản Ứng viên</a></li>
                        <li><a href="{{ url('/dang-ky-quan-ly') }}"><i class="fa-solid fa-chevron-right"></i> Đăng ký Quản lý Nhà hàng</a></li>
                        <li><a href="{{ url('/dang-nhap') }}"><i class="fa-solid fa-chevron-right"></i> Đăng nhập hệ thống</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="col-title">Thương Hiệu Trực Thuộc</h4>
                    <ul class="footer-links">
                        <li><a href="{{ url('/ca-lam-viec?thuong_hieu=golden-hotpot') }}"><i class="fa-solid fa-chevron-right"></i> Golden Hotpot Buffet</a></li>
                        <li><a href="{{ url('/ca-lam-viec?thuong_hieu=golden-bbq') }}"><i class="fa-solid fa-chevron-right"></i> Golden BBQ & Grill</a></li>
                        <li><a href="{{ url('/ca-lam-viec?thuong_hieu=golden-dimsum') }}"><i class="fa-solid fa-chevron-right"></i> Golden Dimsum House</a></li>
                        <li><a href="{{ url('/ca-lam-viec?thuong_hieu=golden-bistro') }}"><i class="fa-solid fa-chevron-right"></i> Golden Bistro & Lounge</a></li>
                    </ul>
                </div>

                <div class="footer-col contact-col">
                    <h4 class="col-title">Ban Điều Hành Golden Shift</h4>
                    <p><i class="fa-solid fa-location-dot"></i> Tầng 12, Golden Stars Tower, Quận 1, TP. HCM</p>
                    <p><i class="fa-solid fa-phone"></i> Hotline: 1900 8899 - (028) 3822 1199</p>
                    <p><i class="fa-solid fa-envelope"></i> contact@goldenshift.vn</p>
                    <div class="db-status-badge">
                        <span class="status-indicator"></span> Hệ Thống Tuyển Dụng F&B Golden Stars
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Golden Shift F&B Management System. Tất cả quyền được bảo lưu.</p>
                <div class="bottom-links">
                    <a href="#">Quy định Xét duyệt Quản lý</a>
                    <span>•</span>
                    <a href="#">Chính sách Chấm công</a>
                    <span>•</span>
                    <a href="#">Bảo mật Dữ liệu</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Main JavaScript -->
    <script src="{{ asset('assets/js/main.js') }}?v={{ @filemtime(public_path('assets/js/main.js')) ?: time() }}"></script>
    @yield('scripts')
    @stack('scripts')

    @if($isLoggedIn)
    {{-- ================================================================
         GOLDEN SHIFT - HỆ THỐNG THÔNG BÁO IN-APP ĐỒNG BỘ TOÀN HỆ THỐNG
         - Đồng bộ thống nhất 1 giao diện duy nhất (White & Gold Luxury theme)
         - Tự động polling cập nhật chuông thông báo mỗi 5 giây
         - Hỗ trợ Toast Popup khi có thông báo mới
         - Áp dụng chuẩn xác cho cả 3 phân hệ: Quản lý, Ứng viên, Quản trị viên
    ================================================================ --}}
    <script>
        (function() {
            'use strict';

            /* ---- Cấu hình ---- */
            const NOTIF_POLL_MS = 5000; // 5 giây / lần polling
            const TOAST_TTL_MS = 5000; // Toast hiển thị trong 5 giây

            /* ---- State nội bộ ---- */
            let _lastNotifCount = -1; // -1 = chưa tải lần đầu
            let _notifOpen = false; // Trạng thái mở dropdown

            /* ---- DOM refs ---- */
            const wrapper = document.getElementById('notificationDropdownWrapper');
            const bellBtn = document.getElementById('btnNotificationBell');
            const menu = document.getElementById('notificationDropdownMenu');
            const badge = document.getElementById('notificationBadge');
            const unreadEl = document.getElementById('notificationUnreadCount');
            const bodyEl = document.getElementById('notificationDropdownBody');
            const markAllBtn = document.getElementById('btnMarkAllRead');

            if (!wrapper || !bellBtn || !menu) return;

            /* ---- Tiện ích Escape HTML chống XSS ---- */
            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            /* ================================================================
               TOAST UTILITY: THÔNG BÁO NỔI GÓC PHẢI
            ================================================================ */
            function showToast(msg, type = 'info') {
                let container = document.getElementById('gsGlobalToastContainer');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'gsGlobalToastContainer';
                    container.style.cssText = 'position:fixed;top:76px;right:20px;z-index:999999;display:flex;flex-direction:column;gap:8px;max-width:360px;width:100%;pointer-events:none;';
                    document.body.appendChild(container);
                }

                const colors = {
                    info: {
                        bg: '#1e293b',
                        border: '#f59e0b',
                        icon: 'fa-bell text-gold'
                    },
                    success: {
                        bg: '#065f46',
                        border: '#10b981',
                        icon: 'fa-circle-check text-success'
                    },
                    warning: {
                        bg: '#78350f',
                        border: '#f59e0b',
                        icon: 'fa-triangle-exclamation text-warning'
                    },
                    error: {
                        bg: '#991b1b',
                        border: '#ef4444',
                        icon: 'fa-circle-xmark text-danger'
                    },
                };
                const c = colors[type] || colors.info;

                const toast = document.createElement('div');
                toast.style.cssText = `background:${c.bg};color:#fff;border-left:4px solid ${c.border};border-radius:10px;padding:12px 16px;font-size:0.875rem;font-weight:500;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);pointer-events:auto;opacity:0;transition:opacity .3s;`;
                toast.innerHTML = `<i class="fa-solid ${c.icon}" style="font-size:1.1rem;flex-shrink:0;"></i><span>${msg}</span>`;
                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.style.opacity = '1';
                });
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 400);
                }, TOAST_TTL_MS);
            }

            /* ================================================================
               RENDER DANH SÁCH THÔNG BÁO (CHUẨN GIAO DIỆN LUXURY GOLDEN SHIFT)
            ================================================================ */
            function renderNotifList(list) {
                if (!bodyEl) return;
                if (!list || list.length === 0) {
                    bodyEl.innerHTML = `
                    <div class="notification-empty">
                        <i class="fa-regular fa-bell-slash"></i>
                        <p>Bạn chưa có thông báo nào.</p>
                    </div>
                `;
                    return;
                }

                let html = '';
                list.forEach(item => {
                    const isUnread = !item.da_doc;
                    const link = item.duong_dan_lien_ket ? `/thong-bao/${item.ma_thong_bao}/doc` : '#';
                    html += `
                    <a href="${link}" class="notification-item ${isUnread ? 'unread' : ''}">
                        <div class="notification-item-top">
                            <div class="notification-item-title">${escapeHtml(item.tieu_de)}</div>
                            ${isUnread ? '<span class="notification-unread-dot" title="Chưa đọc"></span>' : ''}
                        </div>
                        <div class="notification-item-desc">${escapeHtml(item.noi_dung)}</div>
                        <div class="notification-item-bottom">
                            <span class="notification-item-time" title="${item.created_at_full || ''}">
                                <i class="fa-regular fa-clock"></i> ${escapeHtml(item.created_at || 'Vừa xong')}
                            </span>
                            <span class="notification-item-status">${isUnread ? 'Chưa đọc' : 'Đã đọc'}</span>
                        </div>
                    </a>
                `;
                });
                bodyEl.innerHTML = html;
            }

            /* ================================================================
               CẬP NHẬT BADGE & UNREAD COUNT
            ================================================================ */
            function updateBadge(count) {
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = 'inline-flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }
                if (unreadEl) {
                    unreadEl.textContent = count > 0 ? `(${count} mới)` : '';
                }
            }

            /* ================================================================
               FETCH VÀ ĐỒNG BỘ DỮ LIỆU
            ================================================================ */
            function fetchNotifications(isPoll = false) {
                fetch('/thong-bao/danh-sach', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(r => r.ok ? r.json() : null)
                    .then(data => {
                        if (!data || !data.success) return;
                        const count = parseInt(data.chua_doc) || 0;

                        // Nếu số thông báo tăng lên (và không phải lần đầu tải): toast
                        if (_lastNotifCount >= 0 && count > _lastNotifCount) {
                            const diff = count - _lastNotifCount;
                            showToast(`🔔 Bạn có <strong>${diff}</strong> thông báo mới!`, 'info');
                        }
                        _lastNotifCount = count;

                        updateBadge(count);

                        // Cập nhật danh sách nếu dropdown đang mở hoặc lần đầu tải khi mở
                        if (_notifOpen || !isPoll) {
                            renderNotifList(data.danh_sach);
                        }
                    })
                    .catch(() => {});
            }

            /* ================================================================
               ĐÓNG / MỞ DROPDOWN (ANIMATION & STATE)
            ================================================================ */
            function openDropdown() {
                _notifOpen = true;
                menu.classList.add('show');
                bellBtn.classList.add('active');
                bellBtn.setAttribute('aria-expanded', 'true');
                fetchNotifications(false);
            }

            function closeDropdown() {
                _notifOpen = false;
                menu.classList.remove('show');
                bellBtn.classList.remove('active');
                bellBtn.setAttribute('aria-expanded', 'false');
            }

            function toggleDropdown(e) {
                e.stopPropagation();
                if (_notifOpen) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            }

            bellBtn.addEventListener('click', toggleDropdown);

            // Đóng dropdown khi click ra ngoài
            document.addEventListener('click', function(e) {
                if (_notifOpen && !wrapper.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Đóng dropdown khi nhấn phím Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && _notifOpen) {
                    closeDropdown();
                }
            });

            /* ================================================================
               ĐÁNH DẤU TẤT CẢ ĐÃ ĐỌC (AJAX)
            ================================================================ */
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fetch('/thong-bao/doc-tat-ca', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        })
                        .then(r => r.ok ? r.json() : null)
                        .then(data => {
                            if (data && data.success) {
                                _lastNotifCount = 0;
                                updateBadge(0);
                                document.querySelectorAll('.notification-item.unread').forEach(item => {
                                    item.classList.remove('unread');
                                    const dot = item.querySelector('.notification-unread-dot');
                                    if (dot) dot.remove();
                                    const status = item.querySelector('.notification-item-status');
                                    if (status) status.textContent = 'Đã đọc';
                                });
                            }
                        })
                        .catch(err => console.error('Lỗi khi đánh dấu đã đọc:', err));
                });
            }

            /* ================================================================
               KHỞI CHẠY LẦN ĐẦU & POLLING ĐỊNH KỲ (mỗi 5 giây)
            ================================================================ */
            fetchNotifications(true); // Lấy số lượng ban đầu khi tải trang
            setInterval(() => fetchNotifications(true), NOTIF_POLL_MS);

        })();
    </script>
    @endif

    {{-- GIAI ĐOẠN 5: MODAL ĐÁNH GIÁ 2 CHIỀU & BÁO CÁO VI PHẠM --}}
    @if ($isLoggedIn)
        @include('components.modal_danh_gia')
        @include('components.modal_bao_cao')
        @if ($userRole === 'quan_ly_nha_hang')
            @include('components.modal_qr_cham_cong')
        @endif
    @endif
</body>

</html>