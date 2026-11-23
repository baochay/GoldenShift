<?php

use App\Http\Controllers\Admin\BaoCaoController as AdminBaoCaoController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\ThongKeController as AdminThongKeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaoCaoViPhamController;
use App\Http\Controllers\CaLamViecController;
use App\Http\Controllers\ChamCongController;
use App\Http\Controllers\DanhGiaCaLamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuanLy\ThongKeController as QuanLyThongKeController;
use App\Http\Controllers\RealtimeApiController;
use App\Http\Controllers\SoThuNhapController;
use App\Http\Controllers\ThongBaoController;
use App\Http\Controllers\UnderConstructionController;
use App\Http\Controllers\UngTuyenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Golden Shift F&B Recruitment
|--------------------------------------------------------------------------
*/

// 1. TRANG CHỦ
Route::get('/', [HomeController::class, 'index'])->name('home');

// 2. CA LÀM VIỆC (Ứng viên & Khách)
Route::get('/ca-lam-viec', [CaLamViecController::class, 'index'])->name('ca_lam_viec.index');
Route::get('/ca-lam-viec/chi-tiet', [CaLamViecController::class, 'chiTiet'])->name('ca_lam_viec.chi_tiet');
Route::get('/ca-lam-viec/{id}', [CaLamViecController::class, 'chiTiet'])->whereNumber('id')->name('ca-lam-viec.show');
Route::get('/ca-lam-viec/chi-tiet/{id}', [CaLamViecController::class, 'chiTiet'])->whereNumber('id')->name('ca_lam_viec.show');
Route::get('/ca-lam-viec/{id}/ung-tuyen-gap', [CaLamViecController::class, 'ungTuyenGap'])->whereNumber('id')->name('ca-lam-viec.ung-tuyen-gap');
Route::get('/tim-kiem', [CaLamViecController::class, 'index'])->name('ca_lam_viec.tim_kiem');

// 3. QUẢN LÝ CA LÀM VIỆC (Quản lý nhà hàng)
Route::prefix('quan-ly')->group(function () {
    Route::get('/ca-lam-viec', [CaLamViecController::class, 'danhSachQuanLy'])->name('quan_ly.ca_lam_viec');
    Route::post('/ca-lam-viec/luu', [CaLamViecController::class, 'luuCaLam'])->name('quan_ly.ca_lam_viec.luu');
    Route::post('/ca-lam-viec/store', [CaLamViecController::class, 'store'])->name('quan_ly.ca_lam_viec.store');
    Route::post('/ca-lam-viec/update', [CaLamViecController::class, 'update'])->name('quan_ly.ca_lam_viec.update');
    Route::post('/ca-lam-viec/xoa', [CaLamViecController::class, 'xoaCaLam'])->name('quan_ly.ca_lam_viec.xoa');

    // Xét duyệt ứng viên
    Route::get('/ung-tuyen', [UngTuyenController::class, 'danhSachUngVien'])->name('quan_ly.ung_tuyen');
    Route::get('/ung-tuyen/theo-ca', [UngTuyenController::class, 'danhSachTheoCa'])->name('quan_ly.ung_tuyen.theo_ca');
    Route::post('/ung-tuyen/duyet', [UngTuyenController::class, 'xetDuyetUngVien'])->name('quan_ly.ung_tuyen.duyet');

    // Báo cáo thống kê & phân tích chi nhánh
    Route::get('/thong-ke', [QuanLyThongKeController::class, 'index'])->name('quan_ly.thong_ke.index');
    Route::get('/thong-ke/export', [QuanLyThongKeController::class, 'export'])->name('quan_ly.thong_ke.export');
});

// Route name 'ca-lam-viec.index' cho trang Quản lý Ca làm việc theo yêu cầu phân luồng
Route::get('/quan-ly/ca-lam-viec', [CaLamViecController::class, 'danhSachQuanLy'])->name('ca-lam-viec.index');

// 4. ỨNG TUYỂN (Ứng viên nộp & xem ca đã nộp)
Route::post('/ung-tuyen/nop-don', [UngTuyenController::class, 'nopDon'])->name('ung_tuyen.nop_don');
Route::get('/ung-tuyen/da-nop', [UngTuyenController::class, 'danhSachDaNop'])->name('ung_tuyen.da_nop');

// Route alias điều hướng thông báo: /ung-vien/ca-lam-da-ung-tuyen
Route::get('/ung-vien/ca-lam-da-ung-tuyen', [UngTuyenController::class, 'danhSachDaNop'])->name('ung_vien.ca_lam_da_ung_tuyen');

// Route điều hướng thông báo: /quan-ly/ca-lam/{id}/ung-vien
Route::get('/quan-ly/ca-lam/{id}/ung-vien', [UngTuyenController::class, 'danhSachUngVienTheoCa'])->name('quan_ly.ca_lam.ung_vien');

// 5. XÁC THỰC (Auth: Đăng ký, Đăng nhập thủ công chuẩn, Đăng xuất)
Route::get('/dang-nhap', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/dang-nhap', [LoginController::class, 'login']);
Route::get('/login', [LoginController::class, 'showLoginForm']);
Route::post('/login', [LoginController::class, 'login']);
Route::match(['get', 'post'], '/dang-xuat', [LoginController::class, 'logout'])->name('logout');
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout']);

// Đăng ký tài khoản Ứng viên (UC06 - Xác thực OTP)
Route::get('/dang-ky-ung-vien', [AuthController::class, 'showRegisterCandidate'])->name('register.candidate');
Route::post('/dang-ky-ung-vien', [AuthController::class, 'registerCandidate']);
Route::get('/dang-ky-ung-vien/xac-thuc-otp', [AuthController::class, 'showVerifyOtpRegister'])->name('register.candidate.verify_otp');
Route::post('/dang-ky-ung-vien/xac-thuc-otp', [AuthController::class, 'verifyOtpRegister']);
Route::post('/dang-ky-ung-vien/gui-lai-otp', [AuthController::class, 'resendOtpRegister']);

// Quên mật khẩu & Đặt lại mật khẩu qua OTP Email
Route::get('/quen-mat-khau', [ForgotPasswordController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/quen-mat-khau/gui-otp', [ForgotPasswordController::class, 'sendResetOtp'])->name('password.send_otp');
Route::get('/quen-mat-khau/xac-thuc-otp', [ForgotPasswordController::class, 'showVerifyOtp'])->name('password.verify_otp');
Route::post('/quen-mat-khau/xac-nhan-otp', [ForgotPasswordController::class, 'verifyResetOtp'])->name('password.confirm_otp');
Route::post('/quen-mat-khau/dat-lai-mat-khau', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');

// Đăng ký Quản lý Nhà hàng (UC01)
Route::get('/dang-ky-quan-ly', [AuthController::class, 'showRegisterManager'])->name('register.manager');
Route::post('/dang-ky-quan-ly', [AuthController::class, 'registerManager']);

// 5.1 KIỂM TRA GỬI MAIL TRỰC TIẾP (Kiểm tra Gmail & Mailtrap)
Route::get('/test-mail', function (\Illuminate\Http\Request $request) {
    $gmailTo = trim((string) $request->query('gmail', config('mail.mailers.gmail.username') ?: 'nguyenthibaochau.work@gmail.com'));
    $mailtrapTo = trim((string) $request->query('mailtrap', 'quanly.dongkhoi@goldenshift.vn'));

    $gmailResult = [
        'status' => 'pending',
        'to' => $gmailTo,
        'host' => config('mail.mailers.gmail.host', 'smtp.gmail.com') . ':' . config('mail.mailers.gmail.port', 587),
        'encryption' => config('mail.mailers.gmail.encryption', 'tls'),
        'username' => config('mail.mailers.gmail.username'),
        'message' => '',
    ];

    $mailtrapResult = [
        'status' => 'pending',
        'to' => $mailtrapTo,
        'host' => config('mail.mailers.mailtrap.host', 'sandbox.smtp.mailtrap.io') . ':' . config('mail.mailers.mailtrap.port', 2525),
        'encryption' => config('mail.mailers.mailtrap.encryption', 'tls'),
        'username' => config('mail.mailers.mailtrap.username'),
        'message' => '',
    ];

    // 1. Thử gửi email qua Gmail
    try {
        $mailableGmail = new \App\Mail\SendOtpMail(
            (string) random_int(100000, 999999),
            'Kiểm tra gửi mail Gmail - Golden Shift',
            'Đây là email kiểm tra kết nối SMTP Gmail thật từ hệ thống Golden Shift. Nếu bạn nhận được thư này, kết nối Gmail SMTP đã hoạt động 100% hoàn hảo!'
        );
        \Illuminate\Support\Facades\Mail::mailer('gmail')->to($gmailTo)->send($mailableGmail);
        $gmailResult['status'] = 'success';
        $gmailResult['message'] = 'Gửi thành công!';
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error("Lỗi gửi mail: " . $e->getMessage());
        $gmailResult['status'] = 'error';
        $gmailResult['message'] = $e->getMessage();
    }

    // 2. Thử gửi email qua Mailtrap
    try {
        $mailableMailtrap = new \App\Mail\SendOtpMail(
            (string) random_int(100000, 999999),
            'Kiểm tra gửi mail Mailtrap - Golden Shift',
            'Đây là email kiểm tra kết nối SMTP Mailtrap (Hòm thư ảo Quản lý) từ hệ thống Golden Shift. Nếu bạn thấy thư này trên Mailtrap Inbox, kết nối Mailtrap đã hoạt động 100% hoàn hảo!'
        );
        \Illuminate\Support\Facades\Mail::mailer('mailtrap')->to($mailtrapTo)->send($mailableMailtrap);
        $mailtrapResult['status'] = 'success';
        $mailtrapResult['message'] = 'Gửi thành công!';
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error("Lỗi gửi mail: " . $e->getMessage());
        $mailtrapResult['status'] = 'error';
        $mailtrapResult['message'] = $e->getMessage();
    }

    return view('test-mail', compact('gmailResult', 'mailtrapResult', 'gmailTo', 'mailtrapTo'));
})->name('test.mail');

// 6. PHÂN HỆ QUẢN TRỊ VIÊN ADMIN (Được bảo vệ bởi Middleware kiểm tra role 'quan_tri_vien')
Route::middleware(['admin'])->prefix('admin')->group(function () {
    // Quản lý tài khoản: danh sách, duyệt, từ chối, khóa / mở khóa
    Route::get('/tai-khoan', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/quan-ly-tai-khoan', [AdminUserController::class, 'index'])->name('admin.quan_ly_tai_khoan');
    Route::get('/xet-duyet-quan-ly', [AdminUserController::class, 'index'])->name('admin.xet_duyet');
    Route::post('/tai-khoan/{id}/duyet', [AdminUserController::class, 'duyet'])->name('admin.users.duyet');
    Route::post('/tai-khoan/{id}/tu-choi', [AdminUserController::class, 'tuChoi'])->name('admin.users.tu_choi');
    Route::post('/tai-khoan/{id}/toggle-lock', [AdminUserController::class, 'toggleLock'])->name('admin.users.toggle_lock');

    // Quản lý Thương hiệu & Nhà hàng (admin/categories)
    Route::prefix('categories')->group(function () {
        // Trang tổng quan tab (dùng BrandController làm controller chính)
        Route::get('/', [BrandController::class, 'index'])->name('admin.categories.index');

        // CRUD Thương hiệu
        Route::post('/thuong-hieu', [BrandController::class, 'store'])->name('admin.brands.store');
        Route::post('/thuong-hieu/{id}/update', [BrandController::class, 'update'])->name('admin.brands.update');
        Route::post('/thuong-hieu/{id}/toggle-status', [BrandController::class, 'toggleStatus'])->name('admin.brands.toggle_status');
        Route::post('/thuong-hieu/{id}/destroy', [BrandController::class, 'destroy'])->name('admin.brands.destroy');

        // CRUD Nhà hàng
        Route::post('/nha-hang', [RestaurantController::class, 'store'])->name('admin.restaurants.store');
        Route::post('/nha-hang/{id}/update', [RestaurantController::class, 'update'])->name('admin.restaurants.update');
        Route::post('/nha-hang/{id}/toggle-status', [RestaurantController::class, 'toggleStatus'])->name('admin.restaurants.toggle_status');
        Route::post('/nha-hang/{id}/destroy', [RestaurantController::class, 'destroy'])->name('admin.restaurants.destroy');
    });

    // Xem danh sách đánh giá chi tiết của nhà hàng
    Route::get('/nha-hang/{ma_nha_hang}/danh-gia', [RestaurantController::class, 'showDanhGia'])
        ->whereNumber('ma_nha_hang')
        ->name('admin.nha-hang.danh-gia');

    // Redirect alias cũ sang categories mới
    Route::get('/thuong-hieu-nha-hang', function () {
        return redirect()->route('admin.categories.index');
    })->name('admin.thuong_hieu_nha_hang');

    // Quản lý Báo cáo vi phạm (Giai đoạn 5)
    Route::get('/bao-cao-vi-pham', [AdminBaoCaoController::class, 'index'])->name('admin.bao_cao.index');
    Route::post('/bao-cao-vi-pham/{id}/xu-ly', [AdminBaoCaoController::class, 'xuLy'])->name('admin.bao_cao.xu_ly');

    // Báo cáo thống kê & phân tích toàn hệ thống
    Route::get('/bao-cao-thong-ke', [AdminThongKeController::class, 'index'])->name('admin.thong_ke.index');
    Route::get('/bao-cao-thong-ke/export', [AdminThongKeController::class, 'export'])->name('admin.thong_ke.export');
});

// 7. TRANG HỒ SƠ CÁ NHÂN (PROFILE MANAGEMENT - 3 ROLES)
Route::get('/ho-so-ca-nhan', [ProfileController::class, 'index'])->name('profile.index');
Route::get('/thong-tin-ca-nhan', [ProfileController::class, 'index'])->name('profile.thong_tin');
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::post('/ho-so-ca-nhan/update', [ProfileController::class, 'update'])->name('profile.update');
Route::post('/ho-so-ca-nhan/doi-mat-khau', [ProfileController::class, 'changePassword'])->name('profile.change_password');

// 8. CHẤM CÔNG (VÀO / RA CA & XÁC NHẬN GIỜ LÀM)
Route::post('/cham-cong/check-in', [ChamCongController::class, 'checkIn'])->name('cham_cong.check_in');
Route::post('/cham-cong/check-out', [ChamCongController::class, 'checkOut'])->name('cham_cong.check_out');
Route::post('/cham-cong/yeu-cau-qr', [ChamCongController::class, 'requestQr'])->name('cham_cong.qr_request');
Route::match(['get', 'post'], '/cham-cong/qr-scan', [ChamCongController::class, 'scanQr'])->name('cham_cong.qr_scan');
Route::get('/quan-ly/cham-cong', [ChamCongController::class, 'indexQuanLy'])->name('quan_ly.cham_cong');
Route::get('/cham-cong-thu-nhap', [ChamCongController::class, 'indexQuanLy'])->name('cham_cong_thu_nhap');
Route::get('/quan-ly/cham-cong/qr-pending', [ChamCongController::class, 'pendingQr'])->name('quan_ly.cham_cong.qr_pending');
Route::post('/quan-ly/cham-cong/qr-dismiss', [ChamCongController::class, 'dismissQr'])->name('quan_ly.cham_cong.qr_dismiss');
Route::post('/quan-ly/cham-cong/qr-token/{caLamId?}', [ChamCongController::class, 'generateQrToken'])->name('quan_ly.cham_cong.qr_token');
Route::post('/quan-ly/cham-cong/xac-nhan', [ChamCongController::class, 'confirm'])->name('quan_ly.cham_cong.confirm');
Route::post('/quan-ly/cham-cong/tu-choi', [ChamCongController::class, 'reject'])->name('quan_ly.cham_cong.reject');

// SỔ THU NHẬP (ỨNG VIÊN)
Route::get('/ung-vien/so-thu-nhap', [SoThuNhapController::class, 'index'])->name('ung_vien.so_thu_nhap');
Route::get('/so-thu-nhap', [SoThuNhapController::class, 'index'])->name('so_thu_nhap');

// 9. GIAI ĐOẠN 5: ĐÁNH GIÁ 2 CHIỀU & BÁO CÁO VI PHẠM
Route::post('/danh-gia/luu', [DanhGiaCaLamController::class, 'store'])->name('danh_gia.store');
Route::post('/bao-cao-vi-pham/gui', [BaoCaoViPhamController::class, 'store'])->name('bao_cao_vi_pham.store');

Route::get('/dashboard', [UnderConstructionController::class, 'dashboard']);

// 8. HỆ THỐNG THÔNG BÁO IN-APP
Route::get('/thong-bao/danh-sach', [ThongBaoController::class, 'danhSach'])->name('thong_bao.danh_sach');
Route::get('/thong-bao/{id}/doc', [ThongBaoController::class, 'docVaChuyenHuong'])->name('thong_bao.doc');
Route::post('/thong-bao/doc-tat-ca', [ThongBaoController::class, 'docTatCa'])->name('thong_bao.doc_tat_ca');

// 9. REAL-TIME API ENDPOINTS (AJAX Polling - Session auth)
Route::prefix('api')->group(function () {
    Route::get('/notifications/unread-count', [RealtimeApiController::class, 'getUnreadNotificationCount'])->name('api.notifications.unread');
    Route::get('/applications/pending', [RealtimeApiController::class, 'getPendingApplications'])->name('api.applications.pending');
    Route::get('/applications/status', [RealtimeApiController::class, 'getApplicationStatus'])->name('api.applications.status');
});
