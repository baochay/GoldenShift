@extends('layouts.app')

@section('title', 'Quên Mật Khẩu - Golden Shift')

@section('content')
<section class="auth-section">
    <div class="gs-container">
        <div class="auth-card-wrap">
            <div class="auth-card glass-panel">
                <div class="auth-card-header text-center">
                    <div class="auth-logo-badge">
                        <i class="fa-solid fa-key text-gold"></i>
                    </div>
                    <h1 class="auth-title">Quên Mật Khẩu</h1>
                    <p class="auth-subtitle">Nhập địa chỉ email đã đăng ký để nhận mã OTP khôi phục mật khẩu</p>
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

                <form action="{{ url('/quen-mat-khau/gui-otp') }}" method="POST" class="auth-form" id="formForgotPassword">
                    @csrf
                    <div class="form-group">
                        <label for="inputEmail">Địa chỉ Email đăng ký <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-envelope input-icon-left"></i>
                            <input type="email" id="inputEmail" name="email" required placeholder="Nhập địa chỉ email của bạn..." value="{{ old('email') }}" autofocus class="{{ $errors->has('email') ? 'input-error' : '' }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold btn-block btn-lg" style="font-size: 16px; font-weight: 700; padding: 13px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25); margin-top: 18px;">
                        <i class="fa-solid fa-paper-plane"></i> Gửi Mã OTP Qua Email
                    </button>
                </form>

                <div class="auth-card-footer text-center" style="margin-top: 24px;">
                    <p>Nhớ lại mật khẩu?</p>
                    <a href="{{ route('login') }}" class="link-gold" style="font-size: 14px; font-weight: 700;">
                        <i class="fa-solid fa-arrow-left"></i> Đăng nhập ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
