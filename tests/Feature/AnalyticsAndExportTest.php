<?php

namespace Tests\Feature;

use App\Models\BaoCaoViPham;
use App\Models\CaLamViec;
use App\Models\DanhGiaCaLam;
use App\Models\HoSoUngTuyen;
use App\Models\LichSuChamCong;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use App\Models\SoThuNhap;
use App\Models\ThuongHieu;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnalyticsAndExportTest extends TestCase
{
    protected NguoiDung $admin;

    protected NguoiDung $managerA;

    protected NguoiDung $managerB;

    protected NguoiDung $candTop;

    protected NguoiDung $candSecond;

    protected ThuongHieu $brandHotpot;

    protected ThuongHieu $brandBbq;

    protected NhaHang $branchA;

    protected NhaHang $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchemaTables();
        $this->seedAnalyticsFixtures();
    }

    private function createSchemaTables(): void
    {
        if (! Schema::hasTable('thuong_hieu')) {
            Schema::create('thuong_hieu', function (Blueprint $table) {
                $table->increments('ma_thuong_hieu');
                $table->string('ten_thuong_hieu', 150);
                $table->string('ma_dinh_danh', 60)->nullable();
                $table->string('trang_thai', 30)->default('hoat_dong');
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

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

        if (! Schema::hasTable('nha_hang')) {
            Schema::create('nha_hang', function (Blueprint $table) {
                $table->increments('ma_nha_hang');
                $table->integer('ma_thuong_hieu')->default(1);
                $table->integer('ma_quan_ly')->nullable();
                $table->string('ten_nha_hang', 200);
                $table->string('so_dien_thoai_lien_he', 20)->nullable();
                $table->string('dia_chi_chi_tiet', 255)->default('');
                $table->string('trang_thai_hoat_dong', 30)->default('dang_hoat_dong');
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        if (! Schema::hasTable('ca_lam_viec')) {
            Schema::create('ca_lam_viec', function (Blueprint $table) {
                $table->increments('ma_ca_lam');
                $table->integer('ma_nha_hang');
                $table->string('ten_vi_tri_tuyen_dung', 150);
                $table->date('ngay_lam_viec');
                $table->time('gio_bat_dau');
                $table->time('gio_ket_thuc');
                $table->decimal('muc_luong_theo_gio', 12, 2)->default(30000);
                $table->integer('so_luong_tuyen')->default(2);
                $table->integer('so_luong_da_tuyen')->default(1);
                $table->string('trang_thai_ca', 30)->default('hoan_thanh');
                $table->text('ly_do_huy')->nullable();
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        if (! Schema::hasTable('ho_so_ung_tuyen')) {
            Schema::create('ho_so_ung_tuyen', function (Blueprint $table) {
                $table->increments('ma_ung_tuyen');
                $table->integer('ma_ca_lam');
                $table->integer('ma_nguoi_dung');
                $table->dateTime('ngay_nop_don')->nullable();
                $table->string('trang_thai_ung_tuyen', 30)->default('cho_duyet');
                $table->integer('nguoi_duyet')->nullable();
                $table->dateTime('ngay_duyet')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        if (! Schema::hasTable('lich_su_cham_cong')) {
            Schema::create('lich_su_cham_cong', function (Blueprint $table) {
                $table->increments('ma_cham_cong');
                $table->integer('ma_ung_tuyen')->nullable();
                $table->integer('ma_nguoi_dung');
                $table->integer('ma_ca_lam');
                $table->dateTime('thoi_gian_vao_thuc_te')->nullable();
                $table->dateTime('thoi_gian_ra_thuc_te')->nullable();
                $table->decimal('so_gio_lam_thuc_te', 5, 2)->default(0);
                $table->string('trang_thai_cham_cong', 30)->default('da_xac_nhan');
                $table->string('hinh_thuc_xac_thuc', 30)->default('quan_ly_xac_nhan');
                $table->timestamp('ngay_tao')->nullable();
            });
        }

        if (! Schema::hasTable('so_thu_nhap')) {
            Schema::create('so_thu_nhap', function (Blueprint $table) {
                $table->increments('ma_so_thu_nhap');
                $table->integer('ma_nguoi_dung');
                $table->string('ky_luong', 30);
                $table->integer('tong_so_ca_lam')->default(0);
                $table->decimal('tong_gio_lam', 6, 2)->default(0);
                $table->decimal('tong_tien_luong_co_ban', 12, 2)->default(0);
                $table->decimal('tong_tien_thuong', 12, 2)->default(0);
                $table->decimal('tong_tien_phat', 12, 2)->default(0);
                $table->decimal('thuc_nhan', 12, 2)->default(0);
                $table->string('trang_thai_thanh_toan', 30)->default('chua_thanh_toan');
                $table->dateTime('ngay_thanh_toan')->nullable();
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
                $table->integer('so_sao')->default(5);
                $table->integer('tieu_chi_thai_do')->default(5);
                $table->integer('tieu_chi_chuyen_can')->default(5);
                $table->integer('tieu_chi_hieu_suat')->default(5);
                $table->text('noi_dung_nhan_xet')->nullable();
                $table->dateTime('ngay_danh_gia')->nullable();
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
                $table->string('trang_thai_xu_ly', 50)->default('cho_xuly');
                $table->dateTime('ngay_lap_bao_cao')->nullable();
            });
        }
    }

    private function seedAnalyticsFixtures(): void
    {
        $today = Carbon::today();
        $lastMonth = Carbon::now()->subMonth();

        $this->brandHotpot = ThuongHieu::create([
            'ten_thuong_hieu' => 'Golden Hotpot',
            'ma_dinh_danh' => 'golden-hotpot',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->brandBbq = ThuongHieu::create([
            'ten_thuong_hieu' => 'Golden BBQ',
            'ma_dinh_danh' => 'golden-bbq',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->admin = NguoiDung::create([
            'ho_ten' => 'Admin Thống Kê',
            'email' => 'admin.tk@goldenshift.vn',
            'so_dien_thoai' => '0909000001',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_tri_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->managerA = NguoiDung::create([
            'ho_ten' => 'Nguyễn Thị Bảo Châu',
            'email' => 'chau.tk@goldenshift.vn',
            'so_dien_thoai' => '0909000002',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
            'trang_thai' => 'da_duyet',
        ]);

        $this->managerB = NguoiDung::create([
            'ho_ten' => 'Ngô Thị Mỹ Lệ',
            'email' => 'le.tk@goldenshift.vn',
            'so_dien_thoai' => '0909000003',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 2,
            'trang_thai' => 'da_duyet',
        ]);

        $this->candTop = NguoiDung::create([
            'ho_ten' => 'Phạm Thu Hương',
            'email' => 'huong.tk@gmail.com',
            'so_dien_thoai' => '0909000004',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->candSecond = NguoiDung::create([
            'ho_ten' => 'Vũ Đình Trọng',
            'email' => 'trong.tk@gmail.com',
            'so_dien_thoai' => '0909000005',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->branchA = NhaHang::create([
            'ma_thuong_hieu' => $this->brandHotpot->ma_thuong_hieu,
            'ma_quan_ly' => $this->managerA->ma_nguoi_dung,
            'ten_nha_hang' => 'Golden Hotpot - Đồng Khởi',
            'so_dien_thoai_lien_he' => '0283800001',
            'dia_chi_chi_tiet' => '123 Đồng Khởi',
            'trang_thai_hoat_dong' => 'dang_hoat_dong',
        ]);

        $this->branchB = NhaHang::create([
            'ma_thuong_hieu' => $this->brandBbq->ma_thuong_hieu,
            'ma_quan_ly' => $this->managerB->ma_nguoi_dung,
            'ten_nha_hang' => 'Golden BBQ - Landmark 81',
            'so_dien_thoai_lien_he' => '0283800002',
            'dia_chi_chi_tiet' => '720A Điện Biên Phủ',
            'trang_thai_hoat_dong' => 'dang_hoat_dong',
        ]);

        // Chi nhánh A: 3 ca (1 đúng giờ hôm nay, 1 đi muộn tháng trước, 1 đã hủy hôm nay)
        $caA1 = CaLamViec::create([
            'ma_nha_hang' => $this->branchA->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Phục vụ',
            'ngay_lam_viec' => $today->format('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '14:00:00',
            'muc_luong_theo_gio' => 30000,
            'trang_thai_ca' => 'hoan_thanh',
        ]);

        $caA2 = CaLamViec::create([
            'ma_nha_hang' => $this->branchA->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Pha chế',
            'ngay_lam_viec' => $lastMonth->format('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '12:00:00',
            'muc_luong_theo_gio' => 40000,
            'trang_thai_ca' => 'hoan_thanh',
        ]);

        $caA3 = CaLamViec::create([
            'ma_nha_hang' => $this->branchA->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Tiếp thực',
            'ngay_lam_viec' => $today->format('Y-m-d'),
            'gio_bat_dau' => '18:00:00',
            'gio_ket_thuc' => '22:00:00',
            'muc_luong_theo_gio' => 28000,
            'trang_thai_ca' => 'da_huy',
            'ly_do_huy' => 'Thay đổi lịch sự kiện',
        ]);

        // Chi nhánh B: 1 ca
        $caB1 = CaLamViec::create([
            'ma_nha_hang' => $this->branchB->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Bếp',
            'ngay_lam_viec' => $today->format('Y-m-d'),
            'gio_bat_dau' => '09:00:00',
            'gio_ket_thuc' => '14:00:00',
            'muc_luong_theo_gio' => 35000,
            'trang_thai_ca' => 'hoan_thanh',
        ]);

        // Đơn ứng tuyển: 4 đơn (3 da_nhan, 1 tu_choi => 75% thành công toàn mạng lưới; riêng Chi nhánh A: 2 da_nhan / 3 = 66.7%)
        $utA1 = HoSoUngTuyen::create([
            'ma_ca_lam' => $caA1->ma_ca_lam,
            'ma_nguoi_dung' => $this->candTop->ma_nguoi_dung,
            'ngay_nop_don' => $today->copy()->setTime(7, 0),
            'trang_thai_ung_tuyen' => 'da_nhan',
        ]);

        $utA2 = HoSoUngTuyen::create([
            'ma_ca_lam' => $caA2->ma_ca_lam,
            'ma_nguoi_dung' => $this->candSecond->ma_nguoi_dung,
            'trang_thai_ung_tuyen' => 'da_nhan',
        ]);
        $utA2->forceFill(['ngay_nop_don' => $lastMonth->copy()->setTime(9, 0)])->save();

        HoSoUngTuyen::create([
            'ma_ca_lam' => $caA3->ma_ca_lam,
            'ma_nguoi_dung' => $this->candSecond->ma_nguoi_dung,
            'ngay_nop_don' => $today->copy()->setTime(10, 0),
            'trang_thai_ung_tuyen' => 'tu_choi',
        ]);

        $utB1 = HoSoUngTuyen::create([
            'ma_ca_lam' => $caB1->ma_ca_lam,
            'ma_nguoi_dung' => $this->candSecond->ma_nguoi_dung,
            'ngay_nop_don' => $today->copy()->setTime(8, 0),
            'trang_thai_ung_tuyen' => 'da_nhan',
        ]);

        // Chấm công chi nhánh A:
        // caA1: candTop 6h đúng giờ (07:55 < 08:00), lương = 180.000
        LichSuChamCong::create([
            'ma_ung_tuyen' => $utA1->ma_ung_tuyen,
            'ma_nguoi_dung' => $this->candTop->ma_nguoi_dung,
            'ma_ca_lam' => $caA1->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => $today->copy()->setTime(7, 55),
            'thoi_gian_ra_thuc_te' => $today->copy()->setTime(13, 55),
            'so_gio_lam_thuc_te' => 6.0,
            'trang_thai_cham_cong' => 'da_xac_nhan',
            'ngay_tao' => $today->copy()->setTime(14, 0),
        ]);

        // caA2: candSecond 4h đi muộn (08:25 > 08:00 dù đã da_xac_nhan), lương = 160.000
        LichSuChamCong::create([
            'ma_ung_tuyen' => $utA2->ma_ung_tuyen,
            'ma_nguoi_dung' => $this->candSecond->ma_nguoi_dung,
            'ma_ca_lam' => $caA2->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => $lastMonth->copy()->setTime(8, 25),
            'thoi_gian_ra_thuc_te' => $lastMonth->copy()->setTime(12, 25),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'da_xac_nhan',
            'ngay_tao' => $lastMonth->copy()->setTime(12, 30),
        ]);

        // Chấm công chi nhánh B: candSecond 5h đúng giờ, lương = 175.000
        LichSuChamCong::create([
            'ma_ung_tuyen' => $utB1->ma_ung_tuyen,
            'ma_nguoi_dung' => $this->candSecond->ma_nguoi_dung,
            'ma_ca_lam' => $caB1->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => $today->copy()->setTime(8, 58),
            'thoi_gian_ra_thuc_te' => $today->copy()->setTime(13, 58),
            'so_gio_lam_thuc_te' => 5.0,
            'trang_thai_cham_cong' => 'da_xac_nhan',
            'ngay_tao' => $today->copy()->setTime(14, 5),
        ]);

        // Sổ thu nhập đã thanh toán (2.000.000 đ hôm nay)
        SoThuNhap::create([
            'ma_nguoi_dung' => $this->candTop->ma_nguoi_dung,
            'ky_luong' => 'Tháng '.$today->format('m/Y'),
            'tong_so_ca_lam' => 1,
            'tong_gio_lam' => 6.0,
            'tong_tien_luong_co_ban' => 2000000,
            'thuc_nhan' => 2000000,
            'trang_thai_thanh_toan' => 'da_thanh_toan',
            'ngay_thanh_toan' => $today->copy()->setTime(15, 0),
            'ngay_tao' => $today->copy()->setTime(15, 0),
        ]);

        // Đánh giá:
        DanhGiaCaLam::create([
            'ma_ca_lam' => $caA1->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->candTop->ma_nguoi_dung,
            'ma_nguoi_duoc_danh_gia' => $this->managerA->ma_nguoi_dung,
            'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
            'so_sao' => 5,
            'noi_dung_nhan_xet' => 'Nhà hàng hỗ trợ tuyệt vời',
            'ngay_danh_gia' => $today->copy()->setTime(14, 30),
        ]);

        DanhGiaCaLam::create([
            'ma_ca_lam' => $caA1->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->managerA->ma_nguoi_dung,
            'ma_nguoi_duoc_danh_gia' => $this->candTop->ma_nguoi_dung,
            'loai_danh_gia' => 'nha_hang_danh_gia_ung_vien',
            'so_sao' => 5,
            'noi_dung_nhan_xet' => 'Ứng viên xuất sắc',
            'ngay_danh_gia' => $today->copy()->setTime(14, 35),
        ]);

        DanhGiaCaLam::create([
            'ma_ca_lam' => $caA2->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->managerA->ma_nguoi_dung,
            'ma_nguoi_duoc_danh_gia' => $this->candSecond->ma_nguoi_dung,
            'loai_danh_gia' => 'nha_hang_danh_gia_ung_vien',
            'so_sao' => 3,
            'noi_dung_nhan_xet' => 'Đến trễ 25 phút',
            'ngay_danh_gia' => $lastMonth->copy()->setTime(13, 0),
        ]);

        // Báo cáo vi phạm: 1 bùng ca (caA3) + 1 đi muộn (caA2)
        BaoCaoViPham::create([
            'ma_nguoi_vi_pham' => $this->candSecond->ma_nguoi_dung,
            'ma_nguoi_bao_cao' => $this->managerA->ma_nguoi_dung,
            'ma_ca_lam' => $caA3->ma_ca_lam,
            'loai_vi_pham' => 'bung_ca',
            'mo_ta_chi_tiet' => 'Tự ý bỏ ca không báo trước.',
            'trang_thai_xu_ly' => 'cho_xuly',
            'ngay_lap_bao_cao' => $today->copy()->setTime(18, 30),
        ]);

        BaoCaoViPham::create([
            'ma_nguoi_vi_pham' => $this->candSecond->ma_nguoi_dung,
            'ma_nguoi_bao_cao' => $this->managerA->ma_nguoi_dung,
            'ma_ca_lam' => $caA2->ma_ca_lam,
            'loai_vi_pham' => 'di_muon_ve_som',
            'mo_ta_chi_tiet' => 'Đi làm muộn 25 phút.',
            'trang_thai_xu_ly' => 'da_xuly',
            'ngay_lap_bao_cao' => $lastMonth->copy()->setTime(13, 15),
        ]);
    }

    public function test_admin_can_view_analytics_dashboard_with_kpis_charts_and_tables(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.thong_ke.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.thong_ke.index');

        $analytics = $response->viewData('analytics');
        $this->assertSame(4, $analytics['kpis']['tong_ca_lam']);
        $this->assertSame(4, $analytics['kpis']['tong_don_ung_tuyen']);
        $this->assertSame(3, $analytics['kpis']['don_da_nhan']);
        $this->assertEquals(75.0, $analytics['kpis']['ty_le_ung_tuyen_thanh_cong']);
        $this->assertEquals(2000000.0, $analytics['kpis']['chi_phi_da_thanh_toan']);
        $this->assertEquals(515000.0, $analytics['kpis']['tong_quy_luong_cham_cong']);
        $this->assertEquals(4.3, $analytics['kpis']['diem_danh_gia_tb']);

        $response->assertSee('https://cdn.jsdelivr.net/npm/chart.js');
        $response->assertSee('Golden Hotpot');
        $response->assertSee('Golden BBQ');
        $response->assertSee('Bùng ca / Tự ý bỏ ca không báo trước');
        $response->assertSee('Xuất Báo cáo Excel / CSV');
    }

    public function test_admin_analytics_supports_ajax_date_and_brand_filtering(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->getJson(route('admin.thong_ke.index', [
                'preset' => 'hom_nay',
                'ma_thuong_hieu' => $this->brandHotpot->ma_thuong_hieu,
            ]));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        // Chi nhánh A hôm nay có 2 ca (caA1, caA3) và 2 đơn (utA1, hủy/từ chối utA3)
        $response->assertJsonPath('analytics.kpis.tong_ca_lam', 2);
        $response->assertJsonPath('analytics.kpis.tong_don_ung_tuyen', 2);
        $response->assertJsonPath('analytics.kpis.don_da_nhan', 1);
        $response->assertJsonPath('analytics.kpis.tong_quy_luong_cham_cong', 180000);
        $response->assertJsonCount(1, 'analytics.theo_thuong_hieu');
    }

    public function test_admin_can_export_utf8_bom_csv_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.thong_ke.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Golden Hotpot', $content);
        $this->assertStringContainsString('2.000.000', $content);
    }

    public function test_non_admin_is_blocked_from_admin_analytics_and_export(): void
    {
        $resIndex = $this->actingAs($this->managerA)
            ->withSession(['user' => $this->managerA])
            ->get(route('admin.thong_ke.index'));
        $resIndex->assertRedirect('/');

        $resExport = $this->actingAs($this->candTop)
            ->withSession(['user' => $this->candTop])
            ->get(route('admin.thong_ke.export'));
        $resExport->assertRedirect('/');
    }

    public function test_manager_can_view_branch_scoped_analytics_top_candidates_and_payroll(): void
    {
        $response = $this->actingAs($this->managerA)
            ->withSession(['user' => $this->managerA])
            ->get(route('quan_ly.thong_ke.index'));

        $response->assertStatus(200);
        $response->assertViewIs('quan_ly.thong_ke.index');

        $analytics = $response->viewData('analytics');
        // Chỉ tính Chi nhánh A (3 ca, không tính caB1 175.000 của Chi nhánh B)
        $this->assertSame(3, $analytics['kpis']['tong_ca_lam']);
        $this->assertEquals(10.0, $analytics['kpis']['tong_gio_lam']);
        $this->assertEquals(340000.0, $analytics['kpis']['tong_quy_luong']);

        // Hiệu suất ca: 1 đúng giờ (caA1), 1 đi muộn (caA2), 2 hủy/bùng ca (caA3 bị hủy + báo cáo bùng ca)
        $this->assertSame(1, $analytics['hieu_suat_ca']['dung_gio']);
        $this->assertSame(1, $analytics['hieu_suat_ca']['di_muon']);
        $this->assertSame(2, $analytics['hieu_suat_ca']['huy_bung_ca']);

        // Mới chỉ có 1 ca mỗi ứng viên nên chưa đạt ngưỡng >= 2 ca để xếp hạng Top 5
        $this->assertEmpty($analytics['top_ung_vien']);

        // Bổ sung ca thứ 2 hoàn thành đúng giờ cho Phạm Thu Hương tại Chi nhánh A
        $today = Carbon::today();
        $caA4 = CaLamViec::create([
            'ma_nha_hang' => $this->branchA->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Phục vụ',
            'ngay_lam_viec' => $today->format('Y-m-d'),
            'gio_bat_dau' => '15:00:00',
            'gio_ket_thuc' => '19:00:00',
            'muc_luong_theo_gio' => 30000,
            'trang_thai_ca' => 'hoan_thanh',
        ]);
        $utA4 = HoSoUngTuyen::create([
            'ma_ca_lam' => $caA4->ma_ca_lam,
            'ma_nguoi_dung' => $this->candTop->ma_nguoi_dung,
            'ngay_nop_don' => $today->copy()->setTime(14, 0),
            'trang_thai_ung_tuyen' => 'da_nhan',
        ]);
        LichSuChamCong::create([
            'ma_ung_tuyen' => $utA4->ma_ung_tuyen,
            'ma_nguoi_dung' => $this->candTop->ma_nguoi_dung,
            'ma_ca_lam' => $caA4->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => $today->copy()->setTime(14, 55),
            'thoi_gian_ra_thuc_te' => $today->copy()->setTime(18, 55),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'da_xac_nhan',
            'ngay_tao' => $today->copy()->setTime(19, 0),
        ]);
        DanhGiaCaLam::create([
            'ma_ca_lam' => $caA4->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->managerA->ma_nguoi_dung,
            'ma_nguoi_duoc_danh_gia' => $this->candTop->ma_nguoi_dung,
            'loai_danh_gia' => 'nha_hang_danh_gia_ung_vien',
            'so_sao' => 5,
            'noi_dung_nhan_xet' => 'Tiếp tục hoàn thành tốt ca chiều',
            'ngay_danh_gia' => $today->copy()->setTime(19, 10),
        ]);

        $rankedResponse = $this->actingAs($this->managerA)
            ->withSession(['user' => $this->managerA])
            ->get(route('quan_ly.thong_ke.index'));
        $rankedResponse->assertStatus(200);
        $rankedAnalytics = $rankedResponse->viewData('analytics');

        $this->assertCount(1, $rankedAnalytics['top_ung_vien']);
        $this->assertSame('Phạm Thu Hương', $rankedAnalytics['top_ung_vien'][0]['ho_ten']);
        $this->assertSame(2, $rankedAnalytics['top_ung_vien'][0]['so_ca']);
        $this->assertEquals(10.0, $rankedAnalytics['top_ung_vien'][0]['tong_gio_lam']);
        $this->assertEquals(5.0, $rankedAnalytics['top_ung_vien'][0]['diem_danh_gia_tb']);
        $this->assertEquals(100.0, $rankedAnalytics['top_ung_vien'][0]['ty_le_dung_gio']);
        $this->assertEquals(62.5, $rankedAnalytics['top_ung_vien'][0]['diem_xuat_sac']);

        $rankedResponse->assertDontSee('Golden BBQ - Landmark 81');
        $rankedResponse->assertSee('https://cdn.jsdelivr.net/npm/chart.js');
    }

    public function test_manager_analytics_supports_ajax_and_weekly_grouping(): void
    {
        $response = $this->actingAs($this->managerA)
            ->withSession(['user' => $this->managerA])
            ->getJson(route('quan_ly.thong_ke.index', [
                'preset' => 'hom_nay',
                'nhom_ky' => 'tuan',
            ]));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('nhom_ky', 'tuan');
        $response->assertJsonPath('analytics.kpis.tong_quy_luong', 180000);
        $firstWeekLabel = $response->json('analytics.quy_luong_theo_ky.0.ky_label');
        $this->assertStringStartsWith('Tuần ', (string) $firstWeekLabel);
    }

    public function test_manager_can_export_branch_csv_report(): void
    {
        $today = Carbon::today();
        $caA4 = CaLamViec::create([
            'ma_nha_hang' => $this->branchA->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Nhân viên Phục vụ',
            'ngay_lam_viec' => $today->format('Y-m-d'),
            'gio_bat_dau' => '15:00:00',
            'gio_ket_thuc' => '19:00:00',
            'muc_luong_theo_gio' => 30000,
            'trang_thai_ca' => 'hoan_thanh',
        ]);
        $utA4 = HoSoUngTuyen::create([
            'ma_ca_lam' => $caA4->ma_ca_lam,
            'ma_nguoi_dung' => $this->candTop->ma_nguoi_dung,
            'ngay_nop_don' => $today->copy()->setTime(14, 0),
            'trang_thai_ung_tuyen' => 'da_nhan',
        ]);
        LichSuChamCong::create([
            'ma_ung_tuyen' => $utA4->ma_ung_tuyen,
            'ma_nguoi_dung' => $this->candTop->ma_nguoi_dung,
            'ma_ca_lam' => $caA4->ma_ca_lam,
            'thoi_gian_vao_thuc_te' => $today->copy()->setTime(14, 55),
            'thoi_gian_ra_thuc_te' => $today->copy()->setTime(18, 55),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'da_xac_nhan',
            'ngay_tao' => $today->copy()->setTime(19, 0),
        ]);

        $response = $this->actingAs($this->managerA)
            ->withSession(['user' => $this->managerA])
            ->get(route('quan_ly.thong_ke.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Golden Hotpot - Đồng Khởi', $content);
        $this->assertStringContainsString('Phạm Thu Hương', $content);
        $this->assertStringContainsString('/ 100', $content);
        $this->assertStringNotContainsString('Golden BBQ - Landmark 81', $content);
    }

    public function test_candidate_cannot_access_manager_analytics(): void
    {
        $response = $this->actingAs($this->candTop)
            ->withSession(['user' => $this->candTop])
            ->get(route('quan_ly.thong_ke.index'));

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }
}
