<?php

namespace App\Http\Controllers;

use App\Models\CaLamViec;
use App\Models\DanhGiaCaLam;
use App\Models\HoSoUngTuyen;
use App\Models\LichSuChamCong;
use App\Models\NguoiDung;
use App\Models\ThongBao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DanhGiaCaLamController extends Controller
{
    /**
     * Lấy user hiện tại từ Auth hoặc Session
     */
    private function getCurrentUser()
    {
        $user = Auth::user();
        if (! $user && session()->has('user')) {
            $sessionUser = session('user');
            $maNguoiDung = is_array($sessionUser) ? ($sessionUser['ma_nguoi_dung'] ?? null) : ($sessionUser->ma_nguoi_dung ?? null);
            if ($maNguoiDung) {
                $user = NguoiDung::find($maNguoiDung);
            }
        }

        return $user;
    }

    /**
     * Gửi Đánh giá 2 chiều (Quản lý <-> Ứng viên)
     */
    public function store(Request $request)
    {
        $user = $this->getCurrentUser();
        if (! $user) {
            $msg = 'Vui lòng đăng nhập để thực hiện đánh giá.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 401);
            }

            return redirect()->route('login')->with('error', $msg);
        }

        $maCaLam = (int) $request->input('ma_ca_lam', 0);
        $soSao = (int) ($request->input('so_sao') ?? $request->input('diem_danh_gia', 0));
        $noiDungNhanXet = trim((string) ($request->input('noi_dung_nhan_xet') ?? $request->input('nhan_xet', '')));
        $tieuChiThaiDo = max(1, min(5, (int) ($request->input('tieu_chi_thai_do') ?: $soSao)));
        $tieuChiChuyenCan = max(1, min(5, (int) ($request->input('tieu_chi_chuyen_can') ?: $soSao)));
        $tieuChiHieuSuat = max(1, min(5, (int) ($request->input('tieu_chi_hieu_suat') ?: $soSao)));

        if ($maCaLam <= 0) {
            $msg = 'Mã ca làm việc không hợp lệ.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        if ($soSao < 1 || $soSao > 5) {
            $msg = 'Điểm đánh giá phải từ 1 đến 5 sao.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        if (mb_strlen($noiDungNhanXet) < 3) {
            $msg = 'Vui lòng nhập nội dung nhận xét đánh giá ca làm việc (tối thiểu 3 ký tự).';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        $caLam = CaLamViec::with(['nhaHang.thuongHieu'])->find($maCaLam);
        if (! $caLam) {
            $msg = 'Không tìm thấy thông tin ca làm việc.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            return redirect()->back()->with('error', $msg);
        }

        // ==============================================================
        // RÀNG BUỘC THỜI HẠN: Chỉ cho phép đánh giá trong vòng 7 ngày kể từ khi kết thúc ca làm việc
        // ==============================================================
        $thoiGianKetThuc = null;
        $chamCongQuery = LichSuChamCong::where('ma_ca_lam', $maCaLam);
        if ($user->vai_tro === 'ung_vien') {
            $chamCongQuery->where('ma_nguoi_dung', $user->ma_nguoi_dung);
        } elseif ($request->filled('ma_nguoi_duoc_danh_gia')) {
            $chamCongQuery->where('ma_nguoi_dung', (int) $request->input('ma_nguoi_duoc_danh_gia'));
        }
        $chamCong = $chamCongQuery->orderBy('ma_cham_cong', 'desc')->first();

        if ($chamCong && $chamCong->thoi_gian_ra_thuc_te) {
            $thoiGianKetThuc = Carbon::parse($chamCong->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh');
        } elseif (! empty($caLam->ngay_lam_viec)) {
            $gioKetThuc = $caLam->gio_ket_thuc ?: '23:59:59';
            $thoiGianKetThuc = Carbon::parse($caLam->ngay_lam_viec.' '.$gioKetThuc, 'Asia/Ho_Chi_Minh');
        }

        if ($thoiGianKetThuc && $thoiGianKetThuc->copy()->addDays(7)->isPast()) {
            $this->clearPendingReviewSession();
            $msg = 'Đã quá thời hạn 7 ngày kể từ khi kết thúc ca làm việc. Bạn không thể thực hiện đánh giá cho ca này nữa!';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        // ==============================================================
        // 1. ĐIỀU KIỆN ĐÁNH GIÁ (Ứng viên sau khi Ra ca / Quản lý sau khi duyệt)
        // ==============================================================
        $userRole = $user->vai_tro;
        $maNguoiDanhGia = $user->ma_nguoi_dung;
        $isEligible = false;

        if ($userRole === 'ung_vien') {
            // Ứng viên: Cho phép đánh giá ngay sau khi đã Ra ca hoặc ca đã hoàn thành/duyệt
            if ($caLam->trang_thai_ca === 'hoan_thanh') {
                $isEligible = true;
            } else {
                $hasCheckedOut = LichSuChamCong::where('ma_ca_lam', $maCaLam)
                    ->where('ma_nguoi_dung', $maNguoiDanhGia)
                    ->where(function ($q) {
                        $q->whereNotNull('thoi_gian_ra_thuc_te')
                            ->orWhereIn('trang_thai_cham_cong', ['cho_xac_nhan', 'da_xac_nhan', 'tu_dong_duyet']);
                    })
                    ->exists();
                if ($hasCheckedOut) {
                    $isEligible = true;
                }
            }

            if (! $isEligible) {
                $msg = 'Bạn cần hoàn thành ca làm và thực hiện Ra ca trước khi đánh giá!';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }
        } else {
            // Quản lý: Đánh giá khi ca hoàn thành hoặc đã duyệt chấm công
            if ($caLam->trang_thai_ca === 'hoan_thanh') {
                $isEligible = true;
            } else {
                $hasApprovedAttendance = LichSuChamCong::where('ma_ca_lam', $maCaLam)
                    ->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet'])
                    ->exists();
                if ($hasApprovedAttendance) {
                    $isEligible = true;
                }
            }

            if (! $isEligible) {
                $msg = 'Chỉ cho phép đánh giá khi ca làm việc đã hoàn thành hoặc đã được duyệt chấm công!';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }
        }

        // ==============================================================
        // 2. XÁC ĐỊNH LOẠI ĐÁNH GIÁ & ĐỐI TƯỢNG ĐƯỢC ĐÁNH GIÁ
        // ==============================================================
        $userRole = $user->vai_tro;
        $maNguoiDanhGia = $user->ma_nguoi_dung;
        $maNguoiDuocDanhGia = 0;
        $loaiDanhGia = '';

        if ($userRole === 'ung_vien') {
            $loaiDanhGia = 'ung_vien_danh_gia_nha_hang';

            // Kiểm tra ứng viên này có tham gia ca làm không
            $ungTuyen = HoSoUngTuyen::where('ma_ca_lam', $maCaLam)
                ->where('ma_nguoi_dung', $maNguoiDanhGia)
                ->whereIn('trang_thai_ung_tuyen', ['da_nhan', 'da_duyet'])
                ->first();

            if (! $ungTuyen) {
                $msg = 'Bạn chỉ có thể đánh giá ca làm việc mà bạn đã được phê duyệt làm việc!';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }

                return redirect()->back()->with('error', $msg);
            }

            // Người được đánh giá là Quản lý của Nhà hàng
            $nhaHang = $caLam->nhaHang;
            if ($nhaHang && $nhaHang->ma_quan_ly) {
                $maNguoiDuocDanhGia = $nhaHang->ma_quan_ly;
            } else {
                // Tìm quản lý có trực thuộc nhà hàng này
                $ql = NguoiDung::where('vai_tro', 'quan_ly_nha_hang')
                    ->where('ma_nha_hang_truc_thuoc', $caLam->ma_nha_hang)
                    ->first();
                $maNguoiDuocDanhGia = $ql ? $ql->ma_nguoi_dung : ($nhaHang ? $nhaHang->ma_nha_hang : 1);
            }

        } elseif ($userRole === 'quan_ly_nha_hang' || $userRole === 'quan_tri_vien') {
            $loaiDanhGia = 'nha_hang_danh_gia_ung_vien';
            $targetUv = (int) ($request->input('ma_nguoi_duoc_danh_gia') ?? $request->input('ma_ung_vien', 0));

            if ($targetUv <= 0) {
                // Thử tìm ứng viên tham gia ca này
                $ungTuyen = HoSoUngTuyen::where('ma_ca_lam', $maCaLam)
                    ->whereIn('trang_thai_ung_tuyen', ['da_nhan', 'da_duyet'])
                    ->first();
                $targetUv = $ungTuyen ? $ungTuyen->ma_nguoi_dung : 0;
            }

            if ($targetUv <= 0) {
                $msg = 'Không tìm thấy ứng viên cần đánh giá cho ca làm việc này.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }

            $maNguoiDuocDanhGia = $targetUv;
        } else {
            $msg = 'Vai trò của bạn không có quyền thực hiện đánh giá này.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }

            return redirect()->back()->with('error', $msg);
        }

        // ==============================================================
        // 3. KIỂM TRA ĐIỀU KIỆN 1 LẦN DUY NHẤT CHO MỖI CA LÀM
        // ==============================================================
        $daDanhGia = DanhGiaCaLam::where('ma_ca_lam', $maCaLam)
            ->where('ma_nguoi_danh_gia', $maNguoiDanhGia)
            ->where('ma_nguoi_duoc_danh_gia', $maNguoiDuocDanhGia)
            ->first();

        if ($daDanhGia) {
            $this->clearPendingReviewSession();
            $msg = 'Bạn đã thực hiện đánh giá cho ca làm việc này rồi! Mỗi bên chỉ được đánh giá 1 lần duy nhất.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('warning', $msg);
        }

        // ==============================================================
        // 4. LƯU BẢN GHI ĐÁNH GIÁ
        // ==============================================================
        $danhGia = DanhGiaCaLam::create([
            'ma_ca_lam' => $maCaLam,
            'ma_nguoi_danh_gia' => $maNguoiDanhGia,
            'ma_nguoi_duoc_danh_gia' => $maNguoiDuocDanhGia,
            'loai_danh_gia' => $loaiDanhGia,
            'so_sao' => $soSao,
            'tieu_chi_thai_do' => $tieuChiThaiDo,
            'tieu_chi_chuyen_can' => $tieuChiChuyenCan,
            'tieu_chi_hieu_suat' => $tieuChiHieuSuat,
            'noi_dung_nhan_xet' => $noiDungNhanXet,
            'ngay_danh_gia' => Carbon::now('Asia/Ho_Chi_Minh'),
        ]);

        // Xóa cờ session bắt buộc đánh giá sau khi lưu thành công
        $this->clearPendingReviewSession();

        // Đồng bộ điểm đánh giá trung bình vào hồ sơ ứng viên (nếu có cột diem_danh_gia)
        if ($loaiDanhGia === 'nha_hang_danh_gia_ung_vien') {
            try {
                if (Schema::hasColumn('ho_so_ung_vien', 'diem_danh_gia')) {
                    $statsUv = DanhGiaCaLam::tinhDiemTrungBinhUngVien($maNguoiDuocDanhGia);
                    \App\Models\HoSoUngVien::where('ma_nguoi_dung', $maNguoiDuocDanhGia)
                        ->update(['diem_danh_gia' => $statsUv['diem_tb']]);
                }
            } catch (\Throwable $e) {
            }
        }

        // ==============================================================
        // 5. GỬI THÔNG BÁO IN-APP
        // ==============================================================
        try {
            $tenCa = $caLam->ten_vi_tri_tuyen_dung;
            if ($userRole === 'ung_vien') {
                ThongBao::taoBaoTri(
                    $maNguoiDuocDanhGia,
                    'Đánh giá mới từ Ứng viên',
                    "Ứng viên [{$user->ho_ten}] vừa đánh giá {$soSao} sao cho ca làm [{$tenCa}].",
                    '/thong-tin-ca-nhan'
                );
            } else {
                ThongBao::taoBaoTri(
                    $maNguoiDuocDanhGia,
                    'Nhà hàng đã đánh giá ca làm của bạn',
                    "Nhà hàng đã chấm {$soSao} sao cho phần thể hiện của bạn tại ca làm [{$tenCa}].",
                    '/ho-so-ca-nhan'
                );
            }
        } catch (\Throwable $e) {
        }

        $msgSuccess = 'Gửi đánh giá ca làm việc thành công! Cảm ơn bạn đã phản hồi.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msgSuccess,
                'data' => $danhGia,
            ]);
        }

        return redirect()->back()->with('success', $msgSuccess);
    }

    /**
     * Xóa trạng thái bắt buộc đánh giá trong session sau khi hoàn tất hoặc đã tồn tại đánh giá.
     */
    private function clearPendingReviewSession(): void
    {
        session()->forget([
            'show_danh_gia_modal',
            'show_danh_gia_ung_vien_modal',
            'ma_ca_lam_danh_gia',
            'review_shift_id',
            'review_target_id',
            'review_target_name',
            'review_shift_name',
        ]);
    }
}
