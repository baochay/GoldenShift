<?php

namespace App\Http\Controllers;

use App\Helpers\HolidayHelper;
use App\Models\CaLamViec;
use App\Models\DanhGiaCaLam;
use App\Models\HoSoUngTuyen;
use App\Models\ThongBao;
use App\Models\ThuongHieu;
use Illuminate\Http\Request;

class CaLamViecController extends Controller
{
    /**
     * 1. Dành cho Ứng viên & Khách: Danh sách ca làm việc kèm bộ lọc CareerViet đa tiêu chí
     * - KHÔNG hiển thị ca đã hủy (da_huy) hoặc ca đã quá ngày
     */
    public function index(Request $request)
    {
        $tuKhoa = trim($request->input('tu_khoa', ''));
        $thuongHieu = trim($request->input('thuong_hieu', ''));
        $tinhThanh = trim($request->input('tinh_thanh_pho', ''));
        $quanHuyen = trim($request->input('quan_huyen', ''));
        $viTri = trim($request->input('vi_tri', ''));
        $mucLuong = trim($request->input('muc_luong', ''));
        $ngayLamViec = trim($request->input('ngay_lam_viec', ''));
        $nam = $request->filled('nam') ? (int) $request->input('nam') : null;
        $trangThai = trim($request->input('trang_thai', $request->input('trang_thai_ca', '')));

        $currentYear = (int) date('Y');
        if ($nam !== null && $nam > $currentYear) {
            $nam = $currentYear;
        }

        $filters = [
            'tu_khoa' => $tuKhoa,
            'thuong_hieu' => $thuongHieu,
            'tinh_thanh_pho' => $tinhThanh,
            'quan_huyen' => $quanHuyen,
            'vi_tri' => $viTri,
            'muc_luong' => $mucLuong,
            'ngay_lam_viec' => $ngayLamViec,
            'nam' => $nam,
            'trang_thai' => $trangThai,
        ];

        // Tự động đóng các ca đã quá ngày
        CaLamViec::autoClosePastShifts();

        $today = date('Y-m-d');
        $query = CaLamViec::with(['nhaHang.thuongHieu']);

        if ($request->filled('trang_thai') && $request->trang_thai !== 'tat_ca') {
            $query->where('trang_thai_ca', '=', $request->trang_thai);
        } elseif (! empty($trangThai) && $trangThai !== 'tat_ca') {
            $query->where('trang_thai_ca', '=', $trangThai);
        } else {
            $query->where('trang_thai_ca', 'dang_tuyen')
                ->where('ngay_lam_viec', '>=', $today);
        }

        // 1. Lọc từ khóa
        if (! empty($tuKhoa)) {
            $query->where(function ($q) use ($tuKhoa) {
                $q->where('ten_vi_tri_tuyen_dung', 'LIKE', "%{$tuKhoa}%")
                    ->orWhere('yeu_cau_cong_viec', 'LIKE', "%{$tuKhoa}%")
                    ->orWhereHas('nhaHang', function ($sub) use ($tuKhoa) {
                        $sub->where('ten_nha_hang', 'LIKE', "%{$tuKhoa}%")
                            ->orWhere('quan_huyen', 'LIKE', "%{$tuKhoa}%")
                            ->orWhere('tinh_thanh_pho', 'LIKE', "%{$tuKhoa}%")
                            ->orWhereHas('thuongHieu', function ($b) use ($tuKhoa) {
                                $b->where('ten_thuong_hieu', 'LIKE', "%{$tuKhoa}%");
                            });
                    });
            });
        }

        // 2. Lọc Thương hiệu
        if (! empty($thuongHieu)) {
            $query->whereHas('nhaHang.thuongHieu', function ($q) use ($thuongHieu) {
                $q->where('ma_dinh_danh', $thuongHieu)
                    ->orWhere('ten_thuong_hieu', 'LIKE', "%{$thuongHieu}%");
            });
        }

        // 3. Lọc Tỉnh / Thành phố
        if (! empty($tinhThanh)) {
            $query->whereHas('nhaHang', function ($q) use ($tinhThanh) {
                $q->where('tinh_thanh_pho', 'LIKE', "%{$tinhThanh}%");
            });
        }

        // 4. Lọc Quận / Huyện
        if (! empty($quanHuyen)) {
            $query->whereHas('nhaHang', function ($q) use ($quanHuyen) {
                $q->where('quan_huyen', 'LIKE', "%{$quanHuyen}%");
            });
        }

        // 5. Lọc Vị trí
        if (! empty($viTri)) {
            $query->where('ten_vi_tri_tuyen_dung', 'LIKE', "%{$viTri}%");
        }

        // 6. Lọc Ngày làm việc
        if (! empty($ngayLamViec)) {
            $query->where('ngay_lam_viec', $ngayLamViec);
        }

        // 7. Lọc Mức lương
        if (! empty($mucLuong)) {
            if ($mucLuong === 'duoi_25') {
                $query->where('muc_luong_theo_gio', '<', 25000);
            } elseif ($mucLuong === '25_35') {
                $query->whereBetween('muc_luong_theo_gio', [25000, 35000]);
            } elseif ($mucLuong === 'tren_35') {
                $query->where('muc_luong_theo_gio', '>', 35000);
            }
        }

        // 8. Lọc Năm
        if (! empty($nam)) {
            $query->whereYear('ngay_lam_viec', $nam);
        }

        try {
            $caLamList = $query->orderBy('ngay_lam_viec', 'asc')
                ->orderBy('gio_bat_dau', 'asc')
                ->orderBy('ma_ca_lam', 'desc')
                ->paginate(6)
                ->withQueryString();

            $thuongHieuList = ThuongHieu::where('trang_thai', 'hoat_dong')->get();
        } catch (\Throwable $e) {
            $caLamList = $this->getMockAvailableShifts($filters);
            $thuongHieuList = $this->getMockBrands();
        }

        return view('ca_lam_viec.search', compact('caLamList', 'thuongHieuList', 'filters'));
    }

    /**
     * 2. Dành cho Ứng viên & Khách: Xem chi tiết ca làm việc
     * URL: /ca-lam-viec/{id} hoặc /ca-lam-viec/chi-tiet?id={ma_ca_lam}
     */
    public function chiTiet(Request $request, $id = null)
    {
        $id = (int) ($id ?: $request->input('id', 0));
        $today = date('Y-m-d');

        try {
            $shift = CaLamViec::with(['nhaHang.thuongHieu'])->find($id);
        } catch (\Throwable $e) {
            $shift = $this->getMockShiftById($id);
        }

        if (! $shift) {
            return redirect('/ca-lam-viec')->with('error', 'Không tìm thấy ca làm việc yêu cầu hoặc ca làm đã hết hạn.');
        }

        // Ca bị hủy hoặc đã qua ngày → không cho xem chi tiết, redirect về danh sách
        if ($shift->trang_thai_ca === 'da_huy') {
            return redirect('/ca-lam-viec')->with('error', 'Ca làm việc này đã bị hủy.');
        }
        if ($shift->ngay_lam_viec < $today) {
            return redirect('/ca-lam-viec')->with('error', 'Ca làm việc này đã kết thúc và không còn nhận ứng tuyển.');
        }

        $user = session('user') ?? auth()->user();
        $isLoggedIn = (bool) $user;
        $soLuongTuyen = (int) $shift->so_luong_tuyen;
        $soLuongDaTuyen = (int) $shift->so_luong_da_tuyen;
        $isFull = ($soLuongTuyen > 0 && $soLuongDaTuyen >= $soLuongTuyen)
               || in_array($shift->trang_thai_ca, ['da_du_nguoi']);

        // Kiểm tra người dùng đã ứng tuyển ca này chưa
        $daUngTuyen = false;
        $maNguoiDung = null;
        if ($isLoggedIn) {
            $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);
            if ($maNguoiDung > 0) {
                try {
                    $daUngTuyen = HoSoUngTuyen::where('ma_ca_lam', $id)
                        ->where('ma_nguoi_dung', $maNguoiDung)
                        ->exists();
                } catch (\Throwable $e) {
                    $daUngTuyen = false;
                }
            }
        }

        $diemNhaHang = ['diem_tb' => 0.0, 'tong_luot' => 0];
        $danhGiaMoiNhat = collect();
        try {
            $diemNhaHang = DanhGiaCaLam::tinhDiemTrungBinhNhaHang($shift->ma_nha_hang);
            $danhGiaMoiNhat = DanhGiaCaLam::whereHas('caLamViec', function ($q) use ($shift) {
                $q->where('ma_nha_hang', $shift->ma_nha_hang);
            })
                ->where('loai_danh_gia', 'ung_vien_danh_gia_nha_hang')
                ->with('nguoiDanhGia')
                ->orderBy('ngay_danh_gia', 'desc')
                ->paginate(5, ['*'], 'reviews_page')
                ->appends($request->except('reviews_page'));
        } catch (\Throwable $e) {
        }

        return view('ca_lam_viec.chi_tiet', compact(
            'shift', 'user', 'isLoggedIn', 'isFull', 'daUngTuyen',
            'soLuongTuyen', 'soLuongDaTuyen', 'diemNhaHang', 'danhGiaMoiNhat'
        ));
    }

    /**
     * Xử lý sự kiện click "Ứng tuyển ngay" ca làm việc tuyển gấp từ Trang chủ
     * - Khách chưa đăng nhập: Lưu URL ca làm gấp (intended URL) -> Chuyển hướng sang trang Đăng nhập kèm thông báo:
     *   "Vui lòng đăng nhập tài khoản Ứng viên để nộp đơn ứng tuyển!"
     * - Ứng viên đã đăng nhập: Mở thẳng trang Chi tiết ca làm việc để xem thông tin và nộp đơn
     */
    public function ungTuyenGap(Request $request, $id)
    {
        $id = (int) $id;
        $user = session('user') ?? auth()->user();
        $userRole = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');

        // 1. Khách chưa đăng nhập: Lưu URL chi tiết ca làm & ID vào session
        if (! $user) {
            $shiftUrl = route('ca-lam-viec.show', $id);
            session([
                'url.intended' => $shiftUrl,
                'ca_lam_gap_id' => $id,
            ]);

            return redirect()->route('login')
                ->with('warning', 'Vui lòng đăng nhập tài khoản Ứng viên để nộp đơn ứng tuyển!')
                ->with('error', 'Vui lòng đăng nhập tài khoản Ứng viên để nộp đơn ứng tuyển!');
        }

        // 2. Ứng viên đã đăng nhập: Mở thẳng trang Chi tiết ca làm việc
        return redirect()->route('ca-lam-viec.show', $id);
    }

    /**
     * 2. Dành cho Quản lý nhà hàng: Màn hình Quản lý danh sách ca làm việc (ca_lam_viec/index.blade.php)
     * - Hiển thị tất cả ca của nhà hàng: Đang tuyển, Đã đủ số lượng, Đã hoàn thành và Đã hủy
     */
    public function danhSachQuanLy(Request $request)
    {
        $user = session('user') ?? auth()->user();
        $userRole = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        $maNhaHang = is_array($user) ? (int) ($user['ma_nha_hang_truc_thuoc'] ?? 1) : (int) ($user->ma_nha_hang_truc_thuoc ?? 1);

        $thang = $request->filled('thang') ? (int) $request->input('thang') : null;
        $nam = $request->filled('nam') ? (int) $request->input('nam') : null;
        $trangThai = trim($request->input('trang_thai', $request->input('trang_thai_ca', '')));

        $currentYear = (int) date('Y');
        if ($nam !== null && $nam > $currentYear) {
            $nam = $currentYear;
        }

        // Tự động đóng các ca đã quá ngày thành hoan_thanh
        CaLamViec::autoClosePastShifts();

        $query = CaLamViec::with(['nhaHang.thuongHieu'])
            ->withCount([
                'hoSoUngTuyen as so_luong_cho_duyet' => function ($q) {
                    $q->where('trang_thai_ung_tuyen', 'cho_duyet');
                },
                'hoSoUngTuyen as tong_so_ung_vien',
            ]);

        // Nếu là quản lý, chỉ xem nhà hàng trực thuộc; admin xem được hết
        if ($userRole === 'quan_ly_nha_hang' || $maNhaHang > 0) {
            $query->where('ma_nha_hang', $maNhaHang);
        }

        if ($thang !== null && $thang > 0) {
            $query->whereMonth('ngay_lam_viec', $thang);
        }

        if ($nam !== null && $nam > 0) {
            $query->whereYear('ngay_lam_viec', $nam);
        }

        // Lọc theo trạng thái ca làm việc
        // LƯU Ý: So sánh chính xác tuyệt đối từng trạng thái, KHÔNG dùng orWhere lẫn các trạng thái khác
        if (! empty($trangThai) && $trangThai !== 'tat_ca') {
            if ($trangThai === 'hoan_thanh') {
                // Lọc CHÍNH XÁC: chỉ lấy ca có trang_thai_ca = 'hoan_thanh'
                // KHÔNG lẫn ca 'da_huy' hay các trạng thái khác dù đã quá ngày
                $query->where('trang_thai_ca', '=', 'hoan_thanh');
            } elseif ($trangThai === 'dang_tuyen') {
                $today = date('Y-m-d');
                $query->where('trang_thai_ca', '=', 'dang_tuyen')
                    ->where('ngay_lam_viec', '>=', $today);
            } else {
                // da_huy, da_du_nguoi, ... — so sánh chính xác
                $query->where('trang_thai_ca', '=', $trangThai);
            }
        }

        try {
            $caLamList = $query->orderBy('ngay_lam_viec', 'desc')
                ->orderBy('gio_bat_dau', 'desc')
                ->orderBy('ma_ca_lam', 'desc')
                ->paginate(10)
                ->withQueryString();
        } catch (\Throwable $e) {
            $caLamList = $this->getMockManagerShifts($maNhaHang, $thang, $nam, $trangThai);
        }

        return view('ca_lam_viec.index', compact('caLamList', 'thang', 'nam', 'maNhaHang', 'trangThai'));
    }

    /**
     * 3. Xử lý lưu ca làm mới hoặc cập nhật ca đã có
     * - Chỉ cho phép sửa trước và trong ngày có ca làm (ngay_lam_viec >= today)
     * - Chặn sửa ca đã qua ngày hoặc ca đã bị hủy
     */
    /**
     * 3. Xử lý lưu ca làm mới hoặc cập nhật ca đã có
     */
    public function luuCaLam(Request $request)
    {
        $id = (int) $request->input('ma_ca_lam', 0);
        if ($id > 0) {
            return $this->update($request);
        }

        return $this->store($request);
    }

    /**
     * 3a. Tạo ca làm việc mới:
     * - Tự động x3 mức lương nếu ngày làm việc là ngày lễ Việt Nam
     */
    public function store(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->filled('is_ajax');

        $user = session('user') ?? auth()->user();
        if (! $user) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'], 401);
            }

            return redirect('/dang-nhap');
        }

        $userRole = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        $tenViTri = trim($request->input('ten_vi_tri_tuyen_dung', ''));
        $ngayLam = trim($request->input('ngay_lam_viec', ''));
        $gioBatDau = trim($request->input('gio_bat_dau', ''));
        $gioKetThuc = trim($request->input('gio_ket_thuc', ''));
        $mucLuong = (float) $request->input('muc_luong_theo_gio', 0);
        $soLuong = (int) $request->input('so_luong_tuyen', 1);
        $yeuCau = trim($request->input('yeu_cau_cong_viec', ''));
        $quyenLoi = trim($request->input('quyen_loi_ca_lam', ''));

        if ($userRole === 'quan_ly_nha_hang') {
            $maNhaHang = is_array($user) ? (int) ($user['ma_nha_hang_truc_thuoc'] ?? 1) : (int) ($user->ma_nha_hang_truc_thuoc ?? 1);
        } else {
            $maNhaHang = (int) $request->input('ma_nha_hang', 1);
        }

        $allowedPositions = [
            'Nhân viên Phục vụ',
            'Nhân viên Tiếp thực',
            'Nhân viên Pha chế',
            'Nhân viên Phụ bếp',
            'Nhân viên Bếp',
        ];

        if (! in_array($tenViTri, $allowedPositions, true)) {
            foreach ($allowedPositions as $pos) {
                if (mb_stripos($tenViTri, explode(' ', $pos)[2] ?? $pos) !== false) {
                    $tenViTri = $pos;
                    break;
                }
            }
        }

        if (empty($tenViTri) || empty($ngayLam) || empty($gioBatDau) || empty($gioKetThuc) || $mucLuong <= 0 || $soLuong <= 0) {
            $msg = 'Vui lòng chọn Vị trí tuyển dụng F&B hợp lệ và nhập đầy đủ Ngày làm, Khung giờ, Mức lương (>0), Số lượng tuyển (>0).';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $today = date('Y-m-d');
        $gioBatDauFormat = strlen($gioBatDau) <= 5 ? $gioBatDau.':00' : $gioBatDau;
        $gioKetThucFormat = strlen($gioKetThuc) <= 5 ? $gioKetThuc.':00' : $gioKetThuc;

        // Ràng buộc thời gian: Chặn tạo ca mới có thời điểm bắt đầu trong quá khứ
        $startTimestamp = strtotime($ngayLam.' '.$gioBatDauFormat);
        $endTimestamp = strtotime($ngayLam.' '.$gioKetThucFormat);
        $nowTimestamp = time();

        if ($startTimestamp <= $nowTimestamp) {
            $msg = 'Không thể tạo ca làm việc có thời gian bắt đầu trong quá khứ hoặc trước thời điểm hiện tại!';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        if ($endTimestamp <= $startTimestamp) {
            $msg = 'Giờ kết thúc ca làm việc phải sau giờ bắt đầu.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        // TỰ ĐỘNG X3 LƯƠNG NẾU RƠI VÀO NGÀY LỄ VIỆT NAM
        $tenNgayLe = HolidayHelper::isVietnameseHoliday($ngayLam);
        $mucLuongGoc = $mucLuong;
        if (! empty($tenNgayLe)) {
            $mucLuongTinh = $mucLuongGoc * 3;
            $msg = '🎉 Ngày làm việc '.date('d/m/Y', strtotime($ngayLam))." rơi vào ngày lễ [{$tenNgayLe}]. Hệ thống đã tự động nhân 3 mức lương theo giờ (Từ ".number_format($mucLuongGoc, 0, ',', '.').'đ/h -> '.number_format($mucLuongTinh, 0, ',', '.').'đ/h)!';
        } else {
            $mucLuongTinh = $mucLuongGoc;
            $msg = "Đã đăng tuyển ca làm việc mới [{$tenViTri}] thành công!";
        }

        $data = [
            'ma_nha_hang' => $maNhaHang,
            'ten_vi_tri_tuyen_dung' => $tenViTri,
            'ngay_lam_viec' => $ngayLam,
            'gio_bat_dau' => $gioBatDauFormat,
            'gio_ket_thuc' => $gioKetThucFormat,
            'muc_luong_theo_gio' => $mucLuongTinh,
            'so_luong_tuyen' => $soLuong,
            'yeu_cau_cong_viec' => $yeuCau,
            'quyen_loi_ca_lam' => $quyenLoi,
            'trang_thai_ca' => 'dang_tuyen',
        ];

        $shift = CaLamViec::create($data);
        session()->flash('success', $msg);
        if (! empty($tenNgayLe)) {
            session()->flash('holiday_notice', $msg);
        }

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'ma_ca_lam' => $shift->ma_ca_lam,
                'action' => 'create',
                'is_holiday' => ! empty($tenNgayLe),
                'holiday_name' => $tenNgayLe,
                'muc_luong_goc' => $mucLuongGoc,
                'muc_luong_tinh' => $mucLuongTinh,
            ]);
        }

        return redirect('/quan-ly/ca-lam-viec')->with('success', $msg);
    }

    /**
     * 3b. Cập nhật ca làm việc (Ràng buộc Cách 1 - Bảo vệ quyền lợi ứng viên):
     * - Đếm tổng số ứng viên đã nộp đơn cho ca này: HoSoUngTuyen::where('ma_ca_lam', $id)->count()
     * - NẾU $soUngVien > 0 (Đã có ứng viên đăng ký/được duyệt):
     *   + Chỉ cho phép cập nhật: so_luong_tuyen, yeu_cau_cong_viec, quyen_loi_ca_lam
     *   + Loại bỏ/bỏ qua các trường: ten_vi_tri_tuyen_dung, ngay_lam_viec, gio_bat_dau, gio_ket_thuc, muc_luong_theo_gio
     *   + Trả về Flash Warning: "Ca làm đã có ứng viên ứng tuyển. Để bảo đảm quyền lợi ứng viên, hệ thống chỉ cho phép cập nhật Số lượng tuyển, Yêu cầu và Quyền lợi!"
     * - NẾU $soUngVien == 0 (Chưa có ai ứng tuyển):
     *   + Cho phép cập nhật tất cả các trường
     *   + Ràng buộc không cho phép chỉnh sửa thời gian bắt đầu thành quá khứ
     */
    public function update(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->filled('is_ajax');

        $user = session('user') ?? auth()->user();
        if (! $user) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'], 401);
            }

            return redirect('/dang-nhap');
        }

        $userRole = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        $id = (int) $request->input('ma_ca_lam', $request->route('id', 0));

        if ($id <= 0) {
            $msg = 'Mã ca làm việc không hợp lệ.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        $shift = CaLamViec::find($id);
        if (! $shift) {
            $msg = 'Không tìm thấy ca làm việc cần cập nhật.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            return redirect()->back()->with('error', $msg);
        }

        if ($shift->trang_thai_ca === 'da_huy') {
            $msg = 'Ca làm việc đã bị hủy, không thể chỉnh sửa.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }

            return redirect()->back()->with('error', $msg);
        }

        $today = date('Y-m-d');
        // Chỉ cho phép sửa trước và trong ngày có ca làm đó
        if ($shift->ngay_lam_viec < $today) {
            $msg = 'Chỉ cho phép sửa ca làm trước và trong ngày có ca làm đó. Sau ngày đó ca đã tự động khóa thành "Đã hoàn thành" và không thể chỉnh sửa!';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        if ($userRole === 'quan_ly_nha_hang') {
            $maNhaHang = is_array($user) ? (int) ($user['ma_nha_hang_truc_thuoc'] ?? 1) : (int) ($user->ma_nha_hang_truc_thuoc ?? 1);
            if ((int) $shift->ma_nha_hang !== $maNhaHang) {
                $msg = 'Bạn không có quyền chỉnh sửa ca làm việc của nhà hàng khác.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }

                return redirect()->back()->with('error', $msg);
            }
        }

        // Đếm tổng số ứng viên đã nộp đơn cho ca này
        $soUngVien = HoSoUngTuyen::where('ma_ca_lam', $id)->count();

        if ($soUngVien > 0) {
            // NẾU $soUngVien > 0 (Đã có ứng viên đăng ký/được duyệt):
            // Chỉ cho phép cập nhật: so_luong_tuyen, yeu_cau_cong_viec, quyen_loi_ca_lam
            // Loại bỏ/bỏ qua các trường: ten_vi_tri_tuyen_dung, ngay_lam_viec, gio_bat_dau, gio_ket_thuc, muc_luong_theo_gio
            $soLuong = (int) $request->input('so_luong_tuyen', $shift->so_luong_tuyen);
            $yeuCau = trim($request->input('yeu_cau_cong_viec', $shift->yeu_cau_cong_viec));
            $quyenLoi = trim($request->input('quyen_loi_ca_lam', $shift->quyen_loi_ca_lam));

            if ($soLuong <= 0) {
                $msg = 'Chỉ tiêu tuyển dụng phải lớn hơn 0.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return redirect()->back()->with('error', $msg);
            }

            $soLuongDaTuyen = (int) $shift->so_luong_da_tuyen;
            if ($soLuong < $soLuongDaTuyen) {
                $msg = 'Chỉ tiêu tuyển dụng mới ('.$soLuong.') không được nhỏ hơn số lượng ứng viên đã được duyệt nhận ca ('.$soLuongDaTuyen.').';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return redirect()->back()->with('error', $msg);
            }

            $data = [
                'so_luong_tuyen' => $soLuong,
                'yeu_cau_cong_viec' => $yeuCau,
                'quyen_loi_ca_lam' => $quyenLoi,
            ];

            if ($soLuongDaTuyen >= $soLuong) {
                $data['trang_thai_ca'] = 'da_du_nguoi';
            } else {
                $data['trang_thai_ca'] = 'dang_tuyen';
            }

            $shift->update($data);

            // Gửi thông báo cho tất cả ứng viên về việc chỉnh sửa ca
            try {
                $dsUngVien = HoSoUngTuyen::where('ma_ca_lam', $id)
                    ->whereIn('trang_thai_ung_tuyen', ['cho_duyet', 'da_nhan'])->get();
                foreach ($dsUngVien as $ut) {
                    ThongBao::taoBaoTri(
                        $ut->ma_nguoi_dung,
                        'Ca làm việc bạn ứng tuyển đã được cập nhật',
                        "Ca làm việc [#CA-{$id}] mà bạn đã ứng tuyển vừa được nhà hàng điều chỉnh thông tin (số lượng tuyển / yêu cầu / quyền lợi). Vui lòng kiểm tra lại.",
                        '/ung-vien/ca-lam-da-ung-tuyen'
                    );
                }
            } catch (\Throwable $e) { /* ignore */
            }

            $warnMsg = 'Ca làm đã có ứng viên ứng tuyển. Để bảo đảm quyền lợi ứng viên, hệ thống chỉ cho phép cập nhật Số lượng tuyển, Yêu cầu và Quyền lợi!';
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => $warnMsg,
                    'warning' => true,
                    'ma_ca_lam' => $id,
                    'action' => 'update',
                ]);
            }

            return redirect('/quan-ly/ca-lam-viec')->with('warning', $warnMsg);

        } else {
            // NẾU $soUngVien == 0 (Chưa có ai ứng tuyển):
            // Cho phép cập nhật tất cả các trường (vẫn kiểm tra ràng buộc không cho phép chỉnh sửa thời gian bắt đầu thành quá khứ)
            $tenViTri = trim($request->input('ten_vi_tri_tuyen_dung', ''));
            $ngayLam = trim($request->input('ngay_lam_viec', ''));
            $gioBatDau = trim($request->input('gio_bat_dau', ''));
            $gioKetThuc = trim($request->input('gio_ket_thuc', ''));
            $mucLuong = (float) $request->input('muc_luong_theo_gio', 0);
            $soLuong = (int) $request->input('so_luong_tuyen', 1);
            $yeuCau = trim($request->input('yeu_cau_cong_viec', ''));
            $quyenLoi = trim($request->input('quyen_loi_ca_lam', ''));

            $allowedPositions = [
                'Nhân viên Phục vụ',
                'Nhân viên Tiếp thực',
                'Nhân viên Pha chế',
                'Nhân viên Phụ bếp',
                'Nhân viên Bếp',
            ];

            if (! in_array($tenViTri, $allowedPositions, true)) {
                foreach ($allowedPositions as $pos) {
                    if (mb_stripos($tenViTri, explode(' ', $pos)[2] ?? $pos) !== false) {
                        $tenViTri = $pos;
                        break;
                    }
                }
            }

            if (empty($tenViTri) || empty($ngayLam) || empty($gioBatDau) || empty($gioKetThuc) || $mucLuong <= 0 || $soLuong <= 0) {
                $msg = 'Vui lòng chọn Vị trí tuyển dụng F&B hợp lệ và nhập đầy đủ Ngày làm, Khung giờ, Mức lương (>0), Số lượng tuyển (>0).';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return redirect()->back()->with('error', $msg);
            }

            if ($ngayLam < $today) {
                $msg = 'Không thể dời ngày làm việc về thời điểm quá khứ!';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return redirect()->back()->with('error', $msg);
            }

            $gioBatDauFormat = strlen($gioBatDau) <= 5 ? $gioBatDau.':00' : $gioBatDau;
            $gioKetThucFormat = strlen($gioKetThuc) <= 5 ? $gioKetThuc.':00' : $gioKetThuc;

            $startTimestamp = strtotime($ngayLam.' '.$gioBatDauFormat);
            $endTimestamp = strtotime($ngayLam.' '.$gioKetThucFormat);
            $nowTimestamp = time();

            if ($startTimestamp <= $nowTimestamp) {
                $msg = 'Không thể chỉnh sửa ca làm việc có thời gian bắt đầu trong quá khứ hoặc trước thời điểm hiện tại!';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return redirect()->back()->with('error', $msg);
            }

            if ($endTimestamp <= $startTimestamp) {
                $msg = 'Giờ kết thúc ca làm việc phải sau giờ bắt đầu.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return redirect()->back()->with('error', $msg);
            }

            // TỰ ĐỘNG X3 LƯƠNG NẾU RƠI VÀO NGÀY LỄ VIỆT NAM
            $tenNgayLe = HolidayHelper::isVietnameseHoliday($ngayLam);
            $mucLuongGoc = $mucLuong;
            if (! empty($tenNgayLe)) {
                $mucLuongTinh = $mucLuongGoc * 3;
                $msg = '🎉 Ngày làm việc '.date('d/m/Y', strtotime($ngayLam))." rơi vào ngày lễ [{$tenNgayLe}]. Hệ thống đã tự động nhân 3 mức lương theo giờ (Từ ".number_format($mucLuongGoc, 0, ',', '.').'đ/h -> '.number_format($mucLuongTinh, 0, ',', '.').'đ/h)!';
            } else {
                $mucLuongTinh = $mucLuongGoc;
                $msg = "Đã cập nhật thông tin ca làm [{$tenViTri}] thành công!";
            }

            $data = [
                'ten_vi_tri_tuyen_dung' => $tenViTri,
                'ngay_lam_viec' => $ngayLam,
                'gio_bat_dau' => $gioBatDauFormat,
                'gio_ket_thuc' => $gioKetThucFormat,
                'muc_luong_theo_gio' => $mucLuongTinh,
                'so_luong_tuyen' => $soLuong,
                'yeu_cau_cong_viec' => $yeuCau,
                'quyen_loi_ca_lam' => $quyenLoi,
            ];

            $soLuongDaTuyen = (int) $shift->so_luong_da_tuyen;
            if ($soLuong > 0 && $soLuongDaTuyen >= $soLuong) {
                $data['trang_thai_ca'] = 'da_du_nguoi';
            } else {
                $data['trang_thai_ca'] = 'dang_tuyen';
            }

            $shift->update($data);
            session()->flash('success', $msg);
            if (! empty($tenNgayLe)) {
                session()->flash('holiday_notice', $msg);
            }

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'ma_ca_lam' => $id,
                    'action' => 'update',
                    'is_holiday' => ! empty($tenNgayLe),
                    'holiday_name' => $tenNgayLe,
                    'muc_luong_goc' => $mucLuongGoc,
                    'muc_luong_tinh' => $mucLuongTinh,
                ]);
            }

            return redirect('/quan-ly/ca-lam-viec')->with('success', $msg);
        }
    }

    /**
     * 4. Hủy / Xóa ca làm việc:
     * - Bắt buộc nhập lý do hủy ca
     * - Cập nhật trạng thái thành 'da_huy', không xóa vĩnh viễn khỏi CSDL
     * - Ca đã hủy sẽ không hiển thị cho ứng viên/khách nhưng vẫn hiện ở trang quản lý ca với trạng thái 'Đã hủy' và không thể chỉnh sửa
     */
    public function xoaCaLam(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->filled('is_ajax');
        $id = (int) $request->input('ma_ca_lam', 0);
        $lyDoHuy = trim($request->input('ly_do_huy', ''));
        $user = session('user') ?? auth()->user();
        $userRole = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        $maNhaHang = is_array($user) ? (int) ($user['ma_nha_hang_truc_thuoc'] ?? 1) : (int) ($user->ma_nha_hang_truc_thuoc ?? 1);

        if ($id <= 0) {
            $msg = 'Mã ca làm việc không hợp lệ.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        if (empty($lyDoHuy)) {
            $msg = 'Vui lòng nhập lý do xóa / hủy ca làm việc (bắt buộc).';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $shift = CaLamViec::find($id);
        if (! $shift) {
            $msg = 'Không tìm thấy ca làm việc cần hủy.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            return redirect()->back()->with('error', $msg);
        }

        if ($userRole === 'quan_ly_nha_hang' && (int) $shift->ma_nha_hang !== $maNhaHang) {
            $msg = 'Bạn không có quyền hủy ca làm của nhà hàng khác.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }

            return redirect()->back()->with('error', $msg);
        }

        // Chuyển trạng thái sang 'da_huy' và lưu lý do hủy ca
        $shift->update([
            'trang_thai_ca' => 'da_huy',
            'ly_do_huy' => $lyDoHuy,
        ]);

        // Gửi thông báo cho tất cả ứng viên đã nộp đơn vào ca này
        try {
            $dsUngVien = HoSoUngTuyen::where('ma_ca_lam', $id)
                ->whereIn('trang_thai_ung_tuyen', ['cho_duyet', 'da_nhan'])
                ->get();
            foreach ($dsUngVien as $ut) {
                ThongBao::taoBaoTri(
                    $ut->ma_nguoi_dung,
                    'Ca làm việc bạn đăng ký đã bị hủy',
                    "Ca làm việc [#CA-{$id}] mà bạn đã ứng tuyển đã bị nhà hàng hủy.".(! empty($lyDoHuy) ? " Lý do: {$lyDoHuy}" : '').' Vui lòng tìm ca làm việc khác phù hợp.',
                    '/ung-vien/ca-lam-da-ung-tuyen'
                );
            }
        } catch (\Throwable $e) { /* ignore */
        }

        $msg = "Đã hủy thành công ca làm việc [#CA-{$id}]! Trạng thái ca: [Đã hủy].";
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'ma_ca_lam' => $id,
                'ly_do_huy' => $lyDoHuy,
            ]);
        }

        return redirect('/quan-ly/ca-lam-viec')->with('success', $msg);
    }

    /**
     * Mock danh sách ca làm việc phục vụ tìm kiếm khi DB offline
     */
    private function getMockAvailableShifts(array $filters = [])
    {
        $shifts = $this->getBaseMockShifts();

        return collect($shifts)->filter(function ($s) use ($filters) {
            if (! empty($filters['tu_khoa'])) {
                $kw = mb_strtolower($filters['tu_khoa']);
                if (! str_contains(mb_strtolower($s->ten_vi_tri_tuyen_dung), $kw) &&
                    ! str_contains(mb_strtolower($s->nhaHang->ten_nha_hang), $kw)) {
                    return false;
                }
            }
            if (! empty($filters['vi_tri'])) {
                if (! str_contains(mb_strtolower($s->ten_vi_tri_tuyen_dung), mb_strtolower($filters['vi_tri']))) {
                    return false;
                }
            }
            if (! empty($filters['trang_thai']) && $filters['trang_thai'] !== 'tat_ca') {
                if ($s->trang_thai_ca !== $filters['trang_thai']) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    /**
     * Mock danh sách thương hiệu khi DB offline
     */
    private function getMockBrands()
    {
        return collect([
            (object) ['ma_thuong_hieu' => 1, 'ten_thuong_hieu' => 'Golden Hotpot', 'ma_dinh_danh' => 'golden-hotpot'],
            (object) ['ma_thuong_hieu' => 2, 'ten_thuong_hieu' => 'Golden BBQ & Grill', 'ma_dinh_danh' => 'golden-bbq'],
            (object) ['ma_thuong_hieu' => 3, 'ten_thuong_hieu' => 'Golden Dimsum House', 'ma_dinh_danh' => 'golden-dimsum'],
        ]);
    }

    /**
     * Mock chi tiết 1 ca làm theo ID
     */
    private function getMockShiftById(int $id)
    {
        $shifts = $this->getBaseMockShifts();
        foreach ($shifts as $s) {
            if ($s->ma_ca_lam === $id) {
                return $s;
            }
        }

        return $shifts[0] ?? null;
    }

    /**
     * Mock danh sách ca làm việc của Quản lý chi nhánh
     */
    private function getMockManagerShifts(int $maNhaHang, ?int $thang, ?int $nam, string $trangThai)
    {
        $shifts = $this->getBaseMockShifts();

        return collect($shifts)->filter(function ($s) use ($trangThai) {
            if (! empty($trangThai) && $trangThai !== 'tat_ca') {
                if ($s->trang_thai_ca !== $trangThai) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    private function getBaseMockShifts(): array
    {
        return [
            (object) [
                'ma_ca_lam' => 1,
                'ma_nha_hang' => 1,
                'ten_vi_tri_tuyen_dung' => 'Nhân viên Phục vụ',
                'ngay_lam_viec' => date('Y-m-d', strtotime('+1 day')),
                'gio_bat_dau' => '17:00:00',
                'gio_ket_thuc' => '23:00:00',
                'muc_luong_theo_gio' => 30000,
                'so_luong_tuyen' => 4,
                'so_luong_da_tuyen' => 1,
                'yeu_cau_cong_viec' => 'Nhanh nhẹn, trung thực, giao tiếp tiếng Việt chuẩn.',
                'quyen_loi_ca_lam' => 'Hỗ trợ ăn tối ca làm việc, nhận lương ngay sau ca.',
                'trang_thai_ca' => 'dang_tuyen',
                'computed_status' => 'dang_tuyen',
                'so_luong_cho_duyet' => 2,
                'tong_so_ung_vien' => 3,
                'nhaHang' => (object) [
                    'ten_nha_hang' => 'Golden Hotpot - Đồng Khởi',
                    'dia_chi_chi_tiet' => '72 Lê Thánh Tôn, Bến Nghé',
                    'quan_huyen' => 'Quận 1',
                    'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
                    'thuongHieu' => (object) [
                        'ten_thuong_hieu' => 'Golden Hotpot',
                        'ma_dinh_danh' => 'golden-hotpot',
                    ],
                ],
            ],
            (object) [
                'ma_ca_lam' => 2,
                'ma_nha_hang' => 1,
                'ten_vi_tri_tuyen_dung' => 'Nhân viên Tiếp thực',
                'ngay_lam_viec' => date('Y-m-d', strtotime('+2 days')),
                'gio_bat_dau' => '18:00:00',
                'gio_ket_thuc' => '23:00:00',
                'muc_luong_theo_gio' => 28000,
                'so_luong_tuyen' => 2,
                'so_luong_da_tuyen' => 2,
                'yeu_cau_cong_viec' => 'Sức khỏe tốt, có trách nhiệm trong công việc.',
                'quyen_loi_ca_lam' => 'Phụ cấp tiền ăn và gửi xe miễn phí.',
                'trang_thai_ca' => 'da_du_nguoi',
                'computed_status' => 'da_du_nguoi',
                'so_luong_cho_duyet' => 0,
                'tong_so_ung_vien' => 2,
                'nhaHang' => (object) [
                    'ten_nha_hang' => 'Golden Hotpot - Đồng Khởi',
                    'dia_chi_chi_tiet' => '72 Lê Thánh Tôn, Bến Nghé',
                    'quan_huyen' => 'Quận 1',
                    'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
                    'thuongHieu' => (object) [
                        'ten_thuong_hieu' => 'Golden Hotpot',
                        'ma_dinh_danh' => 'golden-hotpot',
                    ],
                ],
            ],
            (object) [
                'ma_ca_lam' => 3,
                'ma_nha_hang' => 1,
                'ten_vi_tri_tuyen_dung' => 'Nhân viên Phụ bếp',
                'ngay_lam_viec' => date('Y-m-d', strtotime('-2 days')),
                'gio_bat_dau' => '09:00:00',
                'gio_ket_thuc' => '15:00:00',
                'muc_luong_theo_gio' => 32000,
                'so_luong_tuyen' => 2,
                'so_luong_da_tuyen' => 2,
                'yeu_cau_cong_viec' => 'Sơ chế rau củ, chuẩn bị nguyên liệu lẩu.',
                'quyen_loi_ca_lam' => 'Bao cơm trưa.',
                'trang_thai_ca' => 'hoan_thanh',
                'computed_status' => 'hoan_thanh',
                'so_luong_cho_duyet' => 0,
                'tong_so_ung_vien' => 2,
                'nhaHang' => (object) [
                    'ten_nha_hang' => 'Golden Hotpot - Đồng Khởi',
                    'dia_chi_chi_tiet' => '72 Lê Thánh Tôn, Bến Nghé',
                    'quan_huyen' => 'Quận 1',
                    'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
                    'thuongHieu' => (object) [
                        'ten_thuong_hieu' => 'Golden Hotpot',
                        'ma_dinh_danh' => 'golden-hotpot',
                    ],
                ],
            ],
            (object) [
                'ma_ca_lam' => 4,
                'ma_nha_hang' => 1,
                'ten_vi_tri_tuyen_dung' => 'Nhân viên Pha chế',
                'ngay_lam_viec' => date('Y-m-d', strtotime('-5 days')),
                'gio_bat_dau' => '10:00:00',
                'gio_ket_thuc' => '16:00:00',
                'muc_luong_theo_gio' => 35000,
                'so_luong_tuyen' => 1,
                'so_luong_da_tuyen' => 0,
                'yeu_cau_cong_viec' => 'Pha chế đồ uống theo công thức chuẩn.',
                'quyen_loi_ca_lam' => 'Phụ cấp ăn trưa.',
                'trang_thai_ca' => 'da_huy',
                'computed_status' => 'da_huy',
                'so_luong_cho_duyet' => 0,
                'tong_so_ung_vien' => 0,
                'nhaHang' => (object) [
                    'ten_nha_hang' => 'Golden Hotpot - Đồng Khởi',
                    'dia_chi_chi_tiet' => '72 Lê Thánh Tôn, Bến Nghé',
                    'quan_huyen' => 'Quận 1',
                    'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
                    'thuongHieu' => (object) [
                        'ten_thuong_hieu' => 'Golden Hotpot',
                        'ma_dinh_danh' => 'golden-hotpot',
                    ],
                ],
            ],
        ];
    }
}
