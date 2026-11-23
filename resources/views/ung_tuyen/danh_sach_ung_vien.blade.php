@extends('layouts.app')

@section('title', 'Duyệt Hồ Sơ Ứng Viên - Golden Shift')

@section('content')
@php
    $applicantList = $applicantList ?? [];
    $maCaLam = $maCaLam ?? 0;
    $flashSuccess = session('success');
    $flashError = session('error');
@endphp

<section class="page-header-section">
    <div class="gs-container">
        <div class="breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ url('/dashboard') }}">Bảng quản trị</a>
            <span>/</span>
            <span>Duyệt ứng viên ứng tuyển</span>
        </div>
        <div class="header-job-highlight">
            <span class="sub-badge"><i class="fa-solid fa-user-check text-gold"></i> PHÂN HỆ QUẢN LÝ NHÀ HÀNG</span>
            <h1 class="page-title">Xét Duyệt Hồ Sơ Ứng Viên Ứng Tuyển</h1>
            <p class="page-subtitle">Xem nhanh thông tin, kinh nghiệm ứng viên và thực hiện Duyệt hoặc Từ chối nhận ca làm.</p>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="gs-container">
        <!-- FLASH ALERTS -->
        @if (!empty($flashSuccess))
            <div class="alert-box success" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i> {{ $flashSuccess }}
            </div>
        @endif

        @if (!empty($flashError))
            <div class="alert-box error" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $flashError }}
            </div>
        @endif

        <!-- METRIC SUMMARY -->
        <div class="admin-approval-summary glass-panel" style="margin-bottom: 2rem;">
            <div class="summary-left">
                <div class="approval-icon-box">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </div>
                <div>
                    <h3>Tổng Số Hồ Sơ Ứng Tuyển: <strong>{{ method_exists($applicantList, 'total') ? $applicantList->total() : count($applicantList) }} hồ sơ</strong></h3>
                    <p>Phê duyệt ứng viên để xếp vào ca làm việc chính thức của nhà hàng.</p>
                </div>
            </div>
            @if ($maCaLam > 0)
                <div class="summary-right">
                    <a href="{{ url('/quan-ly/ung-tuyen') }}" class="btn btn-outline-gold btn-sm">
                        <i class="fa-solid fa-rotate-right"></i> Xem Tất Cả Ứng Viên
                    </a>
                </div>
            @endif
        </div>

        <!-- BẢNG DANH SÁCH ỨNG VIÊN -->
        <div class="dashboard-table-card glass-panel">
            <div class="card-table-header">
                <div>
                    <h3 class="table-card-title"><i class="fa-solid fa-address-card text-gold"></i> Danh Sách Ứng Viên Đăng Ký Ca</h3>
                    <p class="table-card-sub">Dữ liệu hồ sơ nộp đơn ca làm việc</p>
                </div>
            </div>

            @if (empty($applicantList) || count($applicantList) === 0)
                <div class="empty-state-box">
                    <i class="fa-solid fa-user-slash empty-icon"></i>
                    <h3>Chưa có ứng viên nào ứng tuyển ca này</h3>
                    <p>Hiện chưa có đơn nộp ứng tuyển nào. Vui lòng quay lại sau.</p>
                    <a href="{{ url('/quan-ly/ca-lam-viec') }}" class="btn btn-gold">Quản Lý Ca Làm Việc</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="gs-table">
                        <thead>
                            <tr>
                                <th style="width: 95px; white-space: nowrap;">Mã Đơn</th>
                                <th>Thông Tin Ứng Viên</th>
                                <th>Vị Trí & Ca Làm</th>
                                <th style="width: 140px; white-space: nowrap;">Kinh Nghiệm / Lời Nhắn</th>
                                <th style="width: 140px; white-space: nowrap;">Thời Gian Nộp</th>
                                <th style="width: 110px; white-space: nowrap;">Trạng Thái</th>
                                <th class="text-center" style="width: 180px; white-space: nowrap;">Thao Tác Duyệt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applicantList as $item)
                                @php
                                    $itemArr = is_array($item) ? $item : (method_exists($item, 'toArray') ? $item->toArray() : (array)$item);
                                    $maUngTuyen = (int)($itemArr['ma_ung_tuyen'] ?? 0);
                                    $hoTen = $itemArr['ho_ten'] ?? ($item->nguoiDung->ho_ten ?? 'Ứng viên');
                                    $phone = $itemArr['so_dien_thoai'] ?? ($item->nguoiDung->so_dien_thoai ?? '');
                                    $email = $itemArr['email'] ?? ($item->nguoiDung->email ?? '');
                                    $viTri = $itemArr['ten_vi_tri_tuyen_dung'] ?? ($item->caLamViec->ten_vi_tri_tuyen_dung ?? '');
                                    $ngayLam = $itemArr['ngay_lam_viec'] ?? ($item->caLamViec->ngay_lam_viec ?? '');
                                    $gioBatDau = $itemArr['gio_bat_dau'] ?? ($item->caLamViec->gio_bat_dau ?? '');
                                    $gioKetThuc = $itemArr['gio_ket_thuc'] ?? ($item->caLamViec->gio_ket_thuc ?? '');
                                    $status = $itemArr['trang_thai_ung_tuyen'] ?? 'cho_duyet';
                                    $exp = $itemArr['kinh_nghiem_lam_viec'] ?? ($item->nguoiDung->hoSoUngVien->kinh_nghiem_lam_viec ?? 'Chưa cập nhật');
                                    $loiNhan = $itemArr['loi_nhan_ung_vien'] ?? '';
                                    $rejectReason = $itemArr['ghi_chu_nha_tuyen_dung'] ?? '';
                                @endphp
                                <tr>
                                    <td style="white-space: nowrap;"><span class="badge-code">#UT-{{ $maUngTuyen }}</span></td>
                                    <td>
                                        <div style="font-weight: 700; color: #1e293b;">{{ $hoTen }}</div>
                                        @php
                                            $candUid = $itemArr['ma_nguoi_dung'] ?? ($item->nguoiDung->ma_nguoi_dung ?? 0);
                                            $candRating = \App\Models\DanhGiaCaLam::tinhDiemTrungBinhUngVien($candUid);
                                            $candTongLuot = (int) ($candRating['tong_luot'] ?? 0);
                                        @endphp
                                        @if ($candTongLuot > 0)
                                            <div style="font-size: 0.75rem; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; margin: 2px 0;">
                                                <i class="fa-solid fa-star text-gold"></i> {{ number_format($candRating['diem_tb'] ?? 0, 1) }}⭐ ({{ $candTongLuot }} đánh giá)
                                            </div>
                                        @else
                                            <div style="font-size: 0.75rem; color: #64748b; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; margin: 2px 0;">
                                                <i class="fa-regular fa-star" style="color: #94a3b8;"></i> 0 (Chưa có đánh giá)
                                            </div>
                                        @endif
                                        <div style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">
                                            <i class="fa-solid fa-phone text-gold"></i> <a href="tel:{{ $phone }}">{{ $phone }}</a>
                                        </div>
                                        @if ($email)
                                            <div style="font-size: 0.8rem; color: #94a3b8;">
                                                <i class="fa-solid fa-envelope"></i> {{ $email }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #0284c7;">{{ $viTri }}</div>
                                        <div style="font-size: 0.82rem; color: #475569; margin-top: 2px;">
                                            <i class="fa-solid fa-calendar-day text-gold"></i> {{ $ngayLam ? date('d/m/Y', strtotime($ngayLam)) : '' }}
                                        </div>
                                        <div style="font-size: 0.8rem; color: #94a3b8;">
                                            <i class="fa-regular fa-clock text-gold"></i> {{ substr($gioBatDau, 0, 5) }} - {{ substr($gioKetThuc, 0, 5) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem; max-width: 220px; line-height: 1.4;">
                                            @if ($loiNhan)
                                                <p style="margin: 0 0 4px 0; color: #1e293b;"><strong>Lời nhắn:</strong> {{ $loiNhan }}</p>
                                            @endif
                                            <p style="margin: 0; color: #64748b;"><strong>K/Nghiệm:</strong> {{ $exp }}</p>
                                            @if ($status === 'tu_choi' && $rejectReason)
                                                <p style="margin: 4px 0 0 0; color: #dc2626;"><strong>Lý do từ chối:</strong> {{ $rejectReason }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap; font-size: 0.82rem; color: #64748b;">
                                        {{ !empty($itemArr['ngay_nop_don']) ? date('H:i d/m/Y', strtotime($itemArr['ngay_nop_don'])) : '' }}
                                    </td>
                                    <td style="white-space: nowrap;">
                                        @if ($status === 'da_nhan' || $status === 'da_duyet')
                                            <span class="badge-status recruit nowrap" style="background: #d1fae5; color: #065f46; border-color: #a7f3d0;">
                                                <i class="fa-solid fa-circle-check"></i> Đã nhận ca
                                            </span>
                                        @elseif ($status === 'tu_choi')
                                            <span class="badge-status inactive nowrap" style="background: #fee2e2; color: #991b1b; border-color: #fca5a5;">
                                                <i class="fa-solid fa-circle-xmark"></i> Đã từ chối
                                            </span>
                                        @else
                                            <span class="badge-status nowrap" style="background: #fef3c7; color: #92400e; border-color: #fde68a;">
                                                <i class="fa-solid fa-hourglass-half"></i> Chờ duyệt
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        @if ($status === 'cho_duyet')
                                            <div style="display: flex; gap: 6px; justify-content: center;">
                                                <form action="{{ url('/quan-ly/ung-tuyen/duyet') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn DUYỆT ứng viên [{{ $hoTen }}] vào ca làm?');">
                                                    @csrf
                                                    <input type="hidden" name="ma_ung_tuyen" value="{{ $maUngTuyen }}">
                                                    <input type="hidden" name="hanh_dong" value="duyet">
                                                    <button type="submit" class="btn btn-sm" style="background: #10b981; color: #fff; border: 1px solid #10b981; padding: 4px 10px; font-weight: 600;">
                                                        <i class="fa-solid fa-check"></i> Duyệt
                                                    </button>
                                                </form>

                                                <button type="button" class="btn btn-sm btn-open-reject-modal" 
                                                        data-id="{{ $maUngTuyen }}" 
                                                        data-name="{{ $hoTen }}"
                                                        style="background: #fff; color: #dc2626; border: 1px solid #fca5a5; padding: 4px 10px; font-weight: 600;">
                                                    <i class="fa-solid fa-xmark"></i> Từ chối
                                                </button>
                                            </div>
                                        @else
                                            <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                                <span style="font-size: 0.82rem; color: #10b981; font-weight: 600;">
                                                    <i class="fa-solid fa-check"></i> Đã duyệt
                                                </span>
                                                @php
                                                    $caLamObj = $item->caLamViec ?? null;
                                                    $chamCongObj = $item->chamCong ?? null;
                                                    $isCaDone = false;
                                                    if ($caLamObj && $caLamObj->trang_thai_ca === 'hoan_thanh') {
                                                        $isCaDone = true;
                                                    } elseif ($chamCongObj && in_array($chamCongObj->trang_thai_cham_cong, ['da_xac_nhan', 'tu_dong_duyet'])) {
                                                        $isCaDone = true;
                                                    }

                                                    $thoiDiemKetThuc = null;
                                                    if ($chamCongObj && $chamCongObj->thoi_gian_ra_thuc_te) {
                                                        $thoiDiemKetThuc = \Carbon\Carbon::parse($chamCongObj->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh');
                                                    } elseif ($caLamObj && $caLamObj->ngay_lam_viec) {
                                                        $thoiDiemKetThuc = \Carbon\Carbon::parse($caLamObj->ngay_lam_viec . ' ' . ($caLamObj->gio_ket_thuc ?: '23:59:59'), 'Asia/Ho_Chi_Minh');
                                                    }
                                                    $isExpired7Days = $thoiDiemKetThuc ? $thoiDiemKetThuc->copy()->addDays(7)->isPast() : false;
                                                @endphp

                                                @if ($isCaDone && !$isExpired7Days)
                                                    <button type="button"
                                                            onclick="openReportModal({ maCaLam: {{ $itemArr['ma_ca_lam'] ?? 0 }}, targetUserId: {{ $candUid }}, targetName: '{{ addslashes($hoTen) }}', shiftName: '{{ addslashes($viTri) }}', isManager: true })"
                                                            style="background: transparent; border: 1px solid #fca5a5; color: #dc2626; border-radius: 4px; padding: 2px 6px; font-size: 0.72rem; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;"
                                                            title="Báo cáo ứng viên vi phạm (bùng ca, bỏ ca, thái độ...)">
                                                        <i class="fa-solid fa-flag"></i> Báo cáo
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (method_exists($applicantList, 'hasPages') && $applicantList->hasPages())
                    <div class="pagination-wrapper">
                        {{ $applicantList->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>

<!-- MODAL TỪ CHỐI ỨNG VIÊN -->
<div id="rejectModalPage" class="gs-custom-modal-overlay" style="display: none; z-index: 10000;">
    <div class="gs-custom-modal-card" style="max-width: 500px;">
        <div class="gs-custom-modal-header" style="background: #fff; border-bottom: 1px solid #fee2e2;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: #991b1b; font-size: 1.15rem;">Từ Chối Ứng Viên</h3>
                    <p style="margin: 2px 0 0 0; color: #64748b; font-size: 0.85rem;">Ứng viên: <strong id="modalCandName" style="color: #1e293b;">--</strong></p>
                </div>
            </div>
            <button type="button" class="btn-close-custom-modal" id="btnCloseRejectModal">&times;</button>
        </div>

        <form action="{{ url('/quan-ly/ung-tuyen/duyet') }}" method="POST" style="padding: 1.25rem 1.5rem;">
            @csrf
            <input type="hidden" name="ma_ung_tuyen" id="modalMaUngTuyen" value="">
            <input type="hidden" name="hanh_dong" value="tu_choi">

            <div style="margin-bottom: 1rem;">
                <label for="modalLyDo" style="display: block; font-weight: 600; color: #334155; margin-bottom: 6px; font-size: 0.88rem;">
                    Lý do từ chối hồ sơ <span style="color: #dc2626;">* (Bắt buộc)</span>
                </label>
                <textarea name="ly_do_tu_choi" id="modalLyDo" rows="4" required
                          style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.85rem; font-family: inherit; font-size: 0.9rem; resize: vertical;"
                          placeholder="Ví dụ: Đã đủ người cho ca làm này, kinh nghiệm chưa phù hợp..."></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" id="btnCancelRejectModal" class="btn btn-outline btn-sm">Hủy Bỏ</button>
                <button type="submit" class="btn btn-sm" style="background: #dc2626; color: #fff; border: 1px solid #dc2626; font-weight: 600;">
                    <i class="fa-solid fa-check"></i> Xác Nhận Từ Chối
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rejectModalPage');
    const btnClose = document.getElementById('btnCloseRejectModal');
    const btnCancel = document.getElementById('btnCancelRejectModal');
    const modalMaUngTuyen = document.getElementById('modalMaUngTuyen');
    const modalCandName = document.getElementById('modalCandName');

    document.querySelectorAll('.btn-open-reject-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            modalMaUngTuyen.value = this.dataset.id;
            modalCandName.innerText = this.dataset.name;
            modal.style.display = 'flex';
        });
    });

    function closeModal() {
        modal.style.display = 'none';
    }

    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }
});
</script>

{{-- ================================================================
     MANAGER REAL-TIME: Tự động cập nhật số đơn chờ duyệt mỗi 4 giây
================================================================ --}}
@push('scripts')
<script>
(function() {
    'use strict';
    const POLL_MS = 4000;

    // Lấy mã ca làm từ URL hoặc form nếu có
    const urlParams = new URLSearchParams(window.location.search);
    const maCaLam   = parseInt(urlParams.get('ma_ca_lam') || '0') || 0;

    // Đếm số đơn chờ duyệt hiện tại
    function countPendingRows() {
        return document.querySelectorAll('td .badge-status').length
            ? document.querySelectorAll('tr td .badge-status').length
            : 0;
    }

    let _prevPendingCount = -1;
    let _firstPoll = true;

    function showManagerToast(msg) {
        let container = document.getElementById('gsGlobalToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'gsGlobalToastContainer';
            container.style.cssText = 'position:fixed;top:72px;right:20px;z-index:999999;display:flex;flex-direction:column;gap:8px;max-width:360px;width:100%;pointer-events:none;';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.style.cssText = 'background:#78350f;color:#fff;border-left:4px solid #f59e0b;border-radius:10px;padding:12px 16px;font-size:0.875rem;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);pointer-events:auto;opacity:0;transition:opacity .3s;';
        toast.innerHTML = '<i class="fa-solid fa-user-plus" style="font-size:1rem;flex-shrink:0;color:#fbbf24;"></i><span>' + msg + '</span>';
        container.appendChild(toast);
        requestAnimationFrame(function() { toast.style.opacity = '1'; });
        setTimeout(function() { toast.style.opacity = '0'; setTimeout(function() { toast.remove(); }, 400); }, 6000);
    }

    function pollPending() {
        const url = maCaLam > 0
            ? '/api/applications/pending?ma_ca_lam=' + maCaLam
            : '/api/applications/pending';
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.ok ? r.json() : null; })
        .then(function(data) {
            if (!data || !data.success) return;
            const count = parseInt(data.count) || 0;
            if (_firstPoll) {
                _prevPendingCount = count;
                _firstPoll = false;
                return;
            }
            if (_prevPendingCount >= 0 && count > _prevPendingCount) {
                const diff = count - _prevPendingCount;
                showManagerToast('📋 Có <strong>' + diff + '</strong> đơn ứng tuyển mới! <a href="' + window.location.href + '" style="color:#fbbf24;margin-left:4px;">Làm mới trang</a>');
            }
            _prevPendingCount = count;
        })
        .catch(function() {});
    }

    setInterval(pollPending, POLL_MS);
})();
</script>
@endpush
@endsection
