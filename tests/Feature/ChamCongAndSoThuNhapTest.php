<?php

namespace Tests\Feature;

use App\Models\CaLamViec;
use App\Models\HoSoUngTuyen;
use App\Models\LichSuChamCong;
use App\Models\SoThuNhap;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChamCongAndSoThuNhapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Đảm bảo các bảng cần thiết tồn tại trong SQLite test environment
        if (! Schema::hasTable('nguoi_dung')) {
            Schema::create('nguoi_dung', function (Blueprint $table) {
                $table->increments('ma_nguoi_dung');
                $table->string('ho_ten', 100);
                $table->string('email', 150)->unique();
                $table->string('so_dien_thoai', 15)->nullable();
                $table->string('mat_khau', 255)->default('');
                $table->string('vai_tro', 30)->default('ung_vien');
                $table->integer('ma_nha_hang_truc_thuoc')->nullable();
                $table->string('trang_thai', 30)->default('hoat_dong');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('nha_hang')) {
            Schema::create('nha_hang', function (Blueprint $table) {
                $table->increments('ma_nha_hang');
                $table->integer('ma_thuong_hieu')->default(1);
                $table->string('ten_nha_hang', 200);
                $table->string('dia_chi_chi_tiet', 255)->default('');
            });
        }

        if (! Schema::hasTable('ca_lam_viec')) {
            Schema::create('ca_lam_viec', function (Blueprint $table) {
                $table->increments('ma_ca_lam');
                $table->integer('ma_nha_hang')->default(1);
                $table->string('ten_vi_tri_tuyen_dung', 150);
                $table->date('ngay_lam_viec');
                $table->time('gio_bat_dau');
                $table->time('gio_ket_thuc');
                $table->decimal('muc_luong_theo_gio', 12, 2)->default(30000);
                $table->integer('so_luong_tuyen')->default(2);
                $table->integer('so_luong_da_tuyen')->default(1);
                $table->string('trang_thai_ca', 30)->default('dang_tuyen');
                $table->text('ly_do_huy')->nullable();
                $table->text('yeu_cau_cong_viec')->nullable();
                $table->text('quyen_loi_ca_lam')->nullable();
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        if (! Schema::hasTable('ho_so_ung_tuyen')) {
            Schema::create('ho_so_ung_tuyen', function (Blueprint $table) {
                $table->increments('ma_ung_tuyen');
                $table->integer('ma_ca_lam');
                $table->integer('ma_nguoi_dung');
                $table->text('loi_nhan_ung_vien')->nullable();
                $table->string('trang_thai_ung_tuyen', 30)->default('cho_duyet');
                $table->text('ghi_chu_nha_tuyen_dung')->nullable();
                $table->integer('nguoi_duyet')->nullable();
                $table->timestamp('ngay_nop_don')->nullable();
                $table->timestamp('ngay_duyet')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        if (! Schema::hasTable('lich_su_cham_cong')) {
            Schema::create('lich_su_cham_cong', function (Blueprint $table) {
                $table->increments('ma_cham_cong');
                $table->integer('ma_ung_tuyen');
                $table->integer('ma_nguoi_dung');
                $table->integer('ma_ca_lam');
                $table->dateTime('thoi_gian_vao_thuc_te')->nullable();
                $table->dateTime('thoi_gian_ra_thuc_te')->nullable();
                $table->decimal('so_gio_lam_thuc_te', 5, 2)->default(0.00);
                $table->string('trang_thai_cham_cong', 30)->default('chua_vao_ca');
                $table->string('hinh_thuc_xac_thuc', 30)->default('ma_qr');
                $table->text('ghi_chu_cham_cong')->nullable();
                $table->integer('nguoi_xac_nhan')->nullable();
                $table->timestamp('ngay_tao')->nullable();
            });
        }

        if (! Schema::hasTable('so_thu_nhap')) {
            Schema::create('so_thu_nhap', function (Blueprint $table) {
                $table->increments('ma_so_thu_nhap');
                $table->integer('ma_nguoi_dung');
                $table->string('ky_luong', 30);
                $table->integer('tong_so_ca_lam')->default(0);
                $table->decimal('tong_gio_lam', 6, 2)->default(0.00);
                $table->decimal('tong_tien_luong_co_ban', 12, 2)->default(0.00);
                $table->decimal('tong_tien_thuong', 12, 2)->default(0.00);
                $table->decimal('tong_tien_phat', 12, 2)->default(0.00);
                $table->decimal('thuc_nhan', 12, 2)->default(0.00);
                $table->string('trang_thai_thanh_toan', 30)->default('chua_thanh_toan');
                $table->dateTime('ngay_thanh_toan')->nullable();
                $table->text('ghi_chu')->nullable();
                $table->timestamp('ngay_tao')->nullable();
            });
        }
    }

    /**
     * 1. Test Ứng viên Vào ca thành công và ghi nhận trạng thái đúng giờ / đi muộn
     */
    public function test_candidate_can_check_in_on_approved_application()
    {
        $candidate = [
            'ma_nguoi_dung' => 6,
            'ho_ten' => 'Phạm Thu Hương',
            'email' => 'thuhuong.pham@gmail.com',
            'vai_tro' => 'ung_vien',
        ];

        // Tạo ca làm việc hôm nay
        $shift = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Phục vụ Bàn',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '23:59:59',
            'gio_ket_thuc' => '23:59:59',
            'muc_luong_theo_gio' => 35000,
            'so_luong_tuyen' => 2,
            'so_luong_da_tuyen' => 1,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        // Tạo hồ sơ ứng tuyển đã được duyệt
        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift->ma_ca_lam,
            'ma_nguoi_dung' => 6,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        $response = $this->withSession(['user' => $candidate])
            ->post(route('cham_cong.check_in'), [
                'ma_ung_tuyen' => $app->ma_ung_tuyen,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra bản ghi trong bảng lich_su_cham_cong
        $chamCong = LichSuChamCong::where('ma_ung_tuyen', $app->ma_ung_tuyen)->first();
        $this->assertNotNull($chamCong);
        $this->assertEquals(6, $chamCong->ma_nguoi_dung);
        $this->assertEquals($shift->ma_ca_lam, $chamCong->ma_ca_lam);
        $this->assertNotNull($chamCong->thoi_gian_vao_thuc_te);
        // Ca ngày mai nên checkin hôm nay là 'dung_gio'
        $this->assertEquals('dung_gio', $chamCong->trang_thai_cham_cong);
    }

    /**
     * 2. Test Ứng viên Ra ca tính đúng số giờ làm và chuyển sang cho_xac_nhan
     */
    public function test_candidate_can_check_out_and_calculate_hours()
    {
        $candidate = [
            'ma_nguoi_dung' => 6,
            'ho_ten' => 'Phạm Thu Hương',
            'email' => 'thuhuong.pham@gmail.com',
            'vai_tro' => 'ung_vien',
        ];

        $shift = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Tiếp Thực Nhà Hàng',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '13:00:00',
            'muc_luong_theo_gio' => 30000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift->ma_ca_lam,
            'ma_nguoi_dung' => 6,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        // Giả lập đã vào ca 4 tiếng trước
        $chamCong = LichSuChamCong::create([
            'ma_ung_tuyen' => $app->ma_ung_tuyen,
            'ma_nguoi_dung' => 6,
            'ma_ca_lam' => $shift->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => Carbon::now()->subHours(4),
            'trang_thai_cham_cong' => 'dung_gio',
            'ngay_tao' => now(),
        ]);

        $response = $this->withSession(['user' => $candidate])
            ->post(route('cham_cong.check_out'), [
                'ma_ung_tuyen' => $app->ma_ung_tuyen,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $chamCong->refresh();
        $this->assertEquals('cho_xac_nhan', $chamCong->trang_thai_cham_cong);
        $this->assertNotNull($chamCong->thoi_gian_ra_thuc_te);
        $this->assertGreaterThanOrEqual(3.9, $chamCong->so_gio_lam_thuc_te);
    }

    /**
     * 3. Test Quản lý xác nhận chấm công và tự động tích lũy tiền vào Sổ Thu Nhập
     */
    public function test_manager_confirms_attendance_and_accumulates_income()
    {
        $manager = [
            'ma_nguoi_dung' => 2,
            'ho_ten' => 'Bùi Thị Châu',
            'email' => 'chau.ntb@goldenshift.vn',
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
        ];

        $shift = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Phụ Bếp & Sơ Chế',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '10:00:00',
            'gio_ket_thuc' => '15:00:00',
            'muc_luong_theo_gio' => 40000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift->ma_ca_lam,
            'ma_nguoi_dung' => 6,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        $chamCong = LichSuChamCong::create([
            'ma_ung_tuyen' => $app->ma_ung_tuyen,
            'ma_nguoi_dung' => 6,
            'ma_ca_lam' => $shift->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => Carbon::now()->subHours(5),
            'thoi_gian_ra_thuc_te' => Carbon::now(),
            'so_gio_lam_thuc_te' => 5.0,
            'trang_thai_cham_cong' => 'cho_xac_nhan',
            'ngay_tao' => now(),
        ]);

        $response = $this->withSession(['user' => $manager])
            ->post(route('quan_ly.cham_cong.confirm'), [
                'ma_cham_cong' => $chamCong->ma_cham_cong,
                'so_gio_lam_thuc_te' => 5.0,
                'so_tien_thuong' => 50000,
                'ghi_chu_cham_cong' => 'Hoàn thành tốt ca làm việc.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra trạng thái chấm công đã đổi thành da_xac_nhan
        $chamCong->refresh();
        $this->assertEquals('da_xac_nhan', $chamCong->trang_thai_cham_cong);
        $this->assertEquals(2, $chamCong->nguoi_xac_nhan);
        $this->assertStringContainsString('Thưởng thêm', $chamCong->ghi_chu_cham_cong);

        // Kiểm tra ca làm việc chuyển sang hoan_thanh
        $shift->refresh();
        $this->assertEquals('hoan_thanh', $shift->trang_thai_ca);

        // Kiểm tra bản ghi trong bảng so_thu_nhap
        $kyLuong = 'Tháng '.date('m/Y');
        $soThuNhap = SoThuNhap::where('ma_nguoi_dung', 6)->where('ky_luong', $kyLuong)->first();
        $this->assertNotNull($soThuNhap);
        $this->assertEquals(1, $soThuNhap->tong_so_ca_lam);
        $this->assertEquals(5.0, $soThuNhap->tong_gio_lam);
        // 5 giờ * 40.000 = 200.000 VNĐ cơ bản + 50.000 thưởng = 250.000 VNĐ thực nhận
        $this->assertEquals(200000, $soThuNhap->tong_tien_luong_co_ban);
        $this->assertEquals(50000, $soThuNhap->tong_tien_thuong);
        $this->assertEquals(250000, $soThuNhap->thuc_nhan);
    }

    /**
     * 4. Test Ứng viên truy cập Sổ thu nhập hiển thị đầy đủ thông tin
     */
    public function test_candidate_can_view_income_book()
    {
        $candidate = [
            'ma_nguoi_dung' => 6,
            'ho_ten' => 'Phạm Thu Hương',
            'email' => 'thuhuong.pham@gmail.com',
            'vai_tro' => 'ung_vien',
        ];

        // Tạo 1 bản ghi sổ thu nhập
        SoThuNhap::create([
            'ma_nguoi_dung' => 6,
            'ky_luong' => 'Tháng '.date('m/Y'),
            'tong_so_ca_lam' => 3,
            'tong_gio_lam' => 15.0,
            'tong_tien_luong_co_ban' => 600000,
            'tong_tien_thuong' => 50000,
            'tong_tien_phat' => 0,
            'thuc_nhan' => 650000,
            'trang_thai_thanh_toan' => 'chua_thanh_toan',
            'ngay_tao' => now(),
        ]);

        $response = $this->withSession(['user' => $candidate])
            ->get(route('ung_vien.so_thu_nhap'));

        $response->assertStatus(200);
        $response->assertSee('Sổ Thu Nhập Cá Nhân');
        $response->assertSee('650.000');
    }

    /**
     * 5. Test Quản lý truy cập trang Quản lý chấm công
     */
    public function test_manager_can_view_attendance_management_page()
    {
        $manager = [
            'ma_nguoi_dung' => 2,
            'ho_ten' => 'Bùi Thị Châu',
            'email' => 'chau.ntb@goldenshift.vn',
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
        ];

        $response = $this->withSession(['user' => $manager])
            ->get(route('quan_ly.cham_cong'));

        $response->assertStatus(200);
        $response->assertSee('Xét Duyệt Chấm Công');
    }

    /**
     * 6. Test Không thể vào ca nếu chưa tới ngày làm việc
     */
    public function test_candidate_cannot_check_in_if_shift_date_is_in_future()
    {
        $candidate = [
            'ma_nguoi_dung' => 6,
            'ho_ten' => 'Phạm Thu Hương',
            'email' => 'thuhuong.pham@gmail.com',
            'vai_tro' => 'ung_vien',
        ];

        // Ca làm việc ngày mai
        $futureShift = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Pha chế',
            'ngay_lam_viec' => date('Y-m-d', strtotime('+2 days')),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '17:00:00',
            'muc_luong_theo_gio' => 32000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $futureShift->ma_ca_lam,
            'ma_nguoi_dung' => 6,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        $response = $this->withSession(['user' => $candidate])
            ->post(route('cham_cong.check_in'), [
                'ma_ung_tuyen' => $app->ma_ung_tuyen,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Chưa tới ngày làm việc', session('error'));

        // Kiểm tra không có bản ghi chấm công được tạo
        $this->assertNull(LichSuChamCong::where('ma_ung_tuyen', $app->ma_ung_tuyen)->first());
    }

    /**
     * 7. Test Ràng buộc không thể vào 2 ca làm việc cùng một lúc
     */
    public function test_candidate_cannot_check_in_simultaneously_to_two_shifts()
    {
        $candidate = [
            'ma_nguoi_dung' => 6,
            'ho_ten' => 'Phạm Thu Hương',
            'email' => 'thuhuong.pham@gmail.com',
            'vai_tro' => 'ung_vien',
        ];

        // Ca 1: Đang làm việc
        $shift1 = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Ca 1 - Pha Chế',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '12:00:00',
            'muc_luong_theo_gio' => 30000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app1 = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift1->ma_ca_lam,
            'ma_nguoi_dung' => 6,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        // Đã check-in vào ca 1 nhưng chưa check-out
        LichSuChamCong::create([
            'ma_ung_tuyen' => $app1->ma_ung_tuyen,
            'ma_nguoi_dung' => 6,
            'ma_ca_lam' => $shift1->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => Carbon::now()->subHour(),
            'trang_thai_cham_cong' => 'dung_gio',
            'ngay_tao' => now(),
        ]);

        // Ca 2: Đã duyệt, muốn bấm Vào ca
        $shift2 = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Ca 2 - Phục Vụ',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '09:00:00',
            'gio_ket_thuc' => '14:00:00',
            'muc_luong_theo_gio' => 32000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app2 = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift2->ma_ca_lam,
            'ma_nguoi_dung' => 6,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        // Cố gắng check-in vào ca 2
        $response = $this->withSession(['user' => $candidate])
            ->post(route('cham_cong.check_in'), [
                'ma_ung_tuyen' => $app2->ma_ung_tuyen,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('không thể vào 2 ca cùng một lúc', session('error'));

        // Ca 2 không có bản ghi chấm công
        $this->assertNull(LichSuChamCong::where('ma_ung_tuyen', $app2->ma_ung_tuyen)->first());
    }

    /**
     * 8. Test Quản lý từ chối và yêu cầu kiểm tra lại chấm công
     */
    public function test_manager_can_reject_attendance_and_request_recheck()
    {
        $manager = [
            'ma_nguoi_dung' => 2,
            'ho_ten' => 'Bùi Thị Châu',
            'email' => 'chau.ntb@goldenshift.vn',
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
        ];

        $shift = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Rửa chén',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '18:00:00',
            'gio_ket_thuc' => '22:00:00',
            'muc_luong_theo_gio' => 28000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift->ma_ca_lam,
            'ma_nguoi_dung' => 6,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        $chamCong = LichSuChamCong::create([
            'ma_ung_tuyen' => $app->ma_ung_tuyen,
            'ma_nguoi_dung' => 6,
            'ma_ca_lam' => $shift->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => Carbon::now()->subHours(4),
            'thoi_gian_ra_thuc_te' => Carbon::now(),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'cho_xac_nhan',
            'ngay_tao' => now(),
        ]);

        $response = $this->withSession(['user' => $manager])
            ->post(route('quan_ly.cham_cong.reject'), [
                'ma_cham_cong' => $chamCong->ma_cham_cong,
                'ly_do_tu_choi' => 'Giờ ra ca không khớp với sổ phân công.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $chamCong->refresh();
        $this->assertEquals('can_kiem_tra', $chamCong->trang_thai_cham_cong);
        $this->assertStringContainsString('Giờ ra ca không khớp', $chamCong->ghi_chu_cham_cong);
    }

    /**
     * 9. Test Hệ thống tự động duyệt ca sau 60 phút từ khi ra ca
     */
    public function test_auto_approval_after_60_minutes_from_checkout()
    {
        $manager = [
            'ma_nguoi_dung' => 2,
            'ho_ten' => 'Bùi Thị Châu',
            'email' => 'chau.ntb@goldenshift.vn',
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
        ];

        $shift = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Tiếp thực',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '07:00:00',
            'gio_ket_thuc' => '11:00:00',
            'muc_luong_theo_gio' => 30000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift->ma_ca_lam,
            'ma_nguoi_dung' => 6,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        // Ra ca cách đây 70 phút (quá 60 phút)
        $chamCong = LichSuChamCong::create([
            'ma_ung_tuyen' => $app->ma_ung_tuyen,
            'ma_nguoi_dung' => 6,
            'ma_ca_lam' => $shift->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => Carbon::now()->subMinutes(310),
            'thoi_gian_ra_thuc_te' => Carbon::now()->subMinutes(70),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'cho_xac_nhan',
            'ngay_tao' => now()->subMinutes(310),
        ]);

        // Quản lý truy cập trang chấm công -> Tự động kích hoạt quét duyệt tự động
        $response = $this->withSession(['user' => $manager])
            ->get(route('quan_ly.cham_cong'));

        $response->assertStatus(200);

        // Kiểm tra ca làm đã chuyển sang tu_dong_duyet
        $chamCong->refresh();
        $this->assertEquals('tu_dong_duyet', $chamCong->trang_thai_cham_cong);
        $this->assertStringContainsString('tự động duyệt sau 60 phút', $chamCong->ghi_chu_cham_cong);

        // Kiểm tra Sổ thu nhập đã được cộng
        $kyLuong = 'Tháng '.date('m/Y');
        $soThuNhap = SoThuNhap::where('ma_nguoi_dung', 6)->where('ky_luong', $kyLuong)->first();
        $this->assertNotNull($soThuNhap);
        $this->assertGreaterThanOrEqual(1, $soThuNhap->tong_so_ca_lam);
        $this->assertGreaterThanOrEqual(4.0, $soThuNhap->tong_gio_lam);
    }

    /**
     * 10. Test Chặn chỉnh sửa hoặc duyệt ca đã tự động duyệt / quá hạn 60 phút
     */
    public function test_cannot_modify_already_auto_approved_attendance()
    {
        $manager = [
            'ma_nguoi_dung' => 2,
            'ho_ten' => 'Bùi Thị Châu',
            'email' => 'chau.ntb@goldenshift.vn',
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
        ];

        $shift = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Ca thử nghiệm khóa',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '12:00:00',
            'muc_luong_theo_gio' => 30000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift->ma_ca_lam,
            'ma_nguoi_dung' => 6,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        $chamCong = LichSuChamCong::create([
            'ma_ung_tuyen' => $app->ma_ung_tuyen,
            'ma_nguoi_dung' => 6,
            'ma_ca_lam' => $shift->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => Carbon::now()->subMinutes(180),
            'thoi_gian_ra_thuc_te' => Carbon::now()->subMinutes(80),
            'so_gio_lam_thuc_te' => 1.67,
            'trang_thai_cham_cong' => 'tu_dong_duyet',
            'ngay_tao' => now()->subMinutes(180),
        ]);

        // Cố tình gửi form confirm lên ca đã tu_dong_duyet
        $response = $this->withSession(['user' => $manager])
            ->post(route('quan_ly.cham_cong.confirm'), [
                'ma_cham_cong' => $chamCong->ma_cham_cong,
                'so_gio_lam_thuc_te' => 3.0,
                'so_tien_thuong' => 100000,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        // Giá trị giờ làm và trạng thái không bị thay đổi
        $chamCong->refresh();
        $this->assertEquals('tu_dong_duyet', $chamCong->trang_thai_cham_cong);
        $this->assertEquals(1.67, $chamCong->so_gio_lam_thuc_te);
    }

    /**
     * 11. Test Ứng viên mở trang tự động kích hoạt duyệt >60p, tách khỏi Chờ Xác Nhận sang Đã Xác Nhận và cộng Sổ Thu Nhập
     */
    public function test_candidate_page_triggers_auto_approval_after_60m_and_syncs_manager_tabs_and_income()
    {
        if (! Schema::hasTable('thuong_hieu')) {
            Schema::create('thuong_hieu', function (Blueprint $table) {
                $table->increments('ma_thuong_hieu');
                $table->string('ten_thuong_hieu', 100)->default('Golden Stars');
            });
            \Illuminate\Support\Facades\DB::table('thuong_hieu')->insert([
                'ma_thuong_hieu' => 1,
                'ten_thuong_hieu' => 'Golden Stars',
            ]);
        }

        if (! Schema::hasTable('danh_gia_ca_lam')) {
            Schema::create('danh_gia_ca_lam', function (Blueprint $table) {
                $table->increments('ma_danh_gia');
                $table->integer('ma_ca_lam');
                $table->integer('ma_nguoi_danh_gia');
                $table->integer('ma_nguoi_duoc_danh_gia');
                $table->integer('so_sao');
                $table->text('noi_dung_nhan_xet')->nullable();
            });
        }

        if (\Illuminate\Support\Facades\DB::table('nha_hang')->count() === 0) {
            \Illuminate\Support\Facades\DB::table('nha_hang')->insert([
                'ma_nha_hang' => 1,
                'ma_thuong_hieu' => 1,
                'ten_nha_hang' => 'Golden BBQ Test',
                'dia_chi_chi_tiet' => 'Hà Nội',
            ]);
        }

        \Illuminate\Support\Facades\DB::table('nguoi_dung')->insertOrIgnore([
            [
                'ma_nguoi_dung' => 2,
                'ho_ten' => 'Quản Lý BBQ',
                'email' => 'ql60p@goldenshift.vn',
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'quan_ly_nha_hang',
                'ma_nha_hang_truc_thuoc' => 1,
            ],
            [
                'ma_nguoi_dung' => 66,
                'ho_ten' => 'Ứng Viên Quá 60P',
                'email' => 'uv60p@goldenshift.vn',
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'ung_vien',
                'ma_nha_hang_truc_thuoc' => null,
            ],
        ]);

        $candidate = [
            'ma_nguoi_dung' => 66,
            'ho_ten' => 'Ứng Viên Quá 60P',
            'email' => 'uv60p@goldenshift.vn',
            'vai_tro' => 'ung_vien',
        ];

        $manager = [
            'ma_nguoi_dung' => 2,
            'ho_ten' => 'Quản Lý BBQ',
            'email' => 'ql60p@goldenshift.vn',
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
        ];

        $shift = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Pha chế',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '07:00:00',
            'gio_ket_thuc' => '11:00:00',
            'muc_luong_theo_gio' => 40000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift->ma_ca_lam,
            'ma_nguoi_dung' => 66,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        $checkoutAt = Carbon::now('Asia/Ho_Chi_Minh')->subMinutes(75);
        $chamCong = LichSuChamCong::create([
            'ma_ung_tuyen' => $app->ma_ung_tuyen,
            'ma_nguoi_dung' => 66,
            'ma_ca_lam' => $shift->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => $checkoutAt->copy()->subHours(4),
            'thoi_gian_ra_thuc_te' => $checkoutAt,
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'cho_xac_nhan',
            'ngay_tao' => $checkoutAt->copy()->subHours(4),
        ]);

        // 1. Ứng viên mở trang Ca làm đã nộp dù Quản lý CHƯA mở trang chấm công
        $candResp = $this->withSession(['user' => $candidate])
            ->get(route('ung_tuyen.da_nop'));

        $candResp->assertStatus(200)
            ->assertSee('Đã duyệt (Tự động)', false)
            ->assertDontSee('Chờ QL duyệt', false);

        $chamCong->refresh();
        $this->assertEquals('tu_dong_duyet', $chamCong->trang_thai_cham_cong);

        // 2. Quản lý mở tab Chờ Xác Nhận -> không còn bản ghi quá 60p
        $pendingResp = $this->withSession(['user' => $manager])
            ->get(url('/quan-ly/cham-cong?tab=cho_xac_nhan'));

        $pendingResp->assertStatus(200)
            ->assertDontSee('Ứng Viên Quá 60P', false);

        // 3. Quản lý mở tab Đã Xác Nhận -> hiển thị rõ ở Đã Xác Nhận
        $confirmedResp = $this->withSession(['user' => $manager])
            ->get(url('/quan-ly/cham-cong?tab=da_xac_nhan'));

        $confirmedResp->assertStatus(200)
            ->assertSee('Ứng Viên Quá 60P', false)
            ->assertSee('Đã duyệt (Tự động)', false);

        // 4. Ứng viên mở Sổ thu nhập -> hiển thị lịch sử và cộng đúng tiền lương (4.0 * 40,000 = 160,000)
        $incomeResp = $this->withSession(['user' => $candidate])
            ->get(route('ung_vien.so_thu_nhap'));

        $incomeResp->assertStatus(200)
            ->assertSee('Nhân viên Pha chế', false)
            ->assertSee('Hệ thống tự động duyệt (60p)', false);

        $kyLuong = 'Tháng '.$checkoutAt->format('m/Y');
        $stn = SoThuNhap::where('ma_nguoi_dung', 66)->where('ky_luong', $kyLuong)->first();
        $this->assertNotNull($stn);
        $this->assertEquals(1, $stn->tong_so_ca_lam);
        $this->assertEquals(4.0, (float) $stn->tong_gio_lam);
        $this->assertEquals(160000.0, (float) $stn->thuc_nhan);
    }

    /**
     * 12. Test processAutoApprovals chạy nhiều lần liên tiếp không gây cộng trùng Sổ Thu Nhập
     */
    public function test_process_auto_approvals_is_atomic_and_does_not_double_count_income()
    {
        $shift = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Ca Atomic Test',
            'ngay_lam_viec' => date('Y-m-d'),
            'gio_bat_dau' => '07:00:00',
            'gio_ket_thuc' => '11:00:00',
            'muc_luong_theo_gio' => 50000,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $app = HoSoUngTuyen::create([
            'ma_ca_lam' => $shift->ma_ca_lam,
            'ma_nguoi_dung' => 77,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now(),
        ]);

        $checkoutAt = Carbon::now('Asia/Ho_Chi_Minh')->subMinutes(90);
        $chamCong = LichSuChamCong::create([
            'ma_ung_tuyen' => $app->ma_ung_tuyen,
            'ma_nguoi_dung' => 77,
            'ma_ca_lam' => $shift->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => $checkoutAt->copy()->subHours(2),
            'thoi_gian_ra_thuc_te' => $checkoutAt,
            'so_gio_lam_thuc_te' => 2.0,
            'trang_thai_cham_cong' => 'cho_xac_nhan',
            'ngay_tao' => $checkoutAt->copy()->subHours(2),
        ]);

        \App\Http\Controllers\ChamCongController::processAutoApprovals();
        \App\Http\Controllers\ChamCongController::processAutoApprovals();
        \App\Http\Controllers\ChamCongController::executeAutoApproval($chamCong);

        $kyLuong = 'Tháng '.$checkoutAt->format('m/Y');
        $stn = SoThuNhap::where('ma_nguoi_dung', 77)->where('ky_luong', $kyLuong)->first();
        $this->assertNotNull($stn);
        $this->assertEquals(1, $stn->tong_so_ca_lam);
        $this->assertEquals(2.0, (float) $stn->tong_gio_lam);
        $this->assertEquals(100000.0, (float) $stn->thuc_nhan);
    }
}
