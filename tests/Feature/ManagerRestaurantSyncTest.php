<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\NhaHang;
use App\Models\ThuongHieu;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManagerRestaurantSyncTest extends TestCase
{
    protected NguoiDung $admin;

    protected NguoiDung $managerBaoChau;

    protected NguoiDung $managerTuyetNhi;

    protected NguoiDung $managerChoDuyet;

    protected NguoiDung $candidate;

    protected NhaHang $nhaHangDongKhoi;

    protected NhaHang $nhaHangCrescent;

    protected NhaHang $nhaHangUnassigned;

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

        ThuongHieu::create([
            'ten_thuong_hieu' => 'Golden Hotpot',
            'ma_dinh_danh' => 'golden-hotpot',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->admin = NguoiDung::create([
            'ho_ten' => 'Quản Trị Viên',
            'email' => 'admin.sync@goldenshift.vn',
            'so_dien_thoai' => '0901000001',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_tri_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->managerBaoChau = NguoiDung::create([
            'ho_ten' => 'Nguyễn Thị Bảo Châu',
            'email' => 'chau.ntb@goldenshift.vn',
            'so_dien_thoai' => '0901000002',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 1,
            'trang_thai' => 'da_duyet',
        ]);

        $this->managerTuyetNhi = NguoiDung::create([
            'ho_ten' => 'Dương Thị Tuyết Nhi',
            'email' => 'nhi.dtt@goldenshift.vn',
            'so_dien_thoai' => '0901000003',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 2,
            'trang_thai' => 'da_duyet',
        ]);

        $this->managerChoDuyet = NguoiDung::create([
            'ho_ten' => 'Hoàng Kim Yến',
            'email' => 'yen.hk@goldenshift.vn',
            'so_dien_thoai' => '0901000004',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'quan_ly_nha_hang',
            'ma_nha_hang_truc_thuoc' => 3,
            'trang_thai' => 'cho_duyet',
        ]);

        $this->candidate = NguoiDung::create([
            'ho_ten' => 'Phạm Thu Hương',
            'email' => 'huong.cand@gmail.com',
            'so_dien_thoai' => '0901000005',
            'mat_khau' => bcrypt('123456'),
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
        ]);

        $this->nhaHangDongKhoi = NhaHang::create([
            'ma_thuong_hieu' => 1,
            'ma_quan_ly' => $this->managerBaoChau->ma_nguoi_dung,
            'ten_nha_hang' => 'Golden Hotpot - Đồng Khởi',
            'so_dien_thoai_lien_he' => '0283822111',
            'dia_chi_chi_tiet' => '123 Đồng Khởi',
            'quan_huyen' => 'Quận 1',
            'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
            'trang_thai_hoat_dong' => 'dang_hoat_dong',
        ]);

        $this->nhaHangCrescent = NhaHang::create([
            'ma_thuong_hieu' => 1,
            'ma_quan_ly' => $this->managerTuyetNhi->ma_nguoi_dung,
            'ten_nha_hang' => 'Golden Hotpot - Crescent Mall',
            'so_dien_thoai_lien_he' => '0283822222',
            'dia_chi_chi_tiet' => '101 Tôn Dật Tiên',
            'quan_huyen' => 'Quận 7',
            'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
            'trang_thai_hoat_dong' => 'dang_hoat_dong',
        ]);

        $this->nhaHangUnassigned = NhaHang::create([
            'ma_thuong_hieu' => 1,
            'ma_quan_ly' => null,
            'ten_nha_hang' => 'Golden Hotpot - Thủ Đức',
            'so_dien_thoai_lien_he' => '0283822333',
            'dia_chi_chi_tiet' => '240 Võ Văn Ngân',
            'quan_huyen' => 'TP. Thủ Đức',
            'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
            'trang_thai_hoat_dong' => 'dang_hoat_dong',
        ]);
    }

    public function test_nha_hang_and_nguoi_dung_relationships_match_via_ma_quan_ly(): void
    {
        $dongKhoi = NhaHang::with(['quanLy', 'thuongHieu'])->findOrFail($this->nhaHangDongKhoi->ma_nha_hang);
        $this->assertNotNull($dongKhoi->quanLy);
        $this->assertSame('Nguyễn Thị Bảo Châu', $dongKhoi->quanLy->ho_ten);

        $baoChau = NguoiDung::with(['nhaHang', 'nhaHangTrucThuoc'])->findOrFail($this->managerBaoChau->ma_nguoi_dung);
        $this->assertNotNull($baoChau->nhaHang);
        $this->assertSame($this->nhaHangDongKhoi->ma_nha_hang, $baoChau->nhaHang->ma_nha_hang);
        $this->assertSame($this->nhaHangDongKhoi->ma_nha_hang, $baoChau->nhaHangTrucThuoc->ma_nha_hang);
    }

    public function test_admin_categories_displays_correct_manager_and_unassigned_label(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.categories.index', ['tab' => 'nha_hang']));

        $response->assertStatus(200);
        $response->assertSee('Golden Hotpot - Đồng Khởi');
        $response->assertSee('Nguyễn Thị Bảo Châu');
        $response->assertSee('Golden Hotpot - Thủ Đức');
        $response->assertSee('Chưa phân công');
    }

    public function test_admin_update_restaurant_manager_syncs_both_tables_and_rejects_non_manager(): void
    {
        // Cố tình gán ứng viên làm quản lý -> phải bị chặn
        $invalidRes = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->post(route('admin.restaurants.update', $this->nhaHangUnassigned->ma_nha_hang), [
                'ma_thuong_hieu' => 1,
                'ma_quan_ly' => $this->candidate->ma_nguoi_dung,
                'ten_nha_hang' => 'Golden Hotpot - Thủ Đức',
                'so_dien_thoai_lien_he' => '0283822333',
                'dia_chi_chi_tiet' => '240 Võ Văn Ngân',
                'quan_huyen' => 'TP. Thủ Đức',
                'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
                'trang_thai_hoat_dong' => 'dang_hoat_dong',
            ]);

        $invalidRes->assertSessionHas('error');
        $this->assertNull(NhaHang::find($this->nhaHangUnassigned->ma_nha_hang)->ma_quan_ly);

        // Chuyển Tuyết Nhi từ chi nhánh Crescent sang chi nhánh Thủ Đức -> đồng bộ cả 2 bảng
        $validRes = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->post(route('admin.restaurants.update', $this->nhaHangUnassigned->ma_nha_hang), [
                'ma_thuong_hieu' => 1,
                'ma_quan_ly' => $this->managerTuyetNhi->ma_nguoi_dung,
                'ten_nha_hang' => 'Golden Hotpot - Thủ Đức',
                'so_dien_thoai_lien_he' => '0283822333',
                'dia_chi_chi_tiet' => '240 Võ Văn Ngân',
                'quan_huyen' => 'TP. Thủ Đức',
                'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
                'trang_thai_hoat_dong' => 'dang_hoat_dong',
            ]);

        $validRes->assertRedirect(route('admin.categories.index', ['tab' => 'nha_hang']));
        $validRes->assertSessionHas('success');

        $this->assertNull(NhaHang::find($this->nhaHangCrescent->ma_nha_hang)->ma_quan_ly);
        $this->assertSame(
            $this->managerTuyetNhi->ma_nguoi_dung,
            NhaHang::find($this->nhaHangUnassigned->ma_nha_hang)->ma_quan_ly
        );
        $this->assertSame(
            $this->nhaHangUnassigned->ma_nha_hang,
            NguoiDung::find($this->managerTuyetNhi->ma_nguoi_dung)->ma_nha_hang_truc_thuoc
        );
    }

    public function test_manager_login_uses_synced_assigned_restaurant(): void
    {
        // Giả lập trạng thái lệch tạm ở ma_nha_hang_truc_thuoc nhưng ma_quan_ly chuẩn là chi nhánh 1
        $this->managerBaoChau->update(['ma_nha_hang_truc_thuoc' => $this->nhaHangCrescent->ma_nha_hang]);

        $response = $this->post('/dang-nhap', [
            'email' => 'chau.ntb@goldenshift.vn',
            'mat_khau' => '123456',
        ]);

        $response->assertRedirect(route('ca-lam-viec.index'));
        $sessionUser = session('user');
        $this->assertSame('Golden Hotpot - Đồng Khởi', $sessionUser['ten_nha_hang']);
        $this->assertSame($this->nhaHangDongKhoi->ma_nha_hang, $sessionUser['ma_nha_hang_truc_thuoc']);
    }

    public function test_admin_approving_pending_manager_syncs_unassigned_restaurant(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->post(route('admin.users.duyet', $this->managerChoDuyet->ma_nguoi_dung));

        $response->assertSessionHas('success');
        $this->assertSame('da_duyet', NguoiDung::find($this->managerChoDuyet->ma_nguoi_dung)->trang_thai);
        $this->assertSame(
            $this->managerChoDuyet->ma_nguoi_dung,
            NhaHang::find($this->nhaHangUnassigned->ma_nha_hang)->ma_quan_ly
        );
    }

    public function test_sync_migration_repairs_mismatched_and_candidate_ma_quan_ly(): void
    {
        // Giả lập đúng lỗi lệch dữ liệu thực tế: Đồng Khởi trỏ nhầm Tuyết Nhi (#3), Thủ Đức trỏ nhầm ứng viên (#5)
        DB::table('nha_hang')->where('ma_nha_hang', $this->nhaHangDongKhoi->ma_nha_hang)->update([
            'ma_quan_ly' => $this->managerTuyetNhi->ma_nguoi_dung,
        ]);
        DB::table('nha_hang')->where('ma_nha_hang', $this->nhaHangUnassigned->ma_nha_hang)->update([
            'ma_quan_ly' => $this->candidate->ma_nguoi_dung,
        ]);

        $migration = require database_path('migrations/2026_11_21_140000_sync_nha_hang_ma_quan_ly_with_nguoi_dung.php');
        $migration->up();

        $this->assertSame(
            $this->managerBaoChau->ma_nguoi_dung,
            DB::table('nha_hang')->where('ma_nha_hang', $this->nhaHangDongKhoi->ma_nha_hang)->value('ma_quan_ly')
        );
        $this->assertSame(
            $this->managerTuyetNhi->ma_nguoi_dung,
            DB::table('nha_hang')->where('ma_nha_hang', $this->nhaHangCrescent->ma_nha_hang)->value('ma_quan_ly')
        );
        // Thủ Đức chỉ có quản lý cho_duyet -> chuyển về null thay vì để nhầm ứng viên
        $this->assertNull(
            DB::table('nha_hang')->where('ma_nha_hang', $this->nhaHangUnassigned->ma_nha_hang)->value('ma_quan_ly')
        );
    }
}
