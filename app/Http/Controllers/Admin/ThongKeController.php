<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhaHang;
use App\Models\ThuongHieu;
use App\Services\ThongKeService;
use Illuminate\Http\Request;

class ThongKeController extends Controller
{
    public function __construct(private readonly ThongKeService $thongKeService) {}

    /**
     * Màn hình Báo cáo Thống kê & Phân tích toàn hệ thống (Admin).
     */
    public function index(Request $request)
    {
        $range = $this->thongKeService->resolveDateRange($request);
        $brandId = $request->filled('ma_thuong_hieu') ? (int) $request->input('ma_thuong_hieu') : null;
        $restaurantId = $request->filled('ma_nha_hang') ? (int) $request->input('ma_nha_hang') : null;

        $analytics = $this->thongKeService->getAdminAnalytics($range, $brandId, $restaurantId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'range' => [
                    'preset' => $range['preset'],
                    'tu_ngay' => $range['tu_ngay'],
                    'den_ngay' => $range['den_ngay'],
                    'label' => $range['label'],
                ],
                'analytics' => $analytics,
            ]);
        }

        $brands = ThuongHieu::orderBy('ten_thuong_hieu')->get(['ma_thuong_hieu', 'ten_thuong_hieu']);
        $restaurants = NhaHang::orderBy('ten_nha_hang')->get(['ma_nha_hang', 'ma_thuong_hieu', 'ten_nha_hang']);
        $topUngVien = $analytics['top_ung_vien'] ?? [];

        return view('admin.thong_ke.index', compact(
            'analytics',
            'range',
            'brands',
            'restaurants',
            'brandId',
            'restaurantId',
            'topUngVien'
        ));
    }

    /**
     * Xuất báo cáo tổng hợp chi phí & hiệu suất dạng CSV tương thích Excel (UTF-8 BOM).
     */
    public function export(Request $request)
    {
        $range = $this->thongKeService->resolveDateRange($request);
        $brandId = $request->filled('ma_thuong_hieu') ? (int) $request->input('ma_thuong_hieu') : null;
        $restaurantId = $request->filled('ma_nha_hang') ? (int) $request->input('ma_nha_hang') : null;

        $analytics = $this->thongKeService->getAdminAnalytics($range, $brandId, $restaurantId);
        $kpis = $analytics['kpis'];

        $rows = [
            ['BÁO CÁO THỐNG KÊ & PHÂN TÍCH HỆ THỐNG GOLDEN SHIFT (ADMIN)'],
            ['Kỳ báo cáo', $range['label'], 'Ngày xuất', date('d/m/Y H:i:s')],
            [],
            ['I. CHỈ SỐ TỔNG QUAN TOÀN HỆ THỐNG'],
            ['Chỉ tiêu', 'Giá trị'],
            ['Tổng số ca làm việc đã phát hành', $kpis['tong_ca_lam']],
            ['Tổng số đơn ứng tuyển', $kpis['tong_don_ung_tuyen']],
            ['Số đơn ứng tuyển thành công (Đã nhận)', $kpis['don_da_nhan']],
            ['Tỷ lệ ứng tuyển thành công (%)', $kpis['ty_le_ung_tuyen_thanh_cong']],
            ['Tổng chi phí nhân sự đã thanh toán (VNĐ)', number_format($kpis['chi_phi_da_thanh_toan'], 0, ',', '.')],
            ['Tổng quỹ lương theo chấm công đã duyệt (VNĐ)', number_format($kpis['tong_quy_luong_cham_cong'], 0, ',', '.')],
            ['Tổng số giờ làm việc đã duyệt (giờ)', $kpis['tong_gio_lam']],
            ['Điểm đánh giá trung bình toàn mạng lưới', $kpis['diem_danh_gia_tb'].' / 5.0 ('.$kpis['tong_luot_danh_gia'].' lượt)'],
            [],
            ['II. CHI PHÍ NHÂN SỰ & TUYỂN DỤNG THEO THƯƠNG HIỆU'],
            ['Thương hiệu', 'Số chi nhánh', 'Số ca làm', 'Tổng đơn', 'Đơn đã nhận', 'Tỷ lệ thành công (%)', 'Tổng giờ làm', 'Quỹ lương (VNĐ)', 'Điểm ĐG TB'],
        ];

        foreach ($analytics['theo_thuong_hieu'] as $b) {
            $rows[] = [
                $b['ten_thuong_hieu'],
                $b['so_chi_nhanh'],
                $b['so_ca_lam'],
                $b['tong_don'],
                $b['don_da_nhan'],
                $b['ty_le_thanh_cong'],
                $b['tong_gio_lam'],
                number_format($b['quy_luong'], 0, ',', '.'),
                $b['diem_tb'] !== null ? $b['diem_tb'] : 'Chưa có',
            ];
        }

        $rows[] = [];
        $rows[] = ['III. CHI PHÍ NHÂN SỰ & TUYỂN DỤNG THEO CHI NHÁNH NHÀ HÀNG'];
        $rows[] = ['Chi nhánh nhà hàng', 'Thương hiệu', 'Số ca làm', 'Tổng đơn', 'Đơn đã nhận', 'Tỷ lệ thành công (%)', 'Tổng giờ làm', 'Quỹ lương (VNĐ)', 'Điểm ĐG TB'];

        foreach ($analytics['theo_nha_hang'] as $r) {
            $rows[] = [
                $r['ten_nha_hang'],
                $r['ten_thuong_hieu'],
                $r['so_ca_lam'],
                $r['tong_don'],
                $r['don_da_nhan'],
                $r['ty_le_thanh_cong'],
                $r['tong_gio_lam'],
                number_format($r['quy_luong'], 0, ',', '.'),
                $r['diem_tb'] !== null ? $r['diem_tb'] : 'Chưa có',
            ];
        }

        $rows[] = [];
        $rows[] = ['IV. BÁO CÁO VI PHẠM & TUÂN THỦ THEO LOẠI'];
        $rows[] = ['Loại vi phạm', 'Số lượng', 'Tỷ lệ (%)', 'Chờ xử lý', 'Đã xử lý', 'Từ chối'];

        foreach ($analytics['vi_pham_theo_loai'] as $vp) {
            $rows[] = [
                $vp['ten_loai'],
                $vp['so_luong'],
                $vp['ty_le'],
                $vp['cho_xu_ly'],
                $vp['da_xu_ly'],
                $vp['tu_choi'],
            ];
        }

        $rows[] = [];
        $rows[] = ['V. TOP 5 ỨNG VIÊN XUẤT SẮC NHẤT TOÀN HỆ THỐNG'];
        $rows[] = ['Hạng', 'Họ tên', 'Email', 'Số ca hoàn thành', 'Tổng giờ làm', 'Điểm ĐG TB', 'Tỷ lệ đúng giờ (%)', 'Điểm xuất sắc (/100)'];

        foreach ($analytics['top_ung_vien'] ?? [] as $idx => $uv) {
            $rows[] = [
                $idx + 1,
                $uv['ho_ten'],
                $uv['email'],
                $uv['so_ca'],
                $uv['tong_gio_lam'],
                ($uv['so_luot_danh_gia'] ?? 0) > 0 ? $uv['diem_danh_gia_tb'] : 'Chưa có',
                $uv['ty_le_dung_gio'].'%',
                $uv['diem_xuat_sac'].' / 100',
            ];
        }

        $csvContent = $this->thongKeService->buildCsv($rows);
        $fileName = 'bao_cao_thong_ke_admin_'.date('Ymd_His').'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
