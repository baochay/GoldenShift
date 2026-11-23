<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationAndProfileValidationTest extends TestCase
{
    /**
     * Test Candidate registration fails if candidate is under 16 years old.
     */
    public function test_candidate_registration_fails_if_under_16_years_old()
    {
        $under16Date = now()->subYears(15)->format('Y-m-d');

        $response = $this->from('/dang-ky-ung-vien')->post('/dang-ky-ung-vien', [
            'ho_ten' => 'Nguyễn Văn Test',
            'email' => 'test.under16@gmail.com',
            'so_dien_thoai' => '0912345678',
            'ngay_sinh' => $under16Date,
            'so_cccd' => '079123456789',
            'mat_khau' => 'Pass@1234',
            'xac_nhan_mat_khau' => 'Pass@1234',
        ]);

        $response->assertRedirect('/dang-ky-ung-vien');
        $response->assertSessionHasErrors('ngay_sinh');
    }

    /**
     * Test Candidate registration fails if CCCD is invalid (not 12 digits or doesn't start with 0).
     */
    public function test_candidate_registration_fails_if_cccd_invalid()
    {
        $validDate = now()->subYears(20)->format('Y-m-d');

        // CCCD does not start with 0
        $response = $this->from('/dang-ky-ung-vien')->post('/dang-ky-ung-vien', [
            'ho_ten' => 'Nguyễn Văn Test',
            'email' => 'test.cccd@gmail.com',
            'so_dien_thoai' => '0912345678',
            'ngay_sinh' => $validDate,
            'so_cccd' => '123456789012',
            'mat_khau' => 'Pass@1234',
            'xac_nhan_mat_khau' => 'Pass@1234',
        ]);

        $response->assertRedirect('/dang-ky-ung-vien');
        $response->assertSessionHasErrors('so_cccd');

        // CCCD less than 12 digits
        $response2 = $this->from('/dang-ky-ung-vien')->post('/dang-ky-ung-vien', [
            'ho_ten' => 'Nguyễn Văn Test',
            'email' => 'test.cccd2@gmail.com',
            'so_dien_thoai' => '0912345678',
            'ngay_sinh' => $validDate,
            'so_cccd' => '079123456',
            'mat_khau' => 'Pass@1234',
            'xac_nhan_mat_khau' => 'Pass@1234',
        ]);

        $response2->assertRedirect('/dang-ky-ung-vien');
        $response2->assertSessionHasErrors('so_cccd');
    }

    /**
     * Test Candidate registration succeeds with valid data.
     */
    public function test_candidate_registration_succeeds_with_valid_data()
    {
        $validDate = now()->subYears(20)->format('Y-m-d');
        $email = 'hople.' . uniqid() . '@gmail.com';

        $response = $this->post('/dang-ky-ung-vien', [
            'ho_ten' => 'Nguyễn Thị Hợp Lệ',
            'email' => $email,
            'so_dien_thoai' => '09' . rand(10000000, 99999999),
            'ngay_sinh' => $validDate,
            'so_cccd' => '079' . rand(100000000, 999999999),
            'mat_khau' => 'GoldenShift@2026',
            'xac_nhan_mat_khau' => 'GoldenShift@2026',
        ]);

        // Sau khi đăng ký hợp lệ, chuyển sang bước nhập OTP
        $response->assertRedirect(route('register.candidate.verify_otp', ['email' => $email]));
        $response->assertSessionHas('success');

        // Lấy OTP từ Cache và xác thực
        $cached = \Illuminate\Support\Facades\Cache::get('otp_reg_' . $email);
        $this->assertNotNull($cached);
        $this->assertNotEmpty($cached['otp']);

        $verifyResponse = $this->post('/dang-ky-ung-vien/xac-thuc-otp', [
            'email' => $email,
            'otp' => $cached['otp'],
        ]);

        $verifyResponse->assertRedirect(route('login'));
        $verifyResponse->assertSessionHas('success');
    }

    /**
     * Test Change Password validates strong password rules.
     */
    public function test_change_password_requires_strong_password()
    {
        $response = $this->withSession([
            'user' => [
                'ma_nguoi_dung' => 1,
                'ho_ten' => 'Quản Trị Viên',
                'email' => 'admin@goldenshift.vn',
                'vai_tro' => 'quan_tri_vien',
            ],
        ])->from('/ho-so-ca-nhan#password')->post('/ho-so-ca-nhan/doi-mat-khau', [
            'mat_khau_hien_tai' => '123456',
            'mat_khau_moi' => 'simple123', // Thiếu chữ hoa và ký tự đặc biệt
            'xac_nhan_mat_khau' => 'simple123',
        ]);

        $response->assertRedirect('/ho-so-ca-nhan#password');
        $response->assertSessionHasErrors('mat_khau_moi');
    }

    /**
     * Test Profile update for Candidate validates gender must be 'nam' or 'nu'.
     */
    public function test_candidate_profile_gender_must_be_nam_or_nu()
    {
        $validDate = now()->subYears(20)->format('Y-m-d');

        $response = $this->withSession([
            'user' => [
                'ma_nguoi_dung' => 6,
                'ho_ten' => 'Phạm Thu Hương',
                'email' => 'thuhuong.pham@gmail.com',
                'so_dien_thoai' => '0945678901',
                'vai_tro' => 'ung_vien',
            ],
        ])->from('/ho-so-ca-nhan')->post(route('profile.update'), [
            'ho_ten' => 'Phạm Thu Hương',
            'so_dien_thoai' => '0945678901',
            'email' => 'thuhuong.updated@gmail.com',
            'ngay_sinh' => $validDate,
            'so_cccd' => '079123456789',
            'gioi_tinh' => 'khac', // Không hợp lệ, chỉ chấp nhận nam hoặc nu
        ]);

        $response->assertRedirect('/ho-so-ca-nhan');
        $response->assertSessionHasErrors('gioi_tinh');
    }

    /**
     * Test Profile update for Candidate validates CCCD format.
     */
    public function test_candidate_profile_validates_cccd()
    {
        $validDate = now()->subYears(20)->format('Y-m-d');

        $response = $this->withSession([
            'user' => [
                'ma_nguoi_dung' => 6,
                'ho_ten' => 'Phạm Thu Hương',
                'email' => 'thuhuong.pham@gmail.com',
                'so_dien_thoai' => '0945678901',
                'vai_tro' => 'ung_vien',
            ],
        ])->from('/ho-so-ca-nhan')->post(route('profile.update'), [
            'ho_ten' => 'Phạm Thu Hương',
            'so_dien_thoai' => '0945678901',
            'email' => 'thuhuong.updated@gmail.com',
            'ngay_sinh' => $validDate,
            'so_cccd' => '123456789012', // Không bắt đầu bằng 0
            'gioi_tinh' => 'nu',
        ]);

        $response->assertRedirect('/ho-so-ca-nhan');
        $response->assertSessionHasErrors('so_cccd');
    }
}
