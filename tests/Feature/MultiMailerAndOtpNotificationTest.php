<?php

namespace Tests\Feature;

use App\Mail\OtpChangePasswordMail;
use App\Mail\OtpRegisterMail;
use App\Mail\OtpResetPasswordMail;
use App\Mail\ThongBaoKichHoatQuanLyMail;
use App\Mail\ThongBaoUngVienMail;
use App\Models\HoSoUngVien;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use App\Models\ThongBao;
use App\Services\MultiMailerService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MultiMailerAndOtpNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('nguoi_dung')) {
            Schema::create('nguoi_dung', function (Blueprint $table) {
                $table->increments('ma_nguoi_dung');
                $table->string('ho_ten', 100);
                $table->string('email', 150)->unique();
                $table->string('so_dien_thoai', 20)->nullable();
                $table->string('mat_khau', 255)->default('');
                $table->string('vai_tro', 30)->default('ung_vien');
                $table->string('anh_dai_dien', 255)->nullable();
                $table->integer('ma_nha_hang_truc_thuoc')->nullable();
                $table->string('trang_thai', 30)->default('hoat_dong');
                $table->text('ly_do_tu_choi')->nullable();
                $table->dateTime('ngay_duyet')->nullable();
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        if (! Schema::hasTable('ho_so_ung_vien')) {
            Schema::create('ho_so_ung_vien', function (Blueprint $table) {
                $table->increments('ma_ho_so');
                $table->integer('ma_nguoi_dung');
                $table->date('ngay_sinh')->nullable();
                $table->string('so_cccd', 20)->nullable();
                $table->text('gioi_thieu_ban_than')->nullable();
                $table->string('trang_thai_ho_so', 50)->default('san_sang_nhan_viec');
                $table->decimal('diem_danh_gia', 3, 1)->default(0.0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('nha_hang')) {
            Schema::create('nha_hang', function (Blueprint $table) {
                $table->increments('ma_nha_hang');
                $table->integer('ma_thuong_hieu')->nullable();
                $table->string('ten_nha_hang', 150);
                $table->integer('ma_quan_ly')->nullable();
                $table->string('trang_thai_hoat_dong', 30)->default('dang_hoat_dong');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('thong_bao')) {
            Schema::create('thong_bao', function (Blueprint $table) {
                $table->increments('ma_thong_bao');
                $table->integer('ma_nguoi_dung');
                $table->string('tieu_de', 255);
                $table->text('noi_dung');
                $table->string('duong_dan_lien_ket', 255)->nullable();
                $table->tinyInteger('da_doc')->default(0);
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function test_multi_mailer_routing_logic(): void
    {
        // Manager with @goldenshift.vn must route to mailtrap
        $this->assertEquals('mailtrap', MultiMailerService::getMailerForEmail('manager.dongkhoi@goldenshift.vn'));
        $this->assertEquals('mailtrap', MultiMailerService::getMailerForEmail('admin@goldenshift.vn'));

        // Candidate with real gmail / other domains must route to gmail
        $this->assertEquals('gmail', MultiMailerService::getMailerForEmail('candidate@gmail.com'));
        $this->assertEquals('gmail', MultiMailerService::getMailerForEmail('nguyenvana@yahoo.com'));
    }

    public function test_candidate_registration_otp_flow(): void
    {
        Mail::fake();

        $email = 'otp.candidate.' . uniqid() . '@gmail.com';
        $validDate = now()->subYears(20)->format('Y-m-d');

        // 1. Submit registration form
        $response = $this->post('/dang-ky-ung-vien', [
            'ho_ten' => 'Lê Văn OTP',
            'email' => $email,
            'so_dien_thoai' => '09' . rand(10000000, 99999999),
            'ngay_sinh' => $validDate,
            'so_cccd' => '079' . rand(100000000, 999999999),
            'mat_khau' => 'GoldenShift@2026',
            'xac_nhan_mat_khau' => 'GoldenShift@2026',
        ]);

        $response->assertRedirect(route('register.candidate.verify_otp', ['email' => $email]));

        // OTP must be in Cache & Email sent
        $cached = Cache::get('otp_reg_' . $email);
        $this->assertNotNull($cached);
        $this->assertNotEmpty($cached['otp']);

        Mail::assertSent(OtpRegisterMail::class, function ($mail) use ($email, $cached) {
            return $mail->hasTo($email) && $mail->otp === $cached['otp'];
        });

        // 2. Submit wrong OTP -> fails
        $wrongResponse = $this->from('/dang-ky-ung-vien/xac-thuc-otp?email=' . urlencode($email))
            ->post('/dang-ky-ung-vien/xac-thuc-otp', [
                'email' => $email,
                'otp' => '000000',
            ]);
        $wrongResponse->assertRedirect('/dang-ky-ung-vien/xac-thuc-otp?email=' . urlencode($email));
        $wrongResponse->assertSessionHas('error');

        // 3. Submit correct OTP -> succeeds and user created in DB
        $correctResponse = $this->post('/dang-ky-ung-vien/xac-thuc-otp', [
            'email' => $email,
            'otp' => $cached['otp'],
        ]);

        $correctResponse->assertRedirect(route('login'));
        $correctResponse->assertSessionHas('success');

        $this->assertDatabaseHas('nguoi_dung', [
            'email' => $email,
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);
    }

    public function test_forgot_password_and_reset_flow(): void
    {
        Mail::fake();

        $email = 'forgot.' . uniqid() . '@gmail.com';
        $user = NguoiDung::create([
            'ho_ten' => 'Người Dùng Quên Mật Khẩu',
            'email' => $email,
            'so_dien_thoai' => '09' . rand(10000000, 99999999),
            'mat_khau' => Hash::make('OldPassword@123'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        // 1. Request OTP for forgot password
        $response = $this->post('/quen-mat-khau/gui-otp', [
            'email' => $email,
        ]);

        $response->assertRedirect(route('password.verify_otp', ['email' => $email]));

        $cachedOtp = Cache::get('otp_reset_' . $email);
        $this->assertNotNull($cachedOtp);

        Mail::assertSent(OtpResetPasswordMail::class, function ($mail) use ($email, $cachedOtp) {
            return $mail->hasTo($email) && $mail->otp === $cachedOtp;
        });

        // 2. Verify OTP
        $verifyResponse = $this->post('/quen-mat-khau/xac-nhan-otp', [
            'email' => $email,
            'otp' => $cachedOtp,
        ]);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertViewIs('auth.reset-password');

        $token = Cache::get('reset_token_' . $email);
        $this->assertNotNull($token);

        // 3. Reset password
        $resetResponse = $this->post('/quen-mat-khau/dat-lai-mat-khau', [
            'email' => $email,
            'token' => $token,
            'mat_khau' => 'NewStrongPass@2026',
            'xac_nhan_mat_khau' => 'NewStrongPass@2026',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $resetResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('NewStrongPass@2026', $user->mat_khau));
    }

    public function test_profile_change_password_requires_otp(): void
    {
        Mail::fake();

        $email = 'profile.pass.' . uniqid() . '@gmail.com';
        $user = NguoiDung::create([
            'ho_ten' => 'Trần Thị Profile',
            'email' => $email,
            'so_dien_thoai' => '09' . rand(10000000, 99999999),
            'mat_khau' => Hash::make('CurrentPass@123'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        // 1. Submit password change without OTP -> triggers OTP generation and email
        $response = $this->actingAs($user)->post('/ho-so-ca-nhan/doi-mat-khau', [
            'mat_khau_hien_tai' => 'CurrentPass@123',
            'mat_khau_moi' => 'NewPassword@2026',
            'xac_nhan_mat_khau' => 'NewPassword@2026',
        ]);

        $response->assertSessionHas('otp_change_pwd_sent', true);

        $cached = Cache::get('otp_change_pwd_' . $email);
        $this->assertNotNull($cached);
        $this->assertNotEmpty($cached['otp']);

        Mail::assertSent(OtpChangePasswordMail::class, function ($mail) use ($email, $cached) {
            return $mail->hasTo($email) && $mail->otp === $cached['otp'];
        });

        // 2. Submit with valid OTP -> updates password
        $confirmResponse = $this->actingAs($user)->post('/ho-so-ca-nhan/doi-mat-khau', [
            'mat_khau_hien_tai' => 'CurrentPass@123',
            'mat_khau_moi' => 'NewPassword@2026',
            'xac_nhan_mat_khau' => 'NewPassword@2026',
            'otp' => $cached['otp'],
        ]);

        $confirmResponse->assertRedirect(route('profile.index'));
        $confirmResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword@2026', $user->mat_khau));
    }

    public function test_in_app_notification_automatically_sends_email_to_candidate(): void
    {
        Mail::fake();

        $candidate = NguoiDung::create([
            'ho_ten' => 'Đặng Văn Thông Báo',
            'email' => 'candidate.notif.' . uniqid() . '@gmail.com',
            'so_dien_thoai' => '09' . rand(10000000, 99999999),
            'mat_khau' => Hash::make('Pass@1234'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        ThongBao::taoBaoTri(
            $candidate->ma_nguoi_dung,
            'Đơn ứng tuyển ca làm đã được duyệt',
            'Bạn đã được Quản lý duyệt vào ca làm việc Phục vụ lúc 08:00.',
            '/ung-tuyen/da-nop'
        );

        Mail::assertSent(ThongBaoUngVienMail::class, function ($mail) use ($candidate) {
            return $mail->hasTo($candidate->email) && str_contains($mail->tieuDe, 'được duyệt');
        });
    }

    public function test_admin_approval_sends_activation_email_to_manager(): void
    {
        Mail::fake();

        $admin = NguoiDung::create([
            'ho_ten' => 'Admin Hệ Thống',
            'email' => 'admin.test.' . uniqid() . '@goldenshift.vn',
            'so_dien_thoai' => '09' . rand(10000000, 99999999),
            'mat_khau' => Hash::make('AdminPass@123'),
            'vai_tro' => 'quan_tri_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $manager = NguoiDung::create([
            'ho_ten' => 'Nguyễn Quản Lý Mới',
            'email' => 'manager.approval.' . uniqid() . '@goldenshift.vn',
            'so_dien_thoai' => '09' . rand(10000000, 99999999),
            'mat_khau' => Hash::make('ManagerPass@123'),
            'vai_tro' => 'quan_ly_nha_hang',
            'trang_thai' => 'cho_duyet',
        ]);

        $response = $this->actingAs($admin)
            ->withSession([
                'user' => [
                    'ma_nguoi_dung' => $admin->ma_nguoi_dung,
                    'vai_tro' => 'quan_tri_vien',
                ],
            ])
            ->post("/admin/tai-khoan/{$manager->ma_nguoi_dung}/duyet");

        $response->assertRedirect();

        Mail::assertSent(ThongBaoKichHoatQuanLyMail::class, function ($mail) use ($manager) {
            return $mail->hasTo($manager->email);
        });
    }

    public function test_all_mailables_are_synchronous_not_queued(): void
    {
        $mailables = [
            \App\Mail\SendOtpMail::class,
            \App\Mail\OtpRegisterMail::class,
            \App\Mail\OtpResetPasswordMail::class,
            \App\Mail\OtpChangePasswordMail::class,
            \App\Mail\ThongBaoUngVienMail::class,
            \App\Mail\ThongBaoKichHoatQuanLyMail::class,
        ];

        foreach ($mailables as $class) {
            $interfaces = class_implements($class);
            $this->assertArrayNotHasKey(
                \Illuminate\Contracts\Queue\ShouldQueue::class,
                $interfaces,
                "Lỗi: {$class} không được implement ShouldQueue để đảm bảo gửi mail đồng bộ tức thì!"
            );
        }
    }

    public function test_multi_mailer_service_captures_error_on_invalid_data(): void
    {
        $error = null;
        $result = MultiMailerService::send('', new \App\Mail\SendOtpMail('123456'), $error);

        $this->assertFalse($result);
        $this->assertNotNull($error);
        $this->assertEquals($error, MultiMailerService::getLastError());
    }

    public function test_test_mail_route_executes_and_displays_status(): void
    {
        Mail::fake();

        $response = $this->get('/test-mail');

        $response->assertStatus(200);
        $response->assertSee('Kiểm Tra Kết Nối Mailer');
        $response->assertSee('Gmail Mailer (Ứng viên)');
        $response->assertSee('Mailtrap Mailer (Quản lý)');
    }
}
