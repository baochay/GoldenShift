@extends('layouts.app')

@section('title', 'Xác Thực OTP Đăng Ký - Golden Shift')

@section('content')
<section class="auth-section">
    <div class="gs-container">
        <div class="auth-card-wrap">
            <div class="auth-card glass-panel">
                <div class="auth-card-header text-center">
                    <div class="auth-logo-badge">
                        <i class="fa-solid fa-shield-halved text-gold"></i>
                    </div>
                    <h1 class="auth-title">Xác Thực Mã OTP</h1>
                    <p class="auth-subtitle">
                        Mã OTP gồm 6 chữ số đã được gửi tới email:<br>
                        <strong class="text-gold" style="font-size: 15px;">{{ $email }}</strong>
                    </p>
                </div>

                @if (session('error'))
                <div class="alert-box error" style="margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                </div>
                @endif

                @if (session('warning'))
                <div class="alert-box warning" style="margin-bottom: 1.25rem; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #d97706; padding: 12px 16px; border-radius: 8px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('warning') }}
                </div>
                @endif

                @if (session('success'))
                <div class="alert-box success" style="margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="alert-box error" style="margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
                @endif

                <form action="{{ url('/dang-ky-ung-vien/xac-thuc-otp') }}" method="POST" class="auth-form" id="formVerifyOtpRegister">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="form-group text-center">
                        <label for="inputOtp" style="font-weight: 700; margin-bottom: 8px; display: block;">
                            Nhập mã xác thực 6 chữ số <span class="req">*</span>
                        </label>
                        <div style="display: flex; justify-content: center;">
                            <input type="text" id="inputOtp" name="otp" required maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" placeholder="------" 
                                style="width: 240px; font-size: 28px; font-weight: 800; letter-spacing: 10px; text-align: center; padding: 10px 12px; border: 2px solid var(--gold-500); border-radius: 12px; outline: none; background: #ffffff; color: var(--text-heading);" autofocus>
                        </div>
                        <span class="field-hint" style="display: block; margin-top: 8px; font-size: 12.5px; color: var(--text-muted);">
                            Mã có hiệu lực trong <strong id="countdownTimer" style="color: #ef4444;">05:00</strong>
                        </span>
                    </div>

                    <button type="submit" class="btn btn-gold btn-block btn-lg" style="font-size: 16px; font-weight: 700; padding: 13px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25); margin-top: 18px;">
                        <i class="fa-solid fa-circle-check"></i> Xác Nhận &amp; Kích Hoạt Tài Khoản
                    </button>
                </form>

                <div class="text-center" style="margin-top: 20px;">
                    <form action="{{ url('/dang-ky-ung-vien/gui-lai-otp') }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button type="submit" id="btnResendOtp" class="btn btn-outline btn-sm" style="font-size: 13px; font-weight: 600;">
                            <i class="fa-solid fa-arrow-rotate-right"></i> Gửi lại mã OTP
                        </button>
                    </form>
                </div>

                <div class="auth-card-footer text-center" style="margin-top: 24px;">
                    <a href="{{ route('register.candidate') }}" class="link-gold" style="font-size: 13.5px; font-weight: 600;">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại form điền thông tin
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let timeLeft = 300; // 5 phút
        const timerElem = document.getElementById('countdownTimer');
        const resendBtn = document.getElementById('btnResendOtp');

        const interval = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(interval);
                if (timerElem) timerElem.textContent = 'Đã hết hạn!';
                return;
            }
            timeLeft--;
            const mins = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            if (timerElem) {
                timerElem.textContent = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
            }
        }, 1000);
    });
</script>
@endsection
