<?php

namespace Database\Seeders;

use App\Http\Controllers\SoThuNhapController;
use App\Models\CaLamViec;
use App\Models\DanhGiaCaLam;
use App\Models\HoSoUngTuyen;
use App\Models\LichSuChamCong;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DanhGiaCaLamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Đồng bộ toàn bộ dữ liệu Sổ Thu Nhập theo đúng ca làm việc thực tế từ LichSuChamCong
        $soThuNhapCtrl = new SoThuNhapController;
        $candidates = NguoiDung::where('vai_tro', 'ung_vien')->get();
        foreach ($candidates as $cand) {
            $shiftsOfUser = LichSuChamCong::with('caLamViec')
                ->where('ma_nguoi_dung', $cand->ma_nguoi_dung)
                ->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet'])
                ->get();
            $soThuNhapCtrl->syncSoThuNhapFromAttendance($cand->ma_nguoi_dung, $shiftsOfUser);
        }

        // 2. Danh sách nhận xét tích cực mẫu
        $commentsCandidateToRestaurant = [
            'Nhà hàng hỗ trợ nhiệt tình, môi trường làm việc thân thiện và rất chuyên nghiệp, quản lý hướng dẫn chu đáo!',
            'Ca làm việc rõ ràng, phân công hợp lý, đồng nghiệp thân thiện, cơm ca ngon miệng và đúng giờ.',
            'Quản lý và nhân viên hỗ trợ ứng viên rất tận tình, quy trình làm việc chuẩn F&B, trải nghiệm rất tuyệt vời.',
            'Môi trường năng động, thanh toán tiền mặt sòng phẳng trực tiếp ngay sau ca làm, chắc chắn sẽ tiếp tục nhận ca tại đây!',
            'Nhà hàng sạch sẽ, điều phối công việc linh hoạt, quản lý tâm lý và tạo điều kiện tối đa cho nhân viên thời vụ.',
        ];

        $commentsRestaurantToCandidate = [
            'Bạn làm việc rất nhanh nhẹn, thái độ tươi cười, nhiệt tình với khách hàng và tuân thủ đúng nội quy.',
            'Tác phong chuẩn mực, đến đúng giờ, hỗ trợ đồng nghiệp trong sảnh rất tích cực và hoàn thành xuất sắc ca làm.',
            'Chăm chỉ, chủ động trong công việc, kỹ năng nghiệp vụ tốt, quản lý rất hài lòng và mong bạn tiếp tục gắn bó.',
            'Thái độ tích cực, tiếp thu hướng dẫn nhanh, xử lý tình huống phục vụ khéo léo và chu đáo.',
            'Hoàn thành đầy đủ định mức công việc, giữ gìn vệ sinh khu vực làm việc sạch sẽ, đánh giá 5 sao xuất sắc.',
        ];

        // 3. Cập nhật các bản ghi đánh giá cũ đang có nội dung nhận xét rỗng
        $emptyReviews = DanhGiaCaLam::whereNull('noi_dung_nhan_xet')
            ->orWhere('noi_dung_nhan_xet', '')
            ->get();

        foreach ($emptyReviews as $rev) {
            if ($rev->loai_danh_gia === 'ung_vien_danh_gia_nha_hang') {
                $rev->noi_dung_nhan_xet = $commentsCandidateToRestaurant[array_rand($commentsCandidateToRestaurant)];
            } else {
                $rev->noi_dung_nhan_xet = $commentsRestaurantToCandidate[array_rand($commentsRestaurantToCandidate)];
            }
            $rev->save();
        }

        // 4. Lấy danh sách tất cả các ca làm đã hoàn thành hoặc đã có chấm công xác nhận
        $shifts = CaLamViec::with(['nhaHang.quanLy'])
            ->where('trang_thai_ca', 'hoan_thanh')
            ->orWhereHas('lichSuChamCong', function ($q) {
                $q->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet']);
            })
            ->get();

        foreach ($shifts as $shift) {
            $maCaLam = $shift->ma_ca_lam;
            $nhaHang = $shift->nhaHang;
            $maQuanLy = $nhaHang && $nhaHang->ma_quan_ly ? $nhaHang->ma_quan_ly : 2;

            // Tìm danh sách ứng viên đã tham gia ca làm việc này
            $candidateIds = LichSuChamCong::where('ma_ca_lam', $maCaLam)
                ->pluck('ma_nguoi_dung')
                ->unique()
                ->toArray();

            if (empty($candidateIds)) {
                $candidateIds = HoSoUngTuyen::where('ma_ca_lam', $maCaLam)
                    ->whereIn('trang_thai_ung_tuyen', ['da_nhan', 'da_duyet'])
                    ->pluck('ma_nguoi_dung')
                    ->unique()
                    ->toArray();
            }

            // Nếu không có ứng viên nào, gán ứng viên chính (mã 3: Nguyễn Thị Bảo Châu)
            if (empty($candidateIds)) {
                $candidateIds = [3];
            }

            foreach ($candidateIds as $candId) {
                $candUser = NguoiDung::find($candId);
                if (! $candUser || $candUser->vai_tro === 'admin' || $candUser->vai_tro === 'quan_tri_vien') {
                    continue;
                }

                $shiftDate = $shift->ngay_lam_viec ?: '2026-11-20';
                $timeEnd = Carbon::parse($shiftDate.' '.($shift->gio_ket_thuc ?: '22:00:00'));

                // --- ĐÁNH GIÁ 1: Ứng viên đánh giá Nhà hàng ---
                $hasCandReview = DanhGiaCaLam::where('ma_ca_lam', $maCaLam)
                    ->where('ma_nguoi_danh_gia', $candId)
                    ->where('loai_danh_gia', 'ung_vien_danh_gia_nha_hang')
                    ->first();

                if (! $hasCandReview) {
                    $stars = rand(4, 5);
                    DanhGiaCaLam::create([
                        'ma_ca_lam' => $maCaLam,
                        'ma_nguoi_danh_gia' => $candId,
                        'ma_nguoi_duoc_danh_gia' => $maQuanLy,
                        'loai_danh_gia' => 'ung_vien_danh_gia_nha_hang',
                        'so_sao' => $stars,
                        'tieu_chi_thai_do' => 5,
                        'tieu_chi_chuyen_can' => 5,
                        'tieu_chi_hieu_suat' => $stars,
                        'noi_dung_nhan_xet' => $commentsCandidateToRestaurant[array_rand($commentsCandidateToRestaurant)],
                        'ngay_danh_gia' => $timeEnd,
                    ]);
                }

                // --- ĐÁNH GIÁ 2: Nhà hàng đánh giá Ứng viên ---
                $hasManagerReview = DanhGiaCaLam::where('ma_ca_lam', $maCaLam)
                    ->where('ma_nguoi_duoc_danh_gia', $candId)
                    ->where('loai_danh_gia', 'nha_hang_danh_gia_ung_vien')
                    ->first();

                if (! $hasManagerReview) {
                    $stars = rand(4, 5);
                    DanhGiaCaLam::create([
                        'ma_ca_lam' => $maCaLam,
                        'ma_nguoi_danh_gia' => $maQuanLy,
                        'ma_nguoi_duoc_danh_gia' => $candId,
                        'loai_danh_gia' => 'nha_hang_danh_gia_ung_vien',
                        'so_sao' => $stars,
                        'tieu_chi_thai_do' => 5,
                        'tieu_chi_chuyen_can' => 5,
                        'tieu_chi_hieu_suat' => $stars,
                        'noi_dung_nhan_xet' => $commentsRestaurantToCandidate[array_rand($commentsRestaurantToCandidate)],
                        'ngay_danh_gia' => $timeEnd,
                    ]);
                }
            }
        }
    }
}
