@extends('layouts.app')

@section('title', 'Đăng Ký Tài Khoản Ứng Viên - Golden Shift')

@section('content')
<section class="auth-section">
    <div class="gs-container">
        <div class="auth-card-wrap">
            <div class="auth-card glass-panel">
                <div class="auth-card-header text-center">
                    <h1 class="auth-title text-gold">Đăng Ký Tài Khoản Ứng Viên</h1>
                    <p class="auth-subtitle">Gia nhập đội ngũ nhân sự F&amp;B của Golden Shift</p>

                </div>

                @if (session('error'))
                    <div class="alert-box error" style="margin-bottom: 1.25rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert-box error" style="margin-bottom: 1.25rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> Vui lòng kiểm tra lại các thông tin bên dưới:
                        <ul style="margin: 6px 0 0 20px; padding: 0;">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.candidate') }}" method="POST" class="auth-form" id="formRegisterCandidate" novalidate>
                    @csrf
                    <!-- HỌ VÀ TÊN -->
                    <div class="form-group">
                        <label for="candHoTen">Họ và tên của bạn <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-user input-icon-left"></i>
                            <input type="text" id="candHoTen" name="ho_ten" required placeholder="Ví dụ: Đỗ Mỹ Linh" autocomplete="name" value="{{ old('ho_ten') }}" class="{{ $errors->has('ho_ten') ? 'input-error' : '' }}">
                        </div>
                        <small style="color: #64748b; font-size: 0.8rem; margin-top: 3px; display: block;">Họ tên chỉ bao gồm chữ cái và khoảng trắng, không chứa số.</small>
                        @error('ho_ten')
                            <span class="field-error-text active" style="display: block; color: #ef4444; font-size: 0.82rem; margin-top: 4px;">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- EMAIL -->
                    <div class="form-group">
                        <label for="candEmail">Email nhận việc <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-envelope input-icon-left"></i>
                            <input type="email" id="candEmail" name="email" required placeholder="name@domain.vn" autocomplete="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'input-error' : '' }}">
                        </div>
                        @error('email')
                            <span class="field-error-text active" style="display: block; color: #ef4444; font-size: 0.82rem; margin-top: 4px;">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- SỐ ĐIỆN THOẠI -->
                    <div class="form-group">
                        <label for="candSoDienThoai">Số điện thoại / Zalo <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-phone input-icon-left"></i>
                            <input type="tel" id="candSoDienThoai" name="so_dien_thoai" required placeholder="09xxxxxxxx" maxlength="10" autocomplete="tel" value="{{ old('so_dien_thoai') }}" class="{{ $errors->has('so_dien_thoai') ? 'input-error' : '' }}">
                        </div>
                        <small style="color: #64748b; font-size: 0.8rem; margin-top: 3px; display: block;">Bắt buộc đúng 10 chữ số và bắt đầu bằng số 0 (Ví dụ: 0912345678).</small>
                        @error('so_dien_thoai')
                            <span class="field-error-text active" style="display: block; color: #ef4444; font-size: 0.82rem; margin-top: 4px;">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- NGÀY THÁNG NĂM SINH -->
                    <div class="form-group">
                        <label for="candNgaySinh">Ngày tháng năm sinh <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-cake-candles input-icon-left"></i>
                            <input type="date" id="candNgaySinh" name="ngay_sinh" required 
                                   max="{{ now()->subYears(16)->format('Y-m-d') }}"
                                   value="{{ old('ngay_sinh') }}" 
                                   class="{{ $errors->has('ngay_sinh') ? 'input-error' : '' }}">
                        </div>
                        <small style="color: #64748b; font-size: 0.8rem; margin-top: 3px; display: block;">Ứng viên phải từ 16 tuổi trở lên mới đủ điều kiện đăng ký làm việc.</small>
                        @error('ngay_sinh')
                            <span class="field-error-text active" style="display: block; color: #ef4444; font-size: 0.82rem; margin-top: 4px;">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </span>
                        @enderror
                        <span class="field-error-text" id="errCandNgaySinh" style="color: #ef4444; font-size: 0.82rem; margin-top: 4px;">
                            <i class="fa-solid fa-circle-exclamation"></i> <span></span>
                        </span>
                    </div>

                    <!-- SỐ CĂN CƯỚC CÔNG DÂN (CCCD) -->
                    <div class="form-group">
                        <label for="candSoCccd">Số Căn cước công dân (CCCD) <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-id-card input-icon-left"></i>
                            <input type="text" id="candSoCccd" name="so_cccd" required 
                                   placeholder="079xxxxxxxx (12 chữ số)" 
                                   maxlength="12" pattern="^0[0-9]{11}$"
                                   value="{{ old('so_cccd') }}" 
                                   class="{{ $errors->has('so_cccd') ? 'input-error' : '' }}">
                        </div>
                        <small style="color: #64748b; font-size: 0.8rem; margin-top: 3px; display: block;">Số CCCD phải bao gồm 12 chữ số và bắt đầu bằng số 0 (Ví dụ: 079123456789).</small>
                        @error('so_cccd')
                            <span class="field-error-text active" style="display: block; color: #ef4444; font-size: 0.82rem; margin-top: 4px;">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </span>
                        @enderror
                        <span class="field-error-text" id="errCandCccd" style="color: #ef4444; font-size: 0.82rem; margin-top: 4px;">
                            <i class="fa-solid fa-circle-exclamation"></i> <span></span>
                        </span>
                    </div>

                    <!-- MẬT KHẨU -->
                    <div class="form-group">
                        <label for="candMatKhau">Mật khẩu tài khoản <span class="req">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" id="candMatKhau" name="mat_khau" required placeholder="Tạo mật khẩu an toàn" autocomplete="new-password" class="{{ $errors->has('mat_khau') ? 'input-error' : '' }}">
                            <button type="button" class="btn-toggle-password" data-target="candMatKhau" aria-label="Ẩn/Hiện mật khẩu">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <span class="field-error-text {{ $errors->has('mat_khau') ? 'active' : '' }}" id="errCandMatKhau">
                            <i class="fa-solid fa-circle-exclamation"></i> 
                            <span id="errCandMatKhauText">{{ $errors->first('mat_khau') ?: 'Mật khẩu không đạt yêu cầu.' }}</span>
                        </span>

                        <!-- Checklist tiêu chí mật khẩu -->
                        <div class="pwd-checklist" id="pwdChecklistCand" style="{{ old('mat_khau') || $errors->has('mat_khau') ? 'display:block;' : 'display:none;' }}">
                            <div class="pwd-checklist-title"><i class="fa-solid fa-shield-halved text-gold"></i> Yêu cầu mật khẩu:</div>
                            <ul class="pwd-criteria">
                                <li class="criteria-item" id="cand-len"><i class="fa-regular fa-circle"></i> Tối thiểu 8 ký tự</li>
                                <li class="criteria-item" id="cand-upper"><i class="fa-regular fa-circle"></i> Có chữ hoa (A-Z)</li>
                                <li class="criteria-item" id="cand-lower"><i class="fa-regular fa-circle"></i> Có chữ thường (a-z)</li>
                                <li class="criteria-item" id="cand-digit"><i class="fa-regular fa-circle"></i> Có chữ số (0-9)</li>
                                <li class="criteria-item" id="cand-special"><i class="fa-regular fa-circle"></i> Có ký tự đặc biệt (!@#$...)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- XÁC NHẬN MẬT KHẨU -->
                    <div class="form-group">
                        <label for="candXacNhanMatKhau">Xác nhận mật khẩu <span class="req">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" id="candXacNhanMatKhau" name="xac_nhan_mat_khau" required placeholder="Nhập lại mật khẩu" autocomplete="new-password" class="{{ $errors->has('xac_nhan_mat_khau') ? 'input-error' : '' }}">
                            <button type="button" class="btn-toggle-password" data-target="candXacNhanMatKhau" aria-label="Ẩn/Hiện xác nhận mật khẩu">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <span class="field-error-text {{ $errors->has('xac_nhan_mat_khau') ? 'active' : '' }}" id="errCandConfirm">
                            <i class="fa-solid fa-circle-exclamation"></i> 
                            <span id="errCandConfirmText">{{ $errors->first('xac_nhan_mat_khau') ?: 'Xác nhận mật khẩu không trùng khớp.' }}</span>
                        </span>
                    </div>

                    <!-- KINH NGHIỆM / GIỚI THIỆU -->
                    <div class="form-group">
                        <label for="candKinhNghiem">Kinh nghiệm hoặc vị trí mong muốn</label>
                        <textarea id="candKinhNghiem" name="kinh_nghiem" rows="2" placeholder="Ví dụ: Phục vụ bàn ca tối, phụ bếp sơ chế thực phẩm, thu ngân...">{{ old('kinh_nghiem') }}</textarea>
                    </div>

                    

                    <button type="submit" class="btn btn-gold btn-block btn-lg" id="btnSubmitRegCandidate">
                        <i class="fa-solid fa-user-check"></i> Hoàn Tất Đăng Ký Ứng Viên
                    </button>
                </form>

                <div class="auth-card-footer text-center">
                    <p>Đã có tài khoản? <a href="{{ route('login') }}" class="link-gold font-bold">Đăng nhập ngay</a></p>
                    <p style="margin-top:6px;">Đăng ký Quản lý nhà hàng? <a href="{{ route('register.manager') }}" class="link-gold">Đăng ký tại đây</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ====== NÚT ẨN/HIỆN MẬT KHẨU (CON MẮT) ======
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

    // ====== KIỂM TRA TIÊU CHÍ MẬT KHẨU (REALTIME) ======
    const pwdInput = document.getElementById('candMatKhau');
    const pwdChecklist = document.getElementById('pwdChecklistCand');

    function checkPwdCriteria(val) {
        return {
            len:     val.length >= 8,
            upper:   /[A-Z]/.test(val),
            lower:   /[a-z]/.test(val),
            digit:   /[0-9]/.test(val),
            special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(val)
        };
    }

    function updateCriteriaUI(criteria) {
        Object.keys(criteria).forEach(key => {
            const el = document.getElementById('cand-' + key);
            if (!el) return;
            const icon = el.querySelector('i');
            if (criteria[key]) {
                el.classList.add('valid');
                if (icon) icon.className = 'fa-solid fa-circle-check';
            } else {
                el.classList.remove('valid');
                if (icon) icon.className = 'fa-regular fa-circle';
            }
        });
    }

    if (pwdInput) {
        if (pwdInput.value.length > 0) {
            if (pwdChecklist) pwdChecklist.style.display = 'block';
            updateCriteriaUI(checkPwdCriteria(pwdInput.value));
        }

        pwdInput.addEventListener('focus', () => {
            if (pwdChecklist) pwdChecklist.style.display = 'block';
        });
        pwdInput.addEventListener('input', () => {
            const c = checkPwdCriteria(pwdInput.value);
            updateCriteriaUI(c);
            const errEl = document.getElementById('errCandMatKhau');
            if (errEl && c.len && c.upper && c.lower && c.digit && c.special) {
                errEl.classList.remove('active');
                pwdInput.classList.remove('input-error');
            }
        });
        pwdInput.addEventListener('blur', () => {
            if (pwdChecklist && pwdInput.value.length === 0) {
                pwdChecklist.style.display = 'none';
            }
        });
    }

    // ====== VALIDATE FORM KHI SUBMIT ======
    const form = document.getElementById('formRegisterCandidate');
    if (!form) return;

    function showErr(id, show, msg = '') {
        const el = document.getElementById(id);
        if (el) {
            el.classList.toggle('active', show);
            if (msg) {
                const textSpan = el.querySelector('span') || el;
                textSpan.textContent = msg;
            }
        }
    }

    function markInput(inputEl, isError) {
        if (!inputEl) return;
        inputEl.classList.toggle('input-error', isError);
    }

    form.addEventListener('submit', (e) => {
        let valid = true;

        // Họ tên
        const hoTen = document.getElementById('candHoTen');
        const hoTenOk = hoTen && hoTen.value.trim().length >= 2;
        showErr('errCandHoTen', !hoTenOk, 'Vui lòng nhập họ và tên của bạn.');
        markInput(hoTen, !hoTenOk);
        if (!hoTenOk) valid = false;

        // Email
        const email = document.getElementById('candEmail');
        const emailOk = email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());
        showErr('errCandEmail', !emailOk, 'Địa chỉ email không đúng định dạng.');
        markInput(email, !emailOk);
        if (!emailOk) valid = false;

        // Số điện thoại: đúng 10 chữ số bắt đầu bằng 0
        const sdt = document.getElementById('candSoDienThoai');
        const sdtOk = sdt && /^0[0-9]{9}$/.test(sdt.value.trim());
        showErr('errCandSdt', !sdtOk, 'Số điện thoại phải đúng 10 chữ số và bắt đầu bằng số 0.');
        markInput(sdt, !sdtOk);
        if (!sdtOk) valid = false;

        // Ngày sinh (>= 16 tuổi)
        const ngaySinh = document.getElementById('candNgaySinh');
        let ngaySinhOk = false;
        if (ngaySinh && ngaySinh.value) {
            const birth = new Date(ngaySinh.value);
            const today = new Date();
            const minAgeDate = new Date(today.getFullYear() - 16, today.getMonth(), today.getDate());
            ngaySinhOk = !isNaN(birth.getTime()) && birth <= minAgeDate;
        }
        showErr('errCandNgaySinh', !ngaySinhOk, ngaySinh && !ngaySinh.value ? 'Vui lòng chọn ngày tháng năm sinh.' : 'Ứng viên phải từ 16 tuổi trở lên mới đủ điều kiện đăng ký làm việc!');
        markInput(ngaySinh, !ngaySinhOk);
        if (!ngaySinhOk) valid = false;

        // Số CCCD (12 chữ số bắt đầu bằng 0)
        const cccd = document.getElementById('candSoCccd');
        const cccdOk = cccd && /^0[0-9]{11}$/.test(cccd.value.trim());
        showErr('errCandCccd', !cccdOk, 'Số CCCD phải bao gồm 12 chữ số và bắt đầu bằng số 0!');
        markInput(cccd, !cccdOk);
        if (!cccdOk) valid = false;

        // Mật khẩu: đủ 5 tiêu chí (>= 8 ký tự, chữ hoa, chữ thường, số, ký tự đặc biệt)
        const pwdVal = pwdInput ? pwdInput.value : '';
        const c = checkPwdCriteria(pwdVal);
        const pwdOk = c.len && c.upper && c.lower && c.digit && c.special;

        let pwdErrMsg = 'Mật khẩu không đạt yêu cầu an toàn.';
        if (!c.len) pwdErrMsg = 'Mật khẩu phải có tối thiểu 8 ký tự.';
        else if (!c.upper) pwdErrMsg = 'Mật khẩu phải chứa ít nhất 1 chữ hoa (A-Z).';
        else if (!c.lower) pwdErrMsg = 'Mật khẩu phải chứa ít nhất 1 chữ thường (a-z).';
        else if (!c.digit) pwdErrMsg = 'Mật khẩu phải chứa ít nhất 1 chữ số (0-9).';
        else if (!c.special) pwdErrMsg = 'Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt (!@#$...).';

        if (!pwdOk) {
            showErr('errCandMatKhau', true, pwdErrMsg);
            markInput(pwdInput, true);
            if (pwdChecklist) pwdChecklist.style.display = 'block';
            valid = false;
        } else {
            showErr('errCandMatKhau', false);
            markInput(pwdInput, false);
        }

        // Xác nhận mật khẩu
        const confirmPwd = document.getElementById('candXacNhanMatKhau');
        const confirmOk = confirmPwd && pwdVal !== '' && confirmPwd.value === pwdVal;
        showErr('errCandConfirm', !confirmOk, confirmPwd && confirmPwd.value === '' ? 'Vui lòng xác nhận lại mật khẩu.' : 'Xác nhận mật khẩu không trùng khớp.');
        markInput(confirmPwd, !confirmOk);
        if (!confirmOk) valid = false;

        if (!valid) {
            e.preventDefault();
            const firstErr = form.querySelector('.input-error');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Xóa lỗi ngay khi người dùng bắt đầu nhập lại
    ['candHoTen', 'candEmail', 'candSoDienThoai', 'candNgaySinh', 'candSoCccd', 'candMatKhau', 'candXacNhanMatKhau'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => el.classList.remove('input-error'));
        }
    });
});
</script>
@endsection
