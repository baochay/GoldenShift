<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpResetPasswordMail;
use App\Models\NguoiDung;
use App\Services\MultiMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Màn hình nhập Email quên mật khẩu
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Gửi mã OTP xác nhận tới Email
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email của bạn.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
        ]);

        $email = trim($request->email);
        $user = NguoiDung::where('email', $email)->first();

        if (! $user) {
            return back()->withInput()->with('error', 'Địa chỉ email này chưa được đăng ký trong hệ thống Golden Shift.');
        }

        // Sinh mã OTP 6 chữ số ngẫu nhiên
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Lưu vào Cache trong 5 phút (300 giây)
        Cache::put('otp_reset_' . $email, $otp, 300);

        // Gửi email đa kênh
        $mailError = null;
        $mailSent = MultiMailerService::send($email, new OtpResetPasswordMail($otp, $email), $mailError);

        if (! $mailSent && $mailError) {
            return back()->withInput()->with('error', "Lỗi gửi email xác thực OTP: {$mailError}. Vui lòng kiểm tra lại cấu hình SMTP.");
        }

        return redirect()->route('password.verify_otp', ['email' => $email])
            ->with('success', 'Mã xác thực OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư đến (hoặc hòm thư Spam)!');
    }

    /**
     * Màn hình nhập mã OTP xác nhận
     */
    public function showVerifyOtp(Request $request)
    {
        $email = $request->query('email');
        if (empty($email)) {
            return redirect()->route('password.forgot')->with('error', 'Vui lòng nhập email để nhận mã OTP.');
        }

        return view('auth.verify-otp', compact('email'));
    }

    /**
     * Xác thực mã OTP từ người dùng
     */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Vui lòng nhập mã OTP gồm 6 chữ số.',
            'otp.size' => 'Mã OTP phải có đúng 6 chữ số.',
        ]);

        $email = trim($request->email);
        $otp = trim($request->otp);

        $cachedOtp = Cache::get('otp_reset_' . $email);

        if (! $cachedOtp || $cachedOtp !== $otp) {
            return back()->withInput()->with('error', 'Mã OTP không chính xác hoặc đã hết hạn (chỉ có hiệu lực trong 5 phút).');
        }

        // Xóa OTP đã sử dụng
        Cache::forget('otp_reset_' . $email);

        // Sinh token cấp quyền đặt lại mật khẩu trong 10 phút
        $resetToken = Str::random(40);
        Cache::put('reset_token_' . $email, $resetToken, 600);

        return view('auth.reset-password', [
            'email' => $email,
            'token' => $resetToken,
        ])->with('success', 'Xác thực mã OTP thành công! Vui lòng thiết lập mật khẩu mới.');
    }

    /**
     * Cập nhật mật khẩu mới vào CSDL
     */
    public function resetPassword(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'token' => 'required|string',
            'mat_khau' => [
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
            'xac_nhan_mat_khau' => 'required|same:mat_khau',
        ];

        $messages = [
            'mat_khau.required' => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau.min' => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
            'xac_nhan_mat_khau.required' => 'Vui lòng xác nhận mật khẩu mới.',
            'xac_nhan_mat_khau.same' => 'Xác nhận mật khẩu không trùng khớp.',
        ];

        $request->validate($rules, $messages);

        $email = trim($request->email);
        $token = $request->token;

        $cachedToken = Cache::get('reset_token_' . $email);
        if (! $cachedToken || $cachedToken !== $token) {
            return redirect()->route('password.forgot')->with('error', 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thực hiện lại từ đầu.');
        }

        // Cập nhật CSDL
        $user = NguoiDung::where('email', $email)->first();
        if ($user) {
            $user->mat_khau = Hash::make($request->mat_khau);
            $user->save();
        }

        // Xóa token reset
        Cache::forget('reset_token_' . $email);

        return redirect()->route('login')
            ->withInput(['email' => $email])
            ->with('success', 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay với mật khẩu mới.');
    }
}
