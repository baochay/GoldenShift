<?php

namespace App\Http\Controllers;

use App\Models\BaoCaoViPham;
use App\Models\CaLamViec;
use App\Models\LichSuChamCong;
use App\Models\NguoiDung;
use App\Models\ThongBao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaoCaoViPhamController extends Controller
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
     * Gửi Báo cáo vi phạm mới (kèm ràng buộc ca hoàn thành & thời hạn 7 ngày)
     */
    public function store(Request $request)
    {
        $user = $this->getCurrentUser();
        if (! $user) {
            $msg = 'Vui lòng đăng nhập để gửi báo cáo vi phạm.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 401);
            }

            return redirect()->route('login')->with('error', $msg);
        }

        $request->validate([
            'ma_nguoi_vi_pham' => 'required|integer',
            'loai_vi_pham' => 'required|string|max:100',
            'mo_ta_chi_tiet' => 'required|string|min:6',
            'hinh_anh_minh_chung' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ], [
            'ma_nguoi_vi_pham.required' => 'Thiếu thông tin đối tượng bị báo cáo.',
            'loai_vi_pham.required' => 'Vui lòng chọn lý do vi phạm.',
            'mo_ta_chi_tiet.required' => 'Vui lòng nhập mô tả chi tiết diễn biến sự việc.',
            'mo_ta_chi_tiet.min' => 'Mô tả chi tiết vi phạm phải có ít nhất 6 ký tự.',
            'hinh_anh_minh_chung.image' => 'File minh chứng phải là hình ảnh hợp lệ (JPG, PNG, WEBP).',
            'hinh_anh_minh_chung.max' => 'Dung lượng ảnh minh chứng tối đa 5MB.',
        ]);

        $maCaLam = (int) $request->input('ma_ca_lam', 0);
        $maNguoiViPham = (int) $request->input('ma_nguoi_vi_pham');
        $loaiViPham = $request->input('loai_vi_pham');
        $moTa = trim($request->input('mo_ta_chi_tiet'));

        // ==============================================================
        // 1. RÀNG BUỘC THEO CA LÀM VIỆC (NẾU CÓ MÃ CA LÀM)
        // ==============================================================
        if ($maCaLam > 0) {
            $caLam = CaLamViec::find($maCaLam);
            if (! $caLam) {
                $msg = 'Không tìm thấy ca làm việc liên quan.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 404);
                }

                return redirect()->back()->with('error', $msg);
            }

            // ĐIỀU KIỆN 1: Chỉ cho phép báo cáo khi ca làm việc đã hoàn thành
            $isHoanThanh = false;
            if ($caLam->trang_thai_ca === 'hoan_thanh') {
                $isHoanThanh = true;
            } else {
                $hasApprovedAttendance = LichSuChamCong::where('ma_ca_lam', $maCaLam)
                    ->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet'])
                    ->exists();
                if ($hasApprovedAttendance) {
                    $isHoanThanh = true;
                }
            }

            if (! $isHoanThanh) {
                $msg = 'Chỉ được phép gửi báo cáo vi phạm khi ca làm việc đã ở trạng thái hoàn thành!';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }

            // ĐIỀU KIỆN 2: Ràng buộc thời hạn 7 ngày kể từ khi ca kết thúc / ra ca
            $thoiGianKetThuc = null;
            $chamCong = LichSuChamCong::where('ma_ca_lam', $maCaLam)->orderBy('ma_cham_cong', 'desc')->first();
            if ($chamCong && $chamCong->thoi_gian_ra_thuc_te) {
                $thoiGianKetThuc = Carbon::parse($chamCong->thoi_gian_ra_thuc_te);
            } elseif ($caLam->ngay_lam_viec) {
                $gioKetThuc = $caLam->gio_ket_thuc ?: '23:59:59';
                $thoiGianKetThuc = Carbon::parse($caLam->ngay_lam_viec.' '.$gioKetThuc);
            }

            if ($thoiGianKetThuc && $thoiGianKetThuc->copy()->addDays(7)->isPast()) {
                $msg = 'Đã quá thời hạn 7 ngày để gửi báo cáo vi phạm cho ca làm việc này!';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }
        }

        // Kiểm tra đối tượng vi phạm có tồn tại không
        $targetUser = NguoiDung::find($maNguoiViPham);
        if (! $targetUser) {
            $msg = 'Đối tượng bị báo cáo không tồn tại trên hệ thống.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            return redirect()->back()->with('error', $msg);
        }

        // Không tự báo cáo chính mình
        if ($targetUser->ma_nguoi_dung === $user->ma_nguoi_dung) {
            $msg = 'Bạn không thể tự gửi báo cáo vi phạm đối với chính bản thân!';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        // Xử lý upload ảnh minh chứng nếu có
        $hinhAnhPath = null;
        if ($request->hasFile('hinh_anh_minh_chung')) {
            $file = $request->file('hinh_anh_minh_chung');
            $fileName = 'minh_chung_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $destinationPath = public_path('assets/uploads/reports');
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $fileName);
            $hinhAnhPath = 'assets/uploads/reports/'.$fileName;
        }

        // Tạo báo cáo vi phạm (loại bỏ muc_do_nghiem_trong, mặc định 'trung_binh')
        $baoCao = BaoCaoViPham::create([
            'ma_nguoi_vi_pham' => $maNguoiViPham,
            'ma_nguoi_bao_cao' => $user->ma_nguoi_dung,
            'ma_ca_lam' => $maCaLam > 0 ? $maCaLam : null,
            'loai_vi_pham' => $loaiViPham,
            'muc_do_nghiem_trong' => 'trung_binh',
            'mo_ta_chi_tiet' => $moTa,
            'hinh_anh_minh_chung' => $hinhAnhPath,
            'so_tien_phat_du_kien' => 0.00,
            'trang_thai_xu_ly' => 'cho_xuly',
            'ngay_lap_bao_cao' => now(),
        ]);

        // Gửi thông báo đến Quản trị viên
        try {
            $admins = NguoiDung::where('vai_tro', 'quan_tri_vien')->get();
            foreach ($admins as $admin) {
                ThongBao::taoBaoTri(
                    $admin->ma_nguoi_dung,
                    'Báo cáo vi phạm mới cần xử lý',
                    "Có báo cáo vi phạm mới từ [{$user->ho_ten}] về đối tượng [{$targetUser->ho_ten}].",
                    '/admin/bao-cao-vi-pham'
                );
            }
        } catch (\Throwable $e) {
        }

        $msgSuccess = 'Gửi báo cáo vi phạm thành công! Ban quản trị sẽ đối soát minh chứng và xử lý trong thời gian sớm nhất.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msgSuccess,
                'data' => $baoCao,
            ]);
        }

        return redirect()->back()->with('success', $msgSuccess);
    }
}
