<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\OtpChangePasswordMail;
use App\Models\DanhGiaCaLam;
use App\Models\HoSoUngVien;
use App\Models\NguoiDung;
use App\Services\MultiMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Lấy thực thể Người dùng hiện tại từ Auth hoặc Session
     */
    private function getCurrentUser()
    {
        $user = Auth::user();
        if (! $user && session()->has('user')) {
            $sessionUser = session('user');
            $maNguoiDung = is_array($sessionUser) ? ($sessionUser['ma_nguoi_dung'] ?? null) : ($sessionUser->ma_nguoi_dung ?? null);
            if ($maNguoiDung) {
                try {
                    $user = NguoiDung::with(['nhaHang.thuongHieu', 'nhaHangTrucThuoc.thuongHieu', 'hoSoUngVien'])->find($maNguoiDung);
                    if ($user) {
                        Auth::login($user);
                    }
                } catch (\Throwable $e) {
                    $u = new NguoiDung;
                    $u->forceFill(is_array($sessionUser) ? $sessionUser : (array) $sessionUser);
                    $user = $u;
                }
            }
        }

        return $user;
    }

    /**
     * 1. Hiển thị Trang Hồ sơ cá nhân (3 Role)
     */
    public function index(Request $request)
    {
        $user = $this->getCurrentUser();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem thông tin hồ sơ.');
        }

        $user->loadMissing(['nhaHang.thuongHieu', 'nhaHangTrucThuoc.thuongHieu', 'hoSoUngVien']);
        $hoSo = $user->hoSoUngVien ?? new HoSoUngVien;
        $nhaHang = $user->nhaHang ?? ($user->nhaHangTrucThuoc ?? null);

        // Dữ liệu Đánh giá & Phản hồi (Giai đoạn 5)
        $danhGiaList = collect();
        $diemThongKe = [
            'diem_tb' => 0.0,
            'tong_luot' => 0,
            'thai_do_tb' => 0.0,
            'chuyen_can_tb' => 0.0,
            'hieu_suat_tb' => 0.0,
        ];

        try {
            if ($user->vai_tro === 'ung_vien') {
                $danhGiaList = DanhGiaCaLam::with(['caLamViec.nhaHang.thuongHieu', 'nguoiDanhGia'])
                    ->where('ma_nguoi_duoc_danh_gia', $user->ma_nguoi_dung)
                    ->where('loai_danh_gia', 'nha_hang_danh_gia_ung_vien')
                    ->orderBy('ngay_danh_gia', 'desc')
                    ->paginate(5, ['*'], 'reviews_page')
                    ->appends(array_merge($request->except('reviews_page'), ['tab' => 'reviews']));
                $diemThongKe = DanhGiaCaLam::tinhDiemTrungBinhUngVien($user->ma_nguoi_dung);
            } elseif ($user->vai_tro === 'quan_ly_nha_hang') {
                $maNhaHang = (int) ($user->ma_nha_hang_truc_thuoc ?: ($nhaHang ? $nhaHang->ma_nha_hang : 0));
                $danhGiaList = DanhGiaCaLam::with(['caLamViec', 'nguoiDanhGia'])
                    ->whereHas('caLamViec', function ($q) use ($maNhaHang) {
                        $q->where('ma_nha_hang', $maNhaHang);
                    })
                    ->where('loai_danh_gia', 'ung_vien_danh_gia_nha_hang')
                    ->orderBy('ngay_danh_gia', 'desc')
                    ->paginate(5, ['*'], 'reviews_page')
                    ->appends(array_merge($request->except('reviews_page'), ['tab' => 'reviews']));
                $diemThongKe = DanhGiaCaLam::tinhDiemTrungBinhNhaHang($maNhaHang);
            } else {
                $adminReviewQuery = DanhGiaCaLam::with(['caLamViec.nhaHang', 'nguoiDanhGia', 'nguoiDuocDanhGia'])
                    ->orderBy('ngay_danh_gia', 'desc');
                $tongAdmin = (clone $adminReviewQuery)->count();
                $diemThongKe['tong_luot'] = $tongAdmin;
                $diemThongKe['diem_tb'] = $tongAdmin > 0 ? round((float) (clone $adminReviewQuery)->avg('so_sao'), 1) : 0.0;
                $danhGiaList = $adminReviewQuery->paginate(5, ['*'], 'reviews_page')
                    ->appends(array_merge($request->except('reviews_page'), ['tab' => 'reviews']));
            }
        } catch (\Throwable $e) {
        }

        return view('profile.index', compact('user', 'hoSo', 'nhaHang', 'danhGiaList', 'diemThongKe'));
    }

    /**
     * 2. Cập nhật Thông tin cá nhân tùy theo Role
     */
    public function update(Request $request)
    {
        $user = $this->getCurrentUser();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        }

        // Quy tắc xác thực chung & riêng theo vai trò
        $rules = [
            'ho_ten' => [
                'required',
                'string',
                'max:100',
                'regex:/^[\p{L}\s]+$/u', // Không chứa chữ số hoặc ký tự đặc biệt
            ],
            'so_dien_thoai' => [
                'required',
                'regex:/^0[0-9]{9}$/', // 10 chữ số bắt đầu bằng 0
            ],
            'anh_dai_dien' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,gif',
                'max:2048', // Tối đa 2MB
            ],
        ];

        // Nếu là Ứng viên: cho phép cập nhật email (kiểm tra duy nhất), bắt buộc ngày sinh (>=16 tuổi), CCCD (12 số bắt đầu bằng 0), giới tính (nam,nu)
        if ($user->vai_tro === 'ung_vien') {
            $emailRules = ['required', 'email', 'max:150'];
            try {
                if (Schema::hasTable('nguoi_dung')) {
                    $emailRules[] = Rule::unique('nguoi_dung', 'email')->ignore($user->ma_nguoi_dung, 'ma_nguoi_dung');
                }
            } catch (\Throwable $e) {
            }
            $rules['email'] = $emailRules;
            $rules['ngay_sinh'] = [
                'required',
                'date',
                'before_or_equal:'.now()->subYears(16)->format('Y-m-d'),
            ];
            $rules['so_cccd'] = [
                'required',
                'string',
                'regex:/^0\d{11}$/',
            ];
            $rules['gioi_tinh'] = [
                'required',
                'in:nam,nu',
            ];
            $rules['gioi_thieu_ban_than'] = 'nullable|string|max:2000';
        }

        $messages = [
            'ho_ten.required' => 'Họ và tên không được để trống.',
            'ho_ten.regex' => 'Họ và tên chỉ được chứa chữ cái và khoảng trắng, không được chứa chữ số.',
            'so_dien_thoai.required' => 'Số điện thoại không được để trống.',
            'so_dien_thoai.regex' => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0 (Ví dụ: 0912345678).',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng bởi tài khoản khác.',
            'ngay_sinh.required' => 'Vui lòng chọn ngày tháng năm sinh.',
            'ngay_sinh.date' => 'Ngày sinh không hợp lệ.',
            'ngay_sinh.before_or_equal' => 'Ứng viên phải từ 16 tuổi trở lên mới đủ điều kiện đăng ký làm việc!',
            'so_cccd.required' => 'Vui lòng nhập số Căn cước công dân (CCCD).',
            'so_cccd.regex' => 'Số CCCD phải bao gồm 12 chữ số và bắt đầu bằng số 0!',
            'gioi_tinh.required' => 'Vui lòng chọn giới tính.',
            'gioi_tinh.in' => 'Giới tính chỉ được chọn Nam hoặc Nữ.',
            'anh_dai_dien.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'anh_dai_dien.mimes' => 'Hình ảnh đại diện chỉ chấp nhận định dạng: jpeg, png, jpg, webp, gif.',
            'anh_dai_dien.max' => 'Kích thước ảnh đại diện không được vượt quá 2MB.',
        ];

        $validated = $request->validate($rules, $messages);

        // Xử lý upload ảnh đại diện (nếu có)
        if ($request->hasFile('anh_dai_dien')) {
            $file = $request->file('anh_dai_dien');
            $extension = $file->getClientOriginalExtension();
            $filename = 'avatar_'.$user->ma_nguoi_dung.'_'.time().'.'.$extension;

            $destinationPath = public_path('uploads/avatars');
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $user->anh_dai_dien = 'uploads/avatars/'.$filename;
        }

        // Cập nhật thông tin Người dùng chung
        $user->ho_ten = trim($validated['ho_ten']);
        $user->so_dien_thoai = trim($validated['so_dien_thoai']);
        if ($user->vai_tro === 'ung_vien' && isset($validated['email'])) {
            $user->email = trim($validated['email']);
        }
        try {
            $user->save();
        } catch (\Throwable $e) {
        }

        // Cập nhật hồ sơ mở rộng nếu là Ứng viên
        if ($user->vai_tro === 'ung_vien') {
            try {
                HoSoUngVien::updateOrCreate(
                    ['ma_nguoi_dung' => $user->ma_nguoi_dung],
                    [
                        'ngay_sinh' => $request->input('ngay_sinh'),
                        'so_cccd' => trim($request->input('so_cccd')),
                        'gioi_tinh' => $request->input('gioi_tinh'),
                        'gioi_thieu_ban_than' => $request->input('gioi_thieu_ban_than'),
                    ]
                );
            } catch (\Throwable $e) {
            }
        }

        // Đồng bộ dữ liệu trong session để Navbar và các trang khác cập nhật tức thời
        $userArr = $user->toArray();
        $assignedNhaHang = $user->nhaHang ?? ($user->nhaHangTrucThuoc ?? null);
        if ($assignedNhaHang) {
            $userArr['ma_nha_hang_truc_thuoc'] = $assignedNhaHang->ma_nha_hang;
            $userArr['ten_nha_hang'] = $assignedNhaHang->ten_nha_hang;
            $userArr['ten_thuong_hieu'] = $assignedNhaHang->thuongHieu->ten_thuong_hieu ?? 'Golden Hotpot';
            $userArr['slug_thuong_hieu'] = $assignedNhaHang->thuongHieu->ma_dinh_danh ?? 'golden-hotpot';
            $userArr['dia_chi_chi_tiet'] = $assignedNhaHang->dia_chi_chi_tiet ?? '';
            $userArr['quan_huyen'] = $assignedNhaHang->quan_huyen ?? '';
        }
        session(['user' => $userArr]);

        return redirect()->route('profile.index')
            ->with('success', 'Cập nhật thông tin hồ sơ cá nhân thành công!')
            ->with('active_tab', 'profile');
    }

    /**
     * 3. Đổi Mật khẩu
     */
    public function changePassword(Request $request)
    {
        $user = $this->getCurrentUser();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        }

        $rules = [
            'mat_khau_hien_tai' => 'required',
            'mat_khau_moi' => [
                'required',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/[A-Z]/', $value)) {
                        $fail('Mật khẩu mới phải chứa ít nhất 1 chữ hoa (A-Z).');
                    } elseif (! preg_match('/[a-z]/', $value)) {
                        $fail('Mật khẩu mới phải chứa ít nhất 1 chữ thường (a-z).');
                    } elseif (! preg_match('/[0-9]/', $value)) {
                        $fail('Mật khẩu mới phải chứa ít nhất 1 chữ số (0-9).');
                    } elseif (! preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $value)) {
                        $fail('Mật khẩu mới phải chứa ít nhất 1 ký tự đặc biệt (!@#$...).');
                    }
                },
            ],
            'xac_nhan_mat_khau' => 'required|same:mat_khau_moi',
        ];

        $messages = [
            'mat_khau_hien_tai.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'mat_khau_moi.required' => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau_moi.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'xac_nhan_mat_khau.required' => 'Vui lòng nhập xác nhận mật khẩu mới.',
            'xac_nhan_mat_khau.same' => 'Mật khẩu xác nhận không trùng khớp với mật khẩu mới.',
        ];

        $request->validate($rules, $messages);

        $currentInput = (string) $request->input('mat_khau_hien_tai');
        $newInput = (string) $request->input('mat_khau_moi');

        // Kiểm tra mật khẩu hiện tại chuẩn Hash
        $isMatch = Hash::check($currentInput, $user->mat_khau);

        if (! $isMatch) {
            return back()
                ->withErrors(['mat_khau_hien_tai' => 'Mật khẩu hiện tại không chính xác.'])
                ->with('error', 'Mật khẩu hiện tại không chính xác.')
                ->with('active_tab', 'password');
        }

        if ($currentInput === $newInput) {
            return back()
                ->withErrors(['mat_khau_moi' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.'])
                ->with('error', 'Mật khẩu mới không được trùng với mật khẩu hiện tại.')
                ->with('active_tab', 'password');
        }

        $email = $user->email;
        $otp = $request->input('otp');

        // Nếu chưa nhập mã OTP: Sinh OTP 6 số lưu Cache 5 phút và gửi qua Email xác nhận đúng chủ tài khoản
        if (empty($otp)) {
            $generatedOtp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put('otp_change_pwd_' . $email, [
                'otp' => $generatedOtp,
                'new_password' => $newInput,
            ], 300);

            $mailError = null;
            $mailSent = MultiMailerService::send($email, new OtpChangePasswordMail($generatedOtp, $user->ho_ten ?? 'Người dùng'), $mailError);

            if (! $mailSent && $mailError) {
                return back()
                    ->withInput($request->only('mat_khau_hien_tai', 'mat_khau_moi', 'xac_nhan_mat_khau'))
                    ->with('error', "Không thể gửi mã OTP xác thực qua email: {$mailError}. Vui lòng kiểm tra lại cấu hình SMTP.")
                    ->with('active_tab', 'password');
            }

            return back()
                ->withInput($request->only('mat_khau_hien_tai', 'mat_khau_moi', 'xac_nhan_mat_khau'))
                ->with('otp_change_pwd_sent', true)
                ->with('success', "Mã OTP xác thực gồm 6 chữ số đã được gửi tới email [{$email}]. Vui lòng nhập mã OTP bên dưới để xác nhận!")
                ->with('active_tab', 'password');
        }

        // Nếu đã có OTP gửi lên: Xác thực OTP từ Cache
        $cached = Cache::get('otp_change_pwd_' . $email);
        if (! $cached || ! isset($cached['otp']) || $cached['otp'] !== trim((string) $otp)) {
            return back()
                ->withInput($request->only('mat_khau_hien_tai', 'mat_khau_moi', 'xac_nhan_mat_khau'))
                ->withErrors(['otp' => 'Mã OTP không chính xác hoặc đã hết hạn (chỉ có hiệu lực 5 phút).'])
                ->with('error', 'Mã OTP không chính xác hoặc đã hết hạn!')
                ->with('otp_change_pwd_sent', true)
                ->with('active_tab', 'password');
        }

        // Cập nhật mật khẩu mới (bcrypt hash)
        $user->mat_khau = Hash::make($cached['new_password'] ?? $newInput);
        try {
            $user->save();
        } catch (\Throwable $e) {
        }

        Cache::forget('otp_change_pwd_' . $email);

        return redirect()->route('profile.index')
            ->with('success', 'Xác thực OTP thành công! Đổi mật khẩu thành công. Vui lòng ghi nhớ mật khẩu mới của bạn.')
            ->with('active_tab', 'password');
    }
}
