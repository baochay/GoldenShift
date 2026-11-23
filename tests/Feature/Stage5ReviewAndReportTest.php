<?php

namespace Tests\Feature;

use App\Models\BaoCaoViPham;
use App\Models\CaLamViec;
use App\Models\DanhGiaCaLam;
use App\Models\HoSoUngTuyen;
use App\Models\LichSuChamCong;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Stage5ReviewAndReportTest extends TestCase
{
    protected $admin;

    protected $manager;

    protected $candidate;

    protected $shiftCompleted;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo các bảng cần thiết trong SQLite test in-memory
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

        if (! Schema::hasTable('thuong_hieu')) {
            Schema::create('thuong_hieu', function (Blueprint $table) {
                $table->increments('ma_thuong_hieu');
                $table->string('ten_thuong_hieu', 150);
                $table->string('ma_dinh_danh', 60)->nullable();
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        if (! Schema::hasTable('nha_hang')) {
            Schema::create('nha_hang', function (Blueprint $table) {
                $table->increments('ma_nha_hang');
                $table->integer('ma_thuong_hieu')->default(1);
                $table->integer('ma_quan_ly')->nullable();
                $table->string('ten_nha_hang', 200);
                $table->string('dia_chi_chi_tiet', 255)->default('');
                $table->string('so_dien_thoai_lien_he', 20)->nullable();
                $table->string('quan_huyen', 100)->nullable();
                $table->string('tinh_thanh_pho', 100)->nullable();
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
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

        if (! Schema::hasTable('danh_gia_ca_lam')) {
            Schema::create('danh_gia_ca_lam', function (Blueprint $table) {
                $table->increments('ma_danh_gia');
                $table->integer('ma_ca_lam');
                $table->integer('ma_nguoi_danh_gia');
                $table->integer('ma_nguoi_duoc_danh_gia');
                $table->string('loai_danh_gia', 50);
                $table->tinyInteger('so_sao')->default(5);
                $table->tinyInteger('tieu_chi_thai_do')->default(5);
                $table->tinyInteger('tieu_chi_chuyen_can')->default(5);
                $table->tinyInteger('tieu_chi_hieu_suat')->default(5);
                $table->text('noi_dung_nhan_xet')->nullable();
                $table->timestamp('ngay_danh_gia')->nullable();
            });
        }

        if (! Schema::hasTable('bao_cao_vi_pham')) {
            Schema::create('bao_cao_vi_pham', function (Blueprint $table) {
                $table->increments('ma_vi_pham');
                $table->integer('ma_nguoi_vi_pham');
                $table->integer('ma_nguoi_bao_cao');
                $table->integer('ma_ca_lam')->nullable();
                $table->string('loai_vi_pham', 100);
                $table->string('muc_do_nghiem_trong', 30)->default('trung_binh');
                $table->text('mo_ta_chi_tiet');
                $table->string('hinh_anh_minh_chung', 255)->nullable();
                $table->decimal('so_tien_phat_du_kien', 12, 2)->default(0.00);
                $table->string('trang_thai_xu_ly', 50)->default('cho_xuly');
                $table->text('hinh_thuc_xu_ly')->nullable();
                $table->text('ghi_chu_admin')->nullable();
                $table->timestamp('ngay_lap_bao_cao')->nullable();
            });
        }

        if (! Schema::hasTable('thong_bao')) {
            Schema::create('thong_bao', function (Blueprint $table) {
                $table->id('ma_thong_bao');
                $table->unsignedBigInteger('ma_nguoi_dung');
                $table->string('tieu_de', 255);
                $table->text('noi_dung');
                $table->string('duong_dan_lien_ket', 500)->nullable();
                $table->tinyInteger('da_doc')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('so_thu_nhap')) {
            Schema::create('so_thu_nhap', function (Blueprint $table) {
                $table->increments('ma_so_thu_nhap');
                $table->integer('ma_nguoi_dung');
                $table->string('ky_luong', 50);
                $table->integer('tong_so_ca_lam')->default(0);
                $table->decimal('tong_gio_lam', 8, 2)->default(0);
                $table->decimal('tong_tien_luong_co_ban', 12, 2)->default(0);
                $table->decimal('tong_tien_thuong', 12, 2)->default(0);
                $table->decimal('tong_tien_phat', 12, 2)->default(0);
                $table->decimal('thuc_nhan', 12, 2)->default(0);
                $table->string('trang_thai_thanh_toan', 30)->default('chua_thanh_toan');
                $table->dateTime('ngay_thanh_toan')->nullable();
                $table->text('ghi_chu')->nullable();
                $table->timestamp('ngay_tao')->nullable();
            });
        }

        // 2. Tạo dữ liệu mẫu
        $this->admin = NguoiDung::firstOrCreate(
            ['email' => 'admin@goldenshift.vn'],
            [
                'ho_ten' => 'Admin Hệ Thống',
                'so_dien_thoai' => '0901234567',
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'quan_tri_vien',
                'trang_thai' => 'hoat_dong',
            ]
        );

        $this->manager = NguoiDung::firstOrCreate(
            ['email' => 'manager@goldenshift.vn'],
            [
                'ho_ten' => 'Nguyễn Thị Quản Lý',
                'so_dien_thoai' => '0907654321',
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'quan_ly_nha_hang',
                'ma_nha_hang_truc_thuoc' => 1,
                'trang_thai' => 'hoat_dong',
            ]
        );

        $this->candidate = NguoiDung::firstOrCreate(
            ['email' => 'candidate@goldenshift.vn'],
            [
                'ho_ten' => 'Phạm Thu Hương',
                'so_dien_thoai' => '0945678901',
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'ung_vien',
                'trang_thai' => 'hoat_dong',
            ]
        );

        $nhaHang = NhaHang::firstOrCreate(
            ['ma_nha_hang' => 1],
            [
                'ma_thuong_hieu' => 1,
                'ma_quan_ly' => $this->manager->ma_nguoi_dung,
                'ten_nha_hang' => 'Golden Hotpot Đồng Khởi',
                'dia_chi_chi_tiet' => '123 Đồng Khởi, Q.1',
            ]
        );

        $this->shiftCompleted = CaLamViec::firstOrCreate(
            ['ma_ca_lam' => 999],
            [
                'ma_nha_hang' => 1,
                'ten_vi_tri_tuyen_dung' => 'Nhân viên Phục vụ',
                'ngay_lam_viec' => now()->subDays(2)->format('Y-m-d'),
                'gio_bat_dau' => '08:00:00',
                'gio_ket_thuc' => '12:00:00',
                'muc_luong_theo_gio' => 30000,
                'so_luong_tuyen' => 2,
                'so_luong_da_tuyen' => 2,
                'trang_thai_ca' => 'hoan_thanh',
            ]
        );

        HoSoUngTuyen::firstOrCreate(
            [
                'ma_ca_lam' => $this->shiftCompleted->ma_ca_lam,
                'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            ],
            [
                'trang_thai_ung_tuyen' => 'da_nhan',
                'ngay_nop_don' => now()->subDays(3),
            ]
        );

        LichSuChamCong::firstOrCreate(
            [
                'ma_ca_lam' => $this->shiftCompleted->ma_ca_lam,
                'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            ],
            [
                'ma_ung_tuyen' => 1,
                'thoi_gian_vao_thuc_te' => now()->subDays(2)->setTime(8, 0),
                'thoi_gian_ra_thuc_te' => now()->subDays(2)->setTime(12, 0),
                'so_gio_lam_thuc_te' => 4.0,
                'trang_thai_cham_cong' => 'da_xac_nhan',
            ]
        );
    }

    /**
     * 1. Test Ứng viên đánh giá Nhà hàng thành công cho ca hoàn thành
     */
    public function test_candidate_can_review_completed_shift()
    {
        DanhGiaCaLam::where('ma_ca_lam', $this->shiftCompleted->ma_ca_lam)
            ->where('ma_nguoi_danh_gia', $this->candidate->ma_nguoi_dung)
            ->delete();

        $response = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->post('/danh-gia/luu', [
                'ma_ca_lam' => $this->shiftCompleted->ma_ca_lam,
                'so_sao' => 5,
                'noi_dung_nhan_xet' => 'Môi trường làm việc rất chuyên nghiệp, quản lý thân thiện!',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('danh_gia_ca_lam', [
            'ma_ca_lam' => $this->shiftCompleted->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->candidate->ma_nguoi_dung,
            'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
            'so_sao' => 5,
        ]);
    }

    /**
     * 2. Test Mỗi bên chỉ được đánh giá 1 lần duy nhất cho mỗi ca làm
     */
    public function test_cannot_review_twice_for_same_shift()
    {
        DanhGiaCaLam::firstOrCreate(
            [
                'ma_ca_lam' => $this->shiftCompleted->ma_ca_lam,
                'ma_nguoi_danh_gia' => $this->candidate->ma_nguoi_dung,
            ],
            [
                'ma_nguoi_duoc_danh_gia' => $this->manager->ma_nguoi_dung,
                'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
                'so_sao' => 5,
                'noi_dung_nhan_xet' => 'Đánh giá đầu tiên',
                'ngay_danh_gia' => now(),
            ]
        );

        $response = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->post('/danh-gia/luu', [
                'ma_ca_lam' => $this->shiftCompleted->ma_ca_lam,
                'so_sao' => 4,
                'noi_dung_nhan_xet' => 'Đánh giá lần 2 cố tình gửi thêm',
            ]);

        $response->assertSessionHas('warning');
    }

    /**
     * 3. Test Chặn đánh giá khi ca làm chưa hoàn thành
     */
    public function test_cannot_review_shift_not_completed()
    {
        $shiftFuture = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Phụ bếp',
            'ngay_lam_viec' => now()->addDays(5)->format('Y-m-d'),
            'gio_bat_dau' => '10:00:00',
            'gio_ket_thuc' => '14:00:00',
            'muc_luong_theo_gio' => 30000,
            'so_luong_tuyen' => 2,
            'so_luong_da_tuyen' => 0,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $response = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->post('/danh-gia/luu', [
                'ma_ca_lam' => $shiftFuture->ma_ca_lam,
                'so_sao' => 5,
                'noi_dung_nhan_xet' => 'Chưa làm mà đánh giá',
            ]);

        $response->assertSessionHas('error');
    }

    /**
     * 4. Test Gửi báo cáo vi phạm thành công (không cần muc_do_nghiem_trong)
     */
    public function test_send_report_violation_successfully()
    {
        $response = $this->actingAs($this->manager)
            ->withSession(['user' => $this->manager])
            ->post('/bao-cao-vi-pham/gui', [
                'ma_ca_lam' => $this->shiftCompleted->ma_ca_lam,
                'ma_nguoi_vi_pham' => $this->candidate->ma_nguoi_dung,
                'loai_vi_pham' => 'bung_ca',
                'mo_ta_chi_tiet' => 'Ứng viên tự ý bỏ ca không đến và không báo trước làm thiếu người phục vụ bàn.',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bao_cao_vi_pham', [
            'ma_nguoi_bao_cao' => $this->manager->ma_nguoi_dung,
            'ma_nguoi_vi_pham' => $this->candidate->ma_nguoi_dung,
            'loai_vi_pham' => 'bung_ca',
            'trang_thai_xu_ly' => 'cho_xuly',
        ]);
    }

    /**
     * 4b. Test Chặn gửi báo cáo vi phạm khi ca chưa hoàn thành
     */
    public function test_cannot_report_violation_if_shift_not_completed()
    {
        $shiftPending = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Phụ bếp',
            'ngay_lam_viec' => now()->addDays(2)->format('Y-m-d'),
            'gio_bat_dau' => '10:00:00',
            'gio_ket_thuc' => '14:00:00',
            'muc_luong_theo_gio' => 30000,
            'so_luong_tuyen' => 2,
            'so_luong_da_tuyen' => 1,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $response = $this->actingAs($this->manager)
            ->withSession(['user' => $this->manager])
            ->post('/bao-cao-vi-pham/gui', [
                'ma_ca_lam' => $shiftPending->ma_ca_lam,
                'ma_nguoi_vi_pham' => $this->candidate->ma_nguoi_dung,
                'loai_vi_pham' => 'thai_do_khong_phu_hop',
                'mo_ta_chi_tiet' => 'Báo cáo sớm khi ca chưa hoàn thành.',
            ]);

        $response->assertSessionHas('error', 'Chỉ được phép gửi báo cáo vi phạm khi ca làm việc đã ở trạng thái hoàn thành!');
    }

    /**
     * 4c. Test Chặn gửi báo cáo vi phạm khi đã quá thời hạn 7 ngày
     */
    public function test_cannot_report_violation_if_past_7_days()
    {
        $shiftOld = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Rửa bát',
            'ngay_lam_viec' => now()->subDays(10)->format('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '12:00:00',
            'muc_luong_theo_gio' => 30000,
            'so_luong_tuyen' => 1,
            'so_luong_da_tuyen' => 1,
            'trang_thai_ca' => 'hoan_thanh',
        ]);

        LichSuChamCong::create([
            'ma_ca_lam' => $shiftOld->ma_ca_lam,
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'ma_ung_tuyen' => 99,
            'thoi_gian_vao_thuc_te' => now()->subDays(10)->setTime(8, 0),
            'thoi_gian_ra_thuc_te' => now()->subDays(10)->setTime(12, 0),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'da_xac_nhan',
            'ngay_tao' => now()->subDays(10),
        ]);

        $response = $this->actingAs($this->manager)
            ->withSession(['user' => $this->manager])
            ->post('/bao-cao-vi-pham/gui', [
                'ma_ca_lam' => $shiftOld->ma_ca_lam,
                'ma_nguoi_vi_pham' => $this->candidate->ma_nguoi_dung,
                'loai_vi_pham' => 'gay_thiet_hai',
                'mo_ta_chi_tiet' => 'Báo cáo sau 10 ngày kể từ khi ca kết thúc.',
            ]);

        $response->assertSessionHas('error', 'Đã quá thời hạn 7 ngày để gửi báo cáo vi phạm cho ca làm việc này!');
    }

    /**
     * 4c-bis. Test Chặn gửi đánh giá ca làm khi đã quá thời hạn 7 ngày
     */
    public function test_cannot_review_shift_if_past_7_days()
    {
        $shiftOld = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Phụ bếp',
            'ngay_lam_viec' => now()->subDays(10)->format('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '12:00:00',
            'muc_luong_theo_gio' => 30000,
            'so_luong_tuyen' => 1,
            'so_luong_da_tuyen' => 1,
            'trang_thai_ca' => 'hoan_thanh',
        ]);

        LichSuChamCong::create([
            'ma_ca_lam' => $shiftOld->ma_ca_lam,
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'ma_ung_tuyen' => 999,
            'thoi_gian_vao_thuc_te' => now()->subDays(10)->setTime(8, 0),
            'thoi_gian_ra_thuc_te' => now()->subDays(10)->setTime(12, 0),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'da_xac_nhan',
            'ngay_tao' => now()->subDays(10),
        ]);

        $response = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->post('/danh-gia/luu', [
                'ma_ca_lam' => $shiftOld->ma_ca_lam,
                'so_sao' => 5,
                'noi_dung_nhan_xet' => 'Đánh giá sau 10 ngày kể từ khi ca kết thúc.',
            ]);

        $response->assertSessionHas('error', 'Đã quá thời hạn 7 ngày kể từ khi kết thúc ca làm việc. Bạn không thể thực hiện đánh giá cho ca này nữa!');
        $this->assertDatabaseMissing('danh_gia_ca_lam', [
            'ma_ca_lam' => $shiftOld->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->candidate->ma_nguoi_dung,
        ]);
    }

    /**
     * 4d. Test Ứng viên được phép đánh giá ngay sau khi ra ca (trạng thái cho_xac_nhan)
     */
    public function test_candidate_can_review_immediately_after_checkout()
    {
        $shiftCheckout = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Pha chế',
            'ngay_lam_viec' => now()->format('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '12:00:00',
            'muc_luong_theo_gio' => 32000,
            'so_luong_tuyen' => 1,
            'so_luong_da_tuyen' => 1,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $ungTuyen = HoSoUngTuyen::create([
            'ma_ca_lam' => $shiftCheckout->ma_ca_lam,
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now()->subDays(1),
        ]);

        LichSuChamCong::create([
            'ma_ca_lam' => $shiftCheckout->ma_ca_lam,
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'ma_ung_tuyen' => $ungTuyen->ma_ung_tuyen,
            'thoi_gian_vao_thuc_te' => now()->subHours(4),
            'thoi_gian_ra_thuc_te' => now(),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'cho_xac_nhan',
            'ngay_tao' => now(),
        ]);

        $response = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->post('/danh-gia/luu', [
                'ma_ca_lam' => $shiftCheckout->ma_ca_lam,
                'so_sao' => 5,
                'noi_dung_nhan_xet' => 'Vừa ra ca xong đánh giá ngay lập tức!',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('danh_gia_ca_lam', [
            'ma_ca_lam' => $shiftCheckout->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->candidate->ma_nguoi_dung,
            'so_sao' => 5,
        ]);
    }

    /**
     * 4e. Test Quản lý xác nhận chấm công tự động kích hoạt popup đánh giá ứng viên
     */
    public function test_manager_confirm_attendance_flashes_auto_review()
    {
        $shiftToConfirm = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Tiếp thực',
            'ngay_lam_viec' => now()->format('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '12:00:00',
            'muc_luong_theo_gio' => 30000,
            'so_luong_tuyen' => 1,
            'so_luong_da_tuyen' => 1,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $ccPending = LichSuChamCong::create([
            'ma_ca_lam' => $shiftToConfirm->ma_ca_lam,
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'ma_ung_tuyen' => 102,
            'thoi_gian_vao_thuc_te' => now()->subHours(4),
            'thoi_gian_ra_thuc_te' => now(),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'cho_xac_nhan',
            'ngay_tao' => now(),
        ]);

        $response = $this->actingAs($this->manager)
            ->withSession(['user' => $this->manager])
            ->post('/quan-ly/cham-cong/xac-nhan', [
                'ma_cham_cong' => $ccPending->ma_cham_cong,
                'so_gio_lam_thuc_te' => 4.0,
                'so_tien_thuong' => 20000,
                'ghi_chu_cham_cong' => 'Làm tốt, thưởng nóng 20k',
            ]);

        $response->assertSessionHas('auto_open_review_manager', true);
        $response->assertSessionHas('show_danh_gia_ung_vien_modal', true);
        $response->assertSessionHas('ma_ca_lam_danh_gia', $shiftToConfirm->ma_ca_lam);
        $response->assertSessionHas('review_shift_id', $shiftToConfirm->ma_ca_lam);
        $response->assertSessionHas('review_target_id', $this->candidate->ma_nguoi_dung);
        $response->assertSessionHas('review_target_name', $this->candidate->ho_ten);

        $page = $this->actingAs($this->manager)
            ->get(route('quan_ly.cham_cong'));

        $page->assertStatus(200);
        $page->assertSee('data-bs-backdrop="static"', false);
        $page->assertSee('data-bs-keyboard="false"', false);
        $page->assertDontSee('btnReviewModalCloseX');
        $page->assertDontSee('btnReviewModalCancel');
        $page->assertDontSee('btnModalReviewCandidate');
        $page->assertDontSee('Đánh Giá Ứng Viên');
        $page->assertSee('isManager: true', false);
    }

    /**
     * 4f. Test cấu hình timezone Việt Nam (+07:00) trong app & database
     */
    public function test_application_and_database_timezone_is_vietnam()
    {
        $this->assertEquals('Asia/Ho_Chi_Minh', config('app.timezone'));
        $this->assertEquals('+07:00', config('database.connections.mysql.timezone'));
        $this->assertEquals('+07:00', config('database.connections.mariadb.timezone'));
    }

    /**
     * 4g. Test Ứng viên ra ca lưu session bắt buộc đánh giá và tự động mở modal không có nút đóng, sau khi đánh giá sẽ xóa session
     */
    public function test_candidate_checkout_sets_persistent_review_session_and_clears_on_submit()
    {
        $shiftCheckout = CaLamViec::create([
            'ma_nha_hang' => 1,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Thu ngân',
            'ngay_lam_viec' => now()->format('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '13:00:00',
            'muc_luong_theo_gio' => 35000,
            'so_luong_tuyen' => 1,
            'so_luong_da_tuyen' => 1,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        $ungTuyen = HoSoUngTuyen::create([
            'ma_ca_lam' => $shiftCheckout->ma_ca_lam,
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'trang_thai_ung_tuyen' => 'da_nhan',
            'ngay_nop_don' => now()->subDays(1),
        ]);

        LichSuChamCong::create([
            'ma_ca_lam' => $shiftCheckout->ma_ca_lam,
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'ma_ung_tuyen' => $ungTuyen->ma_ung_tuyen,
            'thoi_gian_vao_thuc_te' => now()->subHours(5),
            'trang_thai_cham_cong' => 'dung_gio',
            'ngay_tao' => now(),
        ]);

        $checkoutResponse = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->post(route('cham_cong.check_out'), [
                'ma_ung_tuyen' => $ungTuyen->ma_ung_tuyen,
            ]);

        $checkoutResponse->assertRedirect();
        $checkoutResponse->assertSessionHas('show_danh_gia_modal', true);
        $checkoutResponse->assertSessionHas('ma_ca_lam_danh_gia', $shiftCheckout->ma_ca_lam);

        $page = $this->actingAs($this->candidate)
            ->get('/ung-vien/ca-lam-da-ung-tuyen');

        $page->assertStatus(200);
        $page->assertSee('data-bs-backdrop="static"', false);
        $page->assertSee('data-bs-keyboard="false"', false);
        $page->assertDontSee('btnReviewModalCloseX');
        $page->assertDontSee('btnReviewModalCancel');
        $page->assertSee('window.openReviewModal(reviewOpts)', false);

        // Sau khi gửi đánh giá hợp lệ -> xóa các cờ session bắt buộc đánh giá
        $submitReview = $this->actingAs($this->candidate)
            ->post(route('danh_gia.store'), [
                'ma_ca_lam' => $shiftCheckout->ma_ca_lam,
                'so_sao' => 5,
                'noi_dung_nhan_xet' => 'Quản lý hướng dẫn tận tình, môi trường chuyên nghiệp!',
            ]);

        $submitReview->assertSessionHas('success');
        $submitReview->assertSessionMissing('show_danh_gia_modal');
        $submitReview->assertSessionMissing('show_danh_gia_ung_vien_modal');
        $submitReview->assertSessionMissing('ma_ca_lam_danh_gia');
    }

    /**
     * 5. Test Admin xem danh sách báo cáo vi phạm
     */
    public function test_admin_can_view_report_list()
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get('/admin/bao-cao-vi-pham');

        $response->assertStatus(200);
        $response->assertViewIs('admin.bao_cao.index');
        $response->assertViewHas('reports');
    }

    /**
     * 6. Test Admin xử lý báo cáo: Cảnh cáo
     */
    public function test_admin_can_warn_reported_user()
    {
        $report = BaoCaoViPham::create([
            'ma_nguoi_vi_pham' => $this->candidate->ma_nguoi_dung,
            'ma_nguoi_bao_cao' => $this->manager->ma_nguoi_dung,
            'ma_ca_lam' => $this->shiftCompleted->ma_ca_lam,
            'loai_vi_pham' => 'thai_do_kem',
            'muc_do_nghiem_trong' => 'trung_binh',
            'mo_ta_chi_tiet' => 'Ứng viên có thái độ chưa chuẩn mực với khách hàng.',
            'trang_thai_xu_ly' => 'cho_xuly',
            'ngay_lap_bao_cao' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->post("/admin/bao-cao-vi-pham/{$report->ma_vi_pham}/xu-ly", [
                'hinh_thuc' => 'canh_cao',
                'ghi_chu_admin' => 'Nhắc nhở lần 1, yêu cầu ứng viên cải thiện tác phong.',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bao_cao_vi_pham', [
            'ma_vi_pham' => $report->ma_vi_pham,
            'trang_thai_xu_ly' => 'da_xuly',
        ]);
    }

    /**
     * 7. Test Admin xử lý báo cáo: Tạm khóa tài khoản
     */
    public function test_admin_can_lock_violating_user()
    {
        $badUser = NguoiDung::create([
            'ho_ten' => 'Người Dùng Vi Phạm',
            'email' => 'bad_user_'.time().'@gmail.com',
            'so_dien_thoai' => '0933'.rand(100000, 999999),
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $report = BaoCaoViPham::create([
            'ma_nguoi_vi_pham' => $badUser->ma_nguoi_dung,
            'ma_nguoi_bao_cao' => $this->manager->ma_nguoi_dung,
            'ma_ca_lam' => $this->shiftCompleted->ma_ca_lam,
            'loai_vi_pham' => 'bung_ca',
            'muc_do_nghiem_trong' => 'nghiem_trong',
            'mo_ta_chi_tiet' => 'Bùng ca liên tiếp nhiều lần.',
            'trang_thai_xu_ly' => 'cho_xuly',
            'ngay_lap_bao_cao' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->post("/admin/bao-cao-vi-pham/{$report->ma_vi_pham}/xu-ly", [
                'hinh_thuc' => 'tam_khoa',
                'ghi_chu_admin' => 'Tạm khóa 14 ngày do tái phạm bùng ca.',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bao_cao_vi_pham', [
            'ma_vi_pham' => $report->ma_vi_pham,
            'trang_thai_xu_ly' => 'da_xuly',
        ]);

        $this->assertDatabaseHas('nguoi_dung', [
            'ma_nguoi_dung' => $badUser->ma_nguoi_dung,
            'trang_thai' => 'tam_khoa',
        ]);
    }
}
