<?php

namespace Database\Seeders;

use App\Models\HoSoUngVien;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class NguoiDungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('@Abc1234');

        // ====================================================================
        // 1. DỌN DẸP DỮ LIỆU CŨ (CLEANUP VỚI DISABLE FOREIGN KEY CHECKS)
        // ====================================================================
        Schema::disableForeignKeyConstraints();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } catch (\Throwable $e) {
            // SQLite hoặc driver không hỗ trợ câu lệnh này
        }

        // Tạm thời reset tham chiếu ma_quan_ly trong nha_hang để tránh ràng buộc
        if (Schema::hasTable('nha_hang')) {
            DB::table('nha_hang')->update(['ma_quan_ly' => null]);
        }

        // Làm sạch bảng ho_so_ung_vien và nguoi_dung
        if (Schema::hasTable('ho_so_ung_vien')) {
            DB::table('ho_so_ung_vien')->truncate();
        }
        if (Schema::hasTable('nguoi_dung')) {
            DB::table('nguoi_dung')->truncate();
        }

        // ====================================================================
        // 2. KHỞI TẠO 3 TÀI KHOẢN CHÍNH THỨC CHUẨN ĐỒ ÁN (Mật khẩu: @Abc1234)
        // ====================================================================

        // [Tài khoản 1]: Quản trị viên (Admin)
        $admin = NguoiDung::create([
            'ma_nguoi_dung' => 1,
            'ho_ten' => 'Dương Thị Tuyết Nhi',
            'email' => 'admin@goldenshift.vn',
            'so_dien_thoai' => '0901234567',
            'mat_khau' => $defaultPassword,
            'vai_tro' => 'quan_tri_vien',
            'trang_thai' => 'hoat_dong',
            'ma_nha_hang_truc_thuoc' => null,
            'ngay_duyet' => now(),
        ]);

        // [Tài khoản 2]: Quản lý nhà hàng chính (Manager chính)
        // Phụ trách chi nhánh trọng điểm: Golden Hotpot - Đồng Khởi (ma_nha_hang = 1)
        $managerLead = NguoiDung::create([
            'ma_nguoi_dung' => 2,
            'ho_ten' => 'Ngô Thị Mỹ Lệ',
            'email' => 'le.ntm@goldenshift.vn',
            'so_dien_thoai' => '0923456789',
            'mat_khau' => $defaultPassword,
            'vai_tro' => 'quan_ly_nha_hang',
            'trang_thai' => 'da_duyet',
            'ma_nha_hang_truc_thuoc' => 1,
            'ngay_duyet' => now(),
        ]);

        // [Tài khoản 3]: Ứng viên chính (Candidate chính)
        $candidateLead = NguoiDung::create([
            'ma_nguoi_dung' => 3,
            'ho_ten' => 'Nguyễn Thị Bảo Châu',
            'email' => 'chau.ntb@gmail.com',
            'so_dien_thoai' => '0912345678',
            'mat_khau' => $defaultPassword,
            'vai_tro' => 'ung_vien',
            'trang_thai' => 'hoat_dong',
            'ma_nha_hang_truc_thuoc' => null,
            'ngay_duyet' => now(),
        ]);

        // Hồ sơ chi tiết của Ứng viên chính
        HoSoUngVien::create([
            'ma_nguoi_dung' => 3,
            'ngay_sinh' => '2002-08-12',
            'gioi_tinh' => 'nu',
            'so_cccd' => '079202001234',
            'gioi_thieu_ban_than' => 'Kỹ năng: Phục vụ, Pha chế. Kinh nghiệm: 1 năm làm F&B tại các chuỗi nhà hàng cao cấp. Điểm đánh giá: 5.0. Nhanh nhẹn, đúng giờ, giao tiếp tiếng Anh tốt và có tinh thần trách nhiệm cao.',
            'trang_thai_ho_so' => 'san_sang_nhan_viec',
        ]);

        // ====================================================================
        // 3. TỰ ĐỘNG SINH DỮ LIỆU BỔ SUNG PHÙ HỢP ĐỒ ÁN
        // ====================================================================

        // --- A. DANH SÁCH QUẢN LÝ PHỤ BỔ SUNG (GÁN PHỤ TRÁCH CÁC CHI NHÁNH CÒN LẠI) ---
        $subManagers = [
            [
                'ma_nguoi_dung' => 4,
                'ho_ten' => 'Trần Văn Hùng',
                'email' => 'hung.tv@goldenshift.vn',
                'so_dien_thoai' => '0934567890',
                'ma_nha_hang' => 2, // Golden Hotpot - Crescent Mall
            ],
            [
                'ma_nguoi_dung' => 5,
                'ho_ten' => 'Lê Hoàng Nam',
                'email' => 'nam.lh@goldenshift.vn',
                'so_dien_thoai' => '0945678901',
                'ma_nha_hang' => 3, // Golden BBQ - Landmark 81
            ],
            [
                'ma_nguoi_dung' => 6,
                'ho_ten' => 'Phạm Minh Tuấn',
                'email' => 'tuan.pm@goldenshift.vn',
                'so_dien_thoai' => '0956789012',
                'ma_nha_hang' => 4, // Golden BBQ - Sư Vạn Hạnh
            ],
            [
                'ma_nguoi_dung' => 7,
                'ho_ten' => 'Hoàng Kim Yến',
                'email' => 'yen.hk@goldenshift.vn',
                'so_dien_thoai' => '0967890123',
                'ma_nha_hang' => 5, // Golden Sushi - Saigon Centre
            ],
            [
                'ma_nguoi_dung' => 8,
                'ho_ten' => 'Đặng Văn Long',
                'email' => 'long.dv@goldenshift.vn',
                'so_dien_thoai' => '0978901234',
                'ma_nha_hang' => 6, // Golden Sushi - Thảo Điền
            ],
            [
                'ma_nguoi_dung' => 9,
                'ho_ten' => 'Vũ Thị Mai',
                'email' => 'mai.vt@goldenshift.vn',
                'so_dien_thoai' => '0989012345',
                'ma_nha_hang' => 7, // Golden Express - Nguyễn Trãi
            ],
            [
                'ma_nguoi_dung' => 10,
                'ho_ten' => 'Nguyễn Đức Thắng',
                'email' => 'thang.nd@goldenshift.vn',
                'so_dien_thoai' => '0990123456',
                'ma_nha_hang' => 8, // Golden Express - Phan Xích Long
            ],
        ];

        foreach ($subManagers as $m) {
            NguoiDung::create([
                'ma_nguoi_dung' => $m['ma_nguoi_dung'],
                'ho_ten' => $m['ho_ten'],
                'email' => $m['email'],
                'so_dien_thoai' => $m['so_dien_thoai'],
                'mat_khau' => $defaultPassword,
                'vai_tro' => 'quan_ly_nha_hang',
                'trang_thai' => 'da_duyet',
                'ma_nha_hang_truc_thuoc' => $m['ma_nha_hang'],
                'ngay_duyet' => now(),
            ]);
        }

        // --- B. DANH SÁCH ỨNG VIÊN BỔ SUNG (SINH VIÊN F&B KÈM HỒ SƠ ĐẦY ĐỦ) ---
        $subCandidates = [
            [
                'ma_nguoi_dung' => 11,
                'ho_ten' => 'Hoàng Thảo My',
                'email' => 'my.ht@gmail.com',
                'so_dien_thoai' => '0911223344',
                'ngay_sinh' => '2003-03-20',
                'gioi_tinh' => 'nu',
                'so_cccd' => '079303001111',
                'gioi_thieu' => 'Kỹ năng: Phục vụ bàn, Lễ tân đón khách. Kinh nghiệm: 8 tháng tại nhà hàng lẩu nướng. Vui vẻ, hòa đồng, sẵn sàng tăng ca cuối tuần.',
            ],
            [
                'ma_nguoi_dung' => 12,
                'ho_ten' => 'Đặng Quốc Bảo',
                'email' => 'bao.dq@gmail.com',
                'so_dien_thoai' => '0922334455',
                'ngay_sinh' => '2002-11-10',
                'gioi_tinh' => 'nam',
                'so_cccd' => '079202002222',
                'gioi_thieu' => 'Kỹ năng: Phụ bếp, Nướng thịt tại bàn, Sơ chế thực phẩm. Kinh nghiệm: 1 năm làm phụ bếp F&B. Sức khỏe tốt, chăm chỉ, cẩn thận.',
            ],
            [
                'ma_nguoi_dung' => 13,
                'ho_ten' => 'Vũ Anh Khoa',
                'email' => 'khoa.va@gmail.com',
                'so_dien_thoai' => '0933445566',
                'ngay_sinh' => '2001-07-25',
                'gioi_tinh' => 'nam',
                'so_cccd' => '079201003333',
                'gioi_thieu' => 'Kỹ năng: Thu ngân, Kiểm soát hóa đơn POS, Pha chế nước ép & trà. Kinh nghiệm: 1.5 năm tại chuỗi đồ uống. Cẩn trọng, trung thực.',
            ],
            [
                'ma_nguoi_dung' => 14,
                'ho_ten' => 'Phạm Thu Hương',
                'email' => 'huong.pt@gmail.com',
                'so_dien_thoai' => '0944556677',
                'ngay_sinh' => '2004-02-14',
                'gioi_tinh' => 'nu',
                'so_cccd' => '079304004444',
                'gioi_thieu' => 'Kỹ năng: Chăm sóc khách hàng, Tiếp thực, Dọn dẹp bàn tiệc. Sinh viên năm 2 năng động, linh hoạt giờ giấc làm việc.',
            ],
            [
                'ma_nguoi_dung' => 15,
                'ho_ten' => 'Đỗ Mỹ Linh',
                'email' => 'linh.dm@gmail.com',
                'so_dien_thoai' => '0955667788',
                'ngay_sinh' => '2003-09-08',
                'gioi_tinh' => 'nu',
                'so_cccd' => '079303005555',
                'gioi_thieu' => 'Kỹ năng: Giao tiếp tiếng Anh, Order món tại bàn, Phục vụ khách quốc tế. Kinh nghiệm: 6 tháng tại nhà hàng sushi trung tâm.',
            ],
            [
                'ma_nguoi_dung' => 16,
                'ho_ten' => 'Trần Minh Quân',
                'email' => 'quan.tm@gmail.com',
                'so_dien_thoai' => '0966778899',
                'ngay_sinh' => '2002-04-18',
                'gioi_tinh' => 'nam',
                'so_cccd' => '079202006666',
                'gioi_thieu' => 'Kỹ năng: Bếp lạnh, Tiếp thực nhanh, Vệ sinh an toàn thực phẩm. Nhanh nhẹn, có trách nhiệm và kỷ luật cao.',
            ],
            [
                'ma_nguoi_dung' => 17,
                'ho_ten' => 'Lê Thị Mai',
                'email' => 'mai.lt@gmail.com',
                'so_dien_thoai' => '0977889900',
                'ngay_sinh' => '2004-12-05',
                'gioi_tinh' => 'nu',
                'so_cccd' => '079304007777',
                'gioi_thieu' => 'Kỹ năng: Phục vụ bàn tiệc buffet, Pha chế cơ bản. Kinh nghiệm: 5 tháng làm thời vụ F&B. Ham học hỏi, chịu khó.',
            ],
        ];

        foreach ($subCandidates as $c) {
            NguoiDung::create([
                'ma_nguoi_dung' => $c['ma_nguoi_dung'],
                'ho_ten' => $c['ho_ten'],
                'email' => $c['email'],
                'so_dien_thoai' => $c['so_dien_thoai'],
                'mat_khau' => $defaultPassword,
                'vai_tro' => 'ung_vien',
                'trang_thai' => 'hoat_dong',
                'ma_nha_hang_truc_thuoc' => null,
                'ngay_duyet' => now(),
            ]);

            HoSoUngVien::create([
                'ma_nguoi_dung' => $c['ma_nguoi_dung'],
                'ngay_sinh' => $c['ngay_sinh'],
                'gioi_tinh' => $c['gioi_tinh'],
                'so_cccd' => $c['so_cccd'],
                'gioi_thieu_ban_than' => $c['gioi_thieu'],
                'trang_thai_ho_so' => 'san_sang_nhan_viec',
            ]);
        }

        // ====================================================================
        // 4. CẬP NHẬT ĐỒNG BỘ MỐI QUAN HỆ CSDL
        // ====================================================================

        // --- Cập nhật ma_quan_ly trong bảng nha_hang ---
        if (Schema::hasTable('nha_hang')) {
            // Chi nhánh 1: Ngô Thị Mỹ Lệ (Quản lý chính)
            DB::table('nha_hang')->where('ma_nha_hang', 1)->update(['ma_quan_ly' => 2]);

            // Các chi nhánh 2 -> 8 gán cho các quản lý tương ứng
            foreach ($subManagers as $m) {
                DB::table('nha_hang')->where('ma_nha_hang', $m['ma_nha_hang'])->update(['ma_quan_ly' => $m['ma_nguoi_dung']]);
            }
        }

        // --- Cập nhật đồng bộ các bảng nghiệp vụ cũ để không bị lỗi khóa ngoại ---
        $candidateIds = [3, 11, 12, 13, 14, 15, 16, 17];
        $managerIds = [2, 4, 5, 6, 7, 8, 9, 10];

        // 1. ho_so_ung_tuyen
        if (Schema::hasTable('ho_so_ung_tuyen')) {
            $applications = DB::table('ho_so_ung_tuyen')->get();
            foreach ($applications as $idx => $app) {
                $assignedCandidate = $candidateIds[$idx % count($candidateIds)];
                $assignedManager = $app->nguoi_duyet ? $managerIds[$idx % count($managerIds)] : null;
                DB::table('ho_so_ung_tuyen')
                    ->where('ma_ung_tuyen', $app->ma_ung_tuyen)
                    ->update([
                        'ma_nguoi_dung' => $assignedCandidate,
                        'nguoi_duyet' => $assignedManager,
                    ]);
            }
        }

        // 2. lich_su_cham_cong
        if (Schema::hasTable('lich_su_cham_cong')) {
            $timekeepings = DB::table('lich_su_cham_cong')->get();
            foreach ($timekeepings as $idx => $tk) {
                $assignedCandidate = $candidateIds[$idx % count($candidateIds)];
                $assignedManager = $managerIds[$idx % count($managerIds)];
                DB::table('lich_su_cham_cong')
                    ->where('ma_cham_cong', $tk->ma_cham_cong)
                    ->update([
                        'ma_nguoi_dung' => $assignedCandidate,
                        'nguoi_xac_nhan' => $assignedManager,
                    ]);
            }
        }

        // 3. so_thu_nhap
        if (Schema::hasTable('so_thu_nhap')) {
            $incomes = DB::table('so_thu_nhap')->get();
            foreach ($incomes as $idx => $inc) {
                $assignedCandidate = $candidateIds[$idx % count($candidateIds)];
                DB::table('so_thu_nhap')
                    ->where('ma_so_thu_nhap', $inc->ma_so_thu_nhap)
                    ->update([
                        'ma_nguoi_dung' => $assignedCandidate,
                    ]);
            }
        }

        // 4. danh_gia_ca_lam
        if (Schema::hasTable('danh_gia_ca_lam')) {
            $reviews = DB::table('danh_gia_ca_lam')->get();
            foreach ($reviews as $idx => $rev) {
                if ($rev->loai_danh_gia === 'nha_hang_danh_gia_ung_vien') {
                    $nguoiDanhGia = $managerIds[$idx % count($managerIds)];
                    $nguoiDuocDanhGia = $candidateIds[$idx % count($candidateIds)];
                } else {
                    $nguoiDanhGia = $candidateIds[$idx % count($candidateIds)];
                    $nguoiDuocDanhGia = $managerIds[$idx % count($managerIds)];
                }
                DB::table('danh_gia_ca_lam')
                    ->where('ma_danh_gia', $rev->ma_danh_gia)
                    ->update([
                        'ma_nguoi_danh_gia' => $nguoiDanhGia,
                        'ma_nguoi_duoc_danh_gia' => $nguoiDuocDanhGia,
                    ]);
            }
        }

        // 5. bao_cao_vi_pham
        if (Schema::hasTable('bao_cao_vi_pham')) {
            $reports = DB::table('bao_cao_vi_pham')->get();
            foreach ($reports as $idx => $rep) {
                DB::table('bao_cao_vi_pham')
                    ->where('ma_vi_pham', $rep->ma_vi_pham)
                    ->update([
                        'ma_nguoi_bao_cao' => $managerIds[$idx % count($managerIds)],
                        'ma_nguoi_vi_pham' => $candidateIds[$idx % count($candidateIds)],
                    ]);
            }
        }

        // 6. thong_bao
        if (Schema::hasTable('thong_bao')) {
            $notifications = DB::table('thong_bao')->get();
            $allUserIds = array_merge([1], $managerIds, $candidateIds);
            foreach ($notifications as $idx => $noti) {
                DB::table('thong_bao')
                    ->where('ma_thong_bao', $noti->ma_thong_bao)
                    ->update([
                        'ma_nguoi_dung' => $allUserIds[$idx % count($allUserIds)],
                    ]);
            }
        }

        // Bật lại Foreign Key Checks an toàn
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Throwable $e) {
            // SQLite hoặc driver không hỗ trợ
        }
        Schema::enableForeignKeyConstraints();
    }
}
