<?php

namespace Tests\Feature;

use App\Models\CaLamViec;
use App\Models\DanhGiaCaLam;
use App\Models\LichSuChamCong;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use App\Models\ThuongHieu;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminRestaurantReviewsTest extends TestCase
{
    protected NguoiDung $admin;

    protected NguoiDung $manager;

    protected NguoiDung $candidate;

    protected NhaHang $nhaHangA;

    protected NhaHang $nhaHangB;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('thuong_hieu')) {
            Schema::create('thuong_hieu', function (Blueprint $table) {
                $table->increments('ma_thuong_hieu');
                $table->string('ten_thuong_hieu', 150);
                $table->string('ma_dinh_danh', 60)->nullable();
                $table->string('bieu_tuong', 255)->nullable();
                $table->string('anh_bia', 255)->nullable();
                $table->string('mo_ta_ngan', 255)->nullable();
                $table->text('gioi_thieu_chi_tiet')->nullable();
                $table->string('loai_hinh_am_thuc', 100)->nullable();
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
                $table->string('email_chi_nhanh', 150)->nullable();
                $table->string('dia_chi_chi_tiet', 255)->default('');
                $table->string('phuong_xa', 100)->nullable();
                $table->string('quan_huyen', 100)->nullable();
                $table->string('tinh_thanh_pho', 100)->nullable();
                $table->string('gio_mo_cua', 20)->nullable();
                $table->string('gio_dong_cua', 20)->nullable();
                $table->string('trang_thai_hoat_dong', 30)->default('dang_hoat_dong');
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
                $table->string('trang_thai_ca', 30)->default('hoan_thanh');
                $table->text('ly_do_huy')->nullable();
                $table->text('yeu_cau_cong_viec')->nullable();
                $table->text('quyen_loi_ca_lam')->nullable();
                $table->timestamp('ngay_tao')->nullable();
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
                $table->integer('so_sao');
                $table->integer('tieu_chi_thai_do')->default(5);
                $table->integer('tieu_chi_chuyen_can')->default(5);
                $table->integer('tieu_chi_hieu_suat')->default(5);
                $table->text('noi_dung_nhan_xet')->nullable();
                $table->dateTime('ngay_danh_gia')->nullable();
            });
        }

        ThuongHieu::create([
            'ten_thuong_hieu' => 'Golden Hotpot',
            'ma_dinh_danh' => 'golden-hotpot',
            'loai_hinh_am_thuc' => 'Lẩu cao cấp',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->admin = NguoiDung::create([
            'ho_ten' => 'Quản Trị Hệ Thống',
            'email' => 'admin.review@goldenshift.vn',
            'so_dien_thoai' => '0901112223',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_tri_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->manager = NguoiDung::create([
            'ho_ten' => 'Trần Quản Lý',
            'email' => 'manager.review@goldenshift.vn',
            'so_dien_thoai' => '0902223334',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_ly_nha_hang',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->candidate = NguoiDung::create([
            'ho_ten' => 'Nguyễn Minh Anh',
            'email' => 'candidate.review@goldenshift.vn',
            'so_dien_thoai' => '0903334445',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->nhaHangA = NhaHang::create([
            'ma_thuong_hieu' => 1,
            'ma_quan_ly' => $this->manager->ma_nguoi_dung,
            'ten_nha_hang' => 'Golden Hotpot Đồng Khởi',
            'so_dien_thoai_lien_he' => '0283822111',
            'dia_chi_chi_tiet' => '123 Đồng Khởi',
            'quan_huyen' => 'Quận 1',
            'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
            'trang_thai_hoat_dong' => 'dang_hoat_dong',
        ]);

        $this->nhaHangB = NhaHang::create([
            'ma_thuong_hieu' => 1,
            'ma_quan_ly' => $this->manager->ma_nguoi_dung,
            'ten_nha_hang' => 'Golden Hotpot Lê Lợi',
            'so_dien_thoai_lien_he' => '0283822999',
            'dia_chi_chi_tiet' => '45 Lê Lợi',
            'quan_huyen' => 'Quận 1',
            'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
            'trang_thai_hoat_dong' => 'dang_hoat_dong',
        ]);

        $caA1 = CaLamViec::create([
            'ma_nha_hang' => $this->nhaHangA->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Phục vụ bàn ca sáng',
            'ngay_lam_viec' => now()->subDays(3)->format('Y-m-d'),
            'gio_bat_dau' => '08:00:00',
            'gio_ket_thuc' => '12:00:00',
            'trang_thai_ca' => 'hoan_thanh',
        ]);

        $caA2 = CaLamViec::create([
            'ma_nha_hang' => $this->nhaHangA->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Phụ bếp ca chiều',
            'ngay_lam_viec' => now()->subDays(2)->format('Y-m-d'),
            'gio_bat_dau' => '13:00:00',
            'gio_ket_thuc' => '17:00:00',
            'trang_thai_ca' => 'hoan_thanh',
        ]);

        $caA3 = CaLamViec::create([
            'ma_nha_hang' => $this->nhaHangA->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Thu ngân ca tối',
            'ngay_lam_viec' => now()->subDays(1)->format('Y-m-d'),
            'gio_bat_dau' => '18:00:00',
            'gio_ket_thuc' => '22:00:00',
            'trang_thai_ca' => 'hoan_thanh',
        ]);

        $caB1 = CaLamViec::create([
            'ma_nha_hang' => $this->nhaHangB->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Pha chế chi nhánh khác',
            'ngay_lam_viec' => now()->subDays(1)->format('Y-m-d'),
            'gio_bat_dau' => '09:00:00',
            'gio_ket_thuc' => '13:00:00',
            'trang_thai_ca' => 'hoan_thanh',
        ]);

        LichSuChamCong::create([
            'ma_ca_lam' => $caA1->ma_ca_lam,
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'thoi_gian_vao_thuc_te' => now()->subDays(3)->setTime(8, 0),
            'thoi_gian_ra_thuc_te' => now()->subDays(3)->setTime(12, 5),
            'so_gio_lam_thuc_te' => 4.0,
            'trang_thai_cham_cong' => 'da_xac_nhan',
        ]);

        // 3 đánh giá của ứng viên dành cho Nhà hàng A: 5★, 2★, 4★ (TB = 11 / 3 = 3.7)
        DanhGiaCaLam::create([
            'ma_ca_lam' => $caA1->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->candidate->ma_nguoi_dung,
            'ma_nguoi_duoc_danh_gia' => $this->manager->ma_nguoi_dung,
            'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
            'so_sao' => 5,
            'noi_dung_nhan_xet' => 'Môi trường cực kỳ chuyên nghiệp và hỗ trợ nhiệt tình.',
            'ngay_danh_gia' => now()->subDays(3),
        ]);

        DanhGiaCaLam::create([
            'ma_ca_lam' => $caA2->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->candidate->ma_nguoi_dung,
            'ma_nguoi_duoc_danh_gia' => $this->manager->ma_nguoi_dung,
            'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
            'so_sao' => 2,
            'noi_dung_nhan_xet' => 'Giờ cao điểm hơi thiếu hướng dẫn cho nhân sự mới.',
            'ngay_danh_gia' => now()->subDays(2),
        ]);

        DanhGiaCaLam::create([
            'ma_ca_lam' => $caA3->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->candidate->ma_nguoi_dung,
            'ma_nguoi_duoc_danh_gia' => $this->manager->ma_nguoi_dung,
            'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
            'so_sao' => 4,
            'noi_dung_nhan_xet' => 'Thanh toán đúng hạn, đồng nghiệp thân thiện.',
            'ngay_danh_gia' => now()->subDays(1),
        ]);

        // Đánh giá ngược lại (Quản lý chấm Ứng viên) -> KHÔNG được tính vào đánh giá Nhà hàng A
        DanhGiaCaLam::create([
            'ma_ca_lam' => $caA1->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->manager->ma_nguoi_dung,
            'ma_nguoi_duoc_danh_gia' => $this->candidate->ma_nguoi_dung,
            'loai_danh_gia' => 'nha_hang_danh_gia_ung_vien',
            'so_sao' => 1,
            'noi_dung_nhan_xet' => 'Nhận xét quản lý chấm ứng viên - không được lẫn.',
            'ngay_danh_gia' => now()->subDays(3),
        ]);

        // Đánh giá của Nhà hàng B -> KHÔNG được lẫn vào Nhà hàng A
        DanhGiaCaLam::create([
            'ma_ca_lam' => $caB1->ma_ca_lam,
            'ma_nguoi_danh_gia' => $this->candidate->ma_nguoi_dung,
            'ma_nguoi_duoc_danh_gia' => $this->manager->ma_nguoi_dung,
            'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
            'so_sao' => 5,
            'noi_dung_nhan_xet' => 'Nhận xét thuộc chi nhánh Lê Lợi.',
            'ngay_danh_gia' => now()->subDays(1),
        ]);
    }

    public function test_admin_categories_restaurant_tab_has_review_button_and_no_quick_toggle_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.categories.index', ['tab' => 'nha_hang']));

        $response->assertStatus(200);
        $response->assertSee(route('admin.nha-hang.danh-gia', $this->nhaHangA->ma_nha_hang));
        $response->assertSee('btn btn-outline-info btn-sm rounded-pill');
        $response->assertSee('Xem đánh giá');
        $response->assertDontSee('admin/categories/nha-hang/'.$this->nhaHangA->ma_nha_hang.'/toggle-status');
        $response->assertDontSee('title="Chuyển trạng thái"');
    }

    public function test_admin_can_view_restaurant_reviews_and_summary(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.nha-hang.danh-gia', $this->nhaHangA->ma_nha_hang));

        $response->assertStatus(200);
        $response->assertViewIs('admin.nha_hang.danh_gia');
        $response->assertViewHas('diemTrungBinh', 3.7);
        $response->assertViewHas('tongLuot', 3);
        $response->assertSee('Danh sách đánh giá của nhà hàng:');
        $response->assertSee($this->nhaHangA->ten_nha_hang);
        $response->assertSee('Nguyễn Minh Anh');
        $response->assertSee('Môi trường cực kỳ chuyên nghiệp và hỗ trợ nhiệt tình.');
        $response->assertSee('Giờ cao điểm hơi thiếu hướng dẫn cho nhân sự mới.');
        $response->assertSee('Thanh toán đúng hạn, đồng nghiệp thân thiện.');
        $response->assertDontSee('Nhận xét quản lý chấm ứng viên - không được lẫn.');
        $response->assertDontSee('Nhận xét thuộc chi nhánh Lê Lợi.');
    }

    public function test_admin_can_sort_reviews_high_to_low_and_low_to_high(): void
    {
        $resDesc = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.nha-hang.danh-gia', [
                'ma_nha_hang' => $this->nhaHangA->ma_nha_hang,
                'sort' => 'cao_den_thap',
            ]));

        $resDesc->assertStatus(200);
        $descRatings = $resDesc->viewData('danhGiaList')->pluck('so_sao')->all();
        $this->assertSame([5, 4, 2], $descRatings);

        $resAsc = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.nha-hang.danh-gia', [
                'ma_nha_hang' => $this->nhaHangA->ma_nha_hang,
                'sort' => 'thap_den_cao',
            ]));

        $resAsc->assertStatus(200);
        $ascRatings = $resAsc->viewData('danhGiaList')->pluck('so_sao')->all();
        $this->assertSame([2, 4, 5], $ascRatings);
    }

    public function test_admin_can_filter_reviews_by_specific_star(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.nha-hang.danh-gia', [
                'ma_nha_hang' => $this->nhaHangA->ma_nha_hang,
                'so_sao' => 5,
            ]));

        $response->assertStatus(200);
        $filtered = $response->viewData('danhGiaList');
        $this->assertCount(1, $filtered);
        $this->assertSame(5, $filtered->first()->so_sao);
        $response->assertViewHas('tongLuot', 3);
        $response->assertViewHas('diemTrungBinh', 3.7);
    }

    public function test_non_admin_cannot_access_restaurant_reviews(): void
    {
        $response = $this->actingAs($this->manager)
            ->withSession(['user' => $this->manager])
            ->get(route('admin.nha-hang.danh-gia', $this->nhaHangA->ma_nha_hang));

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    public function test_missing_restaurant_redirects_with_error(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.nha-hang.danh-gia', 999999));

        $response->assertRedirect(route('admin.categories.index', ['tab' => 'nha_hang']));
        $response->assertSessionHas('error', 'Không tìm thấy chi nhánh nhà hàng cần xem đánh giá.');
    }
}
