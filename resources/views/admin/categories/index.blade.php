@extends('layouts.app')

@section('title', 'Quản Lý Thương Hiệu & Nhà Hàng - Golden Shift Admin')

@section('styles')
<style>
/* ===== CATEGORIES PAGE - White & Gold Admin Theme ===== */
.categories-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0c1a30 100%);
    padding: 2.5rem 0 2rem;
    border-bottom: 3px solid #b8973a;
    position: relative;
    overflow: hidden;
}
.categories-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 80% 50%, rgba(184,151,58,0.08) 0%, transparent 60%);
    pointer-events: none;
}
.hero-inner { display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap; }
.hero-title-block { }
.hero-eyebrow { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.6rem; }
.hero-eyebrow-badge {
    background: rgba(184,151,58,0.18); color: #d4a843; border: 1px solid rgba(184,151,58,0.3);
    padding: 3px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; letter-spacing: 0.5px;
}
.hero-page-title { color: #fff; font-size: 2rem; font-weight: 800; margin: 0 0 0.4rem; line-height: 1.2; }
.hero-page-title span { color: #d4a843; }
.hero-page-desc { color: #94a3b8; font-size: 0.9rem; margin: 0; }

/* ===== STAT PILLS ===== */
.hero-stats { display: flex; gap: 1.2rem; flex-wrap: wrap; }
.stat-pill {
    background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px; padding: 0.85rem 1.2rem; text-align: center; min-width: 110px;
    backdrop-filter: blur(8px);
}
.stat-pill-num { color: #d4a843; font-size: 1.7rem; font-weight: 800; line-height: 1; }
.stat-pill-label { color: #94a3b8; font-size: 0.75rem; margin-top: 4px; }

/* ===== TAB SWITCHER ===== */
.tab-switcher-bar {
    display: flex; background: #f8fafc; border-bottom: 2px solid #e2e8f0;
    padding: 0 1.5rem; overflow-x: auto;
}
.tab-btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 1rem 1.5rem; border: none; background: none; cursor: pointer;
    font-family: inherit; font-size: 0.9rem; font-weight: 600; color: #64748b;
    border-bottom: 3px solid transparent; margin-bottom: -2px; white-space: nowrap;
    transition: all .2s ease;
}
.tab-btn:hover { color: #b8973a; }
.tab-btn.active { color: #b8973a; border-bottom-color: #b8973a; }
.tab-btn .tab-count {
    background: #e2e8f0; color: #64748b; font-size: 0.73rem; font-weight: 700;
    padding: 1px 7px; border-radius: 10px;
}
.tab-btn.active .tab-count { background: rgba(184,151,58,0.15); color: #b8973a; }

/* ===== CONTENT PANE ===== */
.tab-pane { display: none; padding: 1.5rem 0; }
.tab-pane.active { display: block; }

/* ===== TOOLBAR ===== */
.toolbar-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;
}
.toolbar-filters { display: flex; gap: 0.6rem; flex-wrap: wrap; flex: 1; }
.toolbar-filters input, .toolbar-filters select {
    border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.5rem 0.85rem;
    font-family: inherit; font-size: 0.88rem; color: #334155; background: #fff;
    outline: none; transition: border-color .2s;
    min-width: 180px;
}
.toolbar-filters input:focus, .toolbar-filters select:focus { border-color: #b8973a; }
.btn-add-gold {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: linear-gradient(135deg, #b8973a, #d4a843); color: #fff;
    border: none; padding: 0.55rem 1.2rem; border-radius: 8px; font-family: inherit;
    font-size: 0.88rem; font-weight: 700; cursor: pointer; white-space: nowrap;
    transition: opacity .2s, transform .15s; box-shadow: 0 2px 8px rgba(184,151,58,0.3);
}
.btn-add-gold:hover { opacity: 0.9; transform: translateY(-1px); }

/* ===== CARDS THƯƠNG HIỆU ===== */
.brand-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 1.25rem;
}
.brand-card {
    background: #fff; border: 1px solid #e8e4d9; border-radius: 14px;
    padding: 1.4rem; transition: box-shadow .2s, transform .2s;
    position: relative; overflow: hidden;
}
.brand-card:hover { box-shadow: 0 6px 24px rgba(184,151,58,0.15); transform: translateY(-2px); }
.brand-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, #b8973a, #d4a843);
}
.brand-card-top { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; }
.brand-icon-box {
    width: 56px; height: 56px; border-radius: 12px; flex-shrink: 0;
    background: linear-gradient(135deg, #1e293b, #0f172a);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: #d4a843;
}
.brand-icon-img { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; flex-shrink: 0; }
.brand-name { font-size: 1.05rem; font-weight: 800; color: #1e293b; margin: 0 0 3px; }
.brand-slug { font-size: 0.78rem; color: #94a3b8; font-family: monospace; }
.brand-type { font-size: 0.8rem; color: #64748b; margin-top: 4px; }
.brand-stats-row { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-top: 0.75rem; }
.brand-stat-chip {
    display: inline-flex; align-items: center; gap: 4px;
    background: #f1f5f9; color: #475569; font-size: 0.78rem; font-weight: 600;
    padding: 3px 10px; border-radius: 20px;
}
.brand-stat-chip i { color: #b8973a; font-size: 0.75rem; }
.brand-actions { display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem; }

/* ===== BADGES TRẠNG THÁI ===== */
.gs-status { display: inline-flex; align-items: center; gap: 5px; font-size: 0.78rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; border: 1px solid; white-space: nowrap; }
.gs-status.active    { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
.gs-status.paused    { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.gs-status.repair    { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
.gs-status.closed    { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }

/* ===== TABLE RESTAURANTS ===== */
.gs-table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
.gs-data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.gs-data-table thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
.gs-data-table thead th { padding: 0.9rem 1rem; font-weight: 700; color: #475569; text-align: left; white-space: nowrap; }
.gs-data-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
.gs-data-table tbody tr:last-child { border-bottom: none; }
.gs-data-table tbody tr:hover { background: #fafaf8; }
.gs-data-table td { padding: 0.85rem 1rem; vertical-align: top; }
.restaurant-name-cell { font-weight: 700; color: #1e293b; }
.restaurant-addr-cell { font-size: 0.8rem; color: #64748b; line-height: 1.4; }

/* ===== ACTION BUTTONS ===== */
.action-btn {
    display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;
    border-radius: 6px; font-family: inherit; font-size: 0.78rem; font-weight: 600;
    cursor: pointer; border: 1px solid; transition: all .15s; text-decoration: none;
    white-space: nowrap;
}
.action-btn.edit   { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.action-btn.edit:hover { background: #dbeafe; }
.action-btn.status { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
.action-btn.status:hover { background: #dcfce7; }
.action-btn.delete { background: #fff5f5; color: #c53030; border-color: #fed7d7; }
.action-btn.delete:hover { background: #fed7d7; }
.action-btn.toggle-pause { background: #fffbeb; color: #92400e; border-color: #fde68a; }
.gs-data-table .btn.btn-outline-info.btn-sm.rounded-pill {
    padding: 4px 11px;
    font-size: 0.78rem;
    gap: 5px;
    white-space: nowrap;
    line-height: 1.3;
    box-sizing: border-box;
    height: 28px;
}
.row-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
}
.row-actions-break { flex-basis: 100%; height: 0; }
.row-actions .action-btn {
    height: 28px;
    box-sizing: border-box;
    justify-content: center;
}

/* ===== MODAL ===== */
.gs-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,0.65); backdrop-filter: blur(3px);
    z-index: 9999; align-items: center; justify-content: center; padding: 1rem;
}
.gs-modal-overlay.open { display: flex; }
.gs-modal {
    background: #fff; border-radius: 18px; width: 100%; max-width: 640px;
    max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    animation: modalPop .25s ease;
}
@keyframes modalPop { from { opacity: 0; transform: scale(0.95) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.gs-modal-header {
    padding: 1.4rem 1.6rem 1rem; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 18px 18px 0 0;
}
.gs-modal-title { color: #d4a843 !important; font-size: 1.1rem; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 0.6rem; }
.gs-modal-title i { color: #d4a843; }
.gs-modal-close {
    background: rgba(255,255,255,0.1); border: none; color: #94a3b8; width: 32px; height: 32px;
    border-radius: 50%; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;
    transition: background .2s; flex-shrink: 0;
}
.gs-modal-close:hover { background: rgba(255,255,255,0.2); color: #fff; }
.gs-modal-body { padding: 1.5rem 1.6rem; }
.gs-modal-footer { padding: 1rem 1.6rem 1.4rem; display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid #f1f5f9; }

/* ===== FORM FIELDS ===== */
.form-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.form-row.cols-2 { grid-template-columns: 1fr 1fr; }
.form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
@media (max-width: 560px) { .form-row.cols-2, .form-row.cols-3 { grid-template-columns: 1fr; } }
.form-field { display: flex; flex-direction: column; gap: 5px; }
.form-label { font-size: 0.82rem; font-weight: 700; color: #334155; }
.form-label .req { color: #dc2626; }
.form-control {
    border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 0.85rem;
    font-family: inherit; font-size: 0.88rem; color: #1e293b; background: #fff;
    outline: none; transition: border-color .2s, box-shadow .2s; width: 100%; box-sizing: border-box;
}
.form-control:focus { border-color: #b8973a; box-shadow: 0 0 0 3px rgba(184,151,58,0.12); }
.form-control.is-invalid { border-color: #dc2626; }
.form-hint { font-size: 0.75rem; color: #94a3b8; }
.form-divider { font-size: 0.75rem; font-weight: 700; color: #b8973a; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin: 1rem 0 0.75rem; }

/* ===== BTN ===== */
.btn-modal-submit {
    background: linear-gradient(135deg, #b8973a, #d4a843); color: #fff; border: none;
    padding: 0.6rem 1.4rem; border-radius: 8px; font-family: inherit; font-size: 0.9rem;
    font-weight: 700; cursor: pointer; transition: opacity .2s; display: inline-flex; align-items: center; gap: 0.4rem;
}
.btn-modal-submit:hover { opacity: 0.9; }
.btn-modal-cancel {
    background: #f1f5f9; color: #64748b; border: 1.5px solid #e2e8f0;
    padding: 0.6rem 1.1rem; border-radius: 8px; font-family: inherit; font-size: 0.9rem;
    font-weight: 600; cursor: pointer; transition: background .15s;
}
.btn-modal-cancel:hover { background: #e2e8f0; }

/* ===== ALERT ===== */
.alert-flash {
    display: flex; align-items: flex-start; gap: 0.75rem;
    padding: 0.85rem 1.1rem; border-radius: 10px; margin-bottom: 1.25rem;
    border: 1px solid; font-size: 0.88rem; font-weight: 500;
}
.alert-flash.success { background: #f0fdf4; border-color: #86efac; color: #166534; }
.alert-flash.error   { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.alert-flash i { margin-top: 1px; flex-shrink: 0; }

/* EMPTY STATE */
.empty-panel {
    text-align: center; padding: 4rem 2rem; color: #94a3b8;
}
.empty-panel i { font-size: 3rem; color: #e2e8f0; display: block; margin-bottom: 1rem; }
.empty-panel h3 { color: #64748b; font-size: 1.1rem; margin-bottom: 0.5rem; }
</style>
@endsection

@section('content')
@php
    $brands           = $brands ?? collect();
    $restaurants      = $restaurants ?? collect();
    $managers         = $managers ?? collect();
    $cities           = $cities ?? collect();
    $brandsForFilter  = $brandsForFilter ?? collect();
    $currentTab       = $currentTab ?? 'thuong_hieu';
    $searchBrand      = $searchBrand ?? '';
    $searchRestaurant = $searchRestaurant ?? '';
    $filterBrand      = $filterBrand ?? 0;
    $filterCity       = $filterCity ?? '';

    $flashSuccess = session('success');
    $flashError   = session('error');

    // Đếm
    $brandCount       = $brandCount ?? count($brands);
    $restaurantCount  = $restaurantCount ?? count($restaurants);
    $activeBrands     = $activeBrands ?? collect($brands)->where('trang_thai', 'hoat_dong')->count();
    $activeRestaurants= $activeRestaurants ?? collect($restaurants)->where('trang_thai_hoat_dong', 'dang_hoat_dong')->count();
@endphp

{{-- HERO --}}
<div class="categories-hero">
    <div class="gs-container">
        <div class="hero-inner">
            <div class="hero-title-block">
                <div class="hero-eyebrow">
                    <span class="hero-eyebrow-badge"><i class="fa-solid fa-shield-halved"></i> PHÂN HỆ ADMIN</span>
                </div>
                <h1 class="hero-page-title"><span>Quản Lý Thương Hiệu & Nhà Hàng</span></h1>
                <p class="hero-page-desc">Quản lý toàn bộ danh mục thương hiệu và chi nhánh nhà hàng trong hệ thống Golden Shift.</p>
            </div>
            <div class="hero-stats">
                <div class="stat-pill">
                    <div class="stat-pill-num">{{ $brandCount }}</div>
                    <div class="stat-pill-label">Thương Hiệu</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-num">{{ $activeBrands }}</div>
                    <div class="stat-pill-label">Đang Hoạt Động</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-num">{{ $restaurantCount }}</div>
                    <div class="stat-pill-label">Chi Nhánh</div>
                </div>
                <div class="stat-pill">
                    <div class="stat-pill-num">{{ $activeRestaurants }}</div>
                    <div class="stat-pill-label">CN Đang Mở</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MAIN CONTENT --}}
<div class="gs-container" style="padding-top: 0; padding-bottom: 3rem;">
    {{-- FLASH ALERTS --}}
    @if(!empty($flashSuccess))
    <div class="alert-flash success" style="margin-top: 1.5rem;">
        <i class="fa-solid fa-circle-check"></i> <span>{{ $flashSuccess }}</span>
    </div>
    @endif
    @if(!empty($flashError))
    <div class="alert-flash error" style="margin-top: 1.5rem;">
        <i class="fa-solid fa-triangle-exclamation"></i> <span>{{ $flashError }}</span>
    </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
    <div class="alert-flash error" style="margin-top: 1.5rem; flex-direction: column;">
        <div style="display:flex;align-items:center;gap:.5rem;font-weight:700;"><i class="fa-solid fa-circle-xmark"></i> Dữ liệu nhập chưa hợp lệ:</div>
        <ul style="margin: 0.5rem 0 0 1.2rem; padding:0;">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- TAB SWITCHER --}}
    <div class="tab-switcher-bar" style="border-radius: 0; margin: 0 -0px; margin-top: 0;">
        <button class="tab-btn {{ $currentTab === 'thuong_hieu' ? 'active' : '' }}"
                onclick="switchTab('thuong_hieu')" type="button" id="tab-btn-thuong_hieu">
            <i class="fa-solid fa-copyright"></i> Quản Lý Thương Hiệu
            <span class="tab-count">{{ $brandCount }}</span>
        </button>
        <button class="tab-btn {{ $currentTab === 'nha_hang' ? 'active' : '' }}"
                onclick="switchTab('nha_hang')" type="button" id="tab-btn-nha_hang">
            <i class="fa-solid fa-store"></i> Quản Lý Chi Nhánh Nhà Hàng
            <span class="tab-count">{{ $restaurantCount }}</span>
        </button>
    </div>

    {{-- ===================== TAB 1: THƯƠNG HIỆU ===================== --}}
    <div class="tab-pane {{ $currentTab === 'thuong_hieu' ? 'active' : '' }}" id="pane-thuong_hieu">
        {{-- TOOLBAR --}}
        <div class="toolbar-row">
            <form method="GET" action="{{ route('admin.categories.index') }}" style="display:flex;gap:.6rem;flex:1;flex-wrap:wrap;">
                <input type="hidden" name="tab" value="thuong_hieu">
                <div class="toolbar-filters">
                    <input type="text" name="search_brand" value="{{ $searchBrand }}"
                           placeholder="🔍 Tìm thương hiệu, loại hình...">
                    <button type="submit" class="action-btn edit"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
                    @if(!empty($searchBrand))
                    <a href="{{ route('admin.categories.index', ['tab'=>'thuong_hieu']) }}" class="action-btn" style="background:#f1f5f9;color:#64748b;border-color:#e2e8f0;">
                        <i class="fa-solid fa-xmark"></i> Xóa bộ lọc
                    </a>
                    @endif
                </div>
            </form>
            <button class="btn-add-gold" type="button" onclick="openModal('modalAddBrand')">
                <i class="fa-solid fa-plus"></i> Thêm Thương Hiệu
            </button>
        </div>

        {{-- BRAND GRID --}}
        @if(count($brands) === 0)
        <div class="empty-panel">
            <i class="fa-solid fa-copyright"></i>
            <h3>Chưa có thương hiệu nào</h3>
            <p>Nhấn "Thêm Thương Hiệu" để bắt đầu xây dựng danh mục.</p>
        </div>
        @else
        <div class="brand-grid">
            @foreach($brands as $brand)
            @php
                $bArr = is_array($brand) ? $brand : (method_exists($brand,'toArray') ? $brand->toArray() : (array)$brand);
                $bId        = $bArr['ma_thuong_hieu'] ?? 0;
                $bName      = $bArr['ten_thuong_hieu'] ?? 'Thương hiệu';
                $bSlug      = $bArr['ma_dinh_danh'] ?? '';
                $bType      = $bArr['loai_hinh_am_thuc'] ?? '—';
                $bDesc      = $bArr['mo_ta_ngan'] ?? '';
                $bDetail    = $bArr['gioi_thieu_chi_tiet'] ?? '';
                $bStatus    = $bArr['trang_thai'] ?? 'hoat_dong';
                $bIcon      = $bArr['bieu_tuong'] ?? null;
                $bLogoUrl   = \App\Models\ThuongHieu::resolveLogoUrl($bIcon);
                $bFaIcon    = \App\Models\ThuongHieu::resolveFallbackIcon($bIcon);
                $bCount     = $bArr['nha_hang_count'] ?? (is_object($brand) && isset($brand->nha_hang_count) ? $brand->nha_hang_count : 0);
            @endphp
            <div class="brand-card">
                <div class="brand-card-top">
                    @if($bLogoUrl)
                        <img src="{{ $bLogoUrl }}" class="brand-icon-img" alt="{{ $bName }}" style="border: 1px solid #e2e8f0; background: #fff;">
                    @else
                        <div class="brand-icon-box"><i class="fa-solid {{ $bFaIcon }}"></i></div>
                    @endif
                    <div style="flex:1;min-width:0;">
                        <div class="brand-name">{{ $bName }}</div>
                        <div class="brand-slug">{{ $bSlug }}</div>
                        <div class="brand-type"><i class="fa-solid fa-bowl-food" style="color:#b8973a;"></i> {{ $bType }}</div>
                    </div>
                    <div>
                        @if($bStatus === 'hoat_dong')
                            <span class="gs-status active"><i class="fa-solid fa-circle-dot"></i> Hoạt động</span>
                        @else
                            <span class="gs-status paused"><i class="fa-solid fa-pause"></i> Tạm dừng</span>
                        @endif
                    </div>
                </div>

                @if($bDesc)
                <p style="font-size:.82rem;color:#64748b;margin:.5rem 0 .75rem;line-height:1.5;">{{ $bDesc }}</p>
                @endif

                <div class="brand-stats-row">
                    <span class="brand-stat-chip">
                        <i class="fa-solid fa-store"></i> {{ $bCount }} chi nhánh
                    </span>
                </div>

                <div class="brand-actions">
                    {{-- NÚT SỬA --}}
                    <button type="button" class="action-btn edit"
                            onclick="openEditBrand({{ $bId }}, {{ json_encode($bName) }}, {{ json_encode($bSlug) }}, {{ json_encode($bType) }}, {{ json_encode($bDesc) }}, {{ json_encode($bDetail) }}, {{ json_encode($bStatus) }}, {{ json_encode($bLogoUrl) }}, {{ json_encode($bFaIcon) }})">
                        <i class="fa-solid fa-pen-to-square"></i> Sửa
                    </button>

                    {{-- NÚT CHUYỂN TRẠNG THÁI --}}
                    <form action="{{ route('admin.brands.toggle_status', $bId) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="action-btn status toggle-pause">
                            @if($bStatus === 'hoat_dong')
                                <i class="fa-solid fa-pause"></i> Tạm dừng
                            @else
                                <i class="fa-solid fa-play"></i> Hoạt động
                            @endif
                        </button>
                    </form>

                    {{-- NÚT XÓA --}}
                    <form action="{{ route('admin.brands.destroy', $bId) }}" method="POST" style="margin:0;"
                          onsubmit="return confirm('Bạn có chắc chắn muốn XÓA thương hiệu [{{ $bName }}]?\nHành động này không thể hoàn tác!');">
                        @csrf
                        <button type="submit" class="action-btn delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @if(method_exists($brands, 'hasPages') && $brands->hasPages())
            <div class="pagination-wrapper">
                {{ $brands->links('pagination::bootstrap-5') }}
            </div>
        @endif
        @endif
    </div>{{-- /pane-thuong_hieu --}}

    {{-- ===================== TAB 2: CHI NHÁNH NHÀ HÀNG ===================== --}}
    <div class="tab-pane {{ $currentTab === 'nha_hang' ? 'active' : '' }}" id="pane-nha_hang">
        {{-- TOOLBAR --}}
        <div class="toolbar-row">
            <form method="GET" action="{{ route('admin.categories.index') }}" style="display:flex;gap:.6rem;flex:1;flex-wrap:wrap;">
                <input type="hidden" name="tab" value="nha_hang">
                <div class="toolbar-filters">
                    <input type="text" name="search_restaurant" value="{{ $searchRestaurant }}"
                           placeholder="🔍 Tên nhà hàng, SĐT, địa chỉ...">
                    <select name="filter_brand">
                        <option value="0">— Tất cả Thương Hiệu —</option>
                        @foreach($brandsForFilter as $bf)
                        @php $bfArr = is_array($bf) ? $bf : (method_exists($bf,'toArray') ? $bf->toArray() : (array)$bf); @endphp
                        <option value="{{ $bfArr['ma_thuong_hieu'] }}"
                                {{ $filterBrand == $bfArr['ma_thuong_hieu'] ? 'selected' : '' }}>
                            {{ $bfArr['ten_thuong_hieu'] }}
                        </option>
                        @endforeach
                    </select>
                    <select name="filter_city">
                        <option value="">— Tất cả Tỉnh/TP —</option>
                        @foreach($cities as $city)
                        <option value="{{ $city }}" {{ $filterCity == $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="action-btn edit"><i class="fa-solid fa-filter"></i> Lọc</button>
                    @if(!empty($searchRestaurant) || $filterBrand > 0 || !empty($filterCity))
                    <a href="{{ route('admin.categories.index', ['tab'=>'nha_hang']) }}" class="action-btn" style="background:#f1f5f9;color:#64748b;border-color:#e2e8f0;">
                        <i class="fa-solid fa-xmark"></i> Xóa bộ lọc
                    </a>
                    @endif
                </div>
            </form>
            <button class="btn-add-gold" type="button" onclick="openModal('modalAddRestaurant')">
                <i class="fa-solid fa-plus"></i> Thêm Chi Nhánh
            </button>
        </div>

        {{-- TABLE --}}
        @if(count($restaurants) === 0)
        <div class="empty-panel">
            <i class="fa-solid fa-store-slash"></i>
            <h3>Chưa có chi nhánh nào</h3>
            <p>Nhấn "Thêm Chi Nhánh" để đăng ký nhà hàng đầu tiên.</p>
        </div>
        @else
        <div class="gs-table-wrapper">
            <table class="gs-data-table">
                <thead>
                    <tr>
                        <th style="width:45px;">#</th>
                        <th>Tên Nhà Hàng</th>
                        <th style="white-space:nowrap;">Thuộc Thương Hiệu</th>
                        <th>Quản Lý Phụ Trách</th>
                        <th>SĐT Hotline</th>
                        <th>Địa Chỉ</th>
                        <th>Trạng Thái</th>
                        <th class="text-center" style="min-width:215px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                @php $rIdx = (method_exists($restaurants, 'firstItem') && $restaurants->firstItem()) ? ($restaurants->firstItem() - 1) : 0; @endphp
                @foreach($restaurants as $r)
                @php
                    $rIdx++;
                    $rArr    = is_array($r) ? $r : (method_exists($r,'toArray') ? $r->toArray() : (array)$r);
                    $rId     = $rArr['ma_nha_hang'] ?? 0;
                    $rName   = $rArr['ten_nha_hang'] ?? '—';
                    $rPhone  = $rArr['so_dien_thoai_lien_he'] ?? '';
                    $rEmail  = $rArr['email_chi_nhanh'] ?? '';
                    $rAddr   = $rArr['dia_chi_chi_tiet'] ?? '';
                    $rPhuong = $rArr['phuong_xa'] ?? '';
                    $rHuyen  = $rArr['quan_huyen'] ?? '';
                    $rTinh   = $rArr['tinh_thanh_pho'] ?? '';
                    $rStatus = $rArr['trang_thai_hoat_dong'] ?? 'dang_hoat_dong';
                    $rMaTH   = $rArr['ma_thuong_hieu'] ?? 0;
                    $rMaQL   = $rArr['ma_quan_ly'] ?? null;

                    // Relations
                    $rBrandName  = (is_object($r) && isset($r->thuongHieu)) ? ($r->thuongHieu->ten_thuong_hieu ?? '—') : ($rArr['ten_thuong_hieu'] ?? '—');
                    $rManagerName = is_object($r) ? ($r->quanLy->ho_ten ?? null) : null;
                    $fullAddr = trim(implode(', ', array_filter([$rAddr, $rPhuong, $rHuyen, $rTinh])));
                @endphp
                <tr>
                    <td style="color:#94a3b8;font-size:.8rem;">{{ $rIdx }}</td>
                    <td>
                        <div class="restaurant-name-cell">{{ $rName }}</div>
                        @if($rEmail)
                        <div class="restaurant-addr-cell"><i class="fa-solid fa-envelope" style="color:#b8973a;"></i> {{ $rEmail }}</div>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        <span style="font-size:.82rem;font-weight:600;color:#0f172a;background:#fef9ec;border:1px solid #fde68a;padding:3px 10px;border-radius:20px;white-space:nowrap;display:inline-block;">
                            {{ $rBrandName }}
                        </span>
                    </td>
                    <td style="font-size:.82rem;">
                        @if($rManagerName)
                            <div style="font-weight:600;color:#1e293b;"><i class="fa-solid fa-user-tie" style="color:#b8973a;"></i> {{ $r->quanLy->ho_ten ?? 'Chưa phân công' }}</div>
                        @else
                            <span style="color:#94a3b8;font-style:italic;">{{ $r->quanLy->ho_ten ?? 'Chưa phân công' }}</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;font-size:.85rem;">
                        <a href="tel:{{ $rPhone }}" style="color:#0284c7;font-weight:600;text-decoration:none;">
                            <i class="fa-solid fa-phone" style="color:#b8973a;"></i> {{ $rPhone }}
                        </a>
                    </td>
                    <td>
                        <div class="restaurant-addr-cell">
                            <i class="fa-solid fa-location-dot" style="color:#b8973a;"></i>
                            {{ $fullAddr }}
                        </div>
                    </td>
                    <td>
                        @if($rStatus === 'dang_hoat_dong')
                            <span class="gs-status active"><i class="fa-solid fa-circle-dot"></i> Đang mở</span>
                        @elseif($rStatus === 'dang_sua_chua')
                            <span class="gs-status repair"><i class="fa-solid fa-screwdriver-wrench"></i> Sửa chữa</span>
                        @else
                            <span class="gs-status closed"><i class="fa-solid fa-door-closed"></i> Tạm ngừng</span>
                        @endif
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="row-actions">
                            <button type="button" class="action-btn edit"
                                    onclick="openEditRestaurant({{ json_encode([
                                        'id'       => $rId,
                                        'maTH'     => $rMaTH,
                                        'maQL'     => $rMaQL,
                                        'name'     => $rName,
                                        'phone'    => $rPhone,
                                        'email'    => $rEmail,
                                        'addr'     => $rAddr,
                                        'phuong'   => $rPhuong,
                                        'huyen'    => $rHuyen,
                                        'tinh'     => $rTinh,
                                        'status'   => $rStatus,
                                    ]) }})">
                                <i class="fa-solid fa-pen-to-square"></i> Sửa
                            </button>

                            <form action="{{ route('admin.restaurants.destroy', $rId) }}" method="POST" style="margin:0;"
                                  onsubmit="return confirm('Bạn có chắc chắn muốn XÓA chi nhánh [{{ $rName }}]?\nNếu đã có ca làm việc, hệ thống sẽ từ chối xóa!');">
                                @csrf
                                <button type="submit" class="action-btn delete" title="Xóa">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                            <div class="row-actions-break"></div>

                            <a href="{{ route('admin.nha-hang.danh-gia', $rId) }}"
                               class="btn btn-outline-info btn-sm rounded-pill"
                               title="Xem chi tiết đánh giá của nhà hàng {{ $rName }}">
                                <i class="fa-solid fa-star"></i> Xem đánh giá
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($restaurants, 'hasPages') && $restaurants->hasPages())
            <div class="pagination-wrapper">
                {{ $restaurants->links('pagination::bootstrap-5') }}
            </div>
        @endif
        @endif
    </div>{{-- /pane-nha_hang --}}

</div>{{-- /gs-container --}}

{{-- ===================== MODAL THÊM THƯƠNG HIỆU ===================== --}}
<div class="gs-modal-overlay" id="modalAddBrand">
    <div class="gs-modal">
        <div class="gs-modal-header">
            <h3 class="gs-modal-title"><i class="fa-solid fa-copyright"></i> Thêm Thương Hiệu Mới</h3>
            <button class="gs-modal-close" onclick="closeModal('modalAddBrand')">&times;</button>
        </div>
        <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="gs-modal-body">
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">Tên Thương Hiệu <span class="req">*</span></label>
                        <input type="text" name="ten_thuong_hieu" class="form-control" required
                               placeholder="VD: Golden Hotpot" value="{{ old('ten_thuong_hieu') }}">
                    </div>
                    <div class="form-field">
                        <label class="form-label">Mã Định Danh (slug) <span class="req">*</span></label>
                        <input type="text" name="ma_dinh_danh" id="add_brand_slug" class="form-control" required
                               placeholder="VD: golden-hotpot" value="{{ old('ma_dinh_danh') }}">
                        <span class="form-hint">Chỉ chữ thường, số và dấu gạch ngang (-)</span>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">Loại Hình Ẩm Thực</label>
                        <input type="text" name="loai_hinh_am_thuc" class="form-control"
                               placeholder="VD: Lẩu Nướng, BBQ Hàn Quốc..." value="{{ old('loai_hinh_am_thuc') }}">
                    </div>
                    <div class="form-field">
                        <label class="form-label">Trạng Thái</label>
                        <select name="trang_thai" class="form-control">
                            <option value="hoat_dong" selected>✅ Hoạt động</option>
                            <option value="tam_dung">⏸ Tạm dừng</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Logo Thương Hiệu (Tùy chọn)</label>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div id="add_brand_logo_preview" style="flex-shrink:0;">
                                <div class="brand-icon-box"><i class="fa-solid fa-utensils"></i></div>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <input type="file" name="logo" id="add_brand_logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                                <span class="form-hint">Định dạng PNG, JPG, WEBP, GIF (tối đa 2MB). Nếu để trống sẽ dùng icon mặc định.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Mô Tả Ngắn</label>
                        <input type="text" name="mo_ta_ngan" class="form-control" maxlength="255"
                               placeholder="Mô tả ngắn về thương hiệu (tối đa 255 ký tự)" value="{{ old('mo_ta_ngan') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Giới Thiệu Chi Tiết</label>
                        <textarea name="gioi_thieu_chi_tiet" class="form-control" rows="3"
                                  placeholder="Nội dung giới thiệu chi tiết về thương hiệu...">{{ old('gioi_thieu_chi_tiet') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="gs-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalAddBrand')">Hủy</button>
                <button type="submit" class="btn-modal-submit"><i class="fa-solid fa-plus"></i> Thêm Thương Hiệu</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== MODAL SỬA THƯƠNG HIỆU ===================== --}}
<div class="gs-modal-overlay" id="modalEditBrand">
    <div class="gs-modal">
        <div class="gs-modal-header">
            <h3 class="gs-modal-title"><i class="fa-solid fa-pen-to-square"></i> Sửa Thông Tin Thương Hiệu</h3>
            <button class="gs-modal-close" onclick="closeModal('modalEditBrand')">&times;</button>
        </div>
        <form id="formEditBrand" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="gs-modal-body">
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">Tên Thương Hiệu <span class="req">*</span></label>
                        <input type="text" name="ten_thuong_hieu" id="edit_brand_name" class="form-control" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Mã Định Danh</label>
                        <input type="text" id="edit_brand_slug_display" class="form-control" disabled
                               style="background:#f8fafc;color:#94a3b8;">
                        <span class="form-hint">Không thể thay đổi sau khi tạo</span>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">Loại Hình Ẩm Thực</label>
                        <input type="text" name="loai_hinh_am_thuc" id="edit_brand_type" class="form-control">
                    </div>
                    <div class="form-field">
                        <label class="form-label">Trạng Thái</label>
                        <select name="trang_thai" id="edit_brand_status" class="form-control">
                            <option value="hoat_dong">✅ Hoạt động</option>
                            <option value="tam_dung">⏸ Tạm dừng</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Logo Thương Hiệu</label>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div id="edit_brand_logo_preview" style="flex-shrink:0;">
                                <div class="brand-icon-box"><i class="fa-solid fa-utensils"></i></div>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <input type="file" name="logo" id="edit_brand_logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                                <span class="form-hint">Để trống nếu giữ nguyên logo hiện tại. Chấp nhận PNG, JPG, WEBP, GIF (tối đa 2MB).</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Mô Tả Ngắn</label>
                        <input type="text" name="mo_ta_ngan" id="edit_brand_desc" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Giới Thiệu Chi Tiết</label>
                        <textarea name="gioi_thieu_chi_tiet" id="edit_brand_detail" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="gs-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditBrand')">Hủy</button>
                <button type="submit" class="btn-modal-submit"><i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== MODAL THÊM NHÀ HÀNG ===================== --}}
<div class="gs-modal-overlay" id="modalAddRestaurant">
    <div class="gs-modal" style="max-width:700px;">
        <div class="gs-modal-header">
            <h3 class="gs-modal-title"><i class="fa-solid fa-store"></i> Thêm Chi Nhánh Nhà Hàng Mới</h3>
            <button class="gs-modal-close" onclick="closeModal('modalAddRestaurant')">&times;</button>
        </div>
        <form action="{{ route('admin.restaurants.store') }}" method="POST">
            @csrf
            <div class="gs-modal-body">
                <div class="form-divider">Thông Tin Cơ Bản</div>
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">Thuộc Thương Hiệu <span class="req">*</span></label>
                        <select name="ma_thuong_hieu" class="form-control" required>
                            <option value="">— Chọn Thương Hiệu —</option>
                            @foreach($brandsForFilter as $bf)
                            @php $bfA = is_array($bf) ? $bf : (method_exists($bf,'toArray') ? $bf->toArray() : (array)$bf); @endphp
                            <option value="{{ $bfA['ma_thuong_hieu'] }}">{{ $bfA['ten_thuong_hieu'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Tên Chi Nhánh <span class="req">*</span></label>
                        <input type="text" name="ten_nha_hang" class="form-control" required
                               placeholder="VD: Golden Hotpot - Chi nhánh Quận 1">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">SĐT Hotline Nhà Hàng <span class="req">*</span></label>
                        <input type="tel" name="so_dien_thoai_lien_he" class="form-control" required
                               placeholder="0xxxxxxxxx" pattern="^0[0-9]{9}$"
                               title="SĐT phải đúng 10 chữ số và bắt đầu bằng số 0">
                        <span class="form-hint">10 chữ số, bắt đầu bằng 0</span>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Email Chi Nhánh</label>
                        <input type="email" name="email_chi_nhanh" class="form-control"
                               placeholder="VD: quan1@goldenshift.vn">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">Quản Lý Phụ Trách</label>
                        <select name="ma_quan_ly" class="form-control">
                            <option value="">— Chưa gán Quản lý —</option>
                            @foreach($managers as $mgr)
                            @php $mArr = is_array($mgr) ? $mgr : (method_exists($mgr,'toArray') ? $mgr->toArray() : (array)$mgr); @endphp
                            <option value="{{ $mArr['ma_nguoi_dung'] }}">{{ $mArr['ho_ten'] }} ({{ $mArr['so_dien_thoai'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Trạng Thái</label>
                        <select name="trang_thai_hoat_dong" class="form-control">
                            <option value="dang_hoat_dong">🟢 Đang hoạt động</option>
                            <option value="dang_sua_chua">🔵 Đang sửa chữa</option>
                            <option value="tam_ngung">🔴 Tạm ngừng</option>
                        </select>
                    </div>
                </div>

                <div class="form-divider">Địa Chỉ Chi Nhánh</div>
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Địa Chỉ Chi Tiết <span class="req">*</span></label>
                        <input type="text" name="dia_chi_chi_tiet" class="form-control" required
                               placeholder="Số nhà, tên đường...">
                    </div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-field">
                        <label class="form-label">Phường/Xã</label>
                        <input type="text" name="phuong_xa" class="form-control" placeholder="VD: Phường Bến Nghé">
                    </div>
                    <div class="form-field">
                        <label class="form-label">Quận/Huyện <span class="req">*</span></label>
                        <input type="text" name="quan_huyen" class="form-control" required placeholder="VD: Quận 1">
                    </div>
                    <div class="form-field">
                        <label class="form-label">Tỉnh/Thành Phố <span class="req">*</span></label>
                        <input type="text" name="tinh_thanh_pho" class="form-control" required
                               placeholder="VD: TP. Hồ Chí Minh">
                    </div>
                </div>
            </div>
            <div class="gs-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalAddRestaurant')">Hủy</button>
                <button type="submit" class="btn-modal-submit"><i class="fa-solid fa-plus"></i> Thêm Chi Nhánh</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== MODAL SỬA NHÀ HÀNG ===================== --}}
<div class="gs-modal-overlay" id="modalEditRestaurant">
    <div class="gs-modal" style="max-width:700px;">
        <div class="gs-modal-header">
            <h3 class="gs-modal-title" style="color:#d4a843;"><i class="fa-solid fa-pen-to-square"></i> Sửa Thông Tin Chi Nhánh</h3>
            <button class="gs-modal-close" onclick="closeModal('modalEditRestaurant')">&times;</button>
        </div>
        <form id="formEditRestaurant" action="" method="POST">
            @csrf
            <div class="gs-modal-body">
                <div class="form-divider">Thông Tin Cơ Bản</div>
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">Thuộc Thương Hiệu <span class="req">*</span></label>
                        <select name="ma_thuong_hieu" id="edit_r_brand" class="form-control" required>
                            <option value="">— Chọn Thương Hiệu —</option>
                            @foreach($brandsForFilter as $bf)
                            @php $bfA = is_array($bf) ? $bf : (method_exists($bf,'toArray') ? $bf->toArray() : (array)$bf); @endphp
                            <option value="{{ $bfA['ma_thuong_hieu'] }}">{{ $bfA['ten_thuong_hieu'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Tên Chi Nhánh <span class="req">*</span></label>
                        <input type="text" name="ten_nha_hang" id="edit_r_name" class="form-control" required>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">SĐT Hotline Nhà Hàng <span class="req">*</span></label>
                        <input type="tel" name="so_dien_thoai_lien_he" id="edit_r_phone" class="form-control"
                               required pattern="^0[0-9]{9}$">
                        <span class="form-hint">10 chữ số, bắt đầu bằng 0</span>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Email Chi Nhánh</label>
                        <input type="email" name="email_chi_nhanh" id="edit_r_email" class="form-control">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-field">
                        <label class="form-label">Quản Lý Phụ Trách</label>
                        <select name="ma_quan_ly" id="edit_r_manager" class="form-control">
                            <option value="">— Chưa gán Quản lý —</option>
                            @foreach($managers as $mgr)
                            @php $mArr = is_array($mgr) ? $mgr : (method_exists($mgr,'toArray') ? $mgr->toArray() : (array)$mgr); @endphp
                            <option value="{{ $mArr['ma_nguoi_dung'] }}">{{ $mArr['ho_ten'] }} ({{ $mArr['so_dien_thoai'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Trạng Thái</label>
                        <select name="trang_thai_hoat_dong" id="edit_r_status" class="form-control">
                            <option value="dang_hoat_dong">🟢 Đang hoạt động</option>
                            <option value="dang_sua_chua">🔵 Đang sửa chữa</option>
                            <option value="tam_ngung">🔴 Tạm ngừng</option>
                        </select>
                    </div>
                </div>

                <div class="form-divider">Địa Chỉ Chi Nhánh</div>
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Địa Chỉ Chi Tiết <span class="req">*</span></label>
                        <input type="text" name="dia_chi_chi_tiet" id="edit_r_addr" class="form-control" required>
                    </div>
                </div>
                <div class="form-row cols-3">
                    <div class="form-field">
                        <label class="form-label">Phường/Xã</label>
                        <input type="text" name="phuong_xa" id="edit_r_phuong" class="form-control">
                    </div>
                    <div class="form-field">
                        <label class="form-label">Quận/Huyện <span class="req">*</span></label>
                        <input type="text" name="quan_huyen" id="edit_r_huyen" class="form-control" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Tỉnh/Thành Phố <span class="req">*</span></label>
                        <input type="text" name="tinh_thanh_pho" id="edit_r_tinh" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="gs-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditRestaurant')">Hủy</button>
                <button type="submit" class="btn-modal-submit"><i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ===== MODAL HELPERS =====
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

// Close on overlay click
document.querySelectorAll('.gs-modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeModal(overlay.id);
    });
});

// ===== TAB SWITCHER =====
function switchTab(tabId) {
    // Hide all panes, deactivate all buttons
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    // Show selected
    document.getElementById('pane-' + tabId).classList.add('active');
    document.getElementById('tab-btn-' + tabId).classList.add('active');
}

// ===== AUTO SLUG =====
const addBrandName = document.querySelector('[name="ten_thuong_hieu"]');
const addBrandSlug = document.getElementById('add_brand_slug');
if (addBrandName && addBrandSlug) {
    addBrandName.addEventListener('input', function() {
        if (!addBrandSlug._userEdited) {
            addBrandSlug.value = this.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd').replace(/Đ/g, 'd')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim().replace(/\s+/g, '-');
        }
    });
    addBrandSlug.addEventListener('input', () => { addBrandSlug._userEdited = true; });
}

// ===== BRAND LOGO LIVE PREVIEW =====
function renderBrandLogoPreview(containerEl, logoUrl, faIcon) {
    if (!containerEl) return;
    containerEl.replaceChildren();
    if (logoUrl) {
        var img = document.createElement('img');
        img.src = logoUrl;
        img.className = 'brand-icon-img';
        img.style.border = '1px solid #e2e8f0';
        img.style.background = '#fff';
        img.alt = 'Logo preview';
        containerEl.appendChild(img);
    } else {
        var box = document.createElement('div');
        box.className = 'brand-icon-box';
        var icon = document.createElement('i');
        icon.className = 'fa-solid ' + (faIcon || 'fa-utensils');
        box.appendChild(icon);
        containerEl.appendChild(box);
    }
}

function bindBrandLogoInput(inputId, previewId) {
    var inputEl = document.getElementById(inputId);
    var previewEl = document.getElementById(previewId);
    if (!inputEl || !previewEl) return;

    inputEl.addEventListener('change', function() {
        var file = this.files && this.files[0];
        if (!file) {
            renderBrandLogoPreview(
                previewEl,
                previewEl.dataset.defaultLogoUrl || '',
                previewEl.dataset.defaultFaIcon || 'fa-utensils'
            );
            return;
        }
        var reader = new FileReader();
        reader.onload = function(ev) {
            renderBrandLogoPreview(previewEl, ev.target.result, 'fa-utensils');
        };
        reader.readAsDataURL(file);
    });
}

bindBrandLogoInput('add_brand_logo', 'add_brand_logo_preview');
bindBrandLogoInput('edit_brand_logo', 'edit_brand_logo_preview');

// ===== OPEN EDIT BRAND MODAL =====
function openEditBrand(id, name, slug, type, desc, detail, status, logoUrl, faIcon) {
    var baseUrl = "{{ url('admin/categories/thuong-hieu') }}";
    document.getElementById('formEditBrand').action = baseUrl + '/' + id + '/update';
    document.getElementById('edit_brand_name').value  = name;
    document.getElementById('edit_brand_slug_display').value = slug;
    document.getElementById('edit_brand_type').value  = type || '';
    document.getElementById('edit_brand_desc').value  = desc || '';
    document.getElementById('edit_brand_detail').value = detail || '';
    document.getElementById('edit_brand_status').value = status || 'hoat_dong';

    var editFileInput = document.getElementById('edit_brand_logo');
    if (editFileInput) editFileInput.value = '';
    var editPreview = document.getElementById('edit_brand_logo_preview');
    if (editPreview) {
        editPreview.dataset.defaultLogoUrl = logoUrl || '';
        editPreview.dataset.defaultFaIcon = faIcon || 'fa-utensils';
        renderBrandLogoPreview(editPreview, logoUrl || '', faIcon || 'fa-utensils');
    }

    openModal('modalEditBrand');
}

// ===== OPEN EDIT RESTAURANT MODAL =====
function openEditRestaurant(data) {
    var baseUrl = "{{ url('admin/categories/nha-hang') }}";
    document.getElementById('formEditRestaurant').action = baseUrl + '/' + data.id + '/update';
    document.getElementById('edit_r_brand').value   = data.maTH || '';
    document.getElementById('edit_r_name').value    = data.name || '';
    document.getElementById('edit_r_phone').value   = data.phone || '';
    document.getElementById('edit_r_email').value   = data.email || '';
    document.getElementById('edit_r_addr').value    = data.addr || '';
    document.getElementById('edit_r_phuong').value  = data.phuong || '';
    document.getElementById('edit_r_huyen').value   = data.huyen || '';
    document.getElementById('edit_r_tinh').value    = data.tinh || '';
    document.getElementById('edit_r_status').value  = data.status || 'dang_hoat_dong';
    var mgr = document.getElementById('edit_r_manager');
    if (mgr) mgr.value = data.maQL ? String(data.maQL) : '';
    openModal('modalEditRestaurant');
}

// ===== AUTO-OPEN MODAL ON VALIDATION ERROR =====
@if($errors->any())
    @if(old('ten_nha_hang') || old('ma_thuong_hieu'))
        openModal('modalAddRestaurant');
    @elseif(old('ten_thuong_hieu') && !old('ten_nha_hang'))
        openModal('modalAddBrand');
    @endif
@endif
</script>
@endsection
