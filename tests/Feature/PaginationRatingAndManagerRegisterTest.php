<?php

namespace Tests\Feature;

use App\Models\CaLamViec;
use App\Models\DanhGiaCaLam;
use App\Models\HoSoUngTuyen;
use App\Models\HoSoUngVien;
use App\Models\LichSuChamCong;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use App\Models\ThuongHieu;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaginationRatingAndManagerRegisterTest extends TestCase
{
    protected NguoiDung $admin;

    protected NguoiDung $manager;

    protected NguoiDung $candidate;

    protected ThuongHieu $brand;

    protected NhaHang $nhaHang;

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

        if (! Schema::hasTable('ho_so_ung_vien')) {
            Schema::create('ho_so_ung_vien', function (Blueprint $table) {
                $table->increments('ma_ho_so');
                $table->integer('ma_nguoi_dung');
                $table->date('ngay_sinh')->nullable();
                $table->string('gioi_tinh', 10)->default('khac');
                $table->string('so_cccd', 20)->nullable();
                $table->text('gioi_thieu_ban_than')->nullable();
                $table->string('trang_thai_ho_so', 40)->default('san_sang_nhan_viec');
                $table->decimal('diem_danh_gia', 3, 1)->default(0.0);
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
                $table->integer('so_luong_da_tuyen')->default(0);
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
                $table->dateTime('ngay_duyet')->nullable();
                $table->timestamp('ngay_nop_don')->nullable();
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
                $table->string('trang_thai_thanh_toan', 30)->default('da_thanh_toan');
                $table->dateTime('ngay_thanh_toan')->nullable();
                $table->text('ghi_chu')->nullable();
                $table->timestamp('ngay_tao')->nullable();
            });
        }

        $this->brand = ThuongHieu::create([
            'ten_thuong_hieu' => 'Golden Hotpot',
            'ma_dinh_danh' => 'golden-hotpot',
            'loai_hinh_am_thuc' => 'Lẩu cao cấp',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->admin = NguoiDung::create([
            'ho_ten' => 'Quản Trị Hệ Thống',
            'email' => 'admin.test@goldenshift.vn',
            'so_dien_thoai' => '0901111111',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_tri_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->nhaHang = NhaHang::create([
            'ma_thuong_hieu' => $this->brand->ma_thuong_hieu,
            'ten_nha_hang' => 'Golden Hotpot Đồng Khởi',
            'so_dien_thoai_lien_he' => '0283822111',
            'dia_chi_chi_tiet' => '123 Đồng Khởi',
            'quan_huyen' => 'Quận 1',
            'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
            'trang_thai_hoat_dong' => 'dang_hoat_dong',
        ]);

        $this->manager = NguoiDung::create([
            'ho_ten' => 'Nguyễn Quản Lý',
            'email' => 'manager.test@goldenshift.vn',
            'so_dien_thoai' => '0902222222',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => $this->nhaHang->ma_nha_hang,
            'trang_thai' => 'da_duyet',
        ]);

        $this->nhaHang->update(['ma_quan_ly' => $this->manager->ma_nguoi_dung]);

        $this->candidate = NguoiDung::create([
            'ho_ten' => 'Phạm Ứng Viên',
            'email' => 'cand.test@gmail.com',
            'so_dien_thoai' => '0903333333',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        HoSoUngVien::create([
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'ngay_sinh' => '2002-05-10',
            'gioi_tinh' => 'nu',
            'so_cccd' => '079202000001',
            'trang_thai_ho_so' => 'san_sang_nhan_viec',
            'diem_danh_gia' => 0.0,
        ]);
    }

    public function test_default_rating_is_zero_when_entity_has_no_reviews(): void
    {
        $candStats = DanhGiaCaLam::tinhDiemTrungBinhUngVien($this->candidate->ma_nguoi_dung);
        $this->assertSame(0, $candStats['tong_luot']);
        $this->assertEquals(0.0, $candStats['diem_tb']);
        $this->assertEquals(0.0, $candStats['thai_do_tb']);
        $this->assertEquals(0.0, $candStats['chuyen_can_tb']);
        $this->assertEquals(0.0, $candStats['hieu_suat_tb']);

        $nhStats = DanhGiaCaLam::tinhDiemTrungBinhNhaHang($this->nhaHang->ma_nha_hang);
        $this->assertSame(0, $nhStats['tong_luot']);
        $this->assertEquals(0.0, $nhStats['diem_tb']);

        $thStats = DanhGiaCaLam::tinhDiemTrungBinhThuongHieu($this->brand->ma_thuong_hieu);
        $this->assertSame(0, $thStats['tong_luot']);
        $this->assertEquals(0.0, $thStats['diem_tb']);

        // Hồ sơ ứng viên mới hiển thị 0 ⭐ và "0 (Chưa có đánh giá)", không hiển thị 5.0 ⭐
        $profileResponse = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->get(route('profile.index'));

        $profileResponse->assertStatus(200);
        $profileResponse->assertSee('0 (Chưa có đánh giá)');
        $profileResponse->assertSee('0 ⭐');
        $profileResponse->assertDontSee('5.0 ⭐');

        // Chi tiết ca làm việc khi nhà hàng chưa có đánh giá
        $futureShift = CaLamViec::create([
            'ma_nha_hang' => $this->nhaHang->ma_nha_hang,
            'ten_vi_tri_tuyen_dung' => 'Phục vụ bàn ca tối',
            'ngay_lam_viec' => now()->addDay()->format('Y-m-d'),
            'gio_bat_dau' => '17:00:00',
            'gio_ket_thuc' => '22:00:00',
            'muc_luong_theo_gio' => 35000,
            'so_luong_tuyen' => 3,
            'so_luong_da_tuyen' => 0,
            'trang_thai_ca' => 'dang_tuyen',
        ]);

        HoSoUngTuyen::create([
            'ma_ca_lam' => $futureShift->ma_ca_lam,
            'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
            'trang_thai_ung_tuyen' => 'cho_duyet',
        ]);

        $detailResponse = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->get(route('ca-lam-viec.show', $futureShift->ma_ca_lam));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('0 (Chưa có đánh giá)');

        // Danh sách ứng viên của Quản lý khi ứng viên chưa có đánh giá
        $applicantsResponse = $this->actingAs($this->manager)
            ->withSession(['user' => $this->manager])
            ->get(route('quan_ly.ung_tuyen'));

        $applicantsResponse->assertStatus(200);
        $applicantsResponse->assertSee('0 (Chưa có đánh giá)');
        $applicantsResponse->assertDontSee('5⭐');
    }

    public function test_all_unpaginated_lists_paginate_5_items_per_page(): void
    {
        // Tạo thêm 6 ứng viên (tổng 7 ứng viên) và 5 quản lý (tổng 6 quản lý)
        for ($i = 1; $i <= 6; $i++) {
            NguoiDung::create([
                'ho_ten' => 'Ứng Viên Số '.$i,
                'email' => "uv{$i}.page@gmail.com",
                'so_dien_thoai' => '090400000'.$i,
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'ung_vien',
                'trang_thai' => 'hoat_dong',
            ]);
        }
        for ($i = 1; $i <= 5; $i++) {
            NguoiDung::create([
                'ho_ten' => 'Quản Lý Số '.$i,
                'email' => "ql{$i}.page@goldenshift.vn",
                'so_dien_thoai' => '090500000'.$i,
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'quan_ly_nha_hang',
                'ma_nha_hang_truc_thuoc' => $this->nhaHang->ma_nha_hang,
                'trang_thai' => 'da_duyet',
            ]);
        }

        // 1. Admin Users: Tab Ứng viên = 5 item/trang, tổng 7
        $resCandUsers = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.users.index', ['tab' => 'ung_vien']));
        $resCandUsers->assertStatus(200);
        $candPaginator = $resCandUsers->viewData('candidates');
        $this->assertSame(5, $candPaginator->perPage());
        $this->assertCount(5, $candPaginator);
        $this->assertSame(7, $candPaginator->total());
        $resCandUsers->assertSee('candidates_page=2');

        // 2. Admin Users: Tab Quản lý = 5 item/trang, tổng 6
        $resMgrUsers = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.users.index', ['tab' => 'quan_ly']));
        $resMgrUsers->assertStatus(200);
        $mgrPaginator = $resMgrUsers->viewData('managers');
        $this->assertSame(5, $mgrPaginator->perPage());
        $this->assertCount(5, $mgrPaginator);
        $this->assertSame(6, $mgrPaginator->total());
        $resMgrUsers->assertSee('managers_page=2');

        // Tạo 7 ca làm + 7 đơn ứng tuyển + 7 chấm công + 7 đánh giá mỗi chiều
        for ($i = 1; $i <= 7; $i++) {
            $shift = CaLamViec::create([
                'ma_nha_hang' => $this->nhaHang->ma_nha_hang,
                'ten_vi_tri_tuyen_dung' => "Vị trí ca {$i}",
                'ngay_lam_viec' => now()->subDays($i)->format('Y-m-d'),
                'gio_bat_dau' => '08:00:00',
                'gio_ket_thuc' => '12:00:00',
                'muc_luong_theo_gio' => 30000,
                'so_luong_tuyen' => 2,
                'so_luong_da_tuyen' => 1,
                'trang_thai_ca' => 'hoan_thanh',
            ]);

            $app = HoSoUngTuyen::create([
                'ma_ca_lam' => $shift->ma_ca_lam,
                'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
                'trang_thai_ung_tuyen' => 'da_nhan',
            ]);

            LichSuChamCong::create([
                'ma_ung_tuyen' => $app->ma_ung_tuyen,
                'ma_nguoi_dung' => $this->candidate->ma_nguoi_dung,
                'ma_ca_lam' => $shift->ma_ca_lam,
                'thoi_gian_vao_thuc_te' => now()->subDays($i)->setTime(8, 0),
                'thoi_gian_ra_thuc_te' => now()->subDays($i)->setTime(12, 0),
                'so_gio_lam_thuc_te' => 4.0,
                'trang_thai_cham_cong' => 'da_xac_nhan',
                'nguoi_xac_nhan' => $this->manager->ma_nguoi_dung,
                'ngay_tao' => now()->subDays($i),
            ]);

            DanhGiaCaLam::create([
                'ma_ca_lam' => $shift->ma_ca_lam,
                'ma_nguoi_danh_gia' => $this->manager->ma_nguoi_dung,
                'ma_nguoi_duoc_danh_gia' => $this->candidate->ma_nguoi_dung,
                'loai_danh_gia' => 'nha_hang_danh_gia_ung_vien',
                'so_sao' => 5,
                'tieu_chi_thai_do' => 5,
                'tieu_chi_chuyen_can' => 5,
                'tieu_chi_hieu_suat' => 5,
                'noi_dung_nhan_xet' => "Quản lý nhận xét ứng viên ca {$i}",
                'ngay_danh_gia' => now()->subDays($i),
            ]);

            DanhGiaCaLam::create([
                'ma_ca_lam' => $shift->ma_ca_lam,
                'ma_nguoi_danh_gia' => $this->candidate->ma_nguoi_dung,
                'ma_nguoi_duoc_danh_gia' => $this->manager->ma_nguoi_dung,
                'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
                'so_sao' => 4,
                'noi_dung_nhan_xet' => "Ứng viên nhận xét nhà hàng ca {$i}",
                'ngay_danh_gia' => now()->subDays($i),
            ]);
        }

        // 3. Profile Reviews = 5 item/trang, tổng 7, giữ tab=reviews
        $resProfile = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->get(route('profile.index'));
        $resProfile->assertStatus(200);
        $profileReviews = $resProfile->viewData('danhGiaList');
        $this->assertSame(5, $profileReviews->perPage());
        $this->assertCount(5, $profileReviews);
        $this->assertSame(7, $profileReviews->total());
        $resProfile->assertSee('tab=reviews');
        $resProfile->assertSee('reviews_page=2');

        // 4. Admin Restaurant Reviews = 5 item/trang, tổng 7
        $resAdminReviews = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.nha-hang.danh-gia', $this->nhaHang->ma_nha_hang));
        $resAdminReviews->assertStatus(200);
        $adminReviews = $resAdminReviews->viewData('danhGiaList');
        $this->assertSame(5, $adminReviews->perPage());
        $this->assertCount(5, $adminReviews);
        $this->assertSame(7, $adminReviews->total());

        // 5. Applied Shifts (Ứng viên) = 5 item/trang, tổng 7
        $resApplied = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->get(route('ung_tuyen.da_nop'));
        $resApplied->assertStatus(200);
        $appliedPaginator = $resApplied->viewData('appliedList');
        $this->assertSame(5, $appliedPaginator->perPage());
        $this->assertCount(5, $appliedPaginator);
        $this->assertSame(7, $appliedPaginator->total());

        // 6. Manager Applicants = 5 item/trang, tổng 7
        $resApplicants = $this->actingAs($this->manager)
            ->withSession(['user' => $this->manager])
            ->get(route('quan_ly.ung_tuyen'));
        $resApplicants->assertStatus(200);
        $applicantsPaginator = $resApplicants->viewData('applicantList');
        $this->assertSame(5, $applicantsPaginator->perPage());
        $this->assertCount(5, $applicantsPaginator);
        $this->assertSame(7, $applicantsPaginator->total());

        // 7. Manager Attendance = 5 item/trang, tổng 7
        $resAttendance = $this->actingAs($this->manager)
            ->withSession(['user' => $this->manager])
            ->get(route('quan_ly.cham_cong', ['tab' => 'tat_ca']));
        $resAttendance->assertStatus(200);
        $attendancePaginator = $resAttendance->viewData('chamCongList');
        $this->assertSame(5, $attendancePaginator->perPage());
        $this->assertCount(5, $attendancePaginator);
        $this->assertSame(7, $attendancePaginator->total());

        // 8. Income Book (Sổ Thu Nhập) = 5 item/trang cho bảng lịch sử, nhưng đồng bộ đủ cả 7 ca (28 giờ)
        $resIncome = $this->actingAs($this->candidate)
            ->withSession(['user' => $this->candidate])
            ->get(route('ung_vien.so_thu_nhap'));
        $resIncome->assertStatus(200);
        $incomePaginator = $resIncome->viewData('lichSuCaLam');
        $this->assertSame(5, $incomePaginator->perPage());
        $this->assertCount(5, $incomePaginator);
        $this->assertSame(7, $incomePaginator->total());
        $resIncome->assertViewHas('tongSoCaLam', 7);
    }

    public function test_manager_registration_requires_valid_restaurant_selection(): void
    {
        $getResponse = $this->get(route('register.manager'));
        $getResponse->assertStatus(200);
        $getResponse->assertSee('<option value="" disabled selected>-- Chọn Nhà hàng/Chi nhánh --</option>', false);
        $getResponse->assertSee('name="ma_nha_hang" required', false);

        // Bỏ trống ma_nha_hang -> lỗi validation với thông báo chính xác
        $missingRes = $this->from(route('register.manager'))->post(route('register.manager'), [
            'ho_ten' => 'Hoàng Kim Yến',
            'email' => 'yen.test@goldenshift.vn',
            'so_dien_thoai' => '0988777666',
            'ma_nha_hang' => '',
            'mat_khau' => 'GoldenShift@2026',
            'xac_nhan_mat_khau' => 'GoldenShift@2026',
        ]);

        $missingRes->assertRedirect(route('register.manager'));
        $missingRes->assertSessionHasErrors([
            'ma_nha_hang' => 'Vui lòng chọn Nhà hàng/Chi nhánh trực thuộc!',
        ]);

        // Truyền ma_nha_hang không tồn tại -> lỗi validation exists
        $invalidRes = $this->from(route('register.manager'))->post(route('register.manager'), [
            'ho_ten' => 'Hoàng Kim Yến',
            'email' => 'yen.test@goldenshift.vn',
            'so_dien_thoai' => '0988777666',
            'ma_nha_hang' => 999999,
            'mat_khau' => 'GoldenShift@2026',
            'xac_nhan_mat_khau' => 'GoldenShift@2026',
        ]);

        $invalidRes->assertRedirect(route('register.manager'));
        $invalidRes->assertSessionHasErrors('ma_nha_hang');

        // Truyền ma_nha_hang hợp lệ -> thành công
        $validRes = $this->post(route('register.manager'), [
            'ho_ten' => 'Hoàng Kim Yến',
            'email' => 'yen.valid@goldenshift.vn',
            'so_dien_thoai' => '0988777555',
            'ma_nha_hang' => $this->nhaHang->ma_nha_hang,
            'mat_khau' => 'GoldenShift@2026',
            'xac_nhan_mat_khau' => 'GoldenShift@2026',
        ]);

        $validRes->assertRedirect(route('login'));
        $validRes->assertSessionHas('success');
        $this->assertDatabaseHas('nguoi_dung', [
            'email' => 'yen.valid@goldenshift.vn',
            'ma_nha_hang_truc_thuoc' => $this->nhaHang->ma_nha_hang,
            'trang_thai' => 'cho_duyet',
        ]);
    }
}
