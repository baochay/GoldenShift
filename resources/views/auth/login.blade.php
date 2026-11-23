@extends('layouts.app')

@section('title', 'Đăng Nhập - Golden Shift')

@section('content')
@php
$flashError = session('error');
$flashSuccess = session('success');
@endphp
<section class="auth-section">
    <div class="gs-container">
        <div class="auth-card-wrap">
            <div class="auth-card glass-panel">
                <div class="auth-card-header text-center">
                    <div class="auth-logo-badge">
                        <i class="fa-solid fa-clock-rotate-left text-gold"></i>
                    </div>
                    <h1 class="auth-title">Đăng Nhập Golden Shift</h1>
                    <p class="auth-subtitle">Cổng kết nối ca làm việc &amp; tuyển dụng F&amp;B thông minh</p>
                </div>

                @if (session('warning'))
                <div class="alert-box warning" style="margin-bottom: 1.25rem; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 0.85rem 1.25rem; border-radius: 10px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem; color: #d97706;"></i>
                    <span>{{ session('warning') }}</span>
                </div>
                @elseif (!empty($flashError))
                <div class="alert-box error" style="margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ $flashError }}
                </div>
                @endif

                @if ($errors->any() && empty($flashError))
                <div class="alert-box error" style="margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    @if ($errors->count() == 1)
                    {{ $errors->first() }}
                    @else
                    <ul style="margin: 4px 0 0 16px; padding: 0;">
                        @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @endif

                @if (!empty($flashSuccess))
                <div class="alert-box success" style="margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-circle-check"></i> {{ $flashSuccess }}
                </div>
                @endif

                <form action="{{ url('/dang-nhap') }}" method="POST" class="auth-form" id="formLogin">
                    @csrf
                    <!-- Ô EMAIL / TÊN ĐĂNG NHẬP: GIỮ LẠI GIÁ TRỊ ĐÃ NHẬP KHI ĐĂNG NHẬP THẤT BẠI -->
                    <div class="form-group">
                        <label for="loginEmail">Email / Tên đăng nhập <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-envelope input-icon-left"></i>
                            <input type="text" id="loginEmail" name="email" required placeholder="Nhập địa chỉ email hoặc tên đăng nhập..." value="{{ old('email') }}" autocomplete="username" class="{{ $errors->has('email') ? 'input-error' : '' }}">
                        </div>
                        @error('email')
                        <span class="field-error-text active" style="display: block; color: #ef4444; font-size: 0.82rem; margin-top: 4px;">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <!-- Ô MẬT KHẨU: XÓA TRẮNG Ô PASSWORD KHI ĐĂNG NHẬP THẤT BẠI -->
                    <div class="form-group">
                        <label for="loginPassword">Mật khẩu <span class="req">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" id="loginPassword" name="mat_khau" required placeholder="Nhập mật khẩu của bạn..." value="" autocomplete="current-password" class="{{ $errors->has('mat_khau') ? 'input-error' : '' }}">
                            <button type="button" class="btn-toggle-password" data-target="loginPassword" aria-label="Ẩn/Hiện mật khẩu">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        @error('mat_khau')
                        <span class="field-error-text active" style="display: block; color: #ef4444; font-size: 0.82rem; margin-top: 4px;">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <!-- TÙY CHỌN GHI NHỚ ĐĂNG NHẬP (REMEMBER ME) & QUÊN MẬT KHẨU -->
                    <div class="form-group remember-forgot-wrap" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
                        <label class="remember-label" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; user-select: none; color: var(--text-secondary); margin-bottom: 0;">
                            <input type="checkbox" name="ghi_nho" id="rememberMe" value="1" {{ old('ghi_nho') ? 'checked' : '' }} style="width: 17px; height: 17px; accent-color: var(--gold-500); cursor: pointer; border-radius: 4px;">
                            <span>Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="{{ url('/quen-mat-khau') }}" class="link-gold" style="font-size: 13.5px; font-weight: 600; text-decoration: none;">
                            Quên mật khẩu?
                        </a>
                    </div>

                    <!-- NÚT BẤM ĐĂNG NHẬP TIÊU CHUẨN KÍCH THƯỚC LỚN -->
                    <button type="submit" class="btn btn-gold btn-block btn-lg" id="btnSubmitLogin" style="font-size: 16px; font-weight: 700; padding: 13px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);">
                        <i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập
                    </button>
                </form>

                <div class="auth-card-footer text-center">
                    <p>Bạn chưa có tài khoản?</p>
                    <div class="register-choices">
                        <a href="{{ route('register.candidate') }}" class="link-gold">
                            <i class="fa-solid fa-user-plus"></i> Đăng ký Tài khoản Ứng Viên
                        </a>
                        <span>•</span>
                        <a href="{{ route('register.manager') }}" class="link-gold">
                            <i class="fa-solid fa-store"></i> Đăng ký Quản Lý Nhà Hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Ẩn/Hiện mật khẩu
        document.querySelectorAll('.btn-toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) return;
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
                }
            });
        });
    });
</script>
@endsection