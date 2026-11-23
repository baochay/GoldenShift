<?php

namespace App\Http\Controllers;

use App\Models\HoSoUngTuyen;
use App\Models\ThongBao;
use Illuminate\Http\Request;

class RealtimeApiController extends Controller
{
    /**
     * GET /api/notifications/unread-count
     * Trả về số lượng thông báo chưa đọc của user hiện tại.
     */
    public function getUnreadNotificationCount(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return response()->json(['count' => 0, 'logged_in' => false]);
        }

        $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);

        try {
            $count = ThongBao::demChuaDoc($maNguoiDung);

            return response()->json(['count' => $count, 'logged_in' => true]);
        } catch (\Throwable $e) {
            return response()->json(['count' => 0, 'logged_in' => true]);
        }
    }

    /**
     * GET /api/applications/pending?ma_ca_lam=X
     * Trả về số lượng đơn ứng tuyển chờ duyệt của 1 ca làm (dành cho Quản lý).
     */
    public function getPendingApplications(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return response()->json(['success' => false, 'count' => 0], 401);
        }

        $maCaLam = (int) $request->input('ma_ca_lam', 0);
        $maNhaHang = is_array($user) ? (int) ($user['ma_nha_hang_truc_thuoc'] ?? 0) : (int) ($user->ma_nha_hang_truc_thuoc ?? 0);

        try {
            if ($maCaLam > 0) {
                $count = HoSoUngTuyen::where('ma_ca_lam', $maCaLam)
                    ->where('trang_thai_ung_tuyen', 'cho_duyet')
                    ->count();

                return response()->json(['success' => true, 'count' => $count, 'ma_ca_lam' => $maCaLam]);
            }

            // Trả về tổng toàn bộ nhà hàng
            $count = HoSoUngTuyen::where('trang_thai_ung_tuyen', 'cho_duyet')
                ->whereHas('caLamViec', function ($q) use ($maNhaHang) {
                    if ($maNhaHang > 0) {
                        $q->where('ma_nha_hang', $maNhaHang);
                    }
                })
                ->count();

            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Throwable $e) {
            return response()->json(['success' => true, 'count' => 0]);
        }
    }

    /**
     * GET /api/applications/status
     * Trả về trạng thái các đơn ứng tuyển của ứng viên hiện tại (dành cho Ứng viên).
     */
    public function getApplicationStatus(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return response()->json(['success' => false, 'data' => []], 401);
        }

        $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);

        try {
            ChamCongController::processAutoApprovals();

            $apps = HoSoUngTuyen::with(['chamCong', 'caLamViec'])
                ->where('ma_nguoi_dung', $maNguoiDung)
                ->orderBy('ngay_nop_don', 'desc')
                ->get();

            $data = $apps->map(fn ($a) => [
                'ma_ung_tuyen' => $a->ma_ung_tuyen,
                'ma_ca_lam' => $a->ma_ca_lam,
                'trang_thai_ung_tuyen' => $a->trang_thai_ung_tuyen,
                'ghi_chu_nha_tuyen_dung' => $a->ghi_chu_nha_tuyen_dung,
                'ngay_lam_viec' => $a->caLamViec ? $a->caLamViec->ngay_lam_viec : null,
                'trang_thai_cham_cong' => $a->chamCong ? $a->chamCong->trang_thai_cham_cong : null,
                'so_gio_lam_thuc_te' => $a->chamCong ? (float) $a->chamCong->so_gio_lam_thuc_te : 0,
            ])->values();

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }
}
