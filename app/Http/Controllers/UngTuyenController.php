<?php

namespace App\Http\Controllers;

use App\Models\CaLamViec;
use App\Models\HoSoUngTuyen;
use App\Models\NguoiDung;
use App\Models\ThongBao;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UngTuyenController extends Controller
{
    /**
     * 1. Tiếp nhận đơn nộp ứng tuyển ca làm từ Ứng viên (POST AJAX)
     */
    public function nopDon(Request $request)
    {
        $user = session('user') ?? auth()->user();
        $maNguoiDung = null;

        if ($user) {
            $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 6) : (int) ($user->ma_nguoi_dung ?? 6);
        } else {
            $hoTen = trim($request->input('ho_ten', ''));
            $soDienThoai = trim($request->input('so_dien_thoai', ''));

            if (empty($hoTen) || empty($soDienThoai)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa đăng nhập. Vui lòng đăng nhập hoặc nhập đầy đủ Họ tên và Số điện thoại để gửi hồ sơ.',
                ], 401);
            }

            // Tìm hoặc gán người dùng ứng viên
            $candidate = NguoiDung::where('so_dien_thoai', $soDienThoai)->first();
            if ($candidate) {
                $maNguoiDung = $candidate->ma_nguoi_dung;
            } else {
                $maNguoiDung = 6; // Default demo candidate ID
            }
        }

        $maCaLam = (int) $request->input('ma_ca_lam', 0);
        $loiNhan = trim($request->input('loi_nhan', ''));

        if ($maCaLam <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Mã ca làm việc không hợp lệ.',
            ], 400);
        }

        $shift = CaLamViec::find($maCaLam);
        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'Ca làm việc không tồn tại hoặc đã ngừng tuyển.',
            ], 404);
        }

        // Kiểm tra ca làm đã đủ người chưa
        $soLuongTuyen = (int) $shift->so_luong_tuyen;
        $soLuongDaTuyen = (int) $shift->so_luong_da_tuyen;
        if (in_array($shift->trang_thai_ca, ['da_du_nguoi', 'hoan_thanh', 'da_huy']) || ($soLuongTuyen > 0 && $soLuongDaTuyen >= $soLuongTuyen)) {
            return response()->json([
                'success' => false,
                'message' => 'Ca làm việc này đã đủ số lượng tuyển dụng, không thể nộp đơn thêm.',
            ], 400);
        }

        // 1. Kiểm tra đã nộp ca này chưa
        $alreadyApplied = HoSoUngTuyen::where('ma_ca_lam', $maCaLam)
            ->where('ma_nguoi_dung', $maNguoiDung)
            ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn ĐÃ ỨNG TUYỂN ca làm này trước đó. Vui lòng chờ quản lý nhà hàng xét duyệt.',
            ], 400);
        }

        // 2. Kiểm tra trùng giờ làm việc trong ngày
        $hasOverlap = HoSoUngTuyen::where('ma_nguoi_dung', $maNguoiDung)
            ->whereIn('trang_thai_ung_tuyen', ['cho_duyet', 'da_nhan'])
            ->whereHas('caLamViec', function ($q) use ($shift) {
                $q->where('ngay_lam_viec', $shift->ngay_lam_viec)
                    ->where('ma_ca_lam', '!=', $shift->ma_ca_lam)
                    ->where(function ($t) use ($shift) {
                        $t->where('gio_bat_dau', '<', $shift->gio_ket_thuc)
                            ->where('gio_ket_thuc', '>', $shift->gio_bat_dau);
                    });
            })->exists();

        if ($hasOverlap) {
            return response()->json([
                'success' => false,
                'message' => 'Ứng tuyển KHÔNG THÀNH CÔNG: Bạn đã có ca làm việc khác trùng khung giờ trong ngày '.date('d/m/Y', strtotime($shift->ngay_lam_viec)).'.',
            ], 400);
        }

        // Tạo hồ sơ ứng tuyển
        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $maCaLam,
            'ma_nguoi_dung' => $maNguoiDung,
            'loi_nhan_ung_vien' => $loiNhan,
            'trang_thai_ung_tuyen' => 'cho_duyet',
        ]);

        // Tạo thông báo cho Quản lý nhà hàng khi có Ứng viên nộp đơn
        try {
            if ($shift->ma_nha_hang) {
                $quanLyList = NguoiDung::where('vai_tro', 'quan_ly_nha_hang')
                    ->where('ma_nha_hang_truc_thuoc', $shift->ma_nha_hang)
                    ->get();
                foreach ($quanLyList as $ql) {
                    ThongBao::taoBaoTri(
                        $ql->ma_nguoi_dung,
                        'Ứng viên mới nộp đơn vào ca làm',
                        "Có ứng viên mới vừa ứng tuyển vào ca [{$shift->ten_vi_tri_tuyen_dung}] ngày ".date('d/m/Y', strtotime($shift->ngay_lam_viec)).'. Vui lòng vào trang quản lý để xét duyệt.',
                        '/quan-ly/ca-lam/'.$shift->ma_ca_lam.'/ung-vien'
                    );
                }
            }
        } catch (\Throwable $e) { /* ignore */
        }

        return response()->json([
            'success' => true,
            'message' => 'Ứng tuyển THÀNH CÔNG! Đơn ứng tuyển ca làm ['.$shift->ten_vi_tri_tuyen_dung.'] đã được nộp với trạng thái [Chờ duyệt].',
            'ma_ung_tuyen' => $app->ma_ung_tuyen,
        ]);
    }

    /**
     * 2. Dành cho Ứng viên: Giao diện xem danh sách các ca làm đã ứng tuyển
     */
    public function danhSachDaNop(Request $request)
    {
        ChamCongController::processAutoApprovals();

        $user = session('user') ?? auth()->user();
        $maNguoiDung = $user ? (is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 6) : (int) ($user->ma_nguoi_dung ?? 6)) : 6;

        $appliedList = HoSoUngTuyen::with(['caLamViec.nhaHang.thuongHieu', 'chamCong'])
            ->where('ma_nguoi_dung', $maNguoiDung)
            ->orderBy('ngay_nop_don', 'desc')
            ->paginate(5)
            ->withQueryString();

        $activeAttendance = \App\Models\LichSuChamCong::where('ma_nguoi_dung', $maNguoiDung)
            ->whereIn('trang_thai_cham_cong', ['dung_gio', 'di_muon'])
            ->whereNull('thoi_gian_ra_thuc_te')
            ->first();
        $activeMaUngTuyen = $activeAttendance ? (int) $activeAttendance->ma_ung_tuyen : 0;

        return view('ung_tuyen.danh_sach_da_nop', compact('appliedList', 'activeMaUngTuyen'));
    }

    /**
     * 3. Dành cho Quản lý nhà hàng: Danh sách ứng viên nộp vào ca làm của nhà hàng
     */
    public function danhSachUngVien(Request $request)
    {
        ChamCongController::processAutoApprovals();

        $user = session('user') ?? auth()->user();
        $userRole = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        $maNhaHang = is_array($user) ? (int) ($user['ma_nha_hang_truc_thuoc'] ?? 1) : (int) ($user->ma_nha_hang_truc_thuoc ?? 1);
        $maCaLam = (int) $request->input('ma_ca_lam', 0);

        $query = HoSoUngTuyen::with(['nguoiDung.hoSoUngVien', 'caLamViec.nhaHang']);

        if ($maCaLam > 0) {
            $query->where('ma_ca_lam', $maCaLam);
        } elseif ($userRole === 'quan_ly_nha_hang' || $maNhaHang > 0) {
            $query->whereHas('caLamViec', function ($q) use ($maNhaHang) {
                $q->where('ma_nha_hang', $maNhaHang);
            });
        }

        $applicantList = $query->orderBy('ngay_nop_don', 'desc')->paginate(5)->withQueryString();

        return view('ung_tuyen.danh_sach_ung_vien', compact('applicantList', 'maCaLam'));
    }

    /**
     * 4. Dành cho Quản lý: Lấy danh sách ứng viên theo mã ca làm việc (AJAX endpoint)
     */
    public function danhSachTheoCa(Request $request)
    {
        $maCaLam = (int) $request->input('ma_ca_lam', 0);
        if ($maCaLam <= 0) {
            return response()->json(['success' => false, 'message' => 'Mã ca làm việc không hợp lệ.'], 400);
        }

        $shift = CaLamViec::with(['nhaHang.thuongHieu'])->find($maCaLam);
        if (! $shift) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy ca làm việc yêu cầu.'], 404);
        }

        $applicants = HoSoUngTuyen::with(['nguoiDung.hoSoUngVien'])
            ->where('ma_ca_lam', $maCaLam)
            ->orderBy('ngay_nop_don', 'desc')
            ->get();

        $pendingCount = $applicants->where('trang_thai_ung_tuyen', 'cho_duyet')->count();
        $soLuongTuyen = (int) $shift->so_luong_tuyen;
        $soLuongDaTuyen = (int) $shift->so_luong_da_tuyen;
        $isFull = in_array($shift->trang_thai_ca, ['da_du_nguoi', 'hoan_thanh']) || ($soLuongTuyen > 0 && $soLuongDaTuyen >= $soLuongTuyen);

        $applicantData = $applicants->map(function ($app) {
            $user = $app->nguoiDung;
            $profile = $user ? $user->hoSoUngVien : null;
            $exp = $profile->kinh_nghiem_lam_viec ?? $user->kinh_nghiem_lam_viec ?? 'Chưa cập nhật kinh nghiệm chi tiết.';

            return [
                'ma_ung_tuyen' => $app->ma_ung_tuyen,
                'ho_ten' => $user->ho_ten ?? 'Ứng viên',
                'so_dien_thoai' => $user->so_dien_thoai ?? '',
                'email' => $user->email ?? '',
                'kinh_nghiem_lam_viec' => $exp,
                'loi_nhan_ung_vien' => $app->loi_nhan_ung_vien,
                'trang_thai_ung_tuyen' => $app->trang_thai_ung_tuyen,
                'ghi_chu_nha_tuyen_dung' => $app->ghi_chu_nha_tuyen_dung,
                'ngay_nop_don' => $app->ngay_nop_don ? $app->ngay_nop_don->toDateTimeString() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'ca_lam' => [
                'ma_ca_lam' => (int) $shift->ma_ca_lam,
                'ten_vi_tri_tuyen_dung' => $shift->ten_vi_tri_tuyen_dung,
                'ten_nha_hang' => $shift->nhaHang->ten_nha_hang ?? '',
                'ten_thuong_hieu' => $shift->nhaHang->thuongHieu->ten_thuong_hieu ?? '',
                'ngay_lam_viec' => date('d/m/Y', strtotime($shift->ngay_lam_viec)),
                'ngay_lam_viec_raw' => $shift->ngay_lam_viec,
                'gio_bat_dau' => substr($shift->gio_bat_dau, 0, 5),
                'gio_ket_thuc' => substr($shift->gio_ket_thuc, 0, 5),
                'muc_luong_theo_gio' => number_format($shift->muc_luong_theo_gio, 0, ',', '.').' đ/h',
                'so_luong_tuyen' => $soLuongTuyen,
                'so_luong_da_tuyen' => $soLuongDaTuyen,
                'trang_thai_ca' => $shift->trang_thai_ca,
                'is_full' => $isFull,
            ],
            'so_luong_cho_duyet' => $pendingCount,
            'tong_ung_vien' => $applicants->count(),
            'ung_vien_list' => $applicantData,
        ]);
    }

    /**
     * 5. Dành cho Quản lý: Xử lý duyệt / từ chối ứng viên (AJAX & POST)
     */
    public function xetDuyetUngVien(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->filled('is_ajax');

        $user = session('user') ?? auth()->user();
        $nguoiDuyetId = $user ? (is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 2) : (int) ($user->ma_nguoi_dung ?? 2)) : 2;

        $maUngTuyen = (int) $request->input('ma_ung_tuyen', 0);
        $action = trim($request->input('hanh_dong', ''));
        $ghiChu = trim($request->input('ly_do_tu_choi', $request->input('ghi_chu', '')));

        if ($maUngTuyen <= 0 || ! in_array($action, ['duyet', 'tu_choi'])) {
            $msg = 'Thao tác xét duyệt không hợp lệ hoặc thiếu mã ứng tuyển.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        if ($action === 'tu_choi' && empty($ghiChu)) {
            $msg = 'Vui lòng nhập lý do từ chối hồ sơ ứng viên (bắt buộc).';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $app = HoSoUngTuyen::with('caLamViec')->find($maUngTuyen);
        if (! $app) {
            $msg = 'Không tìm thấy hồ sơ ứng tuyển.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            return redirect()->back()->with('error', $msg);
        }

        $shift = $app->caLamViec;

        if ($action === 'duyet') {
            if ($shift) {
                $startDateTime = strtotime($shift->ngay_lam_viec.' '.$shift->gio_bat_dau);
                if (time() >= $startDateTime) {
                    $msg = 'Ca làm việc đã bắt đầu hoặc trôi qua, không thể duyệt thêm ứng viên!';
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }

                    return redirect()->back()->with('error', $msg);
                }

                // Tăng số lượng đã tuyển
                if ($app->trang_thai_ung_tuyen !== 'da_nhan') {
                    $shift->so_luong_da_tuyen = (int) $shift->so_luong_da_tuyen + 1;
                    if ($shift->so_luong_da_tuyen >= (int) $shift->so_luong_tuyen) {
                        $shift->trang_thai_ca = 'da_du_nguoi';
                    }
                    $shift->save();
                }
            }
            $status = 'da_nhan';
        } else {
            // Nếu từ chối ca mà trước đó đã nhận, giảm số lượng đã tuyển
            if ($app->trang_thai_ung_tuyen === 'da_nhan' && $shift && $shift->so_luong_da_tuyen > 0) {
                $shift->so_luong_da_tuyen = (int) $shift->so_luong_da_tuyen - 1;
                if ($shift->so_luong_da_tuyen < (int) $shift->so_luong_tuyen && $shift->trang_thai_ca === 'da_du_nguoi') {
                    $shift->trang_thai_ca = 'dang_tuyen';
                }
                $shift->save();
            }
            $status = 'tu_choi';
        }

        $app->update([
            'trang_thai_ung_tuyen' => $status,
            'ghi_chu_nha_tuyen_dung' => $ghiChu,
            'nguoi_duyet' => $nguoiDuyetId,
            'ngay_duyet' => Carbon::now(),
        ]);

        // Tạo thông báo cho Ứng viên khi hồ sơ được duyệt / từ chối
        try {
            $tenViTri = $shift ? $shift->ten_vi_tri_tuyen_dung : 'ca làm việc';
            $ngayLam = $shift ? date('d/m/Y', strtotime($shift->ngay_lam_viec)) : '';
            if ($action === 'duyet') {
                $tieuDe = 'Ố! Hồ sơ của bạn đã được phê duyệt!';
                $noiDung = "Chúc mừng! Hồ sơ ứng tuyển vị trí [{$tenViTri}] ngày {$ngayLam} của bạn đã được nhà hàng phê duyệt. Hãy chuẩn bị sẵn sàng cho ca làm!";
            } else {
                $tieuDe = 'Hồ sơ ứng tuyển của bạn bị từ chối';
                $noiDung = "Rất tiếc, hồ sơ ứng tuyển vị trí [{$tenViTri}] ngày {$ngayLam} của bạn đã bị từ chối.".(! empty($ghiChu) ? " Lý do: {$ghiChu}" : '');
            }
            ThongBao::taoBaoTri(
                $app->ma_nguoi_dung,
                $tieuDe,
                $noiDung,
                '/ung-vien/ca-lam-da-ung-tuyen'
            );
        } catch (\Throwable $e) { /* ignore */
        }

        $pendingCount = HoSoUngTuyen::where('ma_ca_lam', $app->ma_ca_lam)
            ->where('trang_thai_ung_tuyen', 'cho_duyet')
            ->count();

        $soLuongTuyen = $shift ? (int) $shift->so_luong_tuyen : 1;
        $soLuongDaTuyen = $shift ? (int) $shift->so_luong_da_tuyen : 0;
        $isFull = in_array($shift->trang_thai_ca ?? '', ['da_du_nguoi', 'hoan_thanh']) || ($soLuongTuyen > 0 && $soLuongDaTuyen >= $soLuongTuyen);

        if ($isAjax) {
            $msg = ($action === 'duyet')
                ? 'Đã phê duyệt ứng viên thành công! Trạng thái: [Đã duyệt].'
                : 'Đã từ chối ứng viên và lưu lý do thành công.';

            return response()->json([
                'success' => true,
                'message' => $msg,
                'action' => $action,
                'data' => [
                    'ma_ung_tuyen' => $maUngTuyen,
                    'ma_ca_lam' => $app->ma_ca_lam,
                    'trang_thai_ung_tuyen' => $status,
                    'so_luong_cho_duyet' => $pendingCount,
                    'so_luong_da_tuyen' => $soLuongDaTuyen,
                    'so_luong_tuyen' => $soLuongTuyen,
                    'is_full' => $isFull,
                ],
            ]);
        }

        $flashMsg = ($action === 'duyet')
            ? 'Đã PHÊ DUYỆT hồ sơ ứng viên thành công! Trạng thái: [Đã duyệt].'
            : 'Đã TỪ CHỐI hồ sơ ứng viên và lưu lý do từ chối thành công.';

        return redirect()->back()->with('success', $flashMsg);
    }

    /**
     * Alias method cho duyệt ứng viên
     */
    public function duyet(Request $request)
    {
        return $this->xetDuyetUngVien($request);
    }

    /**
     * Route /quan-ly/ca-lam/{id}/ung-vien - Danh sách ứng viên của 1 ca cụ thể (xem từ link thông báo)
     */
    public function danhSachUngVienTheoCa(Request $request, int $id)
    {
        $request->merge(['ma_ca_lam' => $id]);

        return $this->danhSachUngVien($request);
    }
}
