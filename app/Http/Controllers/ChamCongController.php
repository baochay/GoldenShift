<?php

namespace App\Http\Controllers;

use App\Models\CaLamViec;
use App\Models\HoSoUngTuyen;
use App\Models\LichSuChamCong;
use App\Models\NguoiDung;
use App\Models\SoThuNhap;
use App\Models\ThongBao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChamCongController extends Controller
{
    public const QR_TOKEN_TTL_SECONDS = 30;

    public const QR_PENDING_META_TTL_SECONDS = 180;

    public const QR_EXPIRED_MESSAGE = 'Mã QR đã hết hạn (chỉ có hiệu lực trong 30 giây). Vui lòng nhờ Quản lý bấm làm mới mã QR trên màn hình!';

    /**
     * 1. Xử lý Ứng viên Vào ca (checkIn)
     */
    public function checkIn(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            $msg = 'Vui lòng đăng nhập để thực hiện vào ca làm việc.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 401);
            }

            return redirect('/dang-nhap')->with('error', $msg);
        }

        $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);
        $maUngTuyen = (int) $request->input('ma_ung_tuyen', 0);

        if ($maUngTuyen <= 0) {
            $msg = 'Mã hồ sơ ứng tuyển không hợp lệ.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        try {
            $app = HoSoUngTuyen::with('caLamViec')->find($maUngTuyen);
            if (! $app) {
                $msg = 'Không tìm thấy hồ sơ ứng tuyển.';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 404);
                }

                return redirect()->back()->with('error', $msg);
            }

            // Kiểm tra trạng thái đơn: Bắt buộc phải là "da_nhan" hoặc "da_duyet"
            if (! in_array($app->trang_thai_ung_tuyen, ['da_nhan', 'da_duyet'])) {
                $msg = 'Chỉ những ca làm việc đã được nhà hàng phê duyệt mới có thể thực hiện Vào ca!';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }

            $shift = $app->caLamViec;

            if ($this->shouldValidateQr($request)) {
                $qrCaLamId = $shift ? (int) $shift->ma_ca_lam : (int) $request->input('ma_ca_lam', 0);
                $submittedToken = (string) ($request->input('qr_token') ?? $request->input('token', ''));
                if (! $this->validateQrToken($qrCaLamId, $submittedToken)) {
                    return $this->qrExpiredErrorResponse($request);
                }
            }

            $today = Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d');
            $shiftDate = $shift && ! empty($shift->ngay_lam_viec) ? date('Y-m-d', strtotime($shift->ngay_lam_viec)) : null;

            // 1. Kiểm tra ngày làm việc: Chưa tới ngày -> Chặn
            if ($shiftDate && $shiftDate > $today) {
                $msg = 'Chưa tới ngày làm việc ('.date('d/m/Y', strtotime($shiftDate)).'), bạn chỉ có thể vào ca đúng ngày làm việc!';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }

            // 2. Kiểm tra ngày làm việc: Quá ngày hoặc ca đã hoàn thành -> Chặn
            if ($shiftDate && ($shiftDate < $today || ($shift && $shift->trang_thai_ca === 'hoan_thanh') || $app->getDisplayStatus() === 'hoan_thanh')) {
                $msg = 'Ca làm việc đã kết thúc hoặc quá ngày quy định, không thể thực hiện Vào ca!';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }

            // 3. Ràng buộc không thể vào 2 ca cùng một lúc
            $activeOtherShift = LichSuChamCong::where('ma_nguoi_dung', $maNguoiDung)
                ->where('ma_ung_tuyen', '!=', $maUngTuyen)
                ->whereIn('trang_thai_cham_cong', ['dung_gio', 'di_muon'])
                ->whereNull('thoi_gian_ra_thuc_te')
                ->with('caLamViec')
                ->first();

            if ($activeOtherShift) {
                $tenCaKhac = ($activeOtherShift->caLamViec && ! empty($activeOtherShift->caLamViec->ten_vi_tri_tuyen_dung))
                    ? $activeOtherShift->caLamViec->ten_vi_tri_tuyen_dung
                    : 'ca làm việc khác';
                $msg = "Bạn đang trong ca làm việc [{$tenCaKhac}] và chưa bấm Ra ca! Theo quy định, không thể vào 2 ca cùng một lúc.";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }

            // Kiểm tra đã vào ca trước đó chưa
            $existing = LichSuChamCong::where('ma_ung_tuyen', $maUngTuyen)->first();
            if ($existing && $existing->thoi_gian_vao_thuc_te) {
                $msg = 'Bạn đã thực hiện Vào ca trước đó lúc '.Carbon::parse($existing->thoi_gian_vao_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y').'!';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('warning', $msg);
            }

            $now = Carbon::now('Asia/Ho_Chi_Minh');

            // So sánh giờ vào thực tế với giờ bắt đầu ca làm
            $shiftStartTime = null;
            if ($shift && ! empty($shift->ngay_lam_viec) && ! empty($shift->gio_bat_dau)) {
                $shiftStartTime = Carbon::parse($shift->ngay_lam_viec.' '.$shift->gio_bat_dau, 'Asia/Ho_Chi_Minh');
            }

            $trangThaiChamCong = 'dung_gio';
            if ($shiftStartTime && $now->gt($shiftStartTime)) {
                $trangThaiChamCong = 'di_muon';
            }

            $chamCong = LichSuChamCong::updateOrCreate(
                ['ma_ung_tuyen' => $maUngTuyen],
                [
                    'ma_nguoi_dung' => $maNguoiDung,
                    'ma_ca_lam' => $shift ? $shift->ma_ca_lam : 0,
                    'thoi_gian_vao_thuc_te' => $now,
                    'trang_thai_cham_cong' => $trangThaiChamCong,
                    'hinh_thuc_xac_thuc' => $this->shouldValidateQr($request) ? 'ma_qr' : 'quan_ly_xac_nhan',
                    'ngay_tao' => $now,
                ]
            );

            if ($this->shouldValidateQr($request) && $shift && ! empty($shift->ma_nha_hang)) {
                $this->markPendingQrCompleted((int) $shift->ma_nha_hang, (int) $shift->ma_ca_lam, $maUngTuyen);
            }

            // Thông báo In-App cho Quản lý nhà hàng
            if ($shift && $shift->ma_nha_hang) {
                try {
                    $hoTen = is_array($user) ? ($user['ho_ten'] ?? 'Ứng viên') : ($user->ho_ten ?? 'Ứng viên');
                    $qlList = NguoiDung::where('vai_tro', 'quan_ly_nha_hang')
                        ->where('ma_nha_hang_truc_thuoc', $shift->ma_nha_hang)
                        ->get();
                    foreach ($qlList as $ql) {
                        ThongBao::taoBaoTri(
                            $ql->ma_nguoi_dung,
                            'Ứng viên đã vào ca làm việc',
                            "Ứng viên [{$hoTen}] đã thực hiện Vào ca [{$shift->ten_vi_tri_tuyen_dung}] lúc {$now->format('H:i d/m/Y')} (".($trangThaiChamCong === 'di_muon' ? 'Đi muộn' : 'Đúng giờ').').',
                            '/quan-ly/cham-cong'
                        );
                    }
                } catch (\Throwable $e) {
                }
            }

            $statusText = $trangThaiChamCong === 'di_muon' ? 'Đi muộn' : 'Đúng giờ';
            $msg = "VÀO CA THÀNH CÔNG! Thời gian ghi nhận: {$now->format('H:i:s d/m/Y')} (Trạng thái: {$statusText}). Chúc bạn có ca làm việc hiệu quả!";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'trang_thai_cham_cong' => $trangThaiChamCong,
                    'thoi_gian_vao' => $now->format('H:i d/m/Y'),
                    'hinh_thuc_xac_thuc' => $chamCong->hinh_thuc_xac_thuc,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            $msg = 'Có lỗi xảy ra khi thực hiện vào ca: '.$e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }

            return redirect()->back()->with('error', $msg);
        }
    }

    /**
     * 2. Xử lý Ứng viên Ra ca (checkOut)
     */
    public function checkOut(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            $msg = 'Vui lòng đăng nhập để thực hiện ra ca làm việc.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 401);
            }

            return redirect('/dang-nhap')->with('error', $msg);
        }

        $maUngTuyen = (int) $request->input('ma_ung_tuyen', 0);

        if ($maUngTuyen <= 0) {
            $msg = 'Mã hồ sơ ứng tuyển không hợp lệ.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        try {
            $chamCong = LichSuChamCong::where('ma_ung_tuyen', $maUngTuyen)->first();
            if (! $chamCong || ! $chamCong->thoi_gian_vao_thuc_te) {
                $msg = 'Bạn chưa thực hiện Vào ca! Vui lòng bấm Vào ca trước khi thực hiện Ra ca.';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }

            if ($chamCong->thoi_gian_ra_thuc_te && $chamCong->trang_thai_cham_cong !== 'can_kiem_tra') {
                $msg = 'Bạn đã thực hiện Ra ca trước đó lúc '.Carbon::parse($chamCong->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y').'!';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('warning', $msg);
            }

            if ($this->shouldValidateQr($request)) {
                $qrCaLamId = (int) ($chamCong->ma_ca_lam ?: $request->input('ma_ca_lam', 0));
                $submittedToken = (string) ($request->input('qr_token') ?? $request->input('token', ''));
                if (! $this->validateQrToken($qrCaLamId, $submittedToken)) {
                    return $this->qrExpiredErrorResponse($request);
                }
            }

            $thoiGianRa = Carbon::now('Asia/Ho_Chi_Minh');
            $thoiGianVao = Carbon::parse($chamCong->thoi_gian_vao_thuc_te)->setTimezone('Asia/Ho_Chi_Minh');

            // Tính số giờ làm thực tế: diffInMinutes / 60
            $diffMinutes = abs($thoiGianRa->diffInMinutes($thoiGianVao));
            $soGioLam = round($diffMinutes / 60, 2);

            if ($soGioLam <= 0) {
                $soGioLam = 0.1;
            }

            $chamCong->thoi_gian_ra_thuc_te = $thoiGianRa;
            $chamCong->so_gio_lam_thuc_te = $soGioLam;
            $chamCong->trang_thai_cham_cong = 'cho_xac_nhan';
            if ($this->shouldValidateQr($request)) {
                $chamCong->hinh_thuc_xac_thuc = 'ma_qr';
            }
            $chamCong->save();

            // Thông báo In-App cho Quản lý nhà hàng
            $shift = CaLamViec::find($chamCong->ma_ca_lam);
            if ($this->shouldValidateQr($request) && $shift && ! empty($shift->ma_nha_hang)) {
                $this->markPendingQrCompleted((int) $shift->ma_nha_hang, (int) $shift->ma_ca_lam, $maUngTuyen);
            }
            if ($shift && $shift->ma_nha_hang) {
                try {
                    $hoTen = is_array($user) ? ($user['ho_ten'] ?? 'Ứng viên') : ($user->ho_ten ?? 'Ứng viên');
                    $qlList = NguoiDung::where('vai_tro', 'quan_ly_nha_hang')
                        ->where('ma_nha_hang_truc_thuoc', $shift->ma_nha_hang)
                        ->get();
                    foreach ($qlList as $ql) {
                        ThongBao::taoBaoTri(
                            $ql->ma_nguoi_dung,
                            'Ứng viên đã ra ca - Chờ duyệt công',
                            "Ứng viên [{$hoTen}] đã bấm Ra ca [{$shift->ten_vi_tri_tuyen_dung}] lúc {$thoiGianRa->format('H:i d/m/Y')} (Tổng làm: {$soGioLam}h). Vui lòng vào duyệt công.",
                            '/quan-ly/cham-cong'
                        );
                    }
                } catch (\Throwable $e) {
                }
            }

            $msg = "RA CA THÀNH CÔNG! Đã ghi nhận {$soGioLam} giờ làm việc thực tế lúc {$thoiGianRa->format('H:i:s d/m/Y')}. Ca làm chuyển sang trạng thái [Chờ Quản lý xác nhận công].";

            // Chuẩn bị dữ liệu bật popup đánh giá tự động cho Ứng viên
            $targetQlId = 1;
            $tenNhaHang = 'Nhà hàng';
            if (! $shift && $chamCong->hoSoUngTuyen) {
                $shift = CaLamViec::find($chamCong->hoSoUngTuyen->ma_ca_lam);
            }
            if ($shift) {
                $shift->loadMissing('nhaHang');
                if ($shift->nhaHang) {
                    $targetQlId = $shift->nhaHang->ma_quan_ly;
                    if (! $targetQlId) {
                        $targetQlId = NguoiDung::where('vai_tro', 'quan_ly_nha_hang')
                            ->where('ma_nha_hang_truc_thuoc', $shift->ma_nha_hang)
                            ->value('ma_nguoi_dung') ?? 1;
                    }
                    $tenNhaHang = $shift->nhaHang->ten_nha_hang ?? 'Nhà hàng';
                }
            }
            $tenCa = $shift ? $shift->ten_vi_tri_tuyen_dung : 'Ca làm việc';
            $maCaLamReview = $chamCong->ma_ca_lam ?: ($shift ? $shift->ma_ca_lam : 0);

            session([
                'show_danh_gia_modal' => true,
                'ma_ca_lam_danh_gia' => $maCaLamReview,
                'review_shift_id' => $maCaLamReview,
                'review_target_id' => $targetQlId,
                'review_target_name' => $tenNhaHang,
                'review_shift_name' => $tenCa,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'so_gio_lam_thuc_te' => $soGioLam,
                    'trang_thai_cham_cong' => 'cho_xac_nhan',
                    'thoi_gian_ra' => $thoiGianRa->format('H:i d/m/Y'),
                    'hinh_thuc_xac_thuc' => $chamCong->hinh_thuc_xac_thuc,
                    'auto_open_review' => true,
                    'review_data' => [
                        'maCaLam' => $maCaLamReview,
                        'targetUserId' => $targetQlId,
                        'targetName' => $tenNhaHang,
                        'shiftName' => $tenCa,
                        'isManager' => false,
                    ],
                ]);
            }

            return redirect()->back()
                ->with('success', $msg)
                ->with('auto_open_review', true)
                ->with('review_shift_id', $maCaLamReview)
                ->with('review_target_id', $targetQlId)
                ->with('review_target_name', $tenNhaHang)
                ->with('review_shift_name', $tenCa);
        } catch (\Throwable $e) {
            $msg = 'Có lỗi xảy ra khi thực hiện ra ca: '.$e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }

            return redirect()->back()->with('error', $msg);
        }
    }

    /**
     * Ứng viên bấm Vào ca / Ra ca -> validate điều kiện ca làm, sinh QR 30s và đẩy popup sang màn hình Quản lý nhà hàng
     */
    public function requestQr(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện chấm công.'], 401);
        }

        $vaiTro = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        if ($vaiTro !== 'ung_vien') {
            return response()->json(['success' => false, 'message' => 'Chỉ tài khoản Ứng viên mới có thể gửi yêu cầu chấm công QR.'], 403);
        }

        $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);
        $maUngTuyen = (int) $request->input('ma_ung_tuyen', 0);
        $loaiQr = $request->input('loai_qr') === 'ra_ca' ? 'ra_ca' : 'vao_ca';

        if ($maUngTuyen <= 0) {
            return response()->json(['success' => false, 'message' => 'Mã hồ sơ ứng tuyển không hợp lệ.'], 400);
        }

        $app = HoSoUngTuyen::with('caLamViec.nhaHang')->find($maUngTuyen);
        if (! $app) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy hồ sơ ứng tuyển.'], 404);
        }

        if ((int) $app->ma_nguoi_dung !== $maNguoiDung) {
            return response()->json(['success' => false, 'message' => 'Hồ sơ ứng tuyển này không thuộc về tài khoản của bạn.'], 403);
        }

        if (! in_array($app->trang_thai_ung_tuyen, ['da_nhan', 'da_duyet'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ những ca làm việc đã được nhà hàng phê duyệt mới có thể thực hiện chấm công QR!',
            ], 400);
        }

        $shift = $app->caLamViec;
        if (! $shift || empty($shift->ma_nha_hang)) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin ca làm việc hoặc nhà hàng.'], 404);
        }

        if ($loaiQr === 'vao_ca') {
            $today = Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d');
            $shiftDate = ! empty($shift->ngay_lam_viec) ? date('Y-m-d', strtotime($shift->ngay_lam_viec)) : null;

            if ($shiftDate && $shiftDate > $today) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa tới ngày làm việc ('.date('d/m/Y', strtotime($shiftDate)).'), bạn chỉ có thể vào ca đúng ngày làm việc!',
                ], 400);
            }

            if ($shiftDate && ($shiftDate < $today || $shift->trang_thai_ca === 'hoan_thanh' || $app->getDisplayStatus() === 'hoan_thanh')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ca làm việc đã kết thúc hoặc quá ngày quy định, không thể thực hiện Vào ca!',
                ], 400);
            }

            $activeOtherShift = LichSuChamCong::where('ma_nguoi_dung', $maNguoiDung)
                ->where('ma_ung_tuyen', '!=', $maUngTuyen)
                ->whereIn('trang_thai_cham_cong', ['dung_gio', 'di_muon'])
                ->whereNull('thoi_gian_ra_thuc_te')
                ->with('caLamViec')
                ->first();

            if ($activeOtherShift) {
                $tenCaKhac = ($activeOtherShift->caLamViec && ! empty($activeOtherShift->caLamViec->ten_vi_tri_tuyen_dung))
                    ? $activeOtherShift->caLamViec->ten_vi_tri_tuyen_dung
                    : 'ca làm việc khác';

                return response()->json([
                    'success' => false,
                    'message' => "Bạn đang trong ca làm việc [{$tenCaKhac}] và chưa bấm Ra ca! Theo quy định, không thể vào 2 ca cùng một lúc.",
                ], 400);
            }

            $existing = LichSuChamCong::where('ma_ung_tuyen', $maUngTuyen)->first();
            if ($existing && $existing->thoi_gian_vao_thuc_te) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã thực hiện Vào ca trước đó lúc '.Carbon::parse($existing->thoi_gian_vao_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y').'!',
                ], 400);
            }
        } else {
            $chamCong = LichSuChamCong::where('ma_ung_tuyen', $maUngTuyen)->first();
            if (! $chamCong || ! $chamCong->thoi_gian_vao_thuc_te) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa thực hiện Vào ca! Vui lòng thực hiện Vào ca trước khi Ra ca.',
                ], 400);
            }

            if ($chamCong->thoi_gian_ra_thuc_te && $chamCong->trang_thai_cham_cong !== 'can_kiem_tra') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã thực hiện Ra ca trước đó lúc '.Carbon::parse($chamCong->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y').'!',
                ], 400);
            }
        }

        $caLamId = (int) $shift->ma_ca_lam;
        $maNhaHang = (int) $shift->ma_nha_hang;
        $qrToken = Str::random(32);
        Cache::put("qr_token_{$caLamId}", $qrToken, self::QR_TOKEN_TTL_SECONDS);

        $nowTs = Carbon::now('Asia/Ho_Chi_Minh')->timestamp;
        $expiresAt = $nowTs + self::QR_TOKEN_TTL_SECONDS;
        $requestId = (string) Str::uuid();

        $tenUngVien = is_array($user) ? ($user['ho_ten'] ?? 'Ứng viên') : ($user->ho_ten ?? 'Ứng viên');
        $emailUngVien = is_array($user) ? ($user['email'] ?? '') : ($user->email ?? '');
        $tenNhaHang = $shift->nhaHang->ten_nha_hang ?? 'Nhà hàng';
        $qrPayload = route('cham_cong.qr_scan', [
            'ma_ca_lam' => $caLamId,
            'qr_token' => $qrToken,
            'loai_qr' => $loaiQr,
        ]);

        Cache::put($this->pendingQrCacheKey($maNhaHang), [
            'request_id' => $requestId,
            'ma_ca_lam' => $caLamId,
            'ma_ung_tuyen' => $maUngTuyen,
            'ma_nha_hang' => $maNhaHang,
            'ma_nguoi_dung' => $maNguoiDung,
            'ten_ung_vien' => $tenUngVien,
            'email_ung_vien' => $emailUngVien,
            'ten_ca_lam' => $shift->ten_vi_tri_tuyen_dung,
            'ten_nha_hang' => $tenNhaHang,
            'ngay_lam_viec' => ! empty($shift->ngay_lam_viec) ? date('d/m/Y', strtotime($shift->ngay_lam_viec)) : '',
            'khung_gio' => substr((string) $shift->gio_bat_dau, 0, 5).' - '.substr((string) $shift->gio_ket_thuc, 0, 5),
            'loai_qr' => $loaiQr,
            'qr_token' => $qrToken,
            'qr_payload' => $qrPayload,
            'ttl' => self::QR_TOKEN_TTL_SECONDS,
            'created_at' => $nowTs,
            'expires_at' => $expiresAt,
            'completed' => false,
        ], self::QR_PENDING_META_TTL_SECONDS);

        // TUYỆT ĐỐI KHÔNG trả qr_token / qr_payload về phía Ứng viên
        return response()->json([
            'success' => true,
            'message' => $loaiQr === 'ra_ca'
                ? 'Đã gửi yêu cầu RA CA tới màn hình Quản lý nhà hàng! Vui lòng quét mã QR trên màn hình Quản lý trong vòng 30 giây.'
                : 'Đã gửi yêu cầu VÀO CA tới màn hình Quản lý nhà hàng! Vui lòng quét mã QR trên màn hình Quản lý trong vòng 30 giây.',
            'request_id' => $requestId,
            'ma_ung_tuyen' => $maUngTuyen,
            'ma_ca_lam' => $caLamId,
            'ten_ca_lam' => $shift->ten_vi_tri_tuyen_dung,
            'ten_nha_hang' => $tenNhaHang,
            'loai_qr' => $loaiQr,
            'ttl' => self::QR_TOKEN_TTL_SECONDS,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Phân hệ Quản lý nhà hàng poll để tự động popup mã QR 30 giây khi Ứng viên bấm Vào ca / Ra ca
     */
    public function pendingQr(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn.'], 401);
        }

        $vaiTro = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        if ($vaiTro !== 'quan_ly_nha_hang') {
            return response()->json(['success' => false, 'message' => 'Chỉ Quản lý nhà hàng mới có quyền nhận popup QR chấm công.'], 403);
        }

        $nhaHangIds = $this->getManagerRestaurantIds($user);
        $latest = null;
        foreach ($nhaHangIds as $nhaHangId) {
            $candidate = Cache::get($this->pendingQrCacheKey($nhaHangId));
            if (is_array($candidate)) {
                if (! $latest || (int) ($candidate['created_at'] ?? 0) >= (int) ($latest['created_at'] ?? 0)) {
                    $latest = $candidate;
                }
            }
        }

        if (! $latest) {
            return response()->json([
                'success' => true,
                'has_pending' => false,
            ]);
        }

        if (! empty($latest['completed'])) {
            return response()->json([
                'success' => true,
                'has_pending' => false,
                'completed_request' => $latest,
            ]);
        }

        $nowTs = Carbon::now('Asia/Ho_Chi_Minh')->timestamp;
        $remaining = max(0, (int) ($latest['expires_at'] ?? 0) - $nowTs);
        $cachedToken = Cache::get('qr_token_'.(int) ($latest['ma_ca_lam'] ?? 0));
        $isExpired = ($remaining <= 0) || ! is_string($cachedToken) || $cachedToken !== ($latest['qr_token'] ?? '');

        return response()->json([
            'success' => true,
            'has_pending' => true,
            'request' => array_merge($latest, [
                'remaining_seconds' => $isExpired ? 0 : $remaining,
                'is_expired' => $isExpired,
            ]),
        ]);
    }

    /**
     * Quản lý đóng thủ công popup QR đang hiển thị
     */
    public function dismissQr(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn.'], 401);
        }

        $vaiTro = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        if ($vaiTro !== 'quan_ly_nha_hang') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thao tác này.'], 403);
        }

        $requestId = (string) $request->input('request_id', '');
        foreach ($this->getManagerRestaurantIds($user) as $nhaHangId) {
            $key = $this->pendingQrCacheKey($nhaHangId);
            $item = Cache::get($key);
            if (is_array($item) && ($requestId === '' || ($item['request_id'] ?? '') === $requestId)) {
                Cache::forget($key);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Sinh lại / Làm mới mã QR Token ngẫu nhiên có thời hạn 30 giây trong Laravel Cache
     */
    public function generateQrToken(Request $request, $caLamId = null)
    {
        $user = session('user');
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn.'], 401);
        }

        $vaiTro = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        if (! in_array($vaiTro, ['quan_ly_nha_hang', 'quan_tri_vien'])) {
            return response()->json(['success' => false, 'message' => 'Chỉ Quản lý nhà hàng hoặc Quản trị viên mới được phép làm mới mã QR.'], 403);
        }

        $caLamId = (int) ($caLamId ?: $request->input('ma_ca_lam', 0));
        if ($caLamId <= 0) {
            return response()->json(['success' => false, 'message' => 'Mã ca làm việc không hợp lệ.'], 400);
        }

        $shift = CaLamViec::with('nhaHang')->find($caLamId);
        if (! $shift) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy ca làm việc.'], 404);
        }

        $userId = (int) (is_array($user) ? ($user['ma_nguoi_dung'] ?? 0) : ($user->ma_nguoi_dung ?? 0));
        $maNhaHang = is_array($user) ? ($user['ma_nha_hang_truc_thuoc'] ?? null) : ($user->ma_nha_hang_truc_thuoc ?? null);
        if ($vaiTro === 'quan_ly_nha_hang') {
            $ownerId = (int) ($shift->nhaHang->ma_quan_ly ?? 0);
            $matchesBranch = ! empty($maNhaHang) && (int) $shift->ma_nha_hang === (int) $maNhaHang;
            $matchesOwner = $ownerId > 0 && $ownerId === $userId;
            if (! $matchesBranch && ! $matchesOwner) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền làm mới mã QR cho ca của nhà hàng khác.'], 403);
            }
        }

        $qrToken = Str::random(32);
        Cache::put("qr_token_{$caLamId}", $qrToken, self::QR_TOKEN_TTL_SECONDS);

        $loaiQr = in_array($request->input('loai_qr'), ['vao_ca', 'ra_ca'])
            ? $request->input('loai_qr')
            : 'vao_ca';
        $nowTs = Carbon::now('Asia/Ho_Chi_Minh')->timestamp;
        $expiresAt = $nowTs + self::QR_TOKEN_TTL_SECONDS;
        $qrPayload = route('cham_cong.qr_scan', [
            'ma_ca_lam' => $caLamId,
            'qr_token' => $qrToken,
            'loai_qr' => $loaiQr,
        ]);

        if (! empty($shift->ma_nha_hang)) {
            $pendingKey = $this->pendingQrCacheKey((int) $shift->ma_nha_hang);
            $pending = Cache::get($pendingKey);
            if (is_array($pending) && (int) ($pending['ma_ca_lam'] ?? 0) === $caLamId && empty($pending['completed'])) {
                $pending['qr_token'] = $qrToken;
                $pending['qr_payload'] = $qrPayload;
                $pending['loai_qr'] = $request->has('loai_qr') ? $loaiQr : ($pending['loai_qr'] ?? $loaiQr);
                $pending['created_at'] = $nowTs;
                $pending['expires_at'] = $expiresAt;
                Cache::put($pendingKey, $pending, self::QR_PENDING_META_TTL_SECONDS);
            }
        }

        return response()->json([
            'success' => true,
            'ma_ca_lam' => $caLamId,
            'ten_ca_lam' => $shift->ten_vi_tri_tuyen_dung,
            'loai_qr' => $loaiQr,
            'qr_token' => $qrToken,
            'qrToken' => $qrToken,
            'ttl' => self::QR_TOKEN_TTL_SECONDS,
            'expires_at' => $expiresAt,
            'qr_payload' => $qrPayload,
        ]);
    }

    /**
     * Điểm tiếp nhận quét mã QR chấm công (Vào ca / Ra ca) của Ứng viên
     */
    public function scanQr(Request $request)
    {
        $user = session('user');
        if (! $user) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện chấm công QR.'], 401);
            }

            return redirect('/login')->with('error', 'Vui lòng đăng nhập để thực hiện chấm công QR.');
        }

        $vaiTro = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        if ($vaiTro !== 'ung_vien') {
            return response()->json(['success' => false, 'message' => 'Chỉ tài khoản Ứng viên mới có thể quét QR chấm công.'], 403);
        }

        $caLamId = (int) ($request->input('ma_ca_lam') ?? $request->query('ma_ca_lam', 0));
        $submittedToken = (string) ($request->input('qr_token') ?? $request->input('token') ?? $request->query('qr_token') ?? $request->query('token', ''));
        $loaiQr = (string) ($request->input('loai_qr') ?? $request->query('loai_qr', 'vao_ca'));

        if (! $this->validateQrToken($caLamId, $submittedToken)) {
            return $this->qrExpiredErrorResponse($request);
        }

        $maNguoiDung = (int) (is_array($user) ? ($user['ma_nguoi_dung'] ?? 0) : ($user->ma_nguoi_dung ?? 0));
        $app = HoSoUngTuyen::where('ma_nguoi_dung', $maNguoiDung)
            ->where('ma_ca_lam', $caLamId)
            ->whereIn('trang_thai_ung_tuyen', ['da_nhan', 'da_duyet'])
            ->first();

        if (! $app) {
            $msg = 'Bạn không có đơn ứng tuyển đã được duyệt cho ca làm việc này.';
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            return response($msg, 404);
        }

        $request->merge([
            'ma_ung_tuyen' => $app->ma_ung_tuyen,
            'ma_ca_lam' => $caLamId,
            'qr_token' => $submittedToken,
            'hinh_thuc_xac_thuc' => 'ma_qr',
        ]);

        $response = $loaiQr === 'ra_ca'
            ? $this->checkOut($request)
            : $this->checkIn($request);

        if (
            $request->isMethod('get')
            && ! $request->ajax()
            && ! $request->wantsJson()
            && ! $request->expectsJson()
            && $response instanceof \Illuminate\Http\RedirectResponse
        ) {
            return redirect()->to(url('/ung-tuyen/da-nop'));
        }

        return $response;
    }

    protected function getManagerRestaurantIds($user): array
    {
        $userId = (int) (is_array($user) ? ($user['ma_nguoi_dung'] ?? 0) : ($user->ma_nguoi_dung ?? 0));
        $maNhaHang = is_array($user) ? ($user['ma_nha_hang_truc_thuoc'] ?? null) : ($user->ma_nha_hang_truc_thuoc ?? null);

        $ids = [];
        if (! empty($maNhaHang)) {
            $ids[] = (int) $maNhaHang;
        } elseif ($userId > 0) {
            try {
                $dbBranch = NguoiDung::where('ma_nguoi_dung', $userId)->value('ma_nha_hang_truc_thuoc');
                if (! empty($dbBranch)) {
                    $ids[] = (int) $dbBranch;
                }
            } catch (\Throwable $e) {
            }
        }

        if ($userId > 0) {
            try {
                $ownedIds = \App\Models\NhaHang::where('ma_quan_ly', $userId)->pluck('ma_nha_hang')->toArray();
                foreach ($ownedIds as $ownedId) {
                    $ids[] = (int) $ownedId;
                }
            } catch (\Throwable $e) {
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    protected function pendingQrCacheKey(int $maNhaHang): string
    {
        return "qr_pending_restaurant_{$maNhaHang}";
    }

    protected function markPendingQrCompleted(int $maNhaHang, int $caLamId, int $maUngTuyen): void
    {
        if ($maNhaHang <= 0) {
            return;
        }

        $key = $this->pendingQrCacheKey($maNhaHang);
        $pending = Cache::get($key);
        if (! is_array($pending)) {
            return;
        }

        $sameShift = (int) ($pending['ma_ca_lam'] ?? 0) === $caLamId;
        $sameApp = empty($pending['ma_ung_tuyen']) || (int) $pending['ma_ung_tuyen'] === $maUngTuyen;
        if (! $sameShift || ! $sameApp) {
            return;
        }

        $pending['completed'] = true;
        $pending['completed_at'] = Carbon::now('Asia/Ho_Chi_Minh')->timestamp;
        Cache::put($key, $pending, 15);
    }

    protected function shouldValidateQr(Request $request): bool
    {
        return $request->has('qr_token')
            || $request->has('token')
            || $request->input('hinh_thuc_xac_thuc') === 'ma_qr'
            || $request->boolean('require_qr');
    }

    protected function validateQrToken(int $caLamId, string $submittedToken): bool
    {
        if ($caLamId <= 0 || trim($submittedToken) === '') {
            return false;
        }

        $cachedToken = Cache::get("qr_token_{$caLamId}");
        if (! is_string($cachedToken) || $cachedToken === '') {
            return false;
        }

        return hash_equals($cachedToken, trim($submittedToken));
    }

    protected function qrExpiredErrorResponse(Request $request)
    {
        $payload = [
            'success' => false,
            'message' => self::QR_EXPIRED_MESSAGE,
            'code' => 'QR_EXPIRED',
        ];

        if ($request->isMethod('get') && ! $request->ajax() && ! $request->wantsJson() && ! $request->expectsJson()) {
            $safeMsg = e(self::QR_EXPIRED_MESSAGE);

            return response(
                '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><title>Mã QR hết hạn</title></head>'
                .'<body style="font-family:sans-serif;padding:2rem;background:#f8fafc;color:#0f172a;">'
                .'<div style="max-width:480px;margin:2rem auto;background:#fff;border:1px solid #fecaca;border-radius:14px;padding:1.5rem;">'
                .'<h2 style="color:#b91c1c;margin-top:0;">Mã QR đã hết hạn</h2>'
                ."<p>{$safeMsg}</p>"
                .'<a href="'.e(url('/ung-tuyen/da-nop')).'" style="display:inline-block;margin-top:0.75rem;color:#b45309;font-weight:700;">Quay lại Lịch sử ứng tuyển</a>'
                .'</div></body></html>',
                400
            );
        }

        return response()->json($payload, 400);
    }

    /**
     * Tự động duyệt các ca đã ra ca quá 60 phút mà Quản lý chưa duyệt
     */
    public static function processAutoApprovals()
    {
        try {
            $cutoff = Carbon::now('Asia/Ho_Chi_Minh')->subMinutes(60)->format('Y-m-d H:i:s');
            $overdueList = LichSuChamCong::where('trang_thai_cham_cong', 'cho_xac_nhan')
                ->whereNotNull('thoi_gian_ra_thuc_te')
                ->where('thoi_gian_ra_thuc_te', '<=', $cutoff)
                ->with('caLamViec')
                ->get();

            foreach ($overdueList as $cc) {
                try {
                    self::executeAutoApproval($cc);
                } catch (\Throwable $e) {
                    Log::error('Auto-approval failed for attendance #'.$cc->ma_cham_cong.': '.$e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('processAutoApprovals query failed: '.$e->getMessage());
        }
    }

    /**
     * Thực thi tự động duyệt một ca làm việc
     */
    public static function executeAutoApproval(LichSuChamCong $chamCong)
    {
        if ($chamCong->trang_thai_cham_cong !== 'cho_xac_nhan') {
            return;
        }

        $soGioLam = (float) $chamCong->so_gio_lam_thuc_te;
        if ($soGioLam <= 0) {
            $soGioLam = 0.1;
        }

        $tag = 'Hệ thống tự động duyệt sau 60 phút từ khi ra ca';
        $ghiChuMoi = ! empty($chamCong->ghi_chu_cham_cong)
            ? ($chamCong->ghi_chu_cham_cong.' | '.$tag)
            : $tag;

        // Cập nhật nguyên tử để tránh cộng trùng khi nhiều request cùng kích hoạt processAutoApprovals()
        $updated = LichSuChamCong::where('ma_cham_cong', $chamCong->ma_cham_cong)
            ->where('trang_thai_cham_cong', 'cho_xac_nhan')
            ->update([
                'so_gio_lam_thuc_te' => $soGioLam,
                'trang_thai_cham_cong' => 'tu_dong_duyet',
                'ghi_chu_cham_cong' => $ghiChuMoi,
            ]);

        if ($updated === 0) {
            return;
        }

        $chamCong->so_gio_lam_thuc_te = $soGioLam;
        $chamCong->trang_thai_cham_cong = 'tu_dong_duyet';
        $chamCong->ghi_chu_cham_cong = $ghiChuMoi;

        // Tính lương ca vào đúng kỳ lương theo thời điểm ra ca
        $shift = $chamCong->caLamViec;
        $mucLuong = (float) ($shift ? $shift->muc_luong_theo_gio : 0);
        $tienLuongCa = round($soGioLam * $mucLuong);
        $dateForPayroll = $chamCong->thoi_gian_ra_thuc_te ?: Carbon::now('Asia/Ho_Chi_Minh');
        $kyLuong = 'Tháng '.Carbon::parse($dateForPayroll)->setTimezone('Asia/Ho_Chi_Minh')->format('m/Y');

        $soThuNhap = SoThuNhap::firstOrCreate(
            [
                'ma_nguoi_dung' => $chamCong->ma_nguoi_dung,
                'ky_luong' => $kyLuong,
            ],
            [
                'tong_so_ca_lam' => 0,
                'tong_gio_lam' => 0,
                'tong_tien_luong_co_ban' => 0,
                'tong_tien_thuong' => 0,
                'tong_tien_phat' => 0,
                'thuc_nhan' => 0,
                'trang_thai_thanh_toan' => 'chua_thanh_toan',
                'ngay_tao' => Carbon::now('Asia/Ho_Chi_Minh'),
            ]
        );

        $soThuNhap->tong_so_ca_lam += 1;
        $soThuNhap->tong_gio_lam += $soGioLam;
        $soThuNhap->tong_tien_luong_co_ban += $tienLuongCa;
        $soThuNhap->thuc_nhan = $soThuNhap->tong_tien_luong_co_ban + $soThuNhap->tong_tien_thuong - $soThuNhap->tong_tien_phat;
        $soThuNhap->save();

        if ($shift) {
            $shift->trang_thai_ca = 'hoan_thanh';
            $shift->save();
        }

        try {
            $tenViTri = $shift ? $shift->ten_vi_tri_tuyen_dung : 'ca làm';
            ThongBao::taoBaoTri(
                $chamCong->ma_nguoi_dung,
                'Ca làm việc đã được tự động phê duyệt',
                "Ca làm [{$tenViTri}] đã được hệ thống tự động phê duyệt ({$soGioLam} giờ, ".number_format($tienLuongCa, 0, ',', '.').' VNĐ) sau 60 phút từ khi ra ca.',
                '/ung-vien/so-thu-nhap'
            );
        } catch (\Throwable $e) {
        }
    }

    /**
     * 3. Giao diện Quản lý duyệt Chấm công (/quan-ly/cham-cong)
     */
    public function indexQuanLy(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return redirect('/dang-nhap')->with('warning', 'Vui lòng đăng nhập với tài khoản Quản lý.');
        }

        // Tự động quét và chốt các ca chờ duyệt quá 60 phút
        self::processAutoApprovals();

        $userRole = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        $maNhaHang = is_array($user) ? (int) ($user['ma_nha_hang_truc_thuoc'] ?? 0) : (int) ($user->ma_nha_hang_truc_thuoc ?? 0);

        $tab = $request->input('tab', 'cho_xac_nhan'); // 'cho_xac_nhan', 'da_xac_nhan', 'tat_ca'

        $query = LichSuChamCong::with([
            'hoSoUngTuyen.nguoiDung',
            'caLamViec.nhaHang.thuongHieu',
            'caLamViec.nhaHang.quanLy',
            'nguoiXacNhan',
        ]);

        if ($userRole === 'quan_ly_nha_hang' && $maNhaHang > 0) {
            $query->whereHas('caLamViec', function ($q) use ($maNhaHang) {
                $q->where('ma_nha_hang', $maNhaHang);
            });
        }

        if ($tab === 'cho_xac_nhan') {
            $query->where('trang_thai_cham_cong', 'cho_xac_nhan');
        } elseif ($tab === 'da_xac_nhan') {
            $query->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet']);
        }

        $chamCongList = $query->orderBy('ngay_tao', 'desc')->paginate(5)->withQueryString();

        // Thống kê nhanh
        $statsQuery = LichSuChamCong::query();
        if ($userRole === 'quan_ly_nha_hang' && $maNhaHang > 0) {
            $statsQuery->whereHas('caLamViec', function ($q) use ($maNhaHang) {
                $q->where('ma_nha_hang', $maNhaHang);
            });
        }
        $totalPending = (clone $statsQuery)->where('trang_thai_cham_cong', 'cho_xac_nhan')->count();
        $totalConfirmed = (clone $statsQuery)->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet'])->count();
        $totalHours = (clone $statsQuery)->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet'])->sum('so_gio_lam_thuc_te');

        return view('quan_ly.cham_cong.index', compact(
            'chamCongList',
            'tab',
            'totalPending',
            'totalConfirmed',
            'totalHours'
        ));
    }

    /**
     * 4. Quản lý Xác nhận Chấm công & Tự động cộng Sổ Thu Nhập
     */
    public function confirm(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            $msg = 'Vui lòng đăng nhập với tài khoản Quản lý.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 401);
            }

            return redirect('/dang-nhap')->with('error', $msg);
        }

        $currentUserId = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);
        $maChamCong = (int) $request->input('ma_cham_cong', 0);
        $ghiChu = trim($request->input('ghi_chu_cham_cong', ''));

        if ($maChamCong <= 0) {
            $msg = 'Mã chấm công không hợp lệ.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        try {
            $chamCong = LichSuChamCong::with('caLamViec')->find($maChamCong);
            if (! $chamCong) {
                $msg = 'Không tìm thấy bản ghi chấm công cần duyệt.';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 404);
                }

                return redirect()->back()->with('error', $msg);
            }

            // Kiểm tra nếu ca đã được duyệt hoặc tự động duyệt trước đó
            if (in_array($chamCong->trang_thai_cham_cong, ['da_xac_nhan', 'tu_dong_duyet'])) {
                $msg = 'Ca làm việc này đã được phê duyệt trước đó, không thể chỉnh sửa hay thêm thưởng!';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('warning', $msg);
            }

            // Kiểm tra nếu đã quá 60 phút kể từ lúc ra ca -> Tự động duyệt và chặn sửa
            if ($chamCong->thoi_gian_ra_thuc_te) {
                $checkoutTime = Carbon::parse($chamCong->thoi_gian_ra_thuc_te)->setTimezone('Asia/Ho_Chi_Minh');
                if ($checkoutTime->copy()->addMinutes(60)->isPast()) {
                    self::executeAutoApproval($chamCong);
                    $msg = 'Ca làm việc này đã quá thời hạn 60 phút kể từ lúc ra ca và đã được hệ thống tự động phê duyệt! Dữ liệu đã được khóa chỉ đọc.';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg], 400);
                    }

                    return redirect()->back()->with('warning', $msg);
                }
            }

            // Cập nhật giờ vào / ra thực tế nếu quản lý điều chỉnh trong modal
            if ($request->filled('thoi_gian_vao_thuc_te')) {
                try {
                    $chamCong->thoi_gian_vao_thuc_te = Carbon::parse($request->input('thoi_gian_vao_thuc_te'), 'Asia/Ho_Chi_Minh');
                } catch (\Throwable $e) {
                }
            }
            if ($request->filled('thoi_gian_ra_thuc_te')) {
                try {
                    $chamCong->thoi_gian_ra_thuc_te = Carbon::parse($request->input('thoi_gian_ra_thuc_te'), 'Asia/Ho_Chi_Minh');
                } catch (\Throwable $e) {
                }
            }

            // Quản lý có thể điều chỉnh số giờ làm thực tế nếu muốn
            $soGioLam = $request->filled('so_gio_lam_thuc_te')
                ? (float) $request->input('so_gio_lam_thuc_te')
                : (float) $chamCong->so_gio_lam_thuc_te;

            if ($soGioLam <= 0) {
                $soGioLam = (float) $chamCong->so_gio_lam_thuc_te ?: 1.0;
            }

            // Tiền thưởng thêm (nếu có từ modal phê duyệt)
            $soTienThuong = max(0, (float) $request->input('so_tien_thuong', 0));

            // Cập nhật lượt chấm công
            $chamCong->so_gio_lam_thuc_te = $soGioLam;
            $chamCong->trang_thai_cham_cong = 'da_xac_nhan';
            $chamCong->nguoi_xac_nhan = $currentUserId;

            // Xử lý ghi chú chấm công kèm thông tin thưởng nếu có
            $ghiChuFinal = $ghiChu;
            if ($soTienThuong > 0) {
                $thuongTag = 'Thưởng thêm: +'.number_format($soTienThuong, 0, ',', '.').' VNĐ';
                $ghiChuFinal = ! empty($ghiChuFinal) ? ($ghiChuFinal.' | '.$thuongTag) : $thuongTag;
            }
            if (! empty($ghiChuFinal)) {
                $chamCong->ghi_chu_cham_cong = $ghiChuFinal;
            }
            $chamCong->save();

            // Tính tiền lương ca
            $shift = $chamCong->caLamViec;
            $mucLuong = (float) ($shift ? $shift->muc_luong_theo_gio : 0);
            $tienLuongCa = round($soGioLam * $mucLuong);

            // Xác định kỳ lương (ví dụ: 'Tháng 11/2026')
            $kyLuong = 'Tháng '.Carbon::now('Asia/Ho_Chi_Minh')->format('m/Y');

            // Tìm hoặc tạo mới bản ghi so_thu_nhap cho ứng viên trong kỳ lương đó
            $soThuNhap = SoThuNhap::firstOrCreate(
                [
                    'ma_nguoi_dung' => $chamCong->ma_nguoi_dung,
                    'ky_luong' => $kyLuong,
                ],
                [
                    'tong_so_ca_lam' => 0,
                    'tong_gio_lam' => 0,
                    'tong_tien_luong_co_ban' => 0,
                    'tong_tien_thuong' => 0,
                    'tong_tien_phat' => 0,
                    'thuc_nhan' => 0,
                    'trang_thai_thanh_toan' => 'chua_thanh_toan',
                    'ngay_tao' => Carbon::now('Asia/Ho_Chi_Minh'),
                ]
            );

            // Cộng dồn các chỉ số
            $soThuNhap->tong_so_ca_lam += 1;
            $soThuNhap->tong_gio_lam += $soGioLam;
            $soThuNhap->tong_tien_luong_co_ban += $tienLuongCa;
            $soThuNhap->tong_tien_thuong += $soTienThuong;
            $soThuNhap->thuc_nhan = $soThuNhap->tong_tien_luong_co_ban + $soThuNhap->tong_tien_thuong - $soThuNhap->tong_tien_phat;
            $soThuNhap->save();

            // Cập nhật ca làm về trạng thái hoàn thành
            if ($shift) {
                $shift->trang_thai_ca = 'hoan_thanh';
                $shift->save();
            }

            // Gửi thông báo In-App cho Ứng viên
            try {
                $tenViTri = $shift ? $shift->ten_vi_tri_tuyen_dung : 'ca làm';
                $tongTienNhan = $tienLuongCa + $soTienThuong;
                $thongBaoMsg = "Quản lý đã xác nhận {$soGioLam} giờ làm việc cho ca [{$tenViTri}]. Lương ca: ".number_format($tienLuongCa, 0, ',', '.').' VNĐ';
                if ($soTienThuong > 0) {
                    $thongBaoMsg .= ' (Thưởng thêm: +'.number_format($soTienThuong, 0, ',', '.').' VNĐ)';
                }
                $thongBaoMsg .= '. Tổng nhận: '.number_format($tongTienNhan, 0, ',', '.')." VNĐ đã được cộng vào Sổ Thu Nhập kỳ [{$kyLuong}] của bạn.";

                ThongBao::taoBaoTri(
                    $chamCong->ma_nguoi_dung,
                    'Xác nhận chấm công thành công',
                    $thongBaoMsg,
                    '/ung-vien/so-thu-nhap'
                );
            } catch (\Throwable $e) {
            }

            $targetCandidate = NguoiDung::find($chamCong->ma_nguoi_dung);
            $targetCandidateName = $targetCandidate ? $targetCandidate->ho_ten : 'Ứng viên';
            $tenCa = $shift ? $shift->ten_vi_tri_tuyen_dung : 'Ca làm việc';

            $msg = "ĐÃ XÁC NHẬN CHẤM CÔNG THÀNH CÔNG! Ghi nhận {$soGioLam} giờ làm";
            if ($soTienThuong > 0) {
                $msg .= ', thưởng thêm '.number_format($soTienThuong, 0, ',', '.').' VNĐ';
            }
            $msg .= ', tự động cộng '.number_format($tienLuongCa + $soTienThuong, 0, ',', '.').' VNĐ vào Sổ Thu Nhập của ứng viên.';

            session([
                'show_danh_gia_ung_vien_modal' => true,
                'ma_ca_lam_danh_gia' => $chamCong->ma_ca_lam,
                'review_shift_id' => $chamCong->ma_ca_lam,
                'review_target_id' => $chamCong->ma_nguoi_dung,
                'review_target_name' => $targetCandidateName,
                'review_shift_name' => $tenCa,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'so_gio_lam' => $soGioLam,
                    'tien_luong_ca' => $tienLuongCa,
                    'tien_thuong' => $soTienThuong,
                    'tong_nhan' => $tienLuongCa + $soTienThuong,
                    'auto_open_review' => true,
                    'review_data' => [
                        'maCaLam' => $chamCong->ma_ca_lam,
                        'targetUserId' => $chamCong->ma_nguoi_dung,
                        'targetName' => $targetCandidateName,
                        'shiftName' => $tenCa,
                        'isManager' => true,
                    ],
                ]);
            }

            return redirect()->back()
                ->with('success', $msg)
                ->with('auto_open_review_manager', true)
                ->with('review_shift_id', $chamCong->ma_ca_lam)
                ->with('review_target_id', $chamCong->ma_nguoi_dung)
                ->with('review_target_name', $targetCandidateName)
                ->with('review_shift_name', $tenCa);
        } catch (\Throwable $e) {
            $msg = 'Có lỗi xảy ra khi xác nhận chấm công: '.$e->getMessage();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }

            return redirect()->back()->with('error', $msg);
        }
    }

    /**
     * 5. Quản lý Từ chối / Yêu cầu kiểm tra lại chấm công
     */
    public function reject(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            $msg = 'Vui lòng đăng nhập với tài khoản Quản lý.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 401);
            }

            return redirect('/dang-nhap')->with('error', $msg);
        }

        $currentUserId = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);
        $maChamCong = (int) $request->input('ma_cham_cong', 0);
        $lyDo = trim($request->input('ly_do_tu_choi', ''));

        if ($maChamCong <= 0) {
            $msg = 'Mã chấm công không hợp lệ.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        try {
            $chamCong = LichSuChamCong::with('caLamViec')->find($maChamCong);
            if (! $chamCong) {
                $msg = 'Không tìm thấy bản ghi chấm công.';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 404);
                }

                return redirect()->back()->with('error', $msg);
            }

            if ($chamCong->trang_thai_cham_cong !== 'cho_xac_nhan') {
                $msg = 'Chỉ có thể từ chối các ca đang chờ xác nhận.';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }

                return redirect()->back()->with('error', $msg);
            }

            // Chuyển về trạng thái cần kiểm tra lại
            $chamCong->trang_thai_cham_cong = 'can_kiem_tra';
            $chamCong->nguoi_xac_nhan = $currentUserId;
            $lyDoCuoi = ! empty($lyDo) ? $lyDo : 'Quản lý yêu cầu kiểm tra lại thời gian chấm công.';
            $chamCong->ghi_chu_cham_cong = '[Yêu cầu kiểm tra] '.$lyDoCuoi;
            $chamCong->save();

            // Thông báo cho ứng viên
            try {
                $shift = $chamCong->caLamViec;
                $tenViTri = $shift ? $shift->ten_vi_tri_tuyen_dung : 'ca làm';
                ThongBao::taoBaoTri(
                    $chamCong->ma_nguoi_dung,
                    'Chấm công cần kiểm tra lại',
                    "Quản lý yêu cầu kiểm tra lại chấm công ca [{$tenViTri}]. Lý do: {$lyDoCuoi}",
                    '/ung-tuyen/da-nop'
                );
            } catch (\Throwable $e) {
            }

            $msg = 'Đã từ chối và yêu cầu kiểm tra lại chấm công này. Ứng viên sẽ nhận được thông báo.';

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->back()->with('warning', $msg);
        } catch (\Throwable $e) {
            $msg = 'Có lỗi xảy ra: '.$e->getMessage();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }

            return redirect()->back()->with('error', $msg);
        }
    }
}
