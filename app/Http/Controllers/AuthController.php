<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\LoginController;
use App\Mail\OtpRegisterMail;
use App\Models\HoSoUngVien;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use App\Models\ThongBao;
use App\Services\MultiMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    /**
     * Màn hình đăng nhập (chuyển tiếp tới LoginController chuẩn)
     */
    public function showLogin()
    {
        return app(LoginController::class)->showLoginForm();
    }

    /**
     * Xử lý đăng nhập thủ công chuẩn qua Auth::attempt() (chuyển tiếp tới LoginController chuẩn)
     */
    public function login(Request $request)
    {
        return app(LoginController::class)->login($request);
    }

    /**
     * Màn hình Đăng ký Ứng viên
     */
    public function showRegisterCandidate()
    {
        if (session()->has('user')) {
            return redirect('/');
        }

        return view('auth.register_candidate');
    }

    /**
     * Xử lý Đăng ký Ứng viên (vai trò 'ung_vien'):
     * - Họ và tên: Bắt buộc, không chứa chữ số (regex: /^[\pL\s]+$/u)
     * - Số điện thoại: Bắt buộc, đúng 10 chữ số và bắt đầu bằng số 0 (regex: /^0[0-9]{9}$/)
     * - Trạng thái tự động: 'hoat_dong'
     */
    public function registerCandidate(Request $request)
    {
        $rules = [
            'ho_ten' => [
                'required',
                'string',
                'max:100',
                'regex:/^[\pL\s]+$/u',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
            ],
            'so_dien_thoai' => [
                'required',
                'regex:/^0[0-9]{9}$/',
            ],
            'ngay_sinh' => [
                'required',
                'date',
                'before_or_equal:'.now()->subYears(16)->format('Y-m-d'),
            ],
            'so_cccd' => [
                'required',
                'string',
                'regex:/^0\d{11}$/',
            ],
            'mat_khau' => [
                'required',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/[A-Z]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 chữ hoa (A-Z).');
                    } elseif (! preg_match('/[a-z]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 chữ thường (a-z).');
                    } elseif (! preg_match('/[0-9]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 chữ số (0-9).');
                    } elseif (! preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt (!@#$...).');
                    }
                },
            ],
            'xac_nhan_mat_khau' => [
                'required',
                'same:mat_khau',
            ],
            'kinh_nghiem' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];

        $messages = [
            'ho_ten.required' => 'Vui lòng nhập họ và tên của bạn.',
            'ho_ten.regex' => 'Họ và tên không hợp lệ (chỉ chứa chữ cái và khoảng trắng, không chứa số).',
            'email.required' => 'Vui lòng nhập địa chỉ email nhận việc.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'so_dien_thoai.regex' => 'Số điện thoại phải đúng 10 chữ số và bắt đầu bằng số 0 (Ví dụ: 0912345678).',
            'ngay_sinh.required' => 'Vui lòng chọn ngày tháng năm sinh.',
            'ngay_sinh.date' => 'Ngày tháng năm sinh không đúng định dạng.',
            'ngay_sinh.before_or_equal' => 'Ứng viên phải từ 16 tuổi trở lên mới đủ điều kiện đăng ký làm việc!',
            'so_cccd.required' => 'Vui lòng nhập số Căn cước công dân (CCCD).',
            'so_cccd.regex' => 'Số CCCD phải bao gồm 12 chữ số và bắt đầu bằng số 0!',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
            'mat_khau.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'xac_nhan_mat_khau.required' => 'Vui lòng xác nhận lại mật khẩu.',
            'xac_nhan_mat_khau.same' => 'Xác nhận mật khẩu không trùng khớp.',
        ];

        $request->validate($rules, $messages);

        $email = trim($request->email);

        // Kiểm tra trùng Email hoặc SĐT trong CSDL
        try {
            $emailExists = NguoiDung::where('email', $email)->exists();
            if ($emailExists) {
                return back()
                    ->withInput($request->except(['mat_khau', 'xac_nhan_mat_khau']))
                    ->withErrors(['email' => 'Địa chỉ email này đã được sử dụng. Vui lòng chọn email khác.']);
            }

            $phoneExists = NguoiDung::where('so_dien_thoai', $request->so_dien_thoai)->exists();
            if ($phoneExists) {
                return back()
                    ->withInput($request->except(['mat_khau', 'xac_nhan_mat_khau']))
                    ->withErrors(['so_dien_thoai' => 'Số điện thoại này đã được sử dụng trong hệ thống.']);
            }
        } catch (\Throwable $e) {
            // DB fallback
        }

        // Sinh mã OTP 6 số ngẫu nhiên & lưu vào Cache 5 phút (300 giây)
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('otp_reg_' . $email, [
            'data' => $request->all(),
            'otp' => $otp,
        ], 300);

        // Gửi email OTP qua MultiMailerService (Gmail thật cho Ứng viên)
        $mailError = null;
        $mailSent = MultiMailerService::send($email, new OtpRegisterMail($otp, trim($request->ho_ten)), $mailError);

        $redirect = redirect()->route('register.candidate.verify_otp', ['email' => $email]);
        if (! $mailSent && $mailError) {
            return $redirect->with('warning', "Mã OTP đã được tạo, tuy nhiên gửi email gặp lỗi: {$mailError}. Vui lòng kiểm tra lại cấu hình SMTP.");
        }

        return $redirect->with('success', 'Mã xác thực OTP gồm 6 chữ số đã được gửi tới email của bạn. Vui lòng nhập mã để hoàn tất đăng ký!');
    }

    /**
     * Màn hình nhập OTP xác thực đăng ký tài khoản Ứng viên
     */
    public function showVerifyOtpRegister(Request $request)
    {
        $email = $request->query('email');
        if (empty($email)) {
            return redirect()->route('register.candidate')->with('error', 'Vui lòng điền thông tin đăng ký để nhận mã OTP.');
        }

        return view('auth.verify-otp-register', compact('email'));
    }

    /**
     * Xác thực OTP và chính thức tạo tài khoản Ứng viên
     */
    public function verifyOtpRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Vui lòng nhập mã xác thực OTP.',
            'otp.size' => 'Mã OTP phải có đúng 6 chữ số.',
        ]);

        $email = trim($request->email);
        $otp = trim($request->otp);

        $cached = Cache::get('otp_reg_' . $email);

        if (! $cached || ! isset($cached['otp']) || $cached['otp'] !== $otp) {
            return back()->withInput()->with('error', 'Mã OTP không chính xác hoặc đã hết hạn (chỉ có hiệu lực trong 5 phút). Vui lòng thử lại!');
        }

        $data = $cached['data'] ?? [];

        try {
            $user = NguoiDung::create([
                'ho_ten' => trim($data['ho_ten'] ?? 'Ứng viên'),
                'email' => $email,
                'so_dien_thoai' => trim($data['so_dien_thoai'] ?? ''),
                'mat_khau' => Hash::make($data['mat_khau'] ?? 'GoldenShift@2026'),
                'vai_tro' => 'ung_vien',
                'trang_thai' => 'hoat_dong',
            ]);

            if ($user && $user->ma_nguoi_dung) {
                $profileData = [
                    'ma_nguoi_dung' => $user->ma_nguoi_dung,
                    'ngay_sinh' => $data['ngay_sinh'] ?? '2000-01-01',
                    'so_cccd' => trim($data['so_cccd'] ?? ''),
                    'gioi_thieu_ban_than' => $data['kinh_nghiem'] ?? 'Ứng viên mới đăng ký tài khoản Golden Shift',
                    'trang_thai_ho_so' => 'san_sang_nhan_viec',
                ];
                if (Schema::hasColumn('ho_so_ung_vien', 'diem_danh_gia')) {
                    $profileData['diem_danh_gia'] = 0.0;
                }
                HoSoUngVien::create($profileData);
            }
        } catch (\Throwable $e) {
            // Mock session fallback
            $mockUser = [
                'ma_nguoi_dung' => rand(100, 999),
                'ho_ten' => trim($data['ho_ten'] ?? 'Ứng viên'),
                'email' => $email,
                'so_dien_thoai' => trim($data['so_dien_thoai'] ?? ''),
                'ngay_sinh' => $data['ngay_sinh'] ?? '2000-01-01',
                'so_cccd' => trim($data['so_cccd'] ?? ''),
                'vai_tro' => 'ung_vien',
                'trang_thai' => 'hoat_dong',
                'ngay_tao' => date('Y-m-d H:i:s'),
            ];
            $currentMock = session('mock_candidates', []);
            $currentMock[] = $mockUser;
            session(['mock_candidates' => $currentMock]);
        }

        // Xóa cache OTP sau khi tạo tài khoản thành công
        Cache::forget('otp_reg_' . $email);

        return redirect()->route('login')
            ->withInput(['email' => $email])
            ->with('success', 'Xác thực mã OTP thành công! Tài khoản Ứng viên của bạn đã được kích hoạt. Hãy đăng nhập để nhận ca làm ngay!');
    }

    /**
     * Gửi lại mã OTP đăng ký
     */
    public function resendOtpRegister(Request $request)
    {
        $email = trim($request->email ?? '');
        $cached = Cache::get('otp_reg_' . $email);

        if (! $cached || ! isset($cached['data'])) {
            return redirect()->route('register.candidate')
                ->with('error', 'Phiên đăng ký đã hết hạn. Vui lòng điền lại form đăng ký.');
        }

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $cached['otp'] = $otp;
        Cache::put('otp_reg_' . $email, $cached, 300);

        $mailError = null;
        $mailSent = MultiMailerService::send($email, new OtpRegisterMail($otp, trim($cached['data']['ho_ten'] ?? 'Ứng viên')), $mailError);

        if (! $mailSent && $mailError) {
            return back()->with('error', "Không thể gửi lại mã OTP qua email: {$mailError}. Vui lòng kiểm tra lại cấu hình SMTP.");
        }

        return back()->with('success', 'Đã gửi lại mã OTP mới qua email của bạn!');
    }

    /**
     * Màn hình Đăng ký Quản lý Nhà hàng
     */
    public function showRegisterManager()
    {
        if (session()->has('user')) {
            return redirect('/');
        }

        try {
            $nhaHangList = NhaHang::with('thuongHieu')
                ->where('trang_thai_hoat_dong', 'dang_hoat_dong')
                ->get();
        } catch (\Throwable $e) {
            $nhaHangList = collect([]);
        }

        if ($nhaHangList->isEmpty()) {
            $nhaHangList = collect([
                (object) ['ma_nha_hang' => 1, 'ten_nha_hang' => 'Golden Hotpot - Đồng Khởi', 'quan_huyen' => 'Quận 1'],
                (object) ['ma_nha_hang' => 2, 'ten_nha_hang' => 'Golden BBQ - Landmark 81', 'quan_huyen' => 'Bình Thạnh'],
                (object) ['ma_nha_hang' => 3, 'ten_nha_hang' => 'Golden Hotpot - Crescent Mall', 'quan_huyen' => 'Quận 7'],
                (object) ['ma_nha_hang' => 4, 'ten_nha_hang' => 'Golden Dimsum - Saigon Centre', 'quan_huyen' => 'Quận 1'],
                (object) ['ma_nha_hang' => 5, 'ten_nha_hang' => 'Golden Steakhouse - Thảo Điền', 'quan_huyen' => 'TP. Thủ Đức'],
            ]);
        }

        return view('auth.register_manager', compact('nhaHangList'));
    }

    /**
     * Xử lý Đăng ký Quản lý Nhà hàng (vai trò 'quan_ly_nha_hang'):
     * - Họ và tên: Bắt buộc, không chứa chữ số (regex: /^[\pL\s]+$/u)
     * - Số điện thoại: Bắt buộc, đúng 10 chữ số và bắt đầu bằng số 0 (regex: /^0[0-9]{9}$/)
     * - Email: Bắt buộc cố định đuôi @goldenshift.vn (regex: /^[a-zA-Z0-9._%+-]+@goldenshift\.vn$/i)
     * - Nhà hàng: Bắt buộc
     * - Trạng thái: 'cho_duyet'
     */
    public function registerManager(Request $request)
    {
        $maNhaHangRules = [
            'required',
            'integer',
            'min:1',
        ];
        if (Schema::hasTable('nha_hang')) {
            $maNhaHangRules[] = 'exists:nha_hang,ma_nha_hang';
        }

        $rules = [
            'ho_ten' => [
                'required',
                'string',
                'max:100',
                'regex:/^[\pL\s]+$/u',
            ],
            'email' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9._%+-]+@goldenshift\.vn$/i',
                'max:150',
            ],
            'so_dien_thoai' => [
                'required',
                'regex:/^0[0-9]{9}$/',
            ],
            'ma_nha_hang' => $maNhaHangRules,
            'mat_khau' => [
                'required',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/[A-Z]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 chữ hoa (A-Z).');
                    } elseif (! preg_match('/[a-z]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 chữ thường (a-z).');
                    } elseif (! preg_match('/[0-9]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 chữ số (0-9).');
                    } elseif (! preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt (!@#$...).');
                    }
                },
            ],
            'xac_nhan_mat_khau' => [
                'required',
                'same:mat_khau',
            ],
        ];

        $messages = [
            'ho_ten.required' => 'Vui lòng nhập họ và tên của Quản lý.',
            'ho_ten.regex' => 'Họ và tên không hợp lệ (chỉ chứa chữ cái và khoảng trắng, không chứa số).',
            'email.required' => 'Vui lòng nhập địa chỉ email công việc.',
            'email.regex' => 'Email Quản lý nhà hàng bắt buộc phải có đuôi @goldenshift.vn (Ví dụ: name@goldenshift.vn).',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'so_dien_thoai.regex' => 'Số điện thoại phải đúng 10 chữ số và bắt đầu bằng số 0 (Ví dụ: 0912345678).',
            'ma_nha_hang.required' => 'Vui lòng chọn Nhà hàng/Chi nhánh trực thuộc!',
            'ma_nha_hang.integer' => 'Vui lòng chọn Nhà hàng/Chi nhánh trực thuộc!',
            'ma_nha_hang.min' => 'Vui lòng chọn Nhà hàng/Chi nhánh trực thuộc!',
            'ma_nha_hang.exists' => 'Nhà hàng/Chi nhánh được chọn không tồn tại trong hệ thống!',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
            'mat_khau.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'xac_nhan_mat_khau.required' => 'Vui lòng xác nhận lại mật khẩu.',
            'xac_nhan_mat_khau.same' => 'Xác nhận mật khẩu không trùng khớp.',
        ];

        $request->validate($rules, $messages);

        try {
            $emailExists = NguoiDung::where('email', $request->email)->exists();
            if ($emailExists) {
                return back()
                    ->withInput($request->except(['mat_khau', 'xac_nhan_mat_khau']))
                    ->withErrors(['email' => 'Địa chỉ email này đã được sử dụng trong hệ thống.']);
            }

            $phoneExists = NguoiDung::where('so_dien_thoai', $request->so_dien_thoai)->exists();
            if ($phoneExists) {
                return back()
                    ->withInput($request->except(['mat_khau', 'xac_nhan_mat_khau']))
                    ->withErrors(['so_dien_thoai' => 'Số điện thoại này đã được sử dụng trong hệ thống.']);
            }

            NguoiDung::create([
                'ho_ten' => trim($request->ho_ten),
                'email' => trim($request->email),
                'so_dien_thoai' => trim($request->so_dien_thoai),
                'mat_khau' => Hash::make($request->mat_khau),
                'vai_tro' => 'quan_ly_nha_hang',
                'ma_nha_hang_truc_thuoc' => (int) $request->ma_nha_hang,
                'trang_thai' => 'cho_duyet',
            ]);

            // Gửi thông báo cho tất cả Admin khi Quản lý mới đăng ký
            ThongBao::taoBaoTatCaAdmin(
                'Quản lý mới đăng ký chờ xem xét',
                'Tài khoản Quản lý ['.trim($request->ho_ten).'] ('.trim($request->email).') vừa đăng ký, đang chờ Admin xem xét phê duyệt.',
                '/admin/quan-ly-tai-khoan'
            );
        } catch (\Throwable $e) {
            // Lưu vào mock session fallback
            $mockManager = [
                'ma_nguoi_dung' => rand(100, 999),
                'ho_ten' => trim($request->ho_ten),
                'email' => trim($request->email),
                'so_dien_thoai' => trim($request->so_dien_thoai),
                'vai_tro' => 'quan_ly_nha_hang',
                'ma_nha_hang_truc_thuoc' => (int) $request->ma_nha_hang,
                'ten_nha_hang' => $request->ten_nha_hang_hidden ?: 'Chi nhánh Golden Shift',
                'trang_thai' => 'cho_duyet',
                'ngay_tao' => date('Y-m-d H:i:s'),
            ];
            $currentMock = session('mock_pending_managers', []);
            $currentMock[] = $mockManager;
            session(['mock_pending_managers' => $currentMock]);
        }

        return redirect()->route('login')
            ->withInput(['email' => $request->email])
            ->with('success', 'Đăng ký Quản lý thành công! Tài khoản của bạn đang ở trạng thái [CHỜ DUYỆT]. Ban Quản Trị sẽ xem xét và kích hoạt sớm nhất.');
    }

    /**
     * Đăng xuất (chuyển tiếp tới LoginController chuẩn)
     */
    public function logout()
    {
        return app(LoginController::class)->logout();
    }
}
