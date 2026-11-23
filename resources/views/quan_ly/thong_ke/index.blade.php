@extends('layouts.app')

@section('title', 'Thống Kê Chi Nhánh: ' . ($nhaHang->ten_nha_hang ?? 'Nhà Hàng') . ' - Golden Shift')

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
    font-size: 1.7rem;
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
    min-width: 150px;
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
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
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
}
.perf-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
@media (max-width: 720px) {
    .perf-strip { grid-template-columns: 1fr; }
}
.perf-chip-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}
@media (max-width: 960px) {
    .charts-grid { grid-template-columns: 1fr; }
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
.table-card-head {
    padding: 16px 22px;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
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
    margin-top: 12px;
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
.stats-table tbody td,
.stats-table tfoot td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}
.stats-table tfoot td {
    background: #fffbeb;
    font-weight: 800;
    border-top: 2px solid #fde68a;
}
.rank-badge {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12.5px;
    font-weight: 800;
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
    $perf = $analytics['hieu_suat_ca'];
    $brandName = $nhaHang->thuongHieu ? $nhaHang->thuongHieu->ten_thuong_hieu : 'Golden Shift';
@endphp

<section class="analytics-hero">
    <div class="gs-container">
        <div style="font-size: 13px; color: #94a3b8; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('ca-lam-viec.index') }}" style="color: #cbd5e1; text-decoration: none;"><i class="fa-solid fa-calendar-days"></i> Quản lý ca làm</a>
            <span>/</span>
            <span style="color: #f59e0b; font-weight: 700;">Thống kê chi nhánh</span>
        </div>

        <div class="analytics-hero-inner">
            <div>
                <span style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.4); padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-store"></i> {{ $brandName }}
                </span>
                <h1 class="analytics-hero-title">
                    Thống Kê Hiệu Suất &amp; Quỹ Lương: <span>{{ $nhaHang->ten_nha_hang }}</span>
                </h1>
                <p class="analytics-hero-sub">
                    Phân tích tỷ lệ đúng giờ/đi muộn, danh sách ứng viên xuất sắc và chi phí quỹ lương theo tuần/tháng của chi nhánh.
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span id="activeRangeLabel" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.18); color: #fde68a; padding: 7px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 700;">
                    <i class="fa-regular fa-calendar-check"></i> {{ $range['label'] }}
                </span>

                <a id="btnExportManagerCsv"
                   href="{{ route('quan_ly.thong_ke.export', request()->query()) }}"
                   class="btn btn-success btn-sm rounded-pill"
                   style="font-weight: 700; padding: 8px 18px;">
                    <i class="fa-solid fa-file-excel"></i> Xuất Báo cáo Excel / CSV
                </a>
            </div>
        </div>
    </div>
</section>

<section style="padding: 26px 0 60px; background: #f8fafc;">
    <div class="gs-container" id="managerAnalyticsRoot">

        {{-- BỘ LỌC TƯƠNG TÁC --}}
        <form id="managerAnalyticsFilterForm" method="GET" action="{{ route('quan_ly.thong_ke.index') }}" class="analytics-filter-bar">
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
                    <i class="fa-solid fa-spinner fa-spin text-gold"></i> Đang cập nhật dữ liệu...
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
                <div class="filter-field" style="min-width: 190px;">
                    <label for="selectPeriodGroup">Phân nhóm quỹ lương</label>
                    <select name="nhom_ky" id="selectPeriodGroup">
                        <option value="thang" {{ $periodGroup === 'thang' ? 'selected' : '' }}>Theo Tháng / Kỳ lương</option>
                        <option value="tuan" {{ $periodGroup === 'tuan' ? 'selected' : '' }}>Theo Tuần</option>
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

        {{-- 4 KPI CARDS CHI NHÁNH --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon gold"><i class="fa-solid fa-briefcase"></i></div>
                <div>
                    <div class="kpi-label">Tổng ca làm chi nhánh</div>
                    <div class="kpi-value" id="kpiBranchShifts">{{ number_format($kpis['tong_ca_lam'], 0, ',', '.') }}</div>
                    <div class="kpi-sub" id="kpiBranchOnTimeRate">Tỷ lệ đúng giờ: {{ $kpis['ty_le_dung_gio'] }}%</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon blue"><i class="fa-solid fa-hourglass-half"></i></div>
                <div>
                    <div class="kpi-label">Tổng giờ công đã duyệt</div>
                    <div class="kpi-value" id="kpiBranchHours">{{ number_format($kpis['tong_gio_lam'], 1, ',', '.') }} h</div>
                    <div class="kpi-sub" id="kpiBranchAppRate">{{ $kpis['don_da_nhan'] }}/{{ $kpis['tong_don_ung_tuyen'] }} đơn đã nhận ({{ $kpis['ty_le_duyet'] }}%)</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon green"><i class="fa-solid fa-coins"></i></div>
                <div>
                    <div class="kpi-label">Tổng quỹ lương ca làm</div>
                    <div class="kpi-value" id="kpiBranchPayroll" style="color: #15803d;">{{ number_format($kpis['tong_quy_luong'], 0, ',', '.') }} đ</div>
                    <div class="kpi-sub">Tính trên công đã xác nhận</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon amber"><i class="fa-solid fa-star"></i></div>
                <div>
                    <div class="kpi-label">Điểm đánh giá chi nhánh</div>
                    <div class="kpi-value" id="kpiBranchRating" style="color: #b45309;">{{ number_format($kpis['diem_danh_gia_tb'], 1) }} <span style="font-size: 14px;">/ 5.0 ⭐</span></div>
                    <div class="kpi-sub" id="kpiBranchRatingCount">{{ $kpis['tong_luot_danh_gia'] }} lượt đánh giá từ ứng viên</div>
                </div>
            </div>
        </div>

        {{-- CHỈ SỐ HIỆU SUẤT CA LÀM --}}
        <div class="perf-strip">
            <div class="perf-chip-card" style="border-left: 4px solid #10b981;">
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #047857;">HOÀN THÀNH ĐÚNG GIỜ</div>
                    <div style="font-size: 20px; font-weight: 900; color: #0f172a;" id="perfDungGio">{{ $perf['dung_gio'] }} lượt ({{ $perf['ty_le_dung_gio'] }}%)</div>
                </div>
                <i class="fa-solid fa-circle-check" style="font-size: 26px; color: #10b981;"></i>
            </div>
            <div class="perf-chip-card" style="border-left: 4px solid #f59e0b;">
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #b45309;">ĐI MUỘN</div>
                    <div style="font-size: 20px; font-weight: 900; color: #0f172a;" id="perfDiMuon">{{ $perf['di_muon'] }} lượt ({{ $perf['ty_le_di_muon'] }}%)</div>
                </div>
                <i class="fa-solid fa-clock" style="font-size: 26px; color: #f59e0b;"></i>
            </div>
            <div class="perf-chip-card" style="border-left: 4px solid #ef4444;">
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #b91c1c;">HỦY CA / BÙNG CA</div>
                    <div style="font-size: 20px; font-weight: 900; color: #0f172a;" id="perfHuyBung">{{ $perf['huy_bung_ca'] }} lượt ({{ $perf['ty_le_huy_bung_ca'] }}%)</div>
                </div>
                <i class="fa-solid fa-ban" style="font-size: 26px; color: #ef4444;"></i>
            </div>
        </div>

        {{-- BIỂU ĐỒ CHART.JS --}}
        <div class="charts-grid">
            <div class="chart-card">
                <h3 class="chart-title"><i class="fa-solid fa-chart-bar"></i> Quỹ Lương &amp; Số Ca Làm Theo Kỳ</h3>
                <div class="chart-canvas-wrap">
                    <canvas id="chartManagerBarPayroll"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title"><i class="fa-solid fa-chart-line"></i> Xu Hướng Đơn Ứng Tuyển Theo Thời Gian</h3>
                <div class="chart-canvas-wrap">
                    <canvas id="chartManagerLineTrend"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title"><i class="fa-solid fa-chart-pie"></i> Tỷ Lệ Hiệu Suất Ca Làm Việc</h3>
                <div class="chart-canvas-wrap">
                    <canvas id="chartManagerDoughnutPerf"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title"><i class="fa-solid fa-file-circle-check"></i> Tỷ Lệ Trạng Thái Đơn Ứng Tuyển</h3>
                <div class="chart-canvas-wrap">
                    <canvas id="chartManagerDoughnutApp"></canvas>
                </div>
            </div>
        </div>

        {{-- BẢNG QUỸ LƯƠNG THEO TUẦN / THÁNG --}}
        <div class="stats-table-card">
            <div class="table-card-head">
                <h3 class="chart-title">
                    <i class="fa-solid fa-wallet"></i> Báo Cáo Quỹ Lương Ca Làm
                    <span id="payrollPeriodBadge" style="font-size: 12px; color: #b45309; background: #fef3c7; padding: 2px 10px; border-radius: 999px;">
                        {{ $periodGroup === 'tuan' ? 'Theo Tuần' : 'Theo Tháng' }}
                    </span>
                </h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Kỳ Lương</th>
                            <th style="text-align: center;">Số Ca Đã Duyệt</th>
                            <th style="text-align: right;">Tổng Giờ Làm</th>
                            <th style="text-align: right;">Chi Phí Lương (VNĐ)</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPayrollPeriods">
                        @forelse ($analytics['quy_luong_theo_ky'] as $row)
                            <tr>
                                <td style="font-weight: 700; color: #0f172a;">{{ $row['ky_label'] }}</td>
                                <td style="text-align: center; font-weight: 700;">{{ $row['so_ca'] }}</td>
                                <td style="text-align: right; font-weight: 600;">{{ number_format($row['tong_gio'], 1, ',', '.') }} h</td>
                                <td style="text-align: right; font-weight: 800; color: #15803d;">{{ number_format($row['chi_phi_luong'], 0, ',', '.') }} đ</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align: center; padding: 26px; color: #94a3b8;">Chưa có dữ liệu lương ca làm trong kỳ lọc.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot id="tfootPayrollTotals">
                        @if (count($analytics['quy_luong_theo_ky']) > 0)
                            <tr>
                                <td>TỔNG CỘNG</td>
                                <td style="text-align: center;">{{ collect($analytics['quy_luong_theo_ky'])->sum('so_ca') }} ca</td>
                                <td style="text-align: right;">{{ number_format($kpis['tong_gio_lam'], 1, ',', '.') }} h</td>
                                <td style="text-align: right; color: #b45309;">{{ number_format($kpis['tong_quy_luong'], 0, ',', '.') }} đ</td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- BẢNG TOP 5 ỨNG VIÊN XUẤT SẮC NHẤT --}}
        @include('partials.top_candidates_card', [
            'topCandidates' => $analytics['top_ung_vien'] ?? [],
            'cardTitle' => 'Top 5 Ứng Viên Xuất Sắc Nhất Chi Nhánh',
            'tbodyId' => 'tbodyTopCandidates',
        ])

    </div>
</section>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const initialAnalytics = @json($analytics);
    const indexUrl = "{{ route('quan_ly.thong_ke.index') }}";
    const exportBaseUrl = "{{ route('quan_ly.thong_ke.export') }}";

    const form = document.getElementById('managerAnalyticsFilterForm');
    const inputPreset = document.getElementById('inputPreset');
    const inputTuNgay = document.getElementById('inputTuNgay');
    const inputDenNgay = document.getElementById('inputDenNgay');
    const selectPeriodGroup = document.getElementById('selectPeriodGroup');
    const btnExport = document.getElementById('btnExportManagerCsv');
    const statusBadge = document.getElementById('ajaxStatusBadge');
    const rootEl = document.getElementById('managerAnalyticsRoot');

    const fmtNum = (n) => new Intl.NumberFormat('vi-VN').format(Math.round(n || 0));
    const fmtDec = (n, d = 1) => Number(n || 0).toLocaleString('vi-VN', { minimumFractionDigits: d, maximumFractionDigits: d });
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    let charts = {};

    function initCharts(data) {
        if (typeof Chart === 'undefined') return;
        const c = data.charts || {};

        charts.barPayroll = new Chart(document.getElementById('chartManagerBarPayroll'), {
            type: 'bar',
            data: {
                labels: c.bar_payroll.labels || [],
                datasets: [
                    {
                        label: 'Quỹ lương (VNĐ)',
                        data: c.bar_payroll.chi_phi || [],
                        backgroundColor: 'rgba(245, 158, 11, 0.82)',
                        borderColor: '#d97706',
                        borderWidth: 1,
                        borderRadius: 6,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Số ca đã duyệt',
                        data: c.bar_payroll.so_ca || [],
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

        charts.lineTrend = new Chart(document.getElementById('chartManagerLineTrend'), {
            type: 'line',
            data: {
                labels: c.line_applications_trend.labels || [],
                datasets: [{
                    label: 'Đơn ứng tuyển nộp mới',
                    data: c.line_applications_trend.values || [],
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.14)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#0284c7'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        charts.doughnutPerf = new Chart(document.getElementById('chartManagerDoughnutPerf'), {
            type: 'doughnut',
            data: {
                labels: c.doughnut_shift_performance.labels || [],
                datasets: [{
                    data: c.doughnut_shift_performance.values || [0, 0, 0],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        charts.doughnutApp = new Chart(document.getElementById('chartManagerDoughnutApp'), {
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
        if (charts.barPayroll) {
            charts.barPayroll.data.labels = c.bar_payroll.labels || [];
            charts.barPayroll.data.datasets[0].data = c.bar_payroll.chi_phi || [];
            charts.barPayroll.data.datasets[1].data = c.bar_payroll.so_ca || [];
            charts.barPayroll.update();
        }
        if (charts.lineTrend) {
            charts.lineTrend.data.labels = c.line_applications_trend.labels || [];
            charts.lineTrend.data.datasets[0].data = c.line_applications_trend.values || [];
            charts.lineTrend.update();
        }
        if (charts.doughnutPerf) {
            charts.doughnutPerf.data.datasets[0].data = c.doughnut_shift_performance.values || [0, 0, 0];
            charts.doughnutPerf.update();
        }
        if (charts.doughnutApp) {
            charts.doughnutApp.data.datasets[0].data = c.doughnut_application_status.values || [0, 0, 0, 0];
            charts.doughnutApp.update();
        }
    }

    function renderAnalytics(payload) {
        const a = payload.analytics;
        const k = a.kpis;
        const p = a.hieu_suat_ca;
        const r = payload.range;

        document.getElementById('activeRangeLabel').innerHTML = '<i class="fa-regular fa-calendar-check"></i> ' + esc(r.label);
        document.getElementById('payrollPeriodBadge').textContent = payload.nhom_ky === 'tuan' ? 'Theo Tuần' : 'Theo Tháng';
        if (r.tu_ngay !== undefined) inputTuNgay.value = r.tu_ngay || '';
        if (r.den_ngay !== undefined) inputDenNgay.value = r.den_ngay || '';

        document.getElementById('kpiBranchShifts').textContent = fmtNum(k.tong_ca_lam);
        document.getElementById('kpiBranchOnTimeRate').textContent = 'Tỷ lệ đúng giờ: ' + k.ty_le_dung_gio + '%';
        document.getElementById('kpiBranchHours').textContent = fmtDec(k.tong_gio_lam, 1) + ' h';
        document.getElementById('kpiBranchAppRate').textContent = k.don_da_nhan + '/' + k.tong_don_ung_tuyen + ' đơn đã nhận (' + k.ty_le_duyet + '%)';
        document.getElementById('kpiBranchPayroll').textContent = fmtNum(k.tong_quy_luong) + ' đ';
        document.getElementById('kpiBranchRating').innerHTML = fmtDec(k.diem_danh_gia_tb, 1) + ' <span style="font-size:14px;">/ 5.0 ⭐</span>';
        document.getElementById('kpiBranchRatingCount').textContent = k.tong_luot_danh_gia + ' lượt đánh giá từ ứng viên';

        document.getElementById('perfDungGio').textContent = p.dung_gio + ' lượt (' + p.ty_le_dung_gio + '%)';
        document.getElementById('perfDiMuon').textContent = p.di_muon + ' lượt (' + p.ty_le_di_muon + '%)';
        document.getElementById('perfHuyBung').textContent = p.huy_bung_ca + ' lượt (' + p.ty_le_huy_bung_ca + '%)';

        const payrollBody = document.getElementById('tbodyPayrollPeriods');
        const payrollFoot = document.getElementById('tfootPayrollTotals');
        if (a.quy_luong_theo_ky.length) {
            let totalShifts = 0;
            payrollBody.innerHTML = a.quy_luong_theo_ky.map(row => {
                totalShifts += Number(row.so_ca || 0);
                return `
                    <tr>
                        <td style="font-weight:700;color:#0f172a;">${esc(row.ky_label)}</td>
                        <td style="text-align:center;font-weight:700;">${row.so_ca}</td>
                        <td style="text-align:right;font-weight:600;">${fmtDec(row.tong_gio, 1)} h</td>
                        <td style="text-align:right;font-weight:800;color:#15803d;">${fmtNum(row.chi_phi_luong)} đ</td>
                    </tr>
                `;
            }).join('');
            payrollFoot.innerHTML = `
                <tr>
                    <td>TỔNG CỘNG</td>
                    <td style="text-align:center;">${totalShifts} ca</td>
                    <td style="text-align:right;">${fmtDec(k.tong_gio_lam, 1)} h</td>
                    <td style="text-align:right;color:#b45309;">${fmtNum(k.tong_quy_luong)} đ</td>
                </tr>
            `;
        } else {
            payrollBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:26px;color:#94a3b8;">Chưa có dữ liệu lương ca làm trong kỳ lọc.</td></tr>';
            payrollFoot.innerHTML = '';
        }

        if (typeof window.gsRenderTopCandidatesTable === 'function') {
            window.gsRenderTopCandidatesTable(document.getElementById('tbodyTopCandidates'), a.top_ung_vien || []);
        }

        updateCharts(a);
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

    selectPeriodGroup.addEventListener('change', fetchUpdatedAnalytics);

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
        selectPeriodGroup.value = 'thang';
        fetchUpdatedAnalytics();
    });

    initCharts(initialAnalytics);
});
</script>
@endsection
