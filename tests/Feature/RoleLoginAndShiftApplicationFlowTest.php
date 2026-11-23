<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoleLoginAndShiftApplicationFlowTest extends TestCase
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
                $table->string('remember_token', 100)->nullable();
                $table->dateTime('ngay_duyet')->nullable();
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }
    }

    /**
     * 1. Test Admin login redirects to admin.users.index
     */
    public function test_admin_login_redirects_to_admin_users_index()
    {
        $response = $this->post('/dang-nhap', [
            'email' => 'admin@goldenshift.vn',
            'mat_khau' => '123456',
        ]);

        $response->assertRedirect(route('admin.users.index'));
    }

    /**
     * 2. Test Restaurant Manager login redirects to ca-lam-viec.index (/quan-ly/ca-lam-viec)
     */
    public function test_manager_login_redirects_to_ca_lam_viec_index()
    {
        $response = $this->post('/dang-nhap', [
            'email' => 'chau.ntb@goldenshift.vn',
            'mat_khau' => '123456',
        ]);

        $response->assertRedirect(route('ca-lam-viec.index'));
        $this->assertEquals(url('/quan-ly/ca-lam-viec'), route('ca-lam-viec.index'));
    }

    /**
     * 3. Test Candidate normal login redirects to home ('/')
     */
    public function test_candidate_normal_login_redirects_to_home()
    {
        $response = $this->post('/dang-nhap', [
            'email' => 'thuhuong.pham@gmail.com',
            'mat_khau' => '123456',
        ]);

        $response->assertRedirect('/');
    }

    /**
     * 4. Test Guest clicks "Ứng tuyển ngay" on emergency shift:
     * - Saves intended URL in session
     * - Redirects to login with warning message
     */
    public function test_guest_click_emergency_shift_redirects_to_login_with_message()
    {
        $shiftId = 1;
        $response = $this->get(route('ca-lam-viec.ung-tuyen-gap', $shiftId));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('url.intended', route('ca-lam-viec.show', $shiftId));
        $response->assertSessionHas('ca_lam_gap_id', $shiftId);
        $response->assertSessionHas('warning', 'Vui lòng đăng nhập tài khoản Ứng viên để nộp đơn ứng tuyển!');
    }

    /**
     * 5. Test Candidate logs in after clicking emergency shift -> redirects to intended shift detail
     */
    public function test_candidate_login_after_emergency_shift_click_redirects_to_shift_detail()
    {
        $shiftId = 1;

        // Bấm chọn ca làm gấp khi chưa đăng nhập
        $this->get(route('ca-lam-viec.ung-tuyen-gap', $shiftId));

        // Tiến hành đăng nhập với tài khoản Ứng viên
        $response = $this->post('/dang-nhap', [
            'email' => 'thuhuong.pham@gmail.com',
            'mat_khau' => '123456',
        ]);

        // Phải chuyển hướng về đúng trang chi tiết ca gấp đó
        $response->assertRedirect(route('ca-lam-viec.show', $shiftId));
    }

    /**
     * 6. Test Logged-in Candidate accesses emergency shift action -> opens shift detail directly
     */
    public function test_logged_in_candidate_opens_shift_detail_directly()
    {
        $shiftId = 1;
        $response = $this->withSession([
            'user' => [
                'ma_nguoi_dung' => 6,
                'ho_ten' => 'Phạm Thu Hương',
                'email' => 'thuhuong.pham@gmail.com',
                'vai_tro' => 'ung_vien',
            ],
        ])->get(route('ca-lam-viec.ung-tuyen-gap', $shiftId));

        $response->assertRedirect(route('ca-lam-viec.show', $shiftId));
    }

    /**
     * 7. Test login fails when entering incorrect password (no bypass allowed)
     */
    public function test_login_fails_with_incorrect_password()
    {
        $response = $this->from('/dang-nhap')->post('/dang-nhap', [
            'email' => 'admin@goldenshift.vn',
            'mat_khau' => 'wrong_password_123',
        ]);

        $response->assertRedirect('/dang-nhap');
        $response->assertSessionHasErrors('email');
    }

    /**
     * 8. Test login fails with clear error message when account is temporarily locked (tam_khoa)
     */
    public function test_login_fails_when_account_is_temporarily_locked()
    {
        $lockedUser = NguoiDung::create([
            'ho_ten' => 'Người Dùng Bị Khóa',
            'email' => 'locked_user_test@goldenshift.vn',
            'mat_khau' => Hash::make('password123'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'tam_khoa',
        ]);

        $response = $this->from('/dang-nhap')->post('/dang-nhap', [
            'email' => 'locked_user_test@goldenshift.vn',
            'mat_khau' => 'password123',
        ]);

        $response->assertRedirect('/dang-nhap');
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('TẠM KHÓA', session('errors')->first('email'));
    }

    /**
     * 9. Test quick-login routes are completely removed and return 404
     */
    public function test_quick_login_routes_are_removed()
    {
        $response = $this->get('/quick-login/quan_tri_vien');
        $response->assertNotFound();
    }

    /**
     * 10. Test login view has no quick login buttons or preset credentials, and includes Remember Me
     */
    public function test_login_view_has_no_quick_login_and_has_remember_me()
    {
        $response = $this->get('/dang-nhap');
        $response->assertOk();
        $response->assertDontSee('ĐĂNG NHẬP NHANH THEO VAI TRÒ');
        $response->assertDontSee('/quick-login/');
        $response->assertDontSee('role-admin');
        $response->assertDontSee('admin@goldenshift.vn');
        $response->assertSee('Ghi nhớ đăng nhập');
        $response->assertSee('name="ghi_nho"', false);
    }

    /**
     * 11. Test POST /login alias with Remember Me option
     */
    public function test_post_login_alias_with_remember_me()
    {
        $response = $this->post('/login', [
            'email' => 'admin@goldenshift.vn',
            'mat_khau' => '123456',
            'ghi_nho' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Auth::check());
    }
}
