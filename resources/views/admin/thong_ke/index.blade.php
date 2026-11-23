@extends('layouts.app')

@section('title', 'Báo Cáo Thống Kê & Phân Tích Hệ Thống - Golden Shift Admin')

@section('styles')
<style>
.analytics-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #0c1a30 100%);
    padding: 2.2rem 0 1.9rem;
    border-bottom: 3px solid #b8973a;
    position: relative;
    overflow: hidden;
}
.analytics-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 85% 40%, rgba(245, 158, 11, 0.12) 0%, transparent 60%);
    pointer-events: none;
}
.analytics-hero-inner {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.analytics-hero-title {
    color: #ffffff !important;
    font-size: 1.75rem;
    font-weight: 800;
    margin: 10px 0 6px;
    line-height: 1.25;
}
.analytics-hero-title span {
    color: #f59e0b;
}
.analytics-hero-sub {
    color: #cbd5e1;
    font-size: 13.5px;
    margin: 0;
}
.analytics-filter-bar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px 20px;
    margin-bottom: 22px;
    box-shadow: 0 3px 14px rgba(15, 23, 42, 0.04);
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.preset-pills-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.preset-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.preset-btn {
    padding: 6px 14px;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #475569;
    font-family: inherit;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
}
.preset-btn:hover {
    border-color: #f59e0b;
    color: #b45309;
    background: #fffbeb;
}
.preset-btn.active {
    background: #f59e0b;
    border-color: #f59e0b;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}
.filter-controls-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.filter-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 145px;
}
.filter-field label {
    font-size: 11.5px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
}
.filter-field input,
.filter-field select {
    height: 38px;
    border: 1.5px solid #cbd5e1;
    border-radius: 9px;
    padding: 0 11px;
    font-family: inherit;
    font-size: 13px;
    color: #1e293b;
    background: #ffffff;
    outline: none;
}
.filter-field input:focus,
.filter-field select:focus {
    border-color: #f59e0b;
}
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(235px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 18px 20px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    transition: transform 0.2s, border-color 0.2s;
}
.kpi-card:hover {
    transform: translateY(-2px);
    border-color: #fde68a;
}
.kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.kpi-icon.gold { background: #fef3c7; color: #d97706; }
.kpi-icon.blue { background: #e0f2fe; color: #0284c7; }
.kpi-icon.green { background: #dcfce7; color: #16a34a; }
.kpi-icon.amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.kpi-label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.kpi-value {
    font-size: 24px;
    font-weight: 900;
    color: #0f172a;
    margin: 4px 0;
    line-height: 1.2;
}
.kpi-sub {
    font-size: 12px;
    color: #64748b;
    line-height: 1.4;
}
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}
@media (max-width: 960px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}
.chart-card,
.stats-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px 22px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.stats-table-card {
    padding: 0;
    overflow: hidden;
    margin-bottom: 22px;
}
.chart-card-head,
.table-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.table-card-head {
    padding: 16px 22px;
    margin-bottom: 0;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}
.chart-title {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chart-title i {
    color: #d97706;
}
.chart-canvas-wrap {
    position: relative;
    height: 290px;
    width: 100%;
}
.stats-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
}
.stats-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    padding: 11px 16px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.stats-table tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}
.stats-table tbody tr:hover {
    background: #fffdf5;
}
.badge-pill-stat {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}
.loading-overlay {
    opacity: 0.55;
    pointer-events: none;
    transition: opacity 0.2s;
}
</style>
@endsection

@section('content')
@php
    $kpis = $analytics['kpis'];
@endphp

<section class="analytics-hero">
    <div class="gs-container">
        <div style="font-size: 13px; color: #94a3b8; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
            <a href="{{ url('/') }}" style="color: #cbd5e1; text-decoration: none;"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ route('admin.categories.index') }}" style="color: #cbd5e1; text-decoration: none;">Quản trị hệ thống</a>
            <span>/</span>
            <span style="color: #f59e0b; font-weight: 700;">Báo cáo thống kê</span>
        </div>

        <div class="analytics-hero-inner">
            <div>
                <span style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.4); padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-chart-pie"></i> TRUNG TÂM PHÂN TÍCH TOÀN MẠNG LƯỚI
                </span>
                <h1 class="analytics-hero-title">
                    Báo Cáo Thống Kê &amp; <span>Phân Tích Hệ Thống</span>
                </h1>
                <p class="analytics-hero-sub">
                    Theo dõi hiệu suất tuyển dụng, quỹ lương chi phí nhân sự theo thương hiệu/chi nhánh và tỷ lệ tuân thủ kỷ luật.
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span id="activeRangeLabel" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.18); color: #fde68a; padding: 7px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 700;">
                    <i class="fa-regular fa-calendar-check"></i> {{ $range['label'] }}
                </span>

                <a id="btnExportCsv"
                   href="{{ route('admin.thong_ke.export', request()->query()) }}"
                   class="btn btn-success btn-sm rounded-pill"
                   style="font-weight: 700; padding: 8px 18px;">
                    <i class="fa-solid fa-file-excel"></i> Xuất Báo cáo Excel / CSV
                </a>
            </div>
        </div>
    </div>
</section>

<section style="padding: 26px 0 60px; background: #f8fafc;">
    <div class="gs-container" id="adminAnalyticsRoot">

        {{-- BỘ LỌC TƯƠNG TÁC --}}
        <form id="adminAnalyticsFilterForm" method="GET" action="{{ route('admin.thong_ke.index') }}" class="analytics-filter-bar">
            <input type="hidden" name="preset" id="inputPreset" value="{{ $range['preset'] }}">

            <div class="preset-pills-row">
                <div class="preset-pills">
                    @php
                        $presets = [
                            'tat_ca' => 'Tất cả thời gian',
                            'hom_nay' => 'Hôm nay',
                            'tuan_nay' => 'Tuần này',
                            'thang_nay' => 'Tháng này',
                            'quy_nay' => 'Quý này',
                        ];
                    @endphp
                    @foreach ($presets as $pKey => $pText)
                        <button type="button"
                                class="preset-btn {{ $range['preset'] === $pKey ? 'active' : '' }}"
                                data-preset="{{ $pKey }}">
                            {{ $pText }}
                        </button>
                    @endforeach
                </div>

                <span id="ajaxStatusBadge" style="font-size: 12px; color: #64748b; display: none;">
                    <i class="fa-solid fa-spinner fa-spin text-gold"></i> Đang cập nhật số liệu...
                </span>
            </div>

            <div class="filter-controls-row">
                <div class="filter-field">
                    <label for="inputTuNgay">Từ ngày</label>
                    <input type="date" name="tu_ngay" id="inputTuNgay" value="{{ $range['tu_ngay'] }}">
                </div>
                <div class="filter-field">
                    <label for="inputDenNgay">Đến ngày</label>
                    <input type="date" name="den_ngay" id="inputDenNgay" value="{{ $range['den_ngay'] }}">
                </div>
                <div class="filter-field" style="min-width: 190px; flex: 1;">
                    <label for="selectBrand">Thương hiệu</label>
                    <select name="ma_thuong_hieu" id="selectBrand">
                        <option value="">— Tất cả Thương hiệu —</option>
                        @foreach ($brands as $b)
                            <option value="{{ $b->ma_thuong_hieu }}" {{ (int) ($brandId ?? 0) === (int) $b->ma_thuong_hieu ? 'selected' : '' }}>
                                {{ $b->ten_thuong_hieu }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field" style="min-width: 220px; flex: 1.3;">
                    <label for="selectRestaurant">Chi nhánh nhà hàng</label>
                    <select name="ma_nha_hang" id="selectRestaurant">
                        <option value="">— Tất cả Chi nhánh —</option>
                        @foreach ($restaurants as $nh)
                            <option value="{{ $nh->ma_nha_hang }}" data-brand="{{ $nh->ma_thuong_hieu }}" {{ (int) ($restaurantId ?? 0) === (int) $nh->ma_nha_hang ? 'selected' : '' }}>
                                {{ $nh->ten_nha_hang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-gold btn-sm" style="height: 38px; border-radius: 9px; padding: 0 18px;">
                        <i class="fa-solid fa-filter"></i> Lọc dữ liệu
                    </button>
                    <button type="button" id="btnResetFilters" class="btn btn-outline btn-sm" style="height: 38px; border-radius: 9px; padding: 0 14px;">
                        <i class="fa-solid fa-rotate-left"></i> Đặt lại
                    </button>
                </div>
            </div>
        </form>

        {{-- 4 KPI CARDS TOÀN HỆ THỐNG --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon gold"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                    <div class="kpi-label">Ca làm đã phát hành</div>
                    <div class="kpi-value" id="kpiTongCaLam">{{ number_format($kpis['tong_ca_lam'], 0, ',', '.') }}</div>
                    <div class="kpi-sub" id="kpiTongGioLam">{{ number_format($kpis['tong_gio_lam'], 1, ',', '.') }} giờ làm đã duyệt</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon blue"><i class="fa-solid fa-file-signature"></i></div>
                <div>
                    <div class="kpi-label">Tổng đơn ứng tuyển</div>
                    <div class="kpi-value" id="kpiTongDon">{{ number_format($kpis['tong_don_ung_tuyen'], 0, ',', '.') }}</div>
                    <div class="kpi-sub" id="kpiTyLeThanhCong">
                        {{ $kpis['don_da_nhan'] }} đơn đã nhận ({{ $kpis['ty_le_ung_tuyen_thanh_cong'] }}%)
                    </div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon green"><i class="fa-solid fa-money-bill-wave"></i></div>
                <div>
                    <div class="kpi-label">Chi phí nhân sự đã thanh toán</div>
                    <div class="kpi-value" id="kpiChiPhiDaThanhToan" style="color: #15803d;">
                        {{ number_format($kpis['chi_phi_da_thanh_toan'], 0, ',', '.') }} đ
                    </div>
                    <div class="kpi-sub" id="kpiQuyLuongChamCong">
                        Quỹ lương ca duyệt: {{ number_format($kpis['tong_quy_luong_cham_cong'], 0, ',', '.') }} đ
                    </div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon amber"><i class="fa-solid fa-star"></i></div>
                <div>
                    <div class="kpi-label">Đánh giá TB toàn mạng lưới</div>
                    <div class="kpi-value" id="kpiDiemDanhGiaTb" style="color: #b45309;">
                        {{ number_format($kpis['diem_danh_gia_tb'], 1) }} <span style="font-size: 14px; font-weight: 700;">/ 5.0 ⭐</span>
                    </div>
                    <div class="kpi-sub" id="kpiTongLuotDanhGia">
                        {{ $kpis['tong_luot_danh_gia'] }} lượt đánh giá • {{ $kpis['tong_vi_pham'] }} vi phạm
                    </div>
                </div>
            </div>
        </div>

        {{-- BIỂU ĐỒ CHART.JS --}}
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-card-head">
                    <h3 class="chart-title"><i class="fa-solid fa-chart-bar"></i> Chi Phí Nhân Sự &amp; Số Ca Làm Theo Tháng</h3>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartAdminBarMonthly"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-head">
                    <h3 class="chart-title"><i class="fa-solid fa-chart-line"></i> Xu Hướng Đơn Ứng Tuyển Theo Thời Gian</h3>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartAdminLineTrend"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-head">
                    <h3 class="chart-title"><i class="fa-solid fa-clock"></i> Tỷ Lệ Trạng Thái Ca Làm (Đúng giờ / Đi muộn / Bùng ca)</h3>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartAdminDoughnutShift"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card-head">
                    <h3 class="chart-title"><i class="fa-solid fa-chart-pie"></i> Tỷ Lệ Trạng Thái Đơn Ứng Tuyển</h3>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartAdminDoughnutApp"></canvas>
                </div>
            </div>
        </div>

        {{-- TOP 5 ỨNG VIÊN XUẤT SẮC NHẤT TOÀN HỆ THỐNG --}}
        @include('partials.top_candidates_card', [
            'topCandidates' => $analytics['top_ung_vien'] ?? [],
            'cardTitle' => 'Top 5 Ứng Viên Xuất Sắc Nhất Toàn Hệ Thống',
            'tbodyId' => 'tbodyAdminTopCandidates',
        ])

        {{-- BẢNG 1: THEO THƯƠNG HIỆU --}}
        <div class="stats-table-card">
            <div class="table-card-head">
                <h3 class="chart-title"><i class="fa-solid fa-copyright"></i> Chi Phí Nhân Sự &amp; Tuyển Dụng Theo Thương Hiệu</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Thương Hiệu</th>
                            <th style="text-align: center;">Chi Nhánh</th>
                            <th style="text-align: center;">Số Ca Làm</th>
                            <th style="text-align: center;">Đã Nhận / Tổng Đơn</th>
                            <th style="text-align: center;">Tỷ Lệ Thành Công</th>
                            <th style="text-align: right;">Tổng Giờ Làm</th>
                            <th style="text-align: right;">Quỹ Lương (VNĐ)</th>
                            <th style="text-align: center;">Đánh Giá TB</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyBrandStats">
                        @forelse ($analytics['theo_thuong_hieu'] as $b)
                            <tr>
                                <td style="font-weight: 700; color: #0f172a;">{{ $b['ten_thuong_hieu'] }}</td>
                                <td style="text-align: center;">{{ $b['so_chi_nhanh'] }}</td>
                                <td style="text-align: center; font-weight: 700;">{{ $b['so_ca_lam'] }}</td>
                                <td style="text-align: center;">{{ $b['don_da_nhan'] }} / {{ $b['tong_don'] }}</td>
                                <td style="text-align: center;">
                                    <span class="badge-pill-stat" style="background: #dcfce7; color: #166534;">{{ $b['ty_le_thanh_cong'] }}%</span>
                                </td>
                                <td style="text-align: right; font-weight: 600;">{{ number_format($b['tong_gio_lam'], 1, ',', '.') }} h</td>
                                <td style="text-align: right; font-weight: 800; color: #b45309;">{{ number_format($b['quy_luong'], 0, ',', '.') }} đ</td>
                                <td style="text-align: center;">
                                    @if ($b['diem_tb'] !== null)
                                        <span class="badge-pill-stat" style="background: #fef3c7; color: #b45309;">{{ number_format($b['diem_tb'], 1) }} ⭐</span>
                                    @else
                                        <span style="color: #94a3b8;">Chưa có</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align: center; padding: 26px; color: #94a3b8;">Chưa có dữ liệu thương hiệu trong kỳ lọc.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- BẢNG 2: THEO CHI NHÁNH NHÀ HÀNG --}}
        <div class="stats-table-card">
            <div class="table-card-head">
                <h3 class="chart-title"><i class="fa-solid fa-store"></i> Chi Phí Nhân Sự &amp; Tuyển Dụng Theo Chi Nhánh Nhà Hàng</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Chi Nhánh Nhà Hàng</th>
                            <th>Thương Hiệu</th>
                            <th style="text-align: center;">Số Ca Làm</th>
                            <th style="text-align: center;">Đã Nhận / Tổng Đơn</th>
                            <th style="text-align: center;">Tỷ Lệ Thành Công</th>
                            <th style="text-align: right;">Tổng Giờ Làm</th>
                            <th style="text-align: right;">Quỹ Lương (VNĐ)</th>
                            <th style="text-align: center;">Đánh Giá TB</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyBranchStats">
                        @forelse ($analytics['theo_nha_hang'] as $r)
                            <tr>
                                <td style="font-weight: 700; color: #0f172a;">{{ $r['ten_nha_hang'] }}</td>
                                <td><span style="font-size: 12px; color: #475569;">{{ $r['ten_thuong_hieu'] }}</span></td>
                                <td style="text-align: center; font-weight: 700;">{{ $r['so_ca_lam'] }}</td>
                                <td style="text-align: center;">{{ $r['don_da_nhan'] }} / {{ $r['tong_don'] }}</td>
                                <td style="text-align: center;">
                                    <span class="badge-pill-stat" style="background: #e0f2fe; color: #0369a1;">{{ $r['ty_le_thanh_cong'] }}%</span>
                                </td>
                                <td style="text-align: right; font-weight: 600;">{{ number_format($r['tong_gio_lam'], 1, ',', '.') }} h</td>
                                <td style="text-align: right; font-weight: 800; color: #b45309;">{{ number_format($r['quy_luong'], 0, ',', '.') }} đ</td>
                                <td style="text-align: center;">
                                    @if ($r['diem_tb'] !== null)
                                        <span class="badge-pill-stat" style="background: #fef3c7; color: #b45309;">{{ number_format($r['diem_tb'], 1) }} ⭐</span>
                                    @else
                                        <span style="color: #94a3b8;">Chưa có</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align: center; padding: 26px; color: #94a3b8;">Chưa có dữ liệu chi nhánh trong kỳ lọc.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- BẢNG 3: BÁO CÁO VI PHẠM & TUÂN THỦ --}}
        <div class="stats-table-card">
            <div class="table-card-head">
                <h3 class="chart-title"><i class="fa-solid fa-shield-halved"></i> Báo Cáo Vi Phạm &amp; Tuân Thủ Theo Loại</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Loại Vi Phạm</th>
                            <th style="text-align: center;">Số Lượng Báo Cáo</th>
                            <th style="text-align: center;">Tỷ Lệ (%)</th>
                            <th style="text-align: center;">Chờ Xử Lý</th>
                            <th style="text-align: center;">Đã Xử Lý</th>
                            <th style="text-align: center;">Từ Chối</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyViolationStats">
                        @forelse ($analytics['vi_pham_theo_loai'] as $vp)
                            <tr>
                                <td style="font-weight: 700; color: #991b1b;">
                                    <i class="fa-solid fa-triangle-exclamation" style="color: #dc2626; margin-right: 6px;"></i>{{ $vp['ten_loai'] }}
                                </td>
                                <td style="text-align: center; font-weight: 800;">{{ $vp['so_luong'] }}</td>
                                <td style="text-align: center;">
                                    <span class="badge-pill-stat" style="background: #fee2e2; color: #b91c1c;">{{ $vp['ty_le'] }}%</span>
                                </td>
                                <td style="text-align: center; color: #d97706; font-weight: 700;">{{ $vp['cho_xu_ly'] }}</td>
                                <td style="text-align: center; color: #16a34a; font-weight: 700;">{{ $vp['da_xu_ly'] }}</td>
                                <td style="text-align: center; color: #64748b; font-weight: 700;">{{ $vp['tu_choi'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align: center; padding: 26px; color: #94a3b8;">Không ghi nhận báo cáo vi phạm nào trong kỳ lọc.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const initialAnalytics = @json($analytics);
    const indexUrl = "{{ route('admin.thong_ke.index') }}";
    const exportBaseUrl = "{{ route('admin.thong_ke.export') }}";

    const form = document.getElementById('adminAnalyticsFilterForm');
    const inputPreset = document.getElementById('inputPreset');
    const inputTuNgay = document.getElementById('inputTuNgay');
    const inputDenNgay = document.getElementById('inputDenNgay');
    const selectBrand = document.getElementById('selectBrand');
    const selectRestaurant = document.getElementById('selectRestaurant');
    const btnExport = document.getElementById('btnExportCsv');
    const statusBadge = document.getElementById('ajaxStatusBadge');
    const rootEl = document.getElementById('adminAnalyticsRoot');

    const fmtNum = (n) => new Intl.NumberFormat('vi-VN').format(Math.round(n || 0));
    const fmtDec = (n, d = 1) => Number(n || 0).toLocaleString('vi-VN', { minimumFractionDigits: d, maximumFractionDigits: d });
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    let charts = {};

    function initCharts(data) {
        if (typeof Chart === 'undefined') return;
        const c = data.charts || {};

        charts.barMonthly = new Chart(document.getElementById('chartAdminBarMonthly'), {
            type: 'bar',
            data: {
                labels: c.bar_monthly.labels || [],
                datasets: [
                    {
                        label: 'Chi phí lương ca duyệt (VNĐ)',
                        data: c.bar_monthly.chi_phi || [],
                        backgroundColor: 'rgba(245, 158, 11, 0.82)',
                        borderColor: '#d97706',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Số ca làm',
                        data: c.bar_monthly.so_ca || [],
                        backgroundColor: 'rgba(15, 23, 42, 0.78)',
                        borderColor: '#0f172a',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, position: 'left', title: { display: true, text: 'VNĐ' } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Số ca' }, ticks: { precision: 0 } }
                }
            }
        });

        charts.lineTrend = new Chart(document.getElementById('chartAdminLineTrend'), {
            type: 'line',
            data: {
                labels: c.line_applications_trend.labels || [],
                datasets: [{
                    label: 'Số đơn ứng tuyển',
                    data: c.line_applications_trend.values || [],
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.14)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#0284c7'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        charts.doughnutShift = new Chart(document.getElementById('chartAdminDoughnutShift'), {
            type: 'doughnut',
            data: {
                labels: c.doughnut_shift_status.labels || [],
                datasets: [{
                    data: c.doughnut_shift_status.values || [0, 0, 0],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        charts.doughnutApp = new Chart(document.getElementById('chartAdminDoughnutApp'), {
            type: 'doughnut',
            data: {
                labels: c.doughnut_application_status.labels || [],
                datasets: [{
                    data: c.doughnut_application_status.values || [0, 0, 0, 0],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    function updateCharts(data) {
        const c = data.charts || {};
        if (charts.barMonthly) {
            charts.barMonthly.data.labels = c.bar_monthly.labels || [];
            charts.barMonthly.data.datasets[0].data = c.bar_monthly.chi_phi || [];
            charts.barMonthly.data.datasets[1].data = c.bar_monthly.so_ca || [];
            charts.barMonthly.update();
        }
        if (charts.lineTrend) {
            charts.lineTrend.data.labels = c.line_applications_trend.labels || [];
            charts.lineTrend.data.datasets[0].data = c.line_applications_trend.values || [];
            charts.lineTrend.update();
        }
        if (charts.doughnutShift) {
            charts.doughnutShift.data.datasets[0].data = c.doughnut_shift_status.values || [0, 0, 0];
            charts.doughnutShift.update();
        }
        if (charts.doughnutApp) {
            charts.doughnutApp.data.datasets[0].data = c.doughnut_application_status.values || [0, 0, 0, 0];
            charts.doughnutApp.update();
        }
    }

    function renderAnalytics(payload) {
        const a = payload.analytics;
        const k = a.kpis;
        const r = payload.range;

        document.getElementById('activeRangeLabel').innerHTML = '<i class="fa-regular fa-calendar-check"></i> ' + esc(r.label);
        if (r.tu_ngay !== undefined) inputTuNgay.value = r.tu_ngay || '';
        if (r.den_ngay !== undefined) inputDenNgay.value = r.den_ngay || '';

        document.getElementById('kpiTongCaLam').textContent = fmtNum(k.tong_ca_lam);
        document.getElementById('kpiTongGioLam').textContent = fmtDec(k.tong_gio_lam, 1) + ' giờ làm đã duyệt';
        document.getElementById('kpiTongDon').textContent = fmtNum(k.tong_don_ung_tuyen);
        document.getElementById('kpiTyLeThanhCong').textContent = k.don_da_nhan + ' đơn đã nhận (' + k.ty_le_ung_tuyen_thanh_cong + '%)';
        document.getElementById('kpiChiPhiDaThanhToan').textContent = fmtNum(k.chi_phi_da_thanh_toan) + ' đ';
        document.getElementById('kpiQuyLuongChamCong').textContent = 'Quỹ lương ca duyệt: ' + fmtNum(k.tong_quy_luong_cham_cong) + ' đ';
        document.getElementById('kpiDiemDanhGiaTb').innerHTML = fmtDec(k.diem_danh_gia_tb, 1) + ' <span style="font-size: 14px; font-weight: 700;">/ 5.0 ⭐</span>';
        document.getElementById('kpiTongLuotDanhGia').textContent = k.tong_luot_danh_gia + ' lượt đánh giá • ' + k.tong_vi_pham + ' vi phạm';

        const brandBody = document.getElementById('tbodyBrandStats');
        brandBody.innerHTML = a.theo_thuong_hieu.length ? a.theo_thuong_hieu.map(b => `
            <tr>
                <td style="font-weight:700;color:#0f172a;">${esc(b.ten_thuong_hieu)}</td>
                <td style="text-align:center;">${b.so_chi_nhanh}</td>
                <td style="text-align:center;font-weight:700;">${b.so_ca_lam}</td>
                <td style="text-align:center;">${b.don_da_nhan} / ${b.tong_don}</td>
                <td style="text-align:center;"><span class="badge-pill-stat" style="background:#dcfce7;color:#166534;">${b.ty_le_thanh_cong}%</span></td>
                <td style="text-align:right;font-weight:600;">${fmtDec(b.tong_gio_lam, 1)} h</td>
                <td style="text-align:right;font-weight:800;color:#b45309;">${fmtNum(b.quy_luong)} đ</td>
                <td style="text-align:center;">${b.diem_tb !== null ? `<span class="badge-pill-stat" style="background:#fef3c7;color:#b45309;">${fmtDec(b.diem_tb, 1)} ⭐</span>` : '<span style="color:#94a3b8;">Chưa có</span>'}</td>
            </tr>
        `).join('') : '<tr><td colspan="8" style="text-align:center;padding:26px;color:#94a3b8;">Chưa có dữ liệu thương hiệu trong kỳ lọc.</td></tr>';

        const branchBody = document.getElementById('tbodyBranchStats');
        branchBody.innerHTML = a.theo_nha_hang.length ? a.theo_nha_hang.map(nh => `
            <tr>
                <td style="font-weight:700;color:#0f172a;">${esc(nh.ten_nha_hang)}</td>
                <td><span style="font-size:12px;color:#475569;">${esc(nh.ten_thuong_hieu)}</span></td>
                <td style="text-align:center;font-weight:700;">${nh.so_ca_lam}</td>
                <td style="text-align:center;">${nh.don_da_nhan} / ${nh.tong_don}</td>
                <td style="text-align:center;"><span class="badge-pill-stat" style="background:#e0f2fe;color:#0369a1;">${nh.ty_le_thanh_cong}%</span></td>
                <td style="text-align:right;font-weight:600;">${fmtDec(nh.tong_gio_lam, 1)} h</td>
                <td style="text-align:right;font-weight:800;color:#b45309;">${fmtNum(nh.quy_luong)} đ</td>
                <td style="text-align:center;">${nh.diem_tb !== null ? `<span class="badge-pill-stat" style="background:#fef3c7;color:#b45309;">${fmtDec(nh.diem_tb, 1)} ⭐</span>` : '<span style="color:#94a3b8;">Chưa có</span>'}</td>
            </tr>
        `).join('') : '<tr><td colspan="8" style="text-align:center;padding:26px;color:#94a3b8;">Chưa có dữ liệu chi nhánh trong kỳ lọc.</td></tr>';

        const vpBody = document.getElementById('tbodyViolationStats');
        vpBody.innerHTML = a.vi_pham_theo_loai.length ? a.vi_pham_theo_loai.map(vp => `
            <tr>
                <td style="font-weight:700;color:#991b1b;"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;margin-right:6px;"></i>${esc(vp.ten_loai)}</td>
                <td style="text-align:center;font-weight:800;">${vp.so_luong}</td>
                <td style="text-align:center;"><span class="badge-pill-stat" style="background:#fee2e2;color:#b91c1c;">${vp.ty_le}%</span></td>
                <td style="text-align:center;color:#d97706;font-weight:700;">${vp.cho_xu_ly}</td>
                <td style="text-align:center;color:#16a34a;font-weight:700;">${vp.da_xu_ly}</td>
                <td style="text-align:center;color:#64748b;font-weight:700;">${vp.tu_choi}</td>
            </tr>
        `).join('') : '<tr><td colspan="6" style="text-align:center;padding:26px;color:#94a3b8;">Không ghi nhận báo cáo vi phạm nào trong kỳ lọc.</td></tr>';

        if (typeof window.gsRenderTopCandidatesTable === 'function') {
            window.gsRenderTopCandidatesTable(document.getElementById('tbodyAdminTopCandidates'), a.top_ung_vien || []);
        }

        updateCharts(a);
    }

    function syncRestaurantOptions() {
        const chosenBrand = selectBrand.value;
        Array.from(selectRestaurant.options).forEach(opt => {
            if (!opt.value) return;
            const match = !chosenBrand || opt.dataset.brand === chosenBrand;
            opt.hidden = !match;
        });
        const currentOpt = selectRestaurant.selectedOptions[0];
        if (currentOpt && currentOpt.hidden) {
            selectRestaurant.value = '';
        }
    }

    function fetchUpdatedAnalytics() {
        const params = new URLSearchParams(new FormData(form));
        const qs = params.toString();
        btnExport.href = exportBaseUrl + (qs ? ('?' + qs) : '');
        window.history.replaceState({}, '', indexUrl + (qs ? ('?' + qs) : ''));

        statusBadge.style.display = 'inline-block';
        rootEl.classList.add('loading-overlay');

        fetch(indexUrl + (qs ? ('?' + qs) : ''), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(payload => {
            if (payload && payload.success) {
                renderAnalytics(payload);
            }
        })
        .catch(() => form.submit())
        .finally(() => {
            statusBadge.style.display = 'none';
            rootEl.classList.remove('loading-overlay');
        });
    }

    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            inputPreset.value = this.dataset.preset;
            if (this.dataset.preset !== 'tuy_chon') {
                inputTuNgay.value = '';
                inputDenNgay.value = '';
            }
            fetchUpdatedAnalytics();
        });
    });

    [inputTuNgay, inputDenNgay].forEach(inp => {
        inp.addEventListener('change', function() {
            document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
            inputPreset.value = 'tuy_chon';
        });
    });

    selectBrand.addEventListener('change', function() {
        syncRestaurantOptions();
        fetchUpdatedAnalytics();
    });

    selectRestaurant.addEventListener('change', fetchUpdatedAnalytics);

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchUpdatedAnalytics();
    });

    document.getElementById('btnResetFilters').addEventListener('click', function() {
        document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.preset-btn[data-preset="tat_ca"]').classList.add('active');
        inputPreset.value = 'tat_ca';
        inputTuNgay.value = '';
        inputDenNgay.value = '';
        selectBrand.value = '';
        selectRestaurant.value = '';
        syncRestaurantOptions();
        fetchUpdatedAnalytics();
    });

    syncRestaurantOptions();
    initCharts(initialAnalytics);
});
</script>
@endsection
