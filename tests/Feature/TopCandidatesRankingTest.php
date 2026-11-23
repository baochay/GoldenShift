<?php

namespace Tests\Feature;

use App\Models\BaoCaoViPham;
use App\Models\CaLamViec;
use App\Models\DanhGiaCaLam;
use App\Models\HoSoUngTuyen;
use App\Models\LichSuChamCong;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use App\Models\ThuongHieu;
use App\Services\ThongKeService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TopCandidatesRankingTest extends TestCase
{
    protected NguoiDung $admin;

    protected NguoiDung $manager;

    protected NhaHang $nhaHang;

    protected function setUp(): void
    {
        parent::setUp();

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
                $table->integer('so_luong_tuyen')->default(5);
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
                $table->string('trang_thai_ung_tuyen', 30)->default('da_nhan');
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
                $table->string('trang_thai_thanh_toan', 30)->default('da_thanh_toan');
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

        $brand = ThuongHieu::create([
            'ten_thuong_hieu' => 'Golden Hotpot',
            'ma_dinh_danh' => 'golden-hotpot',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->admin = NguoiDung::create([
            'ho_ten' => 'Admin Rank',
            'email' => 'admin.rank@goldenshift.vn',
            'so_dien_thoai' => '0901000001',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_tri_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->nhaHang = NhaHang::create([
            'ma_thuong_hieu' => $brand->ma_thuong_hieu,
            'ten_nha_hang' => 'Golden Hotpot Ranking',
            'so_dien_thoai_lien_he' => '0283800099',
            'dia_chi_chi_tiet' => '99 Nguyễn Huệ',
            'trang_thai_hoat_dong' => 'dang_hoat_dong',
        ]);

        $this->manager = NguoiDung::create([
            'ho_ten' => 'Quản Lý Rank',
            'email' => 'ql.rank@goldenshift.vn',
            'so_dien_thoai' => '0901000002',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => $this->nhaHang->ma_nha_hang,
            'trang_thai' => 'da_duyet',
        ]);

        $this->nhaHang->update(['ma_quan_ly' => $this->manager->ma_nguoi_dung]);
    }

    private function createCandidateShifts(
        NguoiDung $cand,
        int $totalShifts,
        float $hoursPerShift,
        int $lateCount,
        array $ratings
    ): array {
        $today = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
        $createdShifts = [];

        for ($i = 0; $i < $totalShifts; $i++) {
            $day = $today->copy()->subDays($i + 1);
            $shift = CaLamViec::create([
                'ma_nha_hang' => $this->nhaHang->ma_nha_hang,
                'ten_vi_tri_tuyen_dung' => 'Nhân viên Phục vụ',
                'ngay_lam_viec' => $day->format('Y-m-d'),
                'gio_bat_dau' => '08:00:00',
                'gio_ket_thuc' => '18:00:00',
                'muc_luong_theo_gio' => 30000,
                'trang_thai_ca' => 'hoan_thanh',
            ]);

            $app = HoSoUngTuyen::create([
                'ma_ca_lam' => $shift->ma_ca_lam,
                'ma_nguoi_dung' => $cand->ma_nguoi_dung,
                'ngay_nop_don' => $day->copy()->subDay(),
                'trang_thai_ung_tuyen' => 'da_nhan',
            ]);

            $isLate = $i < $lateCount;
            $checkIn = $isLate ? $day->copy()->setTime(8, 20) : $day->copy()->setTime(7, 55);

            LichSuChamCong::create([
                'ma_ung_tuyen' => $app->ma_ung_tuyen,
                'ma_nguoi_dung' => $cand->ma_nguoi_dung,
                'ma_ca_lam' => $shift->ma_ca_lam,
                'thoi_gian_vao_thuc_te' => $checkIn,
                'thoi_gian_ra_thuc_te' => $day->copy()->setTime(17, 55),
                'so_gio_lam_thuc_te' => $hoursPerShift,
                'trang_thai_cham_cong' => $isLate ? 'di_muon' : 'da_xac_nhan',
                'ngay_tao' => $day->copy()->setTime(18, 0),
            ]);

            if (isset($ratings[$i])) {
                DanhGiaCaLam::create([
                    'ma_ca_lam' => $shift->ma_ca_lam,
                    'ma_nguoi_danh_gia' => $this->manager->ma_nguoi_dung,
                    'ma_nguoi_duoc_danh_gia' => $cand->ma_nguoi_dung,
                    'loai_danh_gia' => 'nha_hang_danh_gia_ung_vien',
                    'so_sao' => $ratings[$i],
                    'noi_dung_nhan_xet' => 'Đánh giá xếp hạng #'.$i,
                    'ngay_danh_gia' => $day->copy()->setTime(18, 30),
                ]);
            }

            $createdShifts[] = $shift;
        }

        return $createdShifts;
    }

    public function test_ranking_formula_filter_constraints_and_top_5_limit(): void
    {
        $cands = [];
        for ($i = 1; $i <= 9; $i++) {
            $cands[$i] = NguoiDung::create([
                'ho_ten' => "Ứng Viên Hạng {$i}",
                'email' => "uv.rank{$i}@gmail.com",
                'so_dien_thoai' => '090800000'.$i,
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'ung_vien',
                'trang_thai' => 'hoat_dong',
            ]);
        }

        // #1: 5.0 sao (40), 200h >= 160h cap 40 (40), 4/4 đúng giờ (20) => 100.0 điểm
        $this->createCandidateShifts($cands[1], 4, 50.0, 0, [5, 5, 5, 5]);

        // #2: 4.5 sao (36.0), 80h (20.0), 3/4 đúng giờ (15.0) => 71.0 điểm
        $this->createCandidateShifts($cands[2], 4, 20.0, 1, [5, 4, 5, 4]);

        // #3: 5.0 sao (40.0), 40h (10.0), 2/2 đúng giờ (20.0), cộng thêm 1 báo cáo ĐÃ XỬ LÝ nhưng cách đây 45 ngày (> 30 ngày) -> HỢP LỆ => 70.0 điểm
        $s3 = $this->createCandidateShifts($cands[3], 2, 20.0, 0, [5, 5]);
        BaoCaoViPham::create([
            'ma_nguoi_vi_pham' => $cands[3]->ma_nguoi_dung,
            'ma_nguoi_bao_cao' => $this->manager->ma_nguoi_dung,
            'ma_ca_lam' => $s3[0]->ma_ca_lam,
            'loai_vi_pham' => 'di_muon_ve_som',
            'mo_ta_chi_tiet' => 'Vi phạm cũ đã quá 30 ngày',
            'trang_thai_xu_ly' => 'da_xuly',
            'ngay_lap_bao_cao' => Carbon::now('Asia/Ho_Chi_Minh')->subDays(45),
        ]);

        // #4: 4.0 sao (32.0), 32h (8.0), 2/2 đúng giờ (20.0), cộng 1 báo cáo CHỜ XỬ LÝ 5 ngày trước -> KHÔNG bị loại => 60.0 điểm
        $s4 = $this->createCandidateShifts($cands[4], 2, 16.0, 0, [4, 4]);
        BaoCaoViPham::create([
            'ma_nguoi_vi_pham' => $cands[4]->ma_nguoi_dung,
            'ma_nguoi_bao_cao' => $this->manager->ma_nguoi_dung,
            'ma_ca_lam' => $s4[0]->ma_ca_lam,
            'loai_vi_pham' => 'thai_do_khong_phu_hop',
            'mo_ta_chi_tiet' => 'Chờ admin xác minh',
            'trang_thai_xu_ly' => 'cho_xuly',
            'ngay_lap_bao_cao' => Carbon::now('Asia/Ho_Chi_Minh')->subDays(5),
        ]);

        // #5: Không có đánh giá nào (0.0), 40h (10.0), 2/2 đúng giờ (20.0) => 30.0 điểm
        $this->createCandidateShifts($cands[5], 2, 20.0, 0, []);

        // #6: Điểm rất thấp để test bị đẩy khỏi Top 5 (3.0 sao = 24.0, 4h = 1.0, 0/2 đúng giờ = 0.0 => 25.0 < 30.0)
        $this->createCandidateShifts($cands[6], 2, 2.0, 2, [3, 3]);

        // #7: Điểm cao thứ 7 để xác nhận limit(5): 3.0 sao = 24.0, 8h = 2.0, 0/2 đúng giờ = 0 => 26.0 (< 30.0)
        $this->createCandidateShifts($cands[7], 2, 4.0, 2, [3, 3]);

        // #8 (BỊ LOẠI - Chỉ có 1 ca < 2 ca, dù 5 sao & 20h)
        $this->createCandidateShifts($cands[8], 1, 20.0, 0, [5]);

        // #9 (BỊ LOẠI - Vi phạm da_xuly trong 30 ngày qua dù 200h & 5 sao)
        $s9 = $this->createCandidateShifts($cands[9], 4, 50.0, 0, [5, 5, 5, 5]);
        BaoCaoViPham::create([
            'ma_nguoi_vi_pham' => $cands[9]->ma_nguoi_dung,
            'ma_nguoi_bao_cao' => $this->manager->ma_nguoi_dung,
            'ma_ca_lam' => $s9[0]->ma_ca_lam,
            'loai_vi_pham' => 'bung_ca',
            'mo_ta_chi_tiet' => 'Bùng ca bị xử lý cách đây 10 ngày',
            'trang_thai_xu_ly' => 'da_xuly',
            'ngay_lap_bao_cao' => Carbon::now('Asia/Ho_Chi_Minh')->subDays(10),
        ]);

        $attendances = LichSuChamCong::with(['caLamViec', 'nguoiDung'])->get();
        $reviews = DanhGiaCaLam::with(['caLamViec', 'nguoiDuocDanhGia'])->get();

        $service = app(ThongKeService::class);
        $top = $service->calculateTopCandidatesRanking($attendances, $reviews);

        // Giới hạn đúng Top 5
        $this->assertCount(5, $top);

        // Thứ tự giảm dần chính xác
        $this->assertSame('Ứng Viên Hạng 1', $top[0]['ho_ten']);
        $this->assertEquals(100.0, $top[0]['diem_xuat_sac']);

        $this->assertSame('Ứng Viên Hạng 2', $top[1]['ho_ten']);
        $this->assertEquals(71.0, $top[1]['diem_xuat_sac']);
        $this->assertEquals(75.0, $top[1]['ty_le_dung_gio']);

        $this->assertSame('Ứng Viên Hạng 3', $top[2]['ho_ten']);
        $this->assertEquals(70.0, $top[2]['diem_xuat_sac']);

        $this->assertSame('Ứng Viên Hạng 4', $top[3]['ho_ten']);
        $this->assertEquals(60.0, $top[3]['diem_xuat_sac']);

        $this->assertSame('Ứng Viên Hạng 5', $top[4]['ho_ten']);
        $this->assertEquals(0.0, $top[4]['diem_danh_gia']);
        $this->assertEquals(30.0, $top[4]['diem_xuat_sac']);

        // Đảm bảo #8 (< 2 ca) và #9 (vi phạm 30 ngày) bị loại khỏi bảng xếp hạng
        $names = array_column($top, 'ho_ten');
        $this->assertNotContains('Ứng Viên Hạng 8', $names);
        $this->assertNotContains('Ứng Viên Hạng 9', $names);

        // Kiểm tra hiển thị HTML trên trang Admin & Quản lý
        $adminRes = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.thong_ke.index'));
        $adminRes->assertStatus(200);
        $adminRes->assertSee('🥇 Hạng 1');
        $adminRes->assertSee('🥈 Hạng 2');
        $adminRes->assertSee('🥉 Hạng 3');
        $adminRes->assertSee('100.0 / 100 điểm');
        $adminRes->assertSee('75.0%');
        $adminRes->assertSee('uv.rank1@gmail.com');

        $managerRes = $this->actingAs($this->manager)
            ->withSession(['user' => $this->manager])
            ->get(route('quan_ly.thong_ke.index'));
        $managerRes->assertStatus(200);
        $managerRes->assertSee('🥇 Hạng 1');
        $managerRes->assertSee('100.0 / 100 điểm');
        $managerRes->assertSee('4 ca (200,0 giờ)');
        $managerRes->assertDontSee('Ứng Viên Hạng 9');
    }
}
