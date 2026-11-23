<?php

namespace Tests\Feature;

use App\Http\Controllers\ChamCongController;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DynamicQrAttendanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 11, 22, 8, 5, 0, 'Asia/Ho_Chi_Minh'));
        Cache::flush();

        Schema::dropAllTables();

        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->increments('ma_nguoi_dung');
            $table->string('ho_ten');
            $table->string('email')->unique();
            $table->string('mat_khau')->nullable();
            $table->string('vai_tro')->default('ung_vien');
            $table->integer('ma_nha_hang_truc_thuoc')->nullable();
        });

        Schema::create('thuong_hieu', function (Blueprint $table) {
            $table->increments('ma_thuong_hieu');
            $table->string('ten_thuong_hieu');
            $table->string('slug')->nullable();
            $table->string('trang_thai')->default('hoat_dong');
        });

        Schema::create('nha_hang', function (Blueprint $table) {
            $table->increments('ma_nha_hang');
            $table->integer('ma_thuong_hieu')->default(1);
            $table->string('ten_nha_hang');
            $table->string('dia_chi')->nullable();
            $table->string('dia_chi_chi_tiet')->nullable();
            $table->integer('ma_quan_ly')->nullable();
        });

        Schema::create('ca_lam_viec', function (Blueprint $table) {
            $table->increments('ma_ca_lam');
            $table->integer('ma_nha_hang');
            $table->string('ten_vi_tri_tuyen_dung');
            $table->string('ngay_lam_viec');
            $table->string('gio_bat_dau');
            $table->string('gio_ket_thuc');
            $table->integer('so_luong_tuyen')->default(3);
            $table->integer('muc_luong_theo_gio')->default(35000);
            $table->string('trang_thai_ca')->default('dang_tuyen');
        });

        Schema::create('ho_so_ung_tuyen', function (Blueprint $table) {
            $table->increments('ma_ung_tuyen');
            $table->integer('ma_ca_lam');
            $table->integer('ma_nguoi_dung');
            $table->string('trang_thai_ung_tuyen')->default('da_duyet');
            $table->string('ghi_chu_nha_tuyen_dung')->nullable();
            $table->dateTime('ngay_nop_don')->nullable();
            $table->dateTime('ngay_ung_tuyen')->nullable();
            $table->dateTime('ngay_cap_nhat')->nullable();
        });

        Schema::create('lich_su_cham_cong', function (Blueprint $table) {
            $table->increments('ma_cham_cong');
            $table->integer('ma_ung_tuyen')->unique();
            $table->integer('ma_nguoi_dung');
            $table->integer('ma_ca_lam');
            $table->dateTime('thoi_gian_vao_thuc_te')->nullable();
            $table->dateTime('thoi_gian_ra_thuc_te')->nullable();
            $table->decimal('so_gio_lam_thuc_te', 5, 2)->nullable();
            $table->string('trang_thai_cham_cong')->default('dung_gio');
            $table->string('hinh_thuc_xac_thuc')->default('quan_ly_xac_nhan');
            $table->integer('luong_thuc_nhan')->nullable();
            $table->string('ly_do_tu_choi')->nullable();
            $table->dateTime('ngay_tao')->nullable();
        });

        Schema::create('danh_gia_ca_lam', function (Blueprint $table) {
            $table->increments('ma_danh_gia');
            $table->integer('ma_ca_lam');
            $table->integer('ma_nguoi_danh_gia');
            $table->integer('ma_nguoi_duoc_danh_gia');
            $table->integer('so_sao');
            $table->text('noi_dung_nhan_xet')->nullable();
            $table->dateTime('ngay_danh_gia')->nullable();
        });

        DB::table('thuong_hieu')->insert([
            'ma_thuong_hieu' => 1,
            'ten_thuong_hieu' => 'Golden BBQ',
            'slug' => 'golden-bbq',
            'trang_thai' => 'hoat_dong',
        ]);

        DB::table('nha_hang')->insert([
            [
                'ma_nha_hang' => 1,
                'ma_thuong_hieu' => 1,
                'ten_nha_hang' => 'Golden BBQ Cơ Sở 1',
                'dia_chi' => 'Hà Nội',
                'dia_chi_chi_tiet' => 'Số 1 Phố Huế, Hà Nội',
                'ma_quan_ly' => 10,
            ],
            [
                'ma_nha_hang' => 2,
                'ma_thuong_hieu' => 1,
                'ten_nha_hang' => 'Golden BBQ Cơ Sở 2',
                'dia_chi' => 'TP. HCM',
                'dia_chi_chi_tiet' => 'Số 2 Nguyễn Huệ, TP. HCM',
                'ma_quan_ly' => 11,
            ],
        ]);

        DB::table('nguoi_dung')->insert([
            [
                'ma_nguoi_dung' => 10,
                'ho_ten' => 'Quản lý BBQ 1',
                'email' => 'ql1.qr@test.com',
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'quan_ly_nha_hang',
                'ma_nha_hang_truc_thuoc' => 1,
            ],
            [
                'ma_nguoi_dung' => 11,
                'ho_ten' => 'Quản lý BBQ 2',
                'email' => 'ql2.qr@test.com',
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'quan_ly_nha_hang',
                'ma_nha_hang_truc_thuoc' => 2,
            ],
            [
                'ma_nguoi_dung' => 20,
                'ho_ten' => 'Ứng viên QR',
                'email' => 'uv.qr@test.com',
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'ung_vien',
                'ma_nha_hang_truc_thuoc' => null,
            ],
        ]);

        DB::table('ca_lam_viec')->insert([
            'ma_ca_lam' => 100,
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Phục vụ Bàn',
            'ngay_lam_viec' => '2026-11-22',
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '13:00:00',
            'so_luong_tuyen' => 3,
            'muc_luong_theo_gio' => 35000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        DB::table('ho_so_ung_tuyen')->insert([
            'ma_ung_tuyen' => 500,
            'ma_ca_lam' => 100,
            'ma_nguoi_dung' => 20,
            'trang_thai_ung_tuyen' => 'da_duyet',
            'ngay_nop_don' => '2026-11-21 10:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Cache::flush();
        parent::tearDown();
    }

    private function managerSession(int $userId = 10, int $restaurantId = 1): array
    {
        return [
            'user' => [
                'ma_nguoi_dung' => $userId,
                'ho_ten' => 'Quản lý BBQ '.$restaurantId,
                'email' => "ql{$restaurantId}.qr@test.com",
                'vai_tro' => 'quan_ly_nha_hang',
                'ma_nha_hang_truc_thuoc' => $restaurantId,
            ],
        ];
    }

    private function candidateSession(): array
    {
        return [
            'user' => [
                'ma_nguoi_dung' => 20,
                'ho_ten' => 'Ứng viên QR',
                'email' => 'uv.qr@test.com',
                'vai_tro' => 'ung_vien',
                'ma_nha_hang_truc_thuoc' => null,
            ],
        ];
    }

    public function test_candidate_request_qr_generates_30s_token_and_queues_popup_for_correct_restaurant_manager(): void
    {
        $reqResp = $this->withSession($this->candidateSession())
            ->postJson('/cham-cong/yeu-cau-qr', [
                'ma_ung_tuyen' => 500,
                'loai_qr' => 'vao_ca',
            ]);

        $reqResp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'ma_ung_tuyen' => 500,
                'ma_ca_lam' => 100,
                'loai_qr' => 'vao_ca',
                'ttl' => 30,
            ])
            ->assertJsonMissingPath('qr_token')
            ->assertJsonMissingPath('qr_payload');

        $cachedToken = Cache::get('qr_token_100');
        $this->assertIsString($cachedToken);
        $this->assertSame(32, strlen($cachedToken));

        // Quản lý nhà hàng #1 nhận được popup QR 30s
        $mgr1Pending = $this->withSession($this->managerSession(10, 1))
            ->getJson('/quan-ly/cham-cong/qr-pending');

        $mgr1Pending->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_pending' => true,
                'request' => [
                    'ma_ca_lam' => 100,
                    'ma_ung_tuyen' => 500,
                    'loai_qr' => 'vao_ca',
                    'qr_token' => $cachedToken,
                    'is_expired' => false,
                ],
            ]);
        $this->assertGreaterThan(0, $mgr1Pending->json('request.remaining_seconds'));

        // Quản lý nhà hàng #2 KHÔNG nhận nhầm yêu cầu từ nhà hàng #1
        $mgr2Pending = $this->withSession($this->managerSession(11, 2))
            ->getJson('/quan-ly/cham-cong/qr-pending');

        $mgr2Pending->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_pending' => false,
            ]);
    }

    public function test_candidate_cannot_poll_manager_pending_qr_or_refresh_token(): void
    {
        $this->withSession($this->candidateSession())
            ->getJson('/quan-ly/cham-cong/qr-pending')
            ->assertStatus(403);

        $this->withSession($this->candidateSession())
            ->postJson('/quan-ly/cham-cong/qr-token/100')
            ->assertStatus(403);
    }

    public function test_candidate_check_in_via_qr_within_30s_succeeds_and_marks_manager_popup_completed(): void
    {
        $this->withSession($this->candidateSession())
            ->postJson('/cham-cong/yeu-cau-qr', [
                'ma_ung_tuyen' => 500,
                'loai_qr' => 'vao_ca',
            ])
            ->assertStatus(200);

        $pending = $this->withSession($this->managerSession(10, 1))
            ->getJson('/quan-ly/cham-cong/qr-pending');
        $token = $pending->json('request.qr_token');

        // 15 giây sau -> mã QR vẫn còn hiệu lực
        Carbon::setTestNow(Carbon::now('Asia/Ho_Chi_Minh')->addSeconds(15));

        $scanResp = $this->withSession($this->candidateSession())
            ->postJson('/cham-cong/qr-scan', [
                'ma_ca_lam' => 100,
                'qr_token' => $token,
                'loai_qr' => 'vao_ca',
            ]);

        $scanResp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'hinh_thuc_xac_thuc' => 'ma_qr',
            ]);

        $this->assertDatabaseHas('lich_su_cham_cong', [
            'ma_ung_tuyen' => 500,
            'ma_ca_lam' => 100,
            'hinh_thuc_xac_thuc' => 'ma_qr',
        ]);

        $afterScanPending = $this->withSession($this->managerSession(10, 1))
            ->getJson('/quan-ly/cham-cong/qr-pending');

        $afterScanPending->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_pending' => false,
                'completed_request' => [
                    'ma_ca_lam' => 100,
                    'ma_ung_tuyen' => 500,
                    'completed' => true,
                ],
            ]);
    }

    public function test_candidate_check_out_via_qr_preserves_auto_open_review_modal_session_and_payload(): void
    {
        DB::table('lich_su_cham_cong')->insert([
            'ma_ung_tuyen' => 500,
            'ma_nguoi_dung' => 20,
            'ma_ca_lam' => 100,
            'thoi_gian_vao_thuc_te' => Carbon::now('Asia/Ho_Chi_Minh')->copy()->subHours(2),
            'trang_thai_cham_cong' => 'dung_gio',
            'hinh_thuc_xac_thuc' => 'ma_qr',
        ]);

        // Ứng viên bấm Ra ca -> tự động đẩy popup QR Ra ca sang Quản lý
        $this->withSession($this->candidateSession())
            ->postJson('/cham-cong/yeu-cau-qr', [
                'ma_ung_tuyen' => 500,
                'loai_qr' => 'ra_ca',
            ])
            ->assertStatus(200)
            ->assertJson(['loai_qr' => 'ra_ca']);

        $pendingOut = $this->withSession($this->managerSession(10, 1))
            ->getJson('/quan-ly/cham-cong/qr-pending');
        $pendingOut->assertJson([
            'has_pending' => true,
            'request' => [
                'loai_qr' => 'ra_ca',
                'ma_ca_lam' => 100,
            ],
        ]);
        $outToken = $pendingOut->json('request.qr_token');

        // Ứng viên quét QR Ra ca
        $checkOutResp = $this->withSession($this->candidateSession())
            ->postJson('/cham-cong/qr-scan', [
                'ma_ca_lam' => 100,
                'qr_token' => $outToken,
                'loai_qr' => 'ra_ca',
            ]);

        // Giữ nguyên hoàn toàn dữ liệu bật popup đánh giá tự động
        $checkOutResp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'trang_thai_cham_cong' => 'cho_xac_nhan',
                'hinh_thuc_xac_thuc' => 'ma_qr',
                'auto_open_review' => true,
                'review_data' => [
                    'maCaLam' => 100,
                    'targetUserId' => 10,
                    'targetName' => 'Golden BBQ Cơ Sở 1',
                    'shiftName' => 'Phục vụ Bàn',
                    'isManager' => false,
                ],
            ])
            ->assertSessionHas('show_danh_gia_modal', true)
            ->assertSessionHas('ma_ca_lam_danh_gia', 100);
    }

    public function test_qr_attendance_returns_http_400_after_30s_and_manager_refresh_creates_new_valid_token(): void
    {
        $this->withSession($this->candidateSession())
            ->postJson('/cham-cong/yeu-cau-qr', [
                'ma_ung_tuyen' => 500,
                'loai_qr' => 'vao_ca',
            ])
            ->assertStatus(200);

        $firstToken = Cache::get('qr_token_100');

        // Quá 30 giây -> Cache hết hạn, màn hình Quản lý báo expired & quét mã cũ trả HTTP 400
        Carbon::setTestNow(Carbon::now('Asia/Ho_Chi_Minh')->addSeconds(31));

        $this->withSession($this->managerSession(10, 1))
            ->getJson('/quan-ly/cham-cong/qr-pending')
            ->assertStatus(200)
            ->assertJson([
                'has_pending' => true,
                'request' => [
                    'remaining_seconds' => 0,
                    'is_expired' => true,
                ],
            ]);

        $this->withSession($this->candidateSession())
            ->postJson('/cham-cong/qr-scan', [
                'ma_ca_lam' => 100,
                'qr_token' => $firstToken,
                'loai_qr' => 'vao_ca',
            ])
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => ChamCongController::QR_EXPIRED_MESSAGE,
            ]);

        // Quản lý bấm "Làm mới mã QR" trên modal -> sinh token mới, mã cũ vẫn bị từ chối
        $refreshResp = $this->withSession($this->managerSession(10, 1))
            ->postJson('/quan-ly/cham-cong/qr-token/100', ['loai_qr' => 'vao_ca']);
        $newToken = $refreshResp->json('qr_token');

        $refreshResp->assertStatus(200);
        $this->assertNotSame($firstToken, $newToken);

        $this->withSession($this->candidateSession())
            ->postJson('/cham-cong/qr-scan', [
                'ma_ca_lam' => 100,
                'qr_token' => $firstToken,
                'loai_qr' => 'vao_ca',
            ])
            ->assertStatus(400);

        $this->withSession($this->candidateSession())
            ->postJson('/cham-cong/qr-scan', [
                'ma_ca_lam' => 100,
                'qr_token' => $newToken,
                'loai_qr' => 'vao_ca',
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'hinh_thuc_xac_thuc' => 'ma_qr',
            ]);
    }

    public function test_manager_and_candidate_views_match_auto_popup_qr_flow(): void
    {
        $mgrResponse = $this->withSession($this->managerSession(10, 1))
            ->get('/quan-ly/cham-cong');

        $mgrResponse->assertStatus(200)
            ->assertSee('modalQrChamCong', false)
            ->assertSee('progress-bar', false)
            ->assertSee('btn-refresh-qr', false)
            ->assertSee('Mã QR đã hết hạn sau 30s', false)
            ->assertSee('Làm mới mã QR', false)
            ->assertSee('/quan-ly/cham-cong/qr-pending', false)
            ->assertSee('setInterval', false)
            ->assertDontSee('btnOpenNavbarQrModal', false)
            ->assertDontSee('Tạo Mã QR Chấm Công (30s)', false);

        $candResponse = $this->withSession($this->candidateSession())
            ->get('/ung-tuyen/da-nop');

        $candResponse->assertStatus(200)
            ->assertSee('btn-trigger-qr-checkin', false)
            ->assertSee('/cham-cong/yeu-cau-qr', false)
            ->assertSee('modalCandidateQrScan', false);
    }
}
