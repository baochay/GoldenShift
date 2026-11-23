@extends('layouts.app')

@section('title', 'Sổ Thu Nhập & Lịch Sử Nhận Lương - Golden Shift')

@section('content')
<section class="page-header-section">
    <div class="gs-container">
        <div class="breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ url('/ung-tuyen/da-nop') }}">Ca làm đã ứng tuyển</a>
            <span>/</span>
            <span>Sổ thu nhập</span>
        </div>
        <div class="header-job-highlight">
            <span class="sub-badge"><i class="fa-solid fa-wallet text-gold"></i> PHÂN HỆ ỨNG VIÊN</span>
            <h1 class="page-title">Sổ Thu Nhập Cá Nhân</h1>
            <p class="page-subtitle">Theo dõi tổng hợp giờ làm việc thực tế, tiền lương tích lũy theo từng ca làm và tiến độ chi trả thu nhập.</p>
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

        @if (session('error'))
            <div class="alert-box error" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <!-- THẺ TỔNG QUAN THU NHẬP (METRIC CARDS) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            <!-- Thẻ 1: Thực nhận tích lũy -->
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 14px; background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%); border: 1px solid #fde68a; box-shadow: 0 4px 15px rgba(217, 119, 6, 0.08); display: flex; align-items: center; gap: 1rem;">
                <div style="width: 52px; height: 52px; border-radius: 12px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #78350f; text-transform: uppercase; letter-spacing: 0.5px;">Tổng Thu Nhập Thực Nhận</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #b45309; line-height: 1.2; margin-top: 4px;">
                        {{ number_format($tongThucNhan, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 600;">VNĐ</span>
                    </div>
                    <div style="font-size: 0.78rem; color: #92400e; margin-top: 2px;">
                        Lương cơ bản: {{ number_format($tongLuongCoBan, 0, ',', '.') }} đ
                    </div>
                </div>
            </div>

            <!-- Thẻ 2: Tổng số giờ làm -->
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 14px; background: #fff; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 52px; height: 52px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                    <i class="fa-solid fa-business-time"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Tổng Giờ Làm Việc</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin-top: 4px;">
                        {{ number_format($tongGioLam, 1) }} <span style="font-size: 1rem; font-weight: 600; color: #64748b;">giờ</span>
                    </div>
                    <div style="font-size: 0.78rem; color: #0284c7; margin-top: 2px;">
                        Đã xác nhận chấm công
                    </div>
                </div>
            </div>

            <!-- Thẻ 3: Tổng số ca hoàn thành -->
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 14px; background: #fff; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 52px; height: 52px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Tổng Số Ca Đã Làm</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin-top: 4px;">
                        {{ $tongSoCaLam }} <span style="font-size: 1rem; font-weight: 600; color: #64748b;">ca</span>
                    </div>
                    <div style="font-size: 0.78rem; color: #16a34a; margin-top: 2px;">
                        Hoàn thành 100% ca nhận
                    </div>
                </div>
            </div>

            <!-- Thẻ 4: Kỳ lương hiện tại -->
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 14px; background: #fff; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 52px; height: 52px; border-radius: 12px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Kỳ Lương Hiện Tại</div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: #1e293b; line-height: 1.3; margin-top: 4px;">
                        {{ $kyLuongHienTai }}
                    </div>
                    <div style="font-size: 0.78rem; margin-top: 2px;">
                        <span class="tag-badge" style="background: #fef3c7; color: #92400e; font-size: 0.72rem; padding: 2px 8px;">
                            {{ $soThuNhapHienTai ? number_format($soThuNhapHienTai->thuc_nhan, 0, ',', '.') . ' VNĐ' : '0 VNĐ' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BẢNG 1: CHI TIẾT CÁC CA LÀM VIỆC ĐÃ HOÀN THÀNH & TÍNH TIỀN -->
        <div class="dashboard-table-card glass-panel" style="margin-bottom: 2.5rem;">
            <div class="card-table-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h3 class="table-card-title"><i class="fa-solid fa-receipt text-gold"></i> Chi Tiết Thu Nhập Theo Ca Làm Việc</h3>
                    <p class="table-card-sub">Danh sách các ca đã vào/ra ca và được Quản lý nhà hàng xác nhận giờ làm thực tế</p>
                </div>
                <div>
                    <a href="{{ url('/ung-tuyen/da-nop') }}" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-clipboard-list"></i> Xem Ca Đã Ứng Tuyển
                    </a>
                </div>
            </div>

            @if ($lichSuCaLam->isEmpty())
                <div class="empty-state-box">
                    <i class="fa-solid fa-clock-rotate-left empty-icon"></i>
                    <h3>Chưa có ca làm việc nào hoàn thành chấm công</h3>
                    <p>Sau khi được Quản lý duyệt nhận ca, bạn hãy bấm "Vào ca" và "Ra ca" để được xác nhận giờ làm và tích lũy tiền lương vào sổ thu nhập.</p>
                    <a href="{{ url('/ca-lam-viec') }}" class="btn btn-gold">Khám Phá Ca Làm Việc</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="gs-table">
                        <thead>
                            <tr>
                                <th style="width: 80px; white-space: nowrap;">Mã CC</th>
                                <th>Vị Trí & Nhà Hàng</th>
                                <th style="width: 140px; white-space: nowrap;">Ngày & Khung Giờ</th>
                                <th style="width: 150px; white-space: nowrap;">Vào/Ra Thực Tế</th>
                                <th class="text-center" style="width: 110px; white-space: nowrap;">Giờ Công</th>
                                <th style="width: 120px; white-space: nowrap;">Mức Lương/h</th>
                                <th style="width: 140px; white-space: nowrap;">Thành Tiền (VNĐ)</th>
                                <th class="text-center" style="width: 130px; white-space: nowrap;">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lichSuCaLam as $item)
                                @php
                                    $shift = $item->caLamViec;
                                    $nhaHang = $shift ? $shift->nhaHang : null;
                                    $thuongHieu = $nhaHang ? $nhaHang->thuongHieu : null;
                                    $mucLuong = (float)($shift ? $shift->muc_luong_theo_gio : 0);
                                    $soGio = (float)$item->so_gio_lam_thuc_te;
                                    $thanhTien = round($soGio * $mucLuong);
                                @endphp
                                <tr>
                                    <td style="white-space: nowrap;">
                                        <span class="badge-code">#CC-{{ $item->ma_cham_cong }}</span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #1e293b;">
                                            {{ $shift ? $shift->ten_vi_tri_tuyen_dung : 'Ca làm việc' }}
                                        </div>
                                        <div style="font-size: 0.82rem; color: #475569; margin-top: 2px;">
                                            <i class="fa-solid fa-store text-gold"></i> {{ $nhaHang ? $nhaHang->ten_nha_hang : '' }}
                                            @if ($thuongHieu)
                                                <span class="sub-badge" style="font-size: 0.72rem; padding: 1px 6px; margin-left: 4px;">{{ $thuongHieu->ten_thuong_hieu }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <div style="font-weight: 600; color: #1e293b;">
                                            <i class="fa-solid fa-calendar-day text-gold"></i> {{ $shift && $shift->ngay_lam_viec ? date('d/m/Y', strtotime($shift->ngay_lam_viec)) : '' }}
                                        </div>
                                        <div style="font-size: 0.8rem; color: #64748b;">
                                            <i class="fa-regular fa-clock text-gold"></i> {{ $shift ? substr($shift->gio_bat_dau, 0, 5) . ' - ' . substr($shift->gio_ket_thuc, 0, 5) : '' }}
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap; font-size: 0.82rem;">
                                        <div><i class="fa-solid fa-arrow-right-to-bracket text-emerald-600" style="color: #059669;"></i> Vào: <strong>{{ $item->thoi_gian_vao_thuc_te ? \Carbon\Carbon::parse($item->thoi_gian_vao_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : '—' }}</strong></div>
                                        <div style="margin-top: 2px;"><i class="fa-solid fa-arrow-right-from-bracket" style="color: #dc2626;"></i> Ra: <strong>{{ $item->thoi_gian_ra_thuc_te ? \Carbon\Carbon::parse($item->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : '—' }}</strong></div>
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <span class="tag-badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.88rem; padding: 4px 10px;">
                                            {{ number_format($soGio, 1) }} giờ
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap; font-size: 0.88rem; color: #64748b;">
                                        {{ number_format($mucLuong, 0, ',', '.') }} đ/h
                                    </td>
                                    <td style="white-space: nowrap; font-weight: 800; font-size: 1rem; color: #b45309;">
                                        +{{ number_format($thanhTien, 0, ',', '.') }} VNĐ
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <span class="badge-status recruit nowrap" style="background: #d1fae5; color: #065f46; border-color: #a7f3d0;">
                                            <i class="fa-solid fa-circle-check"></i> Đã xác nhận
                                        </span>
                                        @if ($item->trang_thai_cham_cong === 'tu_dong_duyet')
                                            <div style="font-size: 0.72rem; color: #0284c7; margin-top: 3px; font-weight: 600;">
                                                Hệ thống tự động duyệt (60p)
                                            </div>
                                        @else
                                            @php
                                                $tenNguoiDuyet = ($shift && $shift->nhaHang && $shift->nhaHang->quanLy)
                                                    ? $shift->nhaHang->quanLy->ho_ten
                                                    : ($item->nguoiXacNhan ? $item->nguoiXacNhan->ho_ten : 'Quản lý');
                                            @endphp
                                            @if ($tenNguoiDuyet)
                                                <div style="font-size: 0.72rem; color: #64748b; margin-top: 3px;">
                                                    Duyệt: {{ $tenNguoiDuyet }}
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (method_exists($lichSuCaLam, 'hasPages') && $lichSuCaLam->hasPages())
                    <div class="pagination-wrapper">
                        {{ $lichSuCaLam->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>

        <!-- BẢNG 2: TỔNG HỢP THEO KỲ LƯƠNG (BẢNG SỔ THU NHẬP) -->
        <div class="dashboard-table-card glass-panel">
            <div class="card-table-header">
                <div>
                    <h3 class="table-card-title"><i class="fa-solid fa-calendar-days text-gold"></i> Bảng Tổng Hợp Kỳ Lương</h3>
                    <p class="table-card-sub">Tổng kết số ca, tổng số giờ làm và số tiền thực nhận (Thanh toán tiền mặt trực tiếp tại nhà hàng sau khi hoàn thành ca làm việc)</p>
                </div>
            </div>

            @if ($soThuNhapList->isEmpty())
                <div class="empty-state-box" style="padding: 2.5rem 1rem;">
                    <i class="fa-solid fa-wallet empty-icon"></i>
                    <h3>Chưa có dữ liệu kỳ lương nào</h3>
                    <p>Sổ thu nhập sẽ tự động tổng hợp ngay khi Quản lý duyệt xác nhận ca làm việc đầu tiên của bạn.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="gs-table">
                        <thead>
                            <tr>
                                <th>Kỳ Lương</th>
                                <th class="text-center">Số Ca Hoàn Thành</th>
                                <th class="text-center">Tổng Giờ Làm</th>
                                <th>Lương Cơ Bản</th>
                                <th>Thưởng</th>
                                <th>Phạt</th>
                                <th class="text-center">Thực Nhận</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($soThuNhapList as $stn)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                                            <i class="fa-solid fa-calendar text-gold"></i> {{ $stn->formatted_ky_luong ?? $stn->ky_luong }}
                                        </div>
                                    </td>
                                    <td class="text-center" style="font-weight: 600;">
                                        {{ $stn->tong_so_ca_lam }} ca
                                    </td>
                                    <td class="text-center" style="font-weight: 600; color: #0284c7;">
                                        {{ number_format($stn->tong_gio_lam, 1) }} giờ
                                    </td>
                                    <td style="font-weight: 600; color: #475569;">
                                        {{ number_format($stn->tong_tien_luong_co_ban, 0, ',', '.') }} VNĐ
                                    </td>
                                    <td style="color: #16a34a; font-weight: 600;">
                                        +{{ number_format($stn->tong_tien_thuong, 0, ',', '.') }} VNĐ
                                    </td>
                                    <td style="color: #dc2626; font-weight: 600;">
                                        -{{ number_format($stn->tong_tien_phat, 0, ',', '.') }} VNĐ
                                    </td>
                                    <td class="text-center" style="font-weight: 800; color: #b45309; font-size: 1.05rem;">
                                        {{ number_format($stn->thuc_nhan, 0, ',', '.') }} VNĐ
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
