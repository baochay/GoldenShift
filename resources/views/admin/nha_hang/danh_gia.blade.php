@extends('layouts.app')

@section('title', 'Danh Sách Đánh Giá Nhà Hàng: ' . ($nhaHang->ten_nha_hang ?? 'Chi Nhánh') . ' - Golden Shift Admin')

@section('styles')
<style>
.reviews-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0c1a30 100%);
    padding: 2.2rem 0 1.9rem;
    border-bottom: 3px solid #b8973a;
    position: relative;
    overflow: hidden;
}
.reviews-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 80% 45%, rgba(245, 158, 11, 0.1) 0%, transparent 60%);
    pointer-events: none;
}
.reviews-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #94a3b8;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.reviews-breadcrumb a {
    color: #cbd5e1;
    text-decoration: none;
}
.reviews-breadcrumb a:hover {
    color: #f59e0b;
}
.reviews-hero-inner {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.reviews-hero-title {
    color: #ffffff !important;
    font-size: 1.65rem;
    font-weight: 800;
    margin: 8px 0 10px;
    line-height: 1.3;
}
.reviews-hero-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 13px;
    color: #cbd5e1;
}
.reviews-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.14);
    padding: 4px 12px;
    border-radius: 999px;
    color: #e2e8f0;
}
.reviews-meta-pill i {
    color: #f59e0b;
}
.btn-back-restaurants {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.45);
    color: #fde68a !important;
    padding: 9px 18px;
    border-radius: 999px;
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-back-restaurants:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: #0f172a !important;
}

.reviews-overview-card {
    background: linear-gradient(135deg, #fffbeb 0%, #fef9c3 35%, #ffffff 100%);
    border: 1px solid #fde68a;
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 32px;
    flex-wrap: wrap;
    box-shadow: 0 4px 18px rgba(180, 83, 9, 0.07);
}
.overview-score-box {
    text-align: center;
    min-width: 170px;
    padding-right: 28px;
    border-right: 1px solid rgba(217, 119, 6, 0.25);
}
@media (max-width: 720px) {
    .overview-score-box {
        width: 100%;
        padding-right: 0;
        padding-bottom: 16px;
        border-right: none;
        border-bottom: 1px solid rgba(217, 119, 6, 0.25);
    }
}
.overview-score-num {
    font-size: 48px;
    font-weight: 900;
    color: #b45309;
    line-height: 1;
}
.overview-score-max {
    font-size: 18px;
    font-weight: 700;
    color: #92400e;
}
.overview-bars {
    flex: 1;
    min-width: 260px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.star-bar-row {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    padding: 3px 8px;
    border-radius: 8px;
    transition: background 0.15s;
}
.star-bar-row:hover,
.star-bar-row.active {
    background: rgba(245, 158, 11, 0.14);
}
.star-bar-label {
    width: 60px;
    font-size: 13px;
    font-weight: 700;
    color: #78350f;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}
.star-bar-track {
    flex: 1;
    height: 9px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}
.star-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #f59e0b, #d97706);
    border-radius: 999px;
}
.star-bar-count {
    width: 78px;
    text-align: right;
    font-size: 12.5px;
    font-weight: 700;
    color: #475569;
    white-space: nowrap;
}

.filter-sort-bar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.star-quick-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}
.star-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 13px;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #475569;
    transition: all 0.15s;
}
.star-pill:hover {
    border-color: #f59e0b;
    color: #b45309;
    background: #fffbeb;
}
.star-pill.active {
    background: #f59e0b;
    border-color: #f59e0b;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.28);
}
.filter-selects {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}
.filter-selects select {
    height: 38px;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 0 12px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    cursor: pointer;
}
.filter-selects select:focus {
    border-color: #f59e0b;
}

.review-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px 22px;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}
.review-card:hover {
    border-color: #f59e0b;
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.09);
}
.review-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}
.candidate-box {
    display: flex;
    align-items: center;
    gap: 12px;
}
.candidate-avatar-img {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fde68a;
    flex-shrink: 0;
}
.candidate-avatar-fallback {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #b45309;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 16px;
    border: 1px solid #fcd34d;
    flex-shrink: 0;
}
.review-comment-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 13.5px;
    color: #334155;
    line-height: 1.55;
    margin-bottom: 12px;
}
.review-shift-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding-top: 10px;
    border-top: 1px dashed #e2e8f0;
    font-size: 12.5px;
    color: #64748b;
}
.shift-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    color: #0369a1;
    border: 1px solid #bae6fd;
    padding: 4px 11px;
    border-radius: 999px;
    font-weight: 600;
}
.completed-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
    padding: 4px 11px;
    border-radius: 999px;
    font-weight: 600;
}
</style>
@endsection

@section('content')
@php
    $fullAddr = trim(implode(', ', array_filter([
        $nhaHang->dia_chi_chi_tiet ?? '',
        $nhaHang->phuong_xa ?? '',
        $nhaHang->quan_huyen ?? '',
        $nhaHang->tinh_thanh_pho ?? '',
    ])));
    $brandName = $nhaHang->thuongHieu ? $nhaHang->thuongHieu->ten_thuong_hieu : '—';
    $managerName = $nhaHang->quanLy ? $nhaHang->quanLy->ho_ten : 'Chưa phân công';
@endphp

<section class="reviews-hero">
    <div class="gs-container">
        <div class="reviews-breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ route('admin.categories.index', ['tab' => 'nha_hang']) }}">Thương hiệu &amp; Nhà hàng</a>
            <span>/</span>
            <span style="color: #f59e0b; font-weight: 700;">Đánh giá nhà hàng</span>
        </div>

        <div class="reviews-hero-inner">
            <div style="flex: 1; min-width: 280px;">
                <span style="background: rgba(245, 158, 11, 0.18); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.4); padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-star"></i> ĐÁNH GIÁ &amp; PHẢN HỒI CHI NHÁNH
                </span>
                <h1 class="reviews-hero-title">
                    Danh sách đánh giá của nhà hàng:
                    <span style="color: #f59e0b;">{{ $nhaHang->ten_nha_hang }}</span>
                </h1>
                <div class="reviews-hero-meta">
                    <span class="reviews-meta-pill">
                        <i class="fa-solid fa-copyright"></i> {{ $brandName }}
                    </span>
                    @if (! empty($fullAddr))
                    <span class="reviews-meta-pill">
                        <i class="fa-solid fa-location-dot"></i> {{ $fullAddr }}
                    </span>
                    @endif
                    @if (! empty($nhaHang->so_dien_thoai_lien_he))
                    <span class="reviews-meta-pill">
                        <i class="fa-solid fa-phone"></i> {{ $nhaHang->so_dien_thoai_lien_he }}
                    </span>
                    @endif
                    <span class="reviews-meta-pill">
                        <i class="fa-solid fa-user-tie"></i> QL: {{ $managerName }}
                    </span>
                </div>
            </div>

            <div>
                <a href="{{ route('admin.categories.index', ['tab' => 'nha_hang']) }}" class="btn-back-restaurants">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách chi nhánh
                </a>
            </div>
        </div>
    </div>
</section>

<section style="padding: 28px 0 60px; background: #f8fafc;">
    <div class="gs-container">

        {{-- THẺ TỔNG QUAN ĐIỂM ĐÁNH GIÁ --}}
        <div class="reviews-overview-card">
            <div class="overview-score-box">
                <div style="font-size: 12px; font-weight: 800; color: #92400e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    Điểm Trung Bình
                </div>
                <div>
                    <span class="overview-score-num">{{ number_format($diemTrungBinh, 1) }}</span>
                    <span class="overview-score-max">/ 5.0</span>
                </div>
                <div style="font-size: 18px; margin: 8px 0 6px;">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($tongLuot > 0 && $diemTrungBinh >= $i)
                            <i class="fa-solid fa-star" style="color: #f59e0b;"></i>
                        @elseif ($tongLuot > 0 && $diemTrungBinh >= ($i - 0.5))
                            <i class="fa-solid fa-star-half-stroke" style="color: #f59e0b;"></i>
                        @else
                            <i class="fa-solid fa-star" style="color: #cbd5e1;"></i>
                        @endif
                    @endfor
                </div>
                <div style="font-size: 13px; font-weight: 700; color: #78350f;">
                    {{ $tongLuot }} lượt đánh giá
                </div>
            </div>

            <div class="overview-bars">
                @foreach ([5, 4, 3, 2, 1] as $starLevel)
                    @php
                        $countForStar = $starCounts[$starLevel] ?? 0;
                        $percent = $tongLuot > 0 ? round(($countForStar / $tongLuot) * 100) : 0;
                        $isRowActive = ($soSao === (string) $starLevel);
                    @endphp
                    <a href="{{ route('admin.nha-hang.danh-gia', ['ma_nha_hang' => $nhaHang->ma_nha_hang, 'so_sao' => $isRowActive ? '' : $starLevel, 'sort' => $sort]) }}"
                       class="star-bar-row {{ $isRowActive ? 'active' : '' }}"
                       title="Lọc đánh giá {{ $starLevel }} sao">
                        <span class="star-bar-label">
                            {{ $starLevel }} <i class="fa-solid fa-star" style="color: #f59e0b;"></i>
                        </span>
                        <div class="star-bar-track">
                            <div class="star-bar-fill" style="width: {{ $percent }}%;"></div>
                        </div>
                        <span class="star-bar-count">{{ $countForStar }} lượt ({{ $percent }}%)</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- THANH BỘ LỌC & SẮP XẾP THEO SỐ SAO --}}
        <form method="GET" action="{{ route('admin.nha-hang.danh-gia', $nhaHang->ma_nha_hang) }}" class="filter-sort-bar">
            <div class="star-quick-pills">
                <a href="{{ route('admin.nha-hang.danh-gia', ['ma_nha_hang' => $nhaHang->ma_nha_hang, 'sort' => $sort]) }}"
                   class="star-pill {{ $soSao === '' ? 'active' : '' }}">
                    Tất cả ({{ $tongLuot }})
                </a>
                @foreach ([5, 4, 3, 2, 1] as $starLevel)
                    <a href="{{ route('admin.nha-hang.danh-gia', ['ma_nha_hang' => $nhaHang->ma_nha_hang, 'so_sao' => $starLevel, 'sort' => $sort]) }}"
                       class="star-pill {{ $soSao === (string) $starLevel ? 'active' : '' }}">
                        {{ $starLevel }} <i class="fa-solid fa-star" style="color: {{ $soSao === (string) $starLevel ? '#ffffff' : '#f59e0b' }};"></i>
                        ({{ $starCounts[$starLevel] ?? 0 }})
                    </a>
                @endforeach
            </div>

            <div class="filter-selects">
                <label for="filterSoSao" style="font-size: 12.5px; font-weight: 700; color: #475569;">Lọc sao:</label>
                <select name="so_sao" id="filterSoSao" onchange="this.form.submit()">
                    <option value="">Tất cả số sao ({{ $tongLuot }})</option>
                    @foreach ([5, 4, 3, 2, 1] as $starLevel)
                        <option value="{{ $starLevel }}" {{ $soSao === (string) $starLevel ? 'selected' : '' }}>
                            {{ $starLevel }} sao ({{ $starCounts[$starLevel] ?? 0 }})
                        </option>
                    @endforeach
                </select>

                <label for="sortDanhGia" style="font-size: 12.5px; font-weight: 700; color: #475569;">Sắp xếp:</label>
                <select name="sort" id="sortDanhGia" onchange="this.form.submit()">
                    <option value="all" {{ $sort === 'all' ? 'selected' : '' }}>Tất cả đánh giá (Mới nhất)</option>
                    <option value="cao_den_thap" {{ $sort === 'cao_den_thap' ? 'selected' : '' }}>Sắp xếp: Cao đến Thấp (5 ★ → 1 ★)</option>
                    <option value="thap_den_cao" {{ $sort === 'thap_den_cao' ? 'selected' : '' }}>Sắp xếp: Thấp đến Cao (1 ★ → 5 ★)</option>
                </select>

                @if ($sort !== 'all' || $soSao !== '')
                    <a href="{{ route('admin.nha-hang.danh-gia', $nhaHang->ma_nha_hang) }}"
                       class="btn btn-outline btn-sm rounded-pill"
                       style="font-weight: 600;">
                        <i class="fa-solid fa-xmark"></i> Đặt lại
                    </a>
                @endif
            </div>
        </form>

        {{-- DANH SÁCH ĐÁNH GIÁ CHI TIẾT --}}
        @if ($danhGiaList->isEmpty())
            <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 54px 20px; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #fffbeb; color: #f59e0b; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 12px; border: 1px solid #fde68a;">
                    <i class="fa-regular fa-star"></i>
                </div>
                <h3 style="margin: 0 0 6px; font-size: 17px; font-weight: 800; color: #1e293b;">
                    {{ $tongLuot === 0 ? 'Nhà hàng chưa có đánh giá nào' : 'Không có đánh giá phù hợp với bộ lọc' }}
                </h3>
                <p style="margin: 0; font-size: 13.5px; color: #64748b;">
                    {{ $tongLuot === 0 ? 'Khi ứng viên hoàn thành ca làm việc và gửi đánh giá cho chi nhánh này, thông tin sẽ hiển thị tại đây.' : 'Thử chọn mức sao khác hoặc đặt lại bộ lọc để xem toàn bộ nhận xét.' }}
                </p>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @foreach ($danhGiaList as $dg)
                    @php
                        $ungVien = $dg->nguoiDanhGia;
                        $tenUngVien = $ungVien ? $ungVien->ho_ten : 'Ứng viên';
                        $avatarPath = $ungVien ? ($ungVien->anh_dai_dien ?? '') : '';
                        $hasAvatar = ! empty($avatarPath) && file_exists(public_path($avatarPath));
                        $initial = mb_strtoupper(mb_substr($tenUngVien, 0, 1));

                        $ca = $dg->caLamViec;
                        $chamCong = $ca && $ca->relationLoaded('lichSuChamCong')
                            ? $ca->lichSuChamCong->firstWhere('ma_nguoi_dung', $dg->ma_nguoi_danh_gia)
                            : null;

                        $ngayHoanThanh = '—';
                        if ($chamCong && $chamCong->thoi_gian_ra_thuc_te) {
                            $ngayHoanThanh = \Carbon\Carbon::parse($chamCong->thoi_gian_ra_thuc_te)->format('H:i - d/m/Y');
                        } elseif ($ca && $ca->ngay_lam_viec) {
                            $ngayHoanThanh = date('d/m/Y', strtotime($ca->ngay_lam_viec));
                        }
                    @endphp

                    <div class="review-card">
                        <div class="review-card-top">
                            <div class="candidate-box">
                                @if ($hasAvatar)
                                    <img src="{{ asset($avatarPath) }}" alt="{{ $tenUngVien }}" class="candidate-avatar-img">
                                @else
                                    <div class="candidate-avatar-fallback">{{ $initial }}</div>
                                @endif
                                <div>
                                    <div style="font-size: 15px; font-weight: 800; color: #1e293b;">
                                        {{ $tenUngVien }}
                                    </div>
                                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                        @if ($ungVien && ! empty($ungVien->email))
                                            <span><i class="fa-regular fa-envelope"></i> {{ $ungVien->email }}</span>
                                        @endif
                                        @if ($ungVien && ! empty($ungVien->so_dien_thoai))
                                            <span style="margin-left: 8px;"><i class="fa-solid fa-phone"></i> {{ $ungVien->so_dien_thoai }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 6px; background: #fffbeb; border: 1px solid #fde68a; padding: 4px 12px; border-radius: 999px;">
                                    <span>
                                        @for ($s = 1; $s <= 5; $s++)
                                            <i class="fa-solid fa-star" style="color: {{ $s <= $dg->so_sao ? '#f59e0b' : '#cbd5e1' }}; font-size: 13px;"></i>
                                        @endfor
                                    </span>
                                    <span style="font-size: 12.5px; font-weight: 800; color: #b45309;">{{ $dg->so_sao }}/5</span>
                                </div>
                                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 5px;">
                                    <i class="fa-regular fa-paper-plane"></i>
                                    Ngày gửi: {{ $dg->ngay_danh_gia ? $dg->ngay_danh_gia->format('H:i - d/m/Y') : '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="review-comment-box">
                            @if (! empty($dg->noi_dung_nhan_xet))
                                {{ $dg->noi_dung_nhan_xet }}
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Ứng viên không để lại lời nhận xét bằng chữ.</span>
                            @endif
                        </div>

                        <div class="review-shift-footer">
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                                @if ($ca)
                                    <span class="shift-chip">
                                        <i class="fa-solid fa-briefcase"></i>
                                        Ca làm: {{ $ca->ten_vi_tri_tuyen_dung }}
                                        @if ($ca->gio_bat_dau && $ca->gio_ket_thuc)
                                            ({{ substr($ca->gio_bat_dau, 0, 5) }} - {{ substr($ca->gio_ket_thuc, 0, 5) }})
                                        @endif
                                    </span>
                                @endif
                                <span class="completed-chip">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Hoàn thành: {{ $ngayHoanThanh }}
                                </span>
                            </div>

                            <div style="font-size: 11.5px; color: #94a3b8;">
                                Mã đánh giá: #DG-{{ $dg->ma_danh_gia }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($danhGiaList->hasPages())
                <div class="pagination-wrapper">
                    {{ $danhGiaList->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif

    </div>
</section>
@endsection
