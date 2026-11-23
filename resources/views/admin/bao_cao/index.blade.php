@extends('layouts.app')

@section('title', 'Quản Lý Báo Cáo Vi Phạm - Golden Shift Admin')

@section('content')
@php
$flashSuccess = session('success');
$flashError = session('error');
$flashInfo = session('info');
@endphp

<section class="page-header-section" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 32px 0 28px; color: #fff;">
    <div class="gs-container">
        <div class="breadcrumb" style="font-size: 13px; color: #94a3b8; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <a href="{{ url('/') }}" style="color: #cbd5e1;"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ route('admin.categories.index') }}" style="color: #cbd5e1;">Quản trị hệ thống</a>
            <span>/</span>
            <span style="color: #f59e0b; font-weight: 600;">Báo cáo vi phạm</span>
        </div>
        <div class="header-job-highlight">
            <span class="sub-badge" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.4); padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-shield-halved"></i> TRUNG TÂM GIÁM SÁT &amp; KỶ LUẬT
            </span>
            <h1 class="page-title" style="font-size: 26px; font-weight: 800; margin: 10px 0 6px; color: var(--gold-500, #f59e0b) !important;">
                Quản Lý &amp; Xử Lý Báo Cáo Vi Phạm
            </h1>
            <p class="page-subtitle" style="font-size: 14px; color: #94a3b8; margin: 0;">
                Tiếp nhận khiếu nại, đối soát hình ảnh minh chứng và ban hành chế tài cảnh cáo hoặc tạm khóa tài khoản vi phạm.
            </p>
        </div>
    </div>
</section>

<section class="page-content-section" style="padding: 28px 0 60px; background: #f8fafc;">
    <div class="gs-container">

        <!-- THÔNG BÁO FLASH ALERTS -->
        @if (!empty($flashSuccess))
        <div class="alert-box success" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
            <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 18px;"></i>
            <span>{{ $flashSuccess }}</span>
        </div>
        @endif

        @if (!empty($flashError))
        <div class="alert-box error" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
            <i class="fa-solid fa-circle-exclamation" style="color: #ef4444; font-size: 18px;"></i>
            <span>{{ $flashError }}</span>
        </div>
        @endif

        @if (!empty($flashInfo))
        <div class="alert-box info" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
            <i class="fa-solid fa-circle-info" style="color: #3b82f6; font-size: 18px;"></i>
            <span>{{ $flashInfo }}</span>
        </div>
        @endif

        <!-- TABS TRẠNG THÁI BÁO CÁO -->
        <div style="display: flex; gap: 12px; margin-bottom: 22px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('admin.bao_cao.index', ['status' => 'cho_xuly', 'search' => $search]) }}"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; border-radius: 999px; font-weight: 700; font-size: 13.5px; text-decoration: none; transition: all 0.2s; border: 1px solid {{ $statusFilter === 'cho_xuly' ? '#f59e0b' : '#e2e8f0' }}; background: {{ $statusFilter === 'cho_xuly' ? '#f59e0b' : '#fff' }}; color: {{ $statusFilter === 'cho_xuly' ? '#fff' : '#475569' }}; box-shadow: {{ $statusFilter === 'cho_xuly' ? '0 3px 10px rgba(245, 158, 11, 0.25)' : 'none' }};">
                    <i class="fa-solid fa-clock"></i> Chờ Xử Lý
                    @if ($countPending > 0)
                    <span style="background: {{ $statusFilter === 'cho_xuly' ? '#fff' : '#ef4444' }}; color: {{ $statusFilter === 'cho_xuly' ? '#d97706' : '#fff' }}; font-size: 11px; padding: 1px 7px; border-radius: 999px; font-weight: 800;">
                        {{ $countPending }}
                    </span>
                    @endif
                </a>

                <a href="{{ route('admin.bao_cao.index', ['status' => 'da_xuly', 'search' => $search]) }}"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; border-radius: 999px; font-weight: 700; font-size: 13.5px; text-decoration: none; transition: all 0.2s; border: 1px solid {{ $statusFilter === 'da_xuly' ? '#10b981' : '#e2e8f0' }}; background: {{ $statusFilter === 'da_xuly' ? '#10b981' : '#fff' }}; color: {{ $statusFilter === 'da_xuly' ? '#fff' : '#475569' }}; box-shadow: {{ $statusFilter === 'da_xuly' ? '0 3px 10px rgba(16, 185, 129, 0.25)' : 'none' }};">
                    <i class="fa-solid fa-circle-check"></i> Đã Xử Lý ({{ $countProcessed }})
                </a>

                <a href="{{ route('admin.bao_cao.index', ['status' => 'tu_choi', 'search' => $search]) }}"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; border-radius: 999px; font-weight: 700; font-size: 13.5px; text-decoration: none; transition: all 0.2s; border: 1px solid {{ $statusFilter === 'tu_choi' ? '#64748b' : '#e2e8f0' }}; background: {{ $statusFilter === 'tu_choi' ? '#64748b' : '#fff' }}; color: {{ $statusFilter === 'tu_choi' ? '#fff' : '#475569' }};">
                    <i class="fa-solid fa-ban"></i> Đã Từ Chối ({{ $countRejected }})
                </a>

                <a href="{{ route('admin.bao_cao.index', ['status' => 'all', 'search' => $search]) }}"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; border-radius: 999px; font-weight: 700; font-size: 13.5px; text-decoration: none; transition: all 0.2s; border: 1px solid {{ $statusFilter === 'all' ? '#3b82f6' : '#e2e8f0' }}; background: {{ $statusFilter === 'all' ? '#3b82f6' : '#fff' }}; color: {{ $statusFilter === 'all' ? '#fff' : '#475569' }};">
                    <i class="fa-solid fa-list-ul"></i> Tất Cả ({{ $countTotal }})
                </a>
            </div>

            <!-- TÌM KIẾM -->
            <form action="{{ route('admin.bao_cao.index') }}" method="GET" style="display: flex; gap: 8px; margin: 0;">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo tên, email, nội dung..."
                        style="height: 38px; padding-left: 34px; padding-right: 14px; border: 1px solid #cbd5e1; border-radius: 999px; font-size: 13px; width: 260px; outline: none; background: #fff;">
                </div>
                <button type="submit" class="btn btn-sm btn-gold" style="border-radius: 999px; padding: 0 16px; height: 38px; font-weight: 700;">Tìm</button>
                @if ($search)
                <a href="{{ route('admin.bao_cao.index', ['status' => $statusFilter]) }}" class="btn btn-sm btn-outline" style="border-radius: 999px; padding: 0 12px; height: 38px; display: inline-flex; align-items: center; justify-content: center;" title="Xóa tìm kiếm">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                @endif
            </form>
        </div>

        <!-- BẢNG DANH SÁCH BÁO CÁO -->
        <div class="dashboard-table-card glass-panel" style="background: #ffffff; border-radius: 16px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
            <div class="card-table-header" style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--text-heading); display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-triangle-exclamation text-gold"></i> Danh Sách Báo Cáo Vi Phạm
                    </h3>
                    <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">
                        Xem xét tường trình và chứng cứ trước khi đưa ra quyết định kỷ luật
                    </p>
                </div>
                <span style="font-size: 13px; color: #64748b;">
                    Hiển thị <strong>{{ $reports->count() }}</strong> báo cáo
                </span>
            </div>

            @if ($reports->isEmpty())
            <div style="padding: 60px 20px; text-align: center;">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: #f8fafc; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-size: 30px; margin-bottom: 14px;">
                    <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #475569; margin-bottom: 6px;">
                    Không có báo cáo vi phạm nào trong mục này
                </h3>
                <p style="font-size: 13px; color: #94a3b8; margin: 0;">
                    Tuyệt vời! Hiện tại không có khiếu nại hay sự cố nào cần xử lý.
                </p>
            </div>
            @else
            <div style="width: 100%; overflow-x: auto;">
                <table class="gs-table" style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="width: 85px; text-align: center; padding: 14px 12px; font-weight: 700; color: #475569;">MÃ BC</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #475569; width: 220px;">NGƯỜI BÁO CÁO</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #475569; width: 220px;">ĐỐI TƯỢNG BỊ BÁO CÁO</th>
                            <th style="padding: 14px 16px; font-weight: 700; color: #475569;">CHI TIẾT VI PHẠM</th>
                            <th style="width: 150px; text-align: center; padding: 14px 12px; font-weight: 700; color: #475569;">TRẠNG THÁI</th>
                            <th style="width: 140px; text-align: center; padding: 14px 16px; font-weight: 700; color: #475569;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $item)
                        @php
                        $nguoiBaoCao = $item->nguoiBaoCao;
                        $nguoiViPham = $item->nguoiViPham;
                        $caLam = $item->caLamViec;
                        $stChuan = $item->trang_thai_chuan;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fafaf9'" onmouseout="this.style.background='transparent'">
                            <!-- Cột 1: Mã BC & Ngày -->
                            <td style="text-align: center; padding: 14px 12px; vertical-align: top;">
                                <span style="background: #fef3c7; color: #b45309; font-weight: 800; font-size: 12px; padding: 3px 8px; border-radius: 6px; display: inline-block;">
                                    #BC - {{ $item->ma_vi_pham }}
                                </span>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 5px;">
                                    {{ $item->ngay_lap_bao_cao ? $item->ngay_lap_bao_cao->format('H:i d/m/y') : '' }}
                                </div>
                            </td>

                            <!-- Cột 2: Người Báo Cáo -->
                            <td style="padding: 14px 16px; vertical-align: top;">
                                @if ($nguoiBaoCao)
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                        {{ mb_substr($nguoiBaoCao->ho_ten, 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b; line-height: 1.2;">
                                            {{ $nguoiBaoCao->ho_ten }}
                                        </div>
                                        <span style="font-size: 10.5px; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-block; margin-top: 3px; {{ $nguoiBaoCao->vai_tro === 'quan_ly_nha_hang' ? 'background:#fef3c7;color:#b45309;' : 'background:#ecfdf5;color:#047857;' }}">
                                            {{ $nguoiBaoCao->vai_tro === 'quan_ly_nha_hang' ? 'Quản lý nhà hàng' : ($nguoiBaoCao->vai_tro === 'ung_vien' ? 'Ứng viên' : 'Quản trị viên') }}
                                        </span>
                                        <div style="font-size: 11.5px; color: #64748b; margin-top: 3px;">
                                            <i class="fa-solid fa-phone" style="font-size: 10px;"></i> {{ $nguoiBaoCao->so_dien_thoai }}
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span style="color: #94a3b8;">Ẩn danh / Không tồn tại</span>
                                @endif
                            </td>

                            <!-- Cột 3: Đối Tượng Bị Báo Cáo -->
                            <td style="padding: 14px 16px; vertical-align: top;">
                                @if ($nguoiViPham)
                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                        {{ mb_substr($nguoiViPham->ho_ten, 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b; line-height: 1.2;">
                                            {{ $nguoiViPham->ho_ten }}
                                        </div>
                                        <div style="display: flex; gap: 6px; align-items: center; margin-top: 3px;">
                                            <span style="font-size: 10.5px; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-block; {{ $nguoiViPham->vai_tro === 'quan_ly_nha_hang' ? 'background:#fef3c7;color:#b45309;' : 'background:#ecfdf5;color:#047857;' }}">
                                                {{ $nguoiViPham->vai_tro === 'quan_ly_nha_hang' ? 'Quản lý' : 'Ứng viên' }}
                                            </span>
                                            @if ($nguoiViPham->trang_thai === 'tam_khoa')
                                            <span style="background: #dc2626; color: #fff; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px;">
                                                <i class="fa-solid fa-lock"></i> Đang bị khóa
                                            </span>
                                            @endif
                                        </div>
                                        <div style="font-size: 11.5px; color: #64748b; margin-top: 3px;">
                                            <i class="fa-solid fa-envelope" style="font-size: 10px;"></i> {{ $nguoiViPham->email }}
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span style="color: #94a3b8;">Không xác định</span>
                                @endif
                            </td>

                            <!-- Cột 4: Chi Tiết Vi Phạm -->
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="margin-bottom: 6px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                                        <i class="fa-solid fa-circle-exclamation"></i> {{ $item->ten_loai_vi_pham }}
                                    </span>

                                    @if ($caLam)
                                    <span style="font-size: 11.5px; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 6px;">
                                        <i class="fa-solid fa-briefcase"></i> {{ $caLam->ten_vi_tri_tuyen_dung }} ({{ $caLam->ngay_lam_viec ? date('d/m/Y', strtotime($caLam->ngay_lam_viec)) : '' }})
                                    </span>
                                    @endif
                                </div>

                                <div style="font-size: 13px; color: #334155; line-height: 1.4; background: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 8px;">
                                    {{ $item->mo_ta_chi_tiet }}
                                </div>

                                <!-- Minh chứng hình ảnh -->
                                @if (!empty($item->hinh_anh_minh_chung))
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 11.5px; font-weight: 600; color: #64748b;">
                                        <i class="fa-solid fa-paperclip"></i> Minh chứng:
                                    </span>
                                    <a href="javascript:void(0)" onclick="openProofModal('{{ asset($item->hinh_anh_minh_chung) }}')" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #0284c7; text-decoration: underline; font-weight: 600;">
                                        <img src="{{ asset($item->hinh_anh_minh_chung) }}" alt="Minh chứng" style="width: 28px; height: 28px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1;">
                                        <span>Xem ảnh đính kèm</span>
                                    </a>
                                </div>
                                @else
                                <span style="font-size: 11px; color: #94a3b8; font-style: italic;">(Không đính kèm ảnh minh chứng)</span>
                                @endif
                            </td>

                            <!-- Cột 5: Trạng Thái & Kết Quả Xử Lý -->
                            <td style="text-align: center; padding: 14px 12px; vertical-align: top;">
                                @if ($stChuan === 'cho_xuly')
                                <span style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 999px; display: inline-block;">
                                    <i class="fa-solid fa-hourglass-half"></i> Chờ xử lý
                                </span>
                                @elseif ($stChuan === 'da_xuly')
                                <span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 999px; display: inline-block;">
                                    <i class="fa-solid fa-circle-check"></i> Đã xử lý
                                </span>
                                @if ($item->hinh_thuc_xu_ly)
                                <div style="font-size: 11.5px; color: #475569; margin-top: 6px; max-width: 160px; line-height: 1.3;" title="{{ $item->hinh_thuc_xu_ly }}">
                                    <strong>Kết luận:</strong> {{ \Illuminate\Support\Str::limit($item->hinh_thuc_xu_ly, 45) }}
                                </div>
                                @endif
                                @elseif ($stChuan === 'tu_choi')
                                <span style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 999px; display: inline-block;">
                                    <i class="fa-solid fa-ban"></i> Đã từ chối
                                </span>
                                @if ($item->ghi_chu_admin)
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 5px;">
                                    {{ \Illuminate\Support\Str::limit($item->ghi_chu_admin, 35) }}
                                </div>
                                @endif
                                @endif
                            </td>

                            <!-- Cột 6: Thao Tác -->
                            <td style="text-align: center; padding: 14px 16px; vertical-align: top;">
                                @if ($stChuan === 'cho_xuly')
                                <button type="button"
                                    class="btn btn-sm"
                                    onclick="openProcessModal({{ $item->ma_vi_pham }}, '{{ addslashes($nguoiViPham ? $nguoiViPham->ho_ten : 'Người dùng') }}', '{{ addslashes($item->ten_loai_vi_pham) }}', '{{ addslashes($item->mo_ta_chi_tiet) }}', '{{ $item->hinh_anh_minh_chung ? asset($item->hinh_anh_minh_chung) : '' }}')"
                                    style="background: #dc2626; color: #fff; border: none; padding: 7px 16px; border-radius: 999px; font-weight: 700; font-size: 12.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25);">
                                    <i class="fa-solid fa-gavel"></i> Xử lý
                                </button>
                                @else
                                <button type="button"
                                    class="btn btn-sm btn-outline"
                                    onclick="openProcessModal({{ $item->ma_vi_pham }}, '{{ addslashes($nguoiViPham ? $nguoiViPham->ho_ten : 'Người dùng') }}', '{{ addslashes($item->ten_loai_vi_pham) }}', '{{ addslashes($item->mo_ta_chi_tiet) }}', '{{ $item->hinh_anh_minh_chung ? asset($item->hinh_anh_minh_chung) : '' }}', '{{ addslashes($item->hinh_thuc_xu_ly) }}', '{{ addslashes($item->ghi_chu_admin) }}', true)"
                                    style="border-radius: 999px; padding: 5px 14px; font-size: 12px; font-weight: 600;">
                                    <i class="fa-solid fa-eye"></i> Chi tiết
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- PHÂN TRANG -->
            <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                {{ $reports->links() }}
            </div>
            @endif
        </div>
    </div>
</section>

<!-- ========================================================== -->
<!-- MODAL XỬ LÝ VI PHẠM (ADMIN MODAL) -->
<!-- ========================================================== -->
<div id="modalProcessReport" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 580px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.2); animation: fadeInDown 0.2s ease;">
        <div style="background: #1e293b; color: #fff; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(239, 68, 68, 0.2); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fa-solid fa-gavel"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #fff;">Quyết Định Xử Lý Báo Cáo Vi Phạm</h3>
                    <p style="margin: 2px 0 0; font-size: 12px; color: #94a3b8;" id="modalReportTitle">Báo cáo #</p>
                </div>
            </div>
            <button type="button" onclick="closeProcessModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form id="formProcessReport" method="POST" action="" style="margin: 0; padding: 24px;">
            @csrf

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 20px;">
                <div style="font-size: 13px; color: #475569; margin-bottom: 6px;">
                    <strong>Đối tượng bị báo cáo:</strong> <span id="modalTargetName" style="color: #dc2626; font-weight: 700;"></span>
                </div>
                <div style="font-size: 13px; color: #475569; margin-bottom: 6px;">
                    <strong>Lý do:</strong> <span id="modalReasonName" style="font-weight: 600;"></span>
                </div>
                <div style="font-size: 12.5px; color: #64748b;">
                    <strong>Mô tả:</strong> <span id="modalDescription"></span>
                </div>
                <div id="modalProofPreview" style="margin-top: 10px; display: none;">
                    <strong style="font-size: 12px; color: #475569;">Ảnh minh chứng:</strong><br>
                    <img id="modalProofImg" src="" alt="Minh chứng" style="max-height: 120px; border-radius: 6px; border: 1px solid #cbd5e1; margin-top: 6px; cursor: pointer;" onclick="openProofModal(this.src)">
                </div>
            </div>

            <!-- CHỌN HÌNH THỨC XỬ LÝ -->
            <div id="processActionsSection">
                <label style="display: block; font-size: 13.5px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">
                    Chọn Biện Pháp Xử Lý Kỷ Luật <span style="color: #dc2626;">*</span>
                </label>

                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 18px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.borderColor='#f59e0b'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e2e8f0'">
                        <input type="radio" name="hinh_thuc" value="canh_cao" checked style="margin-top: 3px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #d97706;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Cảnh Cáo Chính Thức
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                Gửi thông báo cảnh báo vi phạm tới người bị báo cáo, ghi nhận vào lịch sử kỷ luật của tài khoản.
                            </div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.borderColor='#dc2626'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e2e8f0'">
                        <input type="radio" name="hinh_thuc" value="tam_khoa" style="margin-top: 3px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #dc2626;">
                                <i class="fa-solid fa-lock"></i> Tạm Khóa Tài Khoản
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                Tự động chuyển trạng thái tài khoản thành <strong>Tạm khóa (tam_khoa)</strong>, chặn đăng nhập và thao tác trên hệ thống.
                            </div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.borderColor='#64748b'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e2e8f0'">
                        <input type="radio" name="hinh_thuc" value="tu_choi" style="margin-top: 3px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #64748b;">
                                <i class="fa-solid fa-ban"></i> Từ Chối / Bác Bỏ Báo Cáo
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                Báo cáo không có căn cứ hoặc không đủ minh chứng xác thực, không áp dụng kỷ luật.
                            </div>
                        </div>
                    </label>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="inputGhiChuAdmin" style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Ghi Chú Quyết Định Của Quản Trị Viên <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="ghi_chu_admin" id="inputGhiChuAdmin" rows="3" required placeholder="Nhập căn cứ xử lý hoặc lý do cảnh cáo / khóa tài khoản..."
                        style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; font-size: 13.5px; outline: none;"></textarea>
                </div>
            </div>

            <div id="processedInfoSection" style="display: none; margin-bottom: 20px;">
                <div style="background: #f1f5f9; padding: 12px; border-radius: 8px;">
                    <div style="font-size: 13px; color: #1e293b; margin-bottom: 4px;">
                        <strong>Hình thức đã xử lý:</strong> <span id="modalProcessedResult"></span>
                    </div>
                    <div style="font-size: 12.5px; color: #475569;">
                        <strong>Ghi chú:</strong> <span id="modalProcessedNote"></span>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeProcessModal()" class="btn btn-outline btn-sm" style="border-radius: 8px; padding: 8px 18px;">
                    Đóng
                </button>
                <button type="submit" id="btnSubmitProcess" class="btn btn-sm btn-gold" style="border-radius: 8px; padding: 8px 22px; font-weight: 700;">
                    <i class="fa-solid fa-check"></i> Ban Hành Quyết Định
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================== -->
<!-- LIGHTBOX XEM ẢNH MINH CHỨNG PHÓNG TO -->
<!-- ========================================================== -->
<div id="lightboxProofModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; padding: 30px;" onclick="closeProofModal()">
    <div style="position: relative; max-width: 90%; max-height: 90%; text-align: center;" onclick="event.stopPropagation()">
        <img id="lightboxImg" src="" alt="Minh chứng vi phạm" style="max-width: 100%; max-height: 80vh; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <button type="button" onclick="closeProofModal()" style="position: absolute; top: -40px; right: 0; background: transparent; border: none; color: #fff; font-size: 28px; cursor: pointer;">&times;</button>
    </div>
</div>

@push('scripts')
<script>
    function openProcessModal(id, targetName, reason, desc, proofUrl, resultText, noteText, isReadOnly) {
        document.getElementById('modalReportTitle').innerText = 'Báo cáo vi phạm #BC - ' + id;
        document.getElementById('modalTargetName').innerText = targetName;
        document.getElementById('modalReasonName').innerText = reason;
        document.getElementById('modalDescription').innerText = desc;

        var form = document.getElementById('formProcessReport');
        form.action = "{{ url('/admin/bao-cao-vi-pham') }}/" + id + "/xu-ly";

        var proofDiv = document.getElementById('modalProofPreview');
        var proofImg = document.getElementById('modalProofImg');
        if (proofUrl) {
            proofImg.src = proofUrl;
            proofDiv.style.display = 'block';
        } else {
            proofDiv.style.display = 'none';
        }

        var actionsSection = document.getElementById('processActionsSection');
        var infoSection = document.getElementById('processedInfoSection');
        var btnSubmit = document.getElementById('btnSubmitProcess');

        if (isReadOnly) {
            actionsSection.style.display = 'none';
            infoSection.style.display = 'block';
            document.getElementById('modalProcessedResult').innerText = resultText || 'Đã xử lý';
            document.getElementById('modalProcessedNote').innerText = noteText || 'Không có ghi chú thêm.';
            btnSubmit.style.display = 'none';
        } else {
            actionsSection.style.display = 'block';
            infoSection.style.display = 'none';
            btnSubmit.style.display = 'inline-flex';
        }

        document.getElementById('modalProcessReport').style.display = 'flex';
    }

    function closeProcessModal() {
        document.getElementById('modalProcessReport').style.display = 'none';
    }

    function openProofModal(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightboxProofModal').style.display = 'flex';
    }

    function closeProofModal() {
        document.getElementById('lightboxProofModal').style.display = 'none';
    }

    // Đóng khi bấm ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProcessModal();
            closeProofModal();
        }
    });
</script>
@endpush
@endsection