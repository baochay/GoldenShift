@extends('layouts.app')

@section('title', 'Đăng Ký Quản Lý Nhà Hàng - Golden Shift')

@section('content')
<section class="auth-section">
    <div class="gs-container">
        <div class="auth-card-wrap wide">
            <div class="auth-card glass-panel">
                <div class="auth-card-header text-center">
                    <span class="sub-badge"><i class="fa-solid fa-crown text-gold"></i> ĐĂNG KÝ THÀNH VIÊN</span>
                    <h1 class="auth-title">Đăng Ký Quản Lý Nhà Hàng</h1>
                    <p class="auth-subtitle">Dành cho cấp Quản lý chi nhánh trực thuộc chuỗi Golden Shift</p>

                
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

                <form action="{{ route('register.manager') }}" method="POST" class="auth-form" id="formRegisterManager" novalidate>
                    @csrf
                    <div class="form-grid-2">
                        <!-- HỌ VÀ TÊN -->
                        <div class="form-group">
                            <label for="regHoTen">Họ và tên Quản lý <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-user input-icon-left"></i>
                                <input type="text" id="regHoTen" name="ho_ten" required placeholder="Ví dụ: Hoàng Kim Yến" autocomplete="name" value="{{ old('ho_ten') }}" class="{{ $errors->has('ho_ten') ? 'input-error' : '' }}">
                            </div>
                            <small style="color: #64748b; font-size: 0.8rem; margin-top: 3px; display: block;">Họ tên chỉ bao gồm chữ cái và khoảng trắng, không chứa số.</small>
                            <span class="field-error-text {{ $errors->has('ho_ten') ? 'active' : '' }}" id="errHoTenMgr">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>{{ $errors->first('ho_ten') ?: 'Vui lòng nhập họ và tên hợp lệ (tối thiểu 2 ký tự).' }}</span>
                            </span>
                        </div>

                        <!-- EMAIL CỐ ĐỊNH ĐUÔI @goldenshift.vn -->
                        <div class="form-group">
                            <label for="regEmail">Email đăng nhập công việc <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-envelope input-icon-left"></i>
                                <input type="email" id="regEmail" name="email" required placeholder="ten.quanly@goldenshift.vn" autocomplete="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'input-error' : '' }}">
                            </div>
                            <small style="color: #d97706; font-size: 0.8rem; margin-top: 3px; display: block; font-weight: 500;">
                                <i class="fa-solid fa-shield text-gold"></i> Bắt buộc dùng email nội bộ: <code>@goldenshift.vn</code>
                            </small>
                            <span class="field-error-text {{ $errors->has('email') ? 'active' : '' }}" id="errEmailMgr">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>{{ $errors->first('email') ?: 'Email Quản lý nhà hàng bắt buộc phải có đuôi @goldenshift.vn.' }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <!-- SỐ ĐIỆN THOẠI -->
                        <div class="form-group">
                            <label for="regSoDienThoai">Số điện thoại liên hệ / Zalo <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-phone input-icon-left"></i>
                                <input type="tel" id="regSoDienThoai" name="so_dien_thoai" required placeholder="09xxxxxxxx" maxlength="10" autocomplete="tel" value="{{ old('so_dien_thoai') }}" class="{{ $errors->has('so_dien_thoai') ? 'input-error' : '' }}">
                            </div>
                            <small style="color: #64748b; font-size: 0.8rem; margin-top: 3px; display: block;">Bắt buộc đúng 10 chữ số và bắt đầu bằng số 0 (Ví dụ: 0912345678).</small>
                            <span class="field-error-text {{ $errors->has('so_dien_thoai') ? 'active' : '' }}" id="errSdtMgr">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>{{ $errors->first('so_dien_thoai') ?: 'Số điện thoại phải đúng 10 chữ số và bắt đầu bằng số 0.' }}</span>
                            </span>
                        </div>

                        <!-- NHÀ HÀNG TRỰC THUỘC -->
                        <div class="form-group">
                            <label for="regNhaHang">Nhà hàng trực thuộc quản lý <span class="req">*</span></label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-store input-icon-left"></i>
                                <select id="regNhaHang" name="ma_nha_hang" required class="gs-select {{ $errors->has('ma_nha_hang') ? 'input-error' : '' }}" style="width: 100%; padding: 0.65rem 0.85rem 0.65rem 2.5rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                                    <option value="" disabled {{ old('ma_nha_hang') ? '' : 'selected' }}>-- Chọn Nhà hàng/Chi nhánh --</option>
                                    @foreach ($nhaHangList as $nh)
                                        @php
                                            $nhId = is_array($nh) ? $nh['ma_nha_hang'] : $nh->ma_nha_hang;
                                            $nhTen = is_array($nh) ? $nh['ten_nha_hang'] : $nh->ten_nha_hang;
                                            $nhHuyen = is_array($nh) ? ($nh['quan_huyen'] ?? 'TP.HCM') : ($nh->quan_huyen ?? 'TP.HCM');
                                        @endphp
                                        <option value="{{ $nhId }}" data-name="{{ $nhTen }}" {{ (old('ma_nha_hang') == $nhId) ? 'selected' : '' }}>
                                            {{ $nhTen }} ({{ $nhHuyen }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" id="tenNhaHangHidden" name="ten_nha_hang_hidden" value="{{ old('ten_nha_hang_hidden') }}">
                            <span class="field-error-text {{ $errors->has('ma_nha_hang') ? 'active' : '' }}" id="errNhaHangMgr">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>{{ $errors->first('ma_nha_hang') ?: 'Vui lòng chọn Nhà hàng/Chi nhánh trực thuộc!' }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <!-- MẬT KHẨU -->
                        <div class="form-group">
                            <label for="regMatKhau">Mật khẩu khởi tạo <span class="req">*</span></label>
                            <div class="input-password-wrap">
                                <input type="password" id="regMatKhau" name="mat_khau" required placeholder="Tạo mật khẩu an toàn" autocomplete="new-password" class="{{ $errors->has('mat_khau') ? 'input-error' : '' }}">
                                <button type="button" class="btn-toggle-password" data-target="regMatKhau" aria-label="Ẩn/Hiện mật khẩu">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <span class="field-error-text {{ $errors->has('mat_khau') ? 'active' : '' }}" id="errMatKhauMgr">
                                <i class="fa-solid fa-circle-exclamation"></i> 
                                <span id="errMatKhauMgrText">{{ $errors->first('mat_khau') ?: 'Mật khẩu không đạt yêu cầu.' }}</span>
                            </span>

                            <!-- Checklist tiêu chí mật khẩu -->
                            <div class="pwd-checklist" id="pwdChecklistMgr" style="{{ old('mat_khau') || $errors->has('mat_khau') ? 'display:block;' : 'display:none;' }}">
                                <div class="pwd-checklist-title"><i class="fa-solid fa-shield-halved text-gold"></i> Yêu cầu mật khẩu:</div>
                                <ul class="pwd-criteria">
                                    <li class="criteria-item" id="mgr-len"><i class="fa-regular fa-circle"></i> Tối thiểu 8 ký tự</li>
                                    <li class="criteria-item" id="mgr-upper"><i class="fa-regular fa-circle"></i> Có chữ hoa (A-Z)</li>
                                    <li class="criteria-item" id="mgr-lower"><i class="fa-regular fa-circle"></i> Có chữ thường (a-z)</li>
                                    <li class="criteria-item" id="mgr-digit"><i class="fa-regular fa-circle"></i> Có chữ số (0-9)</li>
                                    <li class="criteria-item" id="mgr-special"><i class="fa-regular fa-circle"></i> Có ký tự đặc biệt (!@#$...)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- XÁC NHẬN MẬT KHẨU -->
                        <div class="form-group">
                            <label for="regXacNhanMatKhau">Xác nhận mật khẩu <span class="req">*</span></label>
                            <div class="input-password-wrap">
                                <input type="password" id="regXacNhanMatKhau" name="xac_nhan_mat_khau" required placeholder="Nhập lại mật khẩu" autocomplete="new-password" class="{{ $errors->has('xac_nhan_mat_khau') ? 'input-error' : '' }}">
                                <button type="button" class="btn-toggle-password" data-target="regXacNhanMatKhau" aria-label="Ẩn/Hiện xác nhận mật khẩu">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <span class="field-error-text {{ $errors->has('xac_nhan_mat_khau') ? 'active' : '' }}" id="errConfirmMgr">
                                <i class="fa-solid fa-circle-exclamation"></i> 
                                <span id="errConfirmMgrText">{{ $errors->first('xac_nhan_mat_khau') ?: 'Xác nhận mật khẩu không trùng khớp.' }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="form-note-box amber" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-shield-halved text-gold" style="font-size: 1.25rem;"></i>
                        <span>Tài khoản Quản lý sau khi đăng ký sẽ ở trạng thái <strong>CHỜ DUYỆT</strong>. Quản Trị Viên sẽ tiến hành thẩm định và kích hoạt tài khoản trong vòng 24 giờ.</span>
                    </div>

                    <button type="submit" class="btn btn-gold btn-block btn-lg" id="btnSubmitRegManager">
                        <i class="fa-solid fa-id-card-clip"></i> Đăng Ký Tài Khoản Quản Lý
                    </button>
                </form>

                <div class="auth-card-footer text-center">
                    <p>Đã có tài khoản Quản lý? <a href="{{ route('login') }}" class="link-gold font-bold">Đăng nhập tại đây</a></p>
                    <p style="margin-top:6px;">Bạn là Ứng viên tìm việc? <a href="{{ route('register.candidate') }}" class="link-gold">Đăng ký tài khoản Ứng viên</a></p>
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

    // ====== CẬP NHẬT TÊN NHÀ HÀNG ẨN KHI CHỌN ======
    const selectNh = document.getElementById('regNhaHang');
    const inputHidden = document.getElementById('tenNhaHangHidden');
    if (selectNh && inputHidden) {
        selectNh.addEventListener('change', () => {
            const opt = selectNh.options[selectNh.selectedIndex];
            inputHidden.value = opt ? (opt.getAttribute('data-name') || '') : '';
        });
    }

    // ====== KIỂM TRA TIÊU CHÍ MẬT KHẨU (REALTIME) ======
    const pwdInput = document.getElementById('regMatKhau');
    const pwdChecklist = document.getElementById('pwdChecklistMgr');

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
            const el = document.getElementById('mgr-' + key);
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
            const errEl = document.getElementById('errMatKhauMgr');
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
    const form = document.getElementById('formRegisterManager');
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
        const hoTen = document.getElementById('regHoTen');
        const hoTenOk = hoTen && hoTen.value.trim().length >= 2;
        showErr('errHoTenMgr', !hoTenOk);
        markInput(hoTen, !hoTenOk);
        if (!hoTenOk) valid = false;

        // Email: cố định đuôi @goldenshift.vn
        const email = document.getElementById('regEmail');
        const emailOk = email && /^[a-zA-Z0-9._%+-]+@goldenshift\.vn$/i.test(email.value.trim());
        showErr('errEmailMgr', !emailOk);
        markInput(email, !emailOk);
        if (!emailOk) valid = false;

        // SĐT: đúng 10 số bắt đầu bằng 0
        const sdt = document.getElementById('regSoDienThoai');
        const sdtOk = sdt && /^0[0-9]{9}$/.test(sdt.value.trim());
        showErr('errSdtMgr', !sdtOk);
        markInput(sdt, !sdtOk);
        if (!sdtOk) valid = false;

        // Nhà hàng
        const nh = document.getElementById('regNhaHang');
        const nhOk = nh && nh.value !== '';
        showErr('errNhaHangMgr', !nhOk);
        markInput(nh, !nhOk);
        if (!nhOk) valid = false;

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
            showErr('errMatKhauMgr', true, pwdErrMsg);
            markInput(pwdInput, true);
            if (pwdChecklist) pwdChecklist.style.display = 'block';
            valid = false;
        } else {
            showErr('errMatKhauMgr', false);
            markInput(pwdInput, false);
        }

        // Xác nhận mật khẩu
        const confirmPwd = document.getElementById('regXacNhanMatKhau');
        const confirmOk = confirmPwd && pwdVal !== '' && confirmPwd.value === pwdVal;
        showErr('errConfirmMgr', !confirmOk, confirmPwd && confirmPwd.value === '' ? 'Vui lòng xác nhận lại mật khẩu.' : 'Xác nhận mật khẩu không trùng khớp.');
        markInput(confirmPwd, !confirmOk);
        if (!confirmOk) valid = false;

        if (!valid) {
            e.preventDefault();
            const firstErr = form.querySelector('.input-error');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Xóa lỗi ngay khi người dùng bắt đầu nhập lại hoặc chọn lại
    const clearErrMap = {
        regHoTen: 'errHoTenMgr',
        regEmail: 'errEmailMgr',
        regSoDienThoai: 'errSdtMgr',
        regNhaHang: 'errNhaHangMgr',
        regMatKhau: 'errMatKhauMgr',
        regXacNhanMatKhau: 'errConfirmMgr'
    };
    Object.keys(clearErrMap).forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            const clearFn = () => {
                el.classList.remove('input-error');
                showErr(clearErrMap[id], false);
            };
            el.addEventListener('input', clearFn);
            el.addEventListener('change', clearFn);
        }
    });
});
</script>
@endsection
