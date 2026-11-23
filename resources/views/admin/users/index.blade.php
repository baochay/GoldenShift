@extends('layouts.app')

@section('title', 'Quản Lý Tài Khoản Người Dùng - Golden Shift Admin')

@section('styles')
<style>
    .admin-tabs {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 12px;
    }
    .admin-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .admin-tab-btn:hover {
        color: #d97706;
        background: #fef3c7;
        border-color: #fde68a;
    }
    .admin-tab-btn.active {
        color: #b45309;
        background: #fffbeb;
        border-color: #f59e0b;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
    }
    .tab-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.75rem;
        background: #e2e8f0;
        color: #475569;
    }
    .admin-tab-btn.active .tab-badge {
        background: #f59e0b;
        color: #ffffff;
    }
    .tab-badge-pending {
        background: #ef4444;
        color: #ffffff;
        font-size: 0.72rem;
        padding: 2px 7px;
        border-radius: 9999px;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .status-badge.cho_duyet {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .status-badge.da_duyet, .status-badge.hoat_dong {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .status-badge.tam_khoa {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .status-badge.tu_choi {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
    }
    .action-btn-group {
        display: flex;
        gap: 6px;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn-action-duyet {
        background: #10b981;
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-action-duyet:hover {
        background: #059669;
    }
    .btn-action-tuchoi {
        background: #f1f5f9;
        color: #e11d48;
        border: 1px solid #fecdd3;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-action-tuchoi:hover {
        background: #ffe4e6;
    }
    .btn-action-lock {
        background: #fff1f2;
        color: #be123c;
        border: 1px solid #fecdd3;
        padding: 6px 11px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-action-lock:hover {
        background: #e11d48;
        color: #ffffff;
    }
    .btn-action-unlock {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        padding: 6px 11px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-action-unlock:hover {
        background: #059669;
        color: #ffffff;
    }
    .stats-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .admin-stat-card {
        padding: 16px 20px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .admin-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .admin-stat-icon.gold { background: #fef3c7; color: #d97706; }
    .admin-stat-icon.red { background: #fee2e2; color: #ef4444; }
    .admin-stat-icon.green { background: #dcfce7; color: #16a34a; }
    .admin-stat-icon.blue { background: #e0e7ff; color: #4f46e5; }
    .filter-search-bar {
        background: #ffffff;
        padding: 14px 18px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }
    /* Modal Từ chối */
    .gs-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .gs-modal-backdrop.active {
        display: flex;
    }
    .gs-modal-box {
        background: #ffffff;
        border-radius: 16px;
        max-width: 500px;
        width: 100%;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        animation: scaleUp 0.25s ease;
    }
    @keyframes scaleUp {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .gs-modal-header {
        padding: 18px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .gs-modal-body {
        padding: 20px 24px;
    }
    .gs-modal-footer {
        padding: 16px 24px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>
@endsection

@section('content')
<section class="page-header-section">
    <div class="gs-container">
        <div class="breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ url('/dashboard') }}">Quản trị</a>
            <span>/</span>
            <span>Quản lý tài khoản người dùng</span>
        </div>
        <div class="header-job-highlight">
            <span class="sub-badge"><i class="fa-solid fa-shield-halved text-gold"></i> PHÂN HỆ QUẢN TRỊ VIÊN</span>
            <h1 class="page-title">Quản Lý & Phân Quyền Tài Khoản</h1>
            <p class="page-subtitle">Duyệt hồ sơ quản lý nhà hàng mới đăng ký, theo dõi danh sách ứng viên và thực hiện khóa / mở khóa tài khoản.</p>
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
            <div class="alert-box warning" style="margin-bottom: 1.5rem; background: #fffbeb; border: 1px solid #fde68a; color: #b45309; padding: 1rem 1.25rem; border-radius: 10px; display: flex; align-items: center; gap: 0.75rem; font-weight: 500;">
                <i class="fa-solid fa-triangle-exclamation" style="color: #f59e0b; font-size: 1.2rem;"></i> {{ session('warning') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert-box error" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <!-- THỐNG KÊ NHANH -->
        <div class="stats-card-grid">
            <div class="admin-stat-card">
                <div class="admin-stat-icon gold">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Quản Lý Nhà Hàng</span>
                    <h3 style="margin: 0; font-size: 1.5rem; color: #1e293b;">{{ $totalManagers }}</h3>
                </div>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-icon red">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Hồ Sơ Chờ Phê Duyệt</span>
                    <h3 style="margin: 0; font-size: 1.5rem; color: #ef4444;">{{ $pendingCount }}</h3>
                </div>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Ứng Viên Tìm Việc</span>
                    <h3 style="margin: 0; font-size: 1.5rem; color: #1e293b;">{{ $totalCandidates }}</h3>
                </div>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-icon green">
                    <i class="fa-solid fa-shield-check"></i>
                </div>
                <div>
                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Tổng Người Dùng</span>
                    <h3 style="margin: 0; font-size: 1.5rem; color: #1e293b;">{{ $totalNonAdminUsers ?? ($totalManagers + $totalCandidates) }}</h3>
                </div>
            </div>
        </div>

        <!-- TAB CHUYỂN ĐỔI: QUẢN LÝ NHÀ HÀNG / ỨNG VIÊN -->
        <div class="admin-tabs">
            <a href="{{ url('/admin/tai-khoan?tab=quan_ly') }}" class="admin-tab-btn {{ $currentTab === 'quan_ly' ? 'active' : '' }}">
                <i class="fa-solid fa-user-tie"></i>
                <span>Quản Lý Nhà Hàng</span>
                <span class="tab-badge">{{ $totalManagers }}</span>
                @if ($pendingCount > 0)
                    <span class="tab-badge-pending" title="Hồ sơ cần duyệt">{{ $pendingCount }} chờ duyệt</span>
                @endif
            </a>

            <a href="{{ url('/admin/tai-khoan?tab=ung_vien') }}" class="admin-tab-btn {{ $currentTab === 'ung_vien' ? 'active' : '' }}">
                <i class="fa-solid fa-user-graduate"></i>
                <span>Ứng Viên Tuyển Dụng</span>
                <span class="tab-badge">{{ $totalCandidates }}</span>
            </a>
        </div>

        <!-- BỘ LỌC VÀ TÌM KIẾM -->
        <form action="{{ url('/admin/tai-khoan') }}" method="GET" class="filter-search-bar">
            <input type="hidden" name="tab" value="{{ $currentTab }}">
            <div style="flex: 2; min-width: 240px; position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 11px; color: #94a3b8;"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo họ tên, email hoặc số điện thoại..." class="gs-input" style="width: 100%; padding-left: 36px; padding-top: 8px; padding-bottom: 8px; border-radius: 8px; border: 1px solid #cbd5e1;">
            </div>

            <div style="flex: 1; min-width: 180px;">
                <select name="status" class="gs-select" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1;">
                    <option value="">-- Tất cả trạng thái --</option>
                    @if ($currentTab === 'quan_ly')
                        <option value="cho_duyet" {{ $statusFilter === 'cho_duyet' ? 'selected' : '' }}>Chờ duyệt (Chưa kích hoạt)</option>
                        <option value="da_duyet" {{ $statusFilter === 'da_duyet' ? 'selected' : '' }}>Đã duyệt (Đang hoạt động)</option>
                    @else
                        <option value="hoat_dong" {{ $statusFilter === 'hoat_dong' ? 'selected' : '' }}>Đang hoạt động</option>
                    @endif
                    <option value="tam_khoa" {{ $statusFilter === 'tam_khoa' ? 'selected' : '' }}>Tạm khóa (Bị chặn)</option>
                    @if ($currentTab === 'quan_ly')
                        <option value="tu_choi" {{ $statusFilter === 'tu_choi' ? 'selected' : '' }}>Đã từ chối</option>
                    @endif
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-gold btn-sm" style="padding: 8px 16px;">
                    <i class="fa-solid fa-filter"></i> Lọc
                </button>
                @if (!empty($search) || !empty($statusFilter))
                    <a href="{{ url('/admin/tai-khoan?tab=' . $currentTab) }}" class="btn btn-outline btn-sm" style="padding: 8px 12px;">
                        <i class="fa-solid fa-rotate-left"></i> Xóa lọc
                    </a>
                @endif
            </div>
        </form>

        <!-- NỘI DUNG THEO TAB -->
        <div class="dashboard-table-card glass-panel" style="background:#ffffff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden;">
            @if ($currentTab === 'quan_ly')
                <!-- ================== TAB 1: QUẢN LÝ NHÀ HÀNG ================== -->
                <div class="card-table-header" style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 class="table-card-title" style="margin: 0; font-size: 1.15rem; color: #1e293b;">
                            <i class="fa-solid fa-user-tie text-gold"></i> Danh Sách Quản Lý Nhà Hàng
                        </h3>
                        <p class="table-card-sub" style="margin: 4px 0 0; color: #64748b; font-size: 0.85rem;">
                            Duyệt hồ sơ quản lý mới đăng ký hoặc thực hiện khóa / mở khóa tài khoản
                        </p>
                    </div>
                    <span class="tag-gold-outline"><i class="fa-solid fa-shield"></i> Quyền Admin</span>
                </div>

                @if ($managers->isEmpty())
                    <div class="empty-state-box" style="padding: 50px 20px; text-align: center;">
                        <i class="fa-solid fa-user-slash empty-icon" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 12px;"></i>
                        <h3 style="color: #475569;">Không tìm thấy tài khoản Quản lý nào</h3>
                        <p style="color: #94a3b8;">Không có dữ liệu phù hợp với điều kiện tìm kiếm hiện tại.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="gs-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8fafc; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; color: #475569;">
                                    <th style="padding: 12px 16px;">Mã Quản Lý</th>
                                    <th style="padding: 12px 16px;">Họ Và Tên</th>
                                    <th style="padding: 12px 16px;">Thông Tin Liên Hệ</th>
                                    <th style="padding: 12px 16px;">Chi Nhánh Nhà Hàng</th>
                                    <th style="padding: 12px 16px;">Ngày Đăng Ký</th>
                                    <th style="padding: 12px 16px; text-align: center;">Trạng Thái</th>
                                    <th style="padding: 12px 16px; text-align: center; min-width: 180px;">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($managers as $mgr)
                                    @php
                                        $mId = $mgr->ma_nguoi_dung;
                                        $st = $mgr->trang_thai_tai_khoan ?? $mgr->trang_thai ?? '';
                                        $mgrBranch = $mgr->nhaHang ?? ($mgr->nhaHangTrucThuoc ?? null);
                                        $restName = $mgrBranch->ten_nha_hang ?? 'Chưa phân công';
                                        $brandName = $mgrBranch->thuongHieu->ten_thuong_hieu ?? 'Golden Shift';
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.9rem;">
                                        <td style="padding: 14px 16px; font-weight: 600; color: #334155;">
                                            #QL-{{ $mId }}
                                        </td>
                                        <td style="padding: 14px 16px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="width: 38px; height: 38px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                                    {{ mb_substr($mgr->ho_ten, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div style="font-weight: 700; color: #1e293b;">{{ $mgr->ho_ten }}</div>
                                                    <small style="color: #64748b;">Quản lý chi nhánh</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 14px 16px;">
                                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                                <span style="color: #334155; font-weight: 500;">
                                                    <i class="fa-solid fa-envelope text-gold" style="font-size: 0.8rem; width: 16px;"></i> {{ $mgr->email }}
                                                </span>
                                                <span style="color: #64748b; font-size: 0.82rem;">
                                                    <i class="fa-solid fa-phone text-gold" style="font-size: 0.8rem; width: 16px;"></i> {{ $mgr->so_dien_thoai }}
                                                </span>
                                            </div>
                                        </td>
                                        <td style="padding: 14px 16px;">
                                            <div style="font-weight: 600; color: #1e293b;">{{ $restName }}</div>
                                            <small style="color: #d97706; font-weight: 500;">{{ $brandName }}</small>
                                        </td>
                                        <td style="padding: 14px 16px; color: #64748b; font-size: 0.85rem;">
                                            {{ date('d/m/Y H:i', strtotime($mgr->ngay_tao ?? now())) }}
                                        </td>
                                        <td style="padding: 14px 16px; text-align: center;">
                                            @if ($st === 'cho_duyet')
                                                <span class="status-badge cho_duyet">
                                                    <i class="fa-solid fa-clock"></i> Chờ duyệt
                                                </span>
                                            @elseif ($st === 'da_duyet' || $st === 'hoat_dong')
                                                <span class="status-badge da_duyet">
                                                    <i class="fa-solid fa-circle-check"></i> Đã duyệt
                                                </span>
                                            @elseif ($st === 'tam_khoa')
                                                <span class="status-badge tam_khoa">
                                                    <i class="fa-solid fa-lock"></i> Đã khóa
                                                </span>
                                            @elseif ($st === 'tu_choi' || $st === 'da_tu_choi')
                                                <span class="status-badge tu_choi" title="{{ $mgr->ly_do_tu_choi ?: 'Hồ sơ không đạt yêu cầu' }}">
                                                    <i class="fa-solid fa-ban"></i> Đã từ chối
                                                </span>
                                                @if (!empty($mgr->ly_do_tu_choi))
                                                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px; max-width: 160px; margin-left: auto; margin-right: auto; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="{{ $mgr->ly_do_tu_choi }}">
                                                        {{ $mgr->ly_do_tu_choi }}
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td style="padding: 14px 16px; text-align: center;">
                                            <div class="action-btn-group">
                                                @if ($st === 'tu_choi' || $st === 'da_tu_choi')
                                                    {{-- Tài khoản đã bị từ chối: ẩn hoàn toàn tất cả nút thao tác --}}
                                                    <span style="color: #cbd5e1; font-size: 0.85rem;">—</span>
                                                @elseif ($st === 'cho_duyet')
                                                    <!-- NÚT DUYỆT TÀI KHOẢN -->
                                                    <form action="{{ route('admin.users.duyet', $mId) }}" method="POST" onsubmit="return confirm('Xác nhận PHÊ DUYỆT tài khoản quản lý [{{ addslashes($mgr->ho_ten) }}]?');" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn-action-duyet" title="Phê duyệt tài khoản">
                                                            <i class="fa-solid fa-check"></i> Duyệt
                                                        </button>
                                                    </form>

                                                    <!-- NÚT TỪ CHỐI TÀI KHOẢN -->
                                                    <button type="button" class="btn-action-tuchoi btn-open-reject-modal" data-id="{{ $mId }}" data-name="{{ $mgr->ho_ten }}" title="Từ chối hồ sơ">
                                                        <i class="fa-solid fa-xmark"></i> Từ chối
                                                    </button>
                                                @elseif ($st === 'tam_khoa')
                                                    <!-- NÚT MỞ KHÓA TÀI KHOẢN -->
                                                    <form action="{{ route('admin.users.toggle_lock', $mId) }}" method="POST" onsubmit="return confirm('MỞ KHÓA tài khoản quản lý [{{ addslashes($mgr->ho_ten) }}]?');" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn-action-unlock" title="Mở khóa tài khoản">
                                                            <i class="fa-solid fa-lock-open"></i> Mở khóa
                                                        </button>
                                                    </form>
                                                @elseif ($st === 'da_duyet' || $st === 'hoat_dong')
                                                    <!-- NÚT KHÓA TÀI KHOẢN (Chỉ hiển thị cho tài khoản ĐÃ DUYỆT / ĐANG HOẠT ĐỘNG) -->
                                                    <form action="{{ route('admin.users.toggle_lock', $mId) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn KHÓA tài khoản quản lý [{{ addslashes($mgr->ho_ten) }}]? Tài khoản sẽ không thể đăng nhập.');" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn-action-lock" title="Khóa tài khoản">
                                                            <i class="fa-solid fa-lock"></i> Khóa
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Các trạng thái khác: ẩn hoàn toàn thao tác --}}
                                                    <span style="color: #cbd5e1; font-size: 0.85rem;">—</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (method_exists($managers, 'hasPages') && $managers->hasPages())
                        <div class="pagination-wrapper" style="padding: 0 20px 16px;">
                            {{ $managers->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @endif

            @else
                <!-- ================== TAB 2: ỨNG VIÊN ================== -->
                <div class="card-table-header" style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 class="table-card-title" style="margin: 0; font-size: 1.15rem; color: #1e293b;">
                            <i class="fa-solid fa-user-graduate text-gold"></i> Danh Sách Ứng Viên Tìm Việc
                        </h3>
                        <p class="table-card-sub" style="margin: 4px 0 0; color: #64748b; font-size: 0.85rem;">
                            Theo dõi tình trạng hoạt động và quản lý khóa / mở khóa tài khoản ứng viên
                        </p>
                    </div>
                    <span class="tag-gold-outline"><i class="fa-solid fa-shield"></i> Quyền Admin</span>
                </div>

                @if ($candidates->isEmpty())
                    <div class="empty-state-box" style="padding: 50px 20px; text-align: center;">
                        <i class="fa-solid fa-user-slash empty-icon" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 12px;"></i>
                        <h3 style="color: #475569;">Không tìm thấy ứng viên nào</h3>
                        <p style="color: #94a3b8;">Không có dữ liệu phù hợp với điều kiện tìm kiếm.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="gs-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8fafc; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; color: #475569;">
                                    <th style="padding: 12px 16px;">Mã Ứng Viên</th>
                                    <th style="padding: 12px 16px;">Họ Và Tên</th>
                                    <th style="padding: 12px 16px;">Thông Tin Liên Hệ</th>
                                    <th style="padding: 12px 16px;">Giới Thiệu / Hồ Sơ</th>
                                    <th style="padding: 12px 16px;">Ngày Tham Gia</th>
                                    <th style="padding: 12px 16px; text-align: center;">Trạng Thái</th>
                                    <th style="padding: 12px 16px; text-align: center; min-width: 140px;">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($candidates as $cand)
                                    @php
                                        $cId = $cand->ma_nguoi_dung;
                                        $st = $cand->trang_thai;
                                        $bio = $cand->hoSoUngVien->gioi_thieu_ban_than ?? 'Chưa cập nhật phần giới thiệu';
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.9rem;">
                                        <td style="padding: 14px 16px; font-weight: 600; color: #334155;">
                                            #UV-{{ $cId }}
                                        </td>
                                        <td style="padding: 14px 16px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="width: 38px; height: 38px; border-radius: 50%; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                                    {{ mb_substr($cand->ho_ten, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div style="font-weight: 700; color: #1e293b;">{{ $cand->ho_ten }}</div>
                                                    <small style="color: #64748b;">Ứng viên F&amp;B</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 14px 16px;">
                                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                                <span style="color: #334155; font-weight: 500;">
                                                    <i class="fa-solid fa-envelope text-gold" style="font-size: 0.8rem; width: 16px;"></i> {{ $cand->email }}
                                                </span>
                                                <span style="color: #64748b; font-size: 0.82rem;">
                                                    <i class="fa-solid fa-phone text-gold" style="font-size: 0.8rem; width: 16px;"></i> {{ $cand->so_dien_thoai }}
                                                </span>
                                            </div>
                                        </td>
                                        <td style="padding: 14px 16px; max-width: 280px;">
                                            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #475569;" title="{{ $bio }}">
                                                {{ $bio }}
                                            </div>
                                        </td>
                                        <td style="padding: 14px 16px; color: #64748b; font-size: 0.85rem;">
                                            {{ date('d/m/Y', strtotime($cand->ngay_tao ?? now())) }}
                                        </td>
                                        <td style="padding: 14px 16px; text-align: center;">
                                            @if ($st === 'tam_khoa')
                                                <span class="status-badge tam_khoa">
                                                    <i class="fa-solid fa-lock"></i> Đã khóa
                                                </span>
                                            @else
                                                <span class="status-badge hoat_dong">
                                                    <i class="fa-solid fa-circle-check"></i> Hoạt động
                                                </span>
                                            @endif
                                        </td>
                                        <td style="padding: 14px 16px; text-align: center;">
                                            <div class="action-btn-group">
                                                @if ($st === 'tam_khoa')
                                                    <!-- MỞ KHÓA ỨNG VIÊN -->
                                                    <form action="{{ route('admin.users.toggle_lock', $cId) }}" method="POST" onsubmit="return confirm('MỞ KHÓA tài khoản ứng viên [{{ addslashes($cand->ho_ten) }}]?');" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn-action-unlock" title="Mở khóa tài khoản">
                                                            <i class="fa-solid fa-lock-open"></i> Mở khóa
                                                        </button>
                                                    </form>
                                                @else
                                                    <!-- KHÓA ỨNG VIÊN -->
                                                    <form action="{{ route('admin.users.toggle_lock', $cId) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn KHÓA tài khoản ứng viên [{{ addslashes($cand->ho_ten) }}]?');" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn-action-lock" title="Khóa tài khoản">
                                                            <i class="fa-solid fa-lock"></i> Khóa
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (method_exists($candidates, 'hasPages') && $candidates->hasPages())
                        <div class="pagination-wrapper" style="padding: 0 20px 16px;">
                            {{ $candidates->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </div>
</section>

<!-- MODAL TỪ CHỐI TÀI KHOẢN QUẢN LÝ -->
<div class="gs-modal-backdrop" id="modalRejectManager">
    <div class="gs-modal-box">
        <form id="formRejectManager" method="POST" action="">
            @csrf
            <div class="gs-modal-header">
                <h3 style="margin: 0; font-size: 1.1rem; color: #be123c; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-xmark"></i> Từ Chối Tài Khoản Quản Lý
                </h3>
                <button type="button" class="btn-close-modal" id="btnCloseRejectModal" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #94a3b8;">&times;</button>
            </div>
            <div class="gs-modal-body">
                <p style="margin-top: 0; color: #475569; font-size: 0.95rem;">
                    Bạn đang thực hiện từ chối hồ sơ đăng ký của: <strong id="rejectTargetName" style="color: #1e293b;"></strong>
                </p>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="lyDoTuChoiInput" style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px;">Lý do từ chối <span style="color: #ef4444;">*</span></label>
                    <textarea name="ly_do_tu_choi" id="lyDoTuChoiInput" rows="3" required placeholder="Ví dụ: Chưa xác minh được chức vụ quản lý tại chi nhánh đăng ký..." class="gs-input" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;"></textarea>
                </div>
            </div>
            <div class="gs-modal-footer">
                <button type="button" class="btn btn-outline" id="btnCancelRejectModal">Hủy bỏ</button>
                <button type="submit" class="btn btn-outline-danger" style="background: #e11d48; color: #ffffff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 600;">
                    <i class="fa-solid fa-ban"></i> Xác Nhận Từ Chối
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalRejectManager');
    const form = document.getElementById('formRejectManager');
    const targetName = document.getElementById('rejectTargetName');
    const btnClose = document.getElementById('btnCloseRejectModal');
    const btnCancel = document.getElementById('btnCancelRejectModal');

    document.querySelectorAll('.btn-open-reject-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            targetName.textContent = name;
            form.action = "{{ url('/admin/tai-khoan') }}/" + id + "/tu-choi";
            modal.classList.add('active');
        });
    });

    function closeModal() {
        modal.classList.remove('active');
    }

    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
});
</script>
@endsection
