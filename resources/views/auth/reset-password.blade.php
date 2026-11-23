@extends('layouts.app')

@section('title', 'Đặt Lại Mật Khẩu Mới - Golden Shift')

@section('content')
<section class="auth-section">
    <div class="gs-container">
        <div class="auth-card-wrap">
            <div class="auth-card glass-panel">
                <div class="auth-card-header text-center">
                    <div class="auth-logo-badge">
                        <i class="fa-solid fa-lock text-gold"></i>
                    </div>
                    <h1 class="auth-title">Thiết Lập Mật Khẩu Mới</h1>
                    <p class="auth-subtitle">
                        Tài khoản: <strong class="text-gold">{{ $email }}</strong>
                    </p>
                </div>

                @if (session('error'))
                <div class="alert-box error" style="margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
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

                <form action="{{ url('/quen-mat-khau/dat-lai-mat-khau') }}" method="POST" class="auth-form" id="formResetPassword">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="token" value="{{ $token ?? '' }}">

                    <div class="form-group">
                        <label for="matKhauMoi">Mật khẩu mới <span class="req">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" id="matKhauMoi" name="mat_khau" required placeholder="Tối thiểu 8 ký tự, chữ hoa, thường, số..." autocomplete="new-password" class="{{ $errors->has('mat_khau') ? 'input-error' : '' }}">
                            <button type="button" class="btn-toggle-password" data-target="matKhauMoi" aria-label="Ẩn/Hiện">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="xacNhanMatKhau">Xác nhận mật khẩu mới <span class="req">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" id="xacNhanMatKhau" name="xac_nhan_mat_khau" required placeholder="Nhập lại mật khẩu mới..." autocomplete="new-password" class="{{ $errors->has('xac_nhan_mat_khau') ? 'input-error' : '' }}">
                            <button type="button" class="btn-toggle-password" data-target="xacNhanMatKhau" aria-label="Ẩn/Hiện">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold btn-block btn-lg" style="font-size: 16px; font-weight: 700; padding: 13px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25); margin-top: 18px;">
                        <i class="fa-solid fa-check"></i> Lưu Mật Khẩu Mới
                    </button>
                </form>

                <div class="auth-card-footer text-center" style="margin-top: 24px;">
                    <a href="{{ route('login') }}" class="link-gold" style="font-size: 13.5px; font-weight: 600;">
                        <i class="fa-solid fa-arrow-left"></i> Hủy &amp; Quay lại Đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
