<?php

namespace App\Http\Controllers\QuanLy;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use App\Services\ThongKeService;
use Illuminate\Http\Request;

class ThongKeController extends Controller
{
    public function __construct(private readonly ThongKeService $thongKeService) {}

    /**
     * Màn hình Báo cáo Thống kê & Hiệu suất Ca làm cho Quản lý chi nhánh.
     */
    public function index(Request $request)
    {
        $resolved = $this->resolveManagerAndRestaurant($request);
        if (! isset($resolved['nhaHang'])) {
            return $resolved['response'];
        }

        /** @var NhaHang $nhaHang */
        $nhaHang = $resolved['nhaHang'];
        $range = $this->thongKeService->resolveDateRange($request);
        $periodInput = trim((string) $request->input('nhom_ky', 'thang'));
        $periodGroup = in_array($periodInput, ['tuan', 'thang'], true) ? $periodInput : 'thang';

        $analytics = $this->thongKeService->getManagerAnalytics($nhaHang, $range, $periodGroup);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'nhom_ky' => $periodGroup,
                'range' => [
                    'preset' => $range['preset'],
                    'tu_ngay' => $range['tu_ngay'],
                    'den_ngay' => $range['den_ngay'],
                    'label' => $range['label'],
                ],
                'analytics' => $analytics,
            ]);
        }

        $topUngVien = $analytics['top_ung_vien'] ?? [];

        return view('quan_ly.thong_ke.index', compact(
            'nhaHang',
            'analytics',
            'range',
            'periodGroup',
            'topUngVien'
        ));
    }

    /**
     * Xuất báo cáo chi nhánh dạng CSV tương thích Excel (UTF-8 BOM).
     */
    public function export(Request $request)
    {
        $resolved = $this->resolveManagerAndRestaurant($request);
        if (! isset($resolved['nhaHang'])) {
            return $resolved['response'];
        }

        /** @var NhaHang $nhaHang */
        $nhaHang = $resolved['nhaHang'];
        $range = $this->thongKeService->resolveDateRange($request);
        $periodInput = trim((string) $request->input('nhom_ky', 'thang'));
        $periodGroup = in_array($periodInput, ['tuan', 'thang'], true) ? $periodInput : 'thang';

        $analytics = $this->thongKeService->getManagerAnalytics($nhaHang, $range, $periodGroup);
        $kpis = $analytics['kpis'];
        $perf = $analytics['hieu_suat_ca'];

        $rows = [
            ['BÁO CÁO HIỆU SUẤT & QUỸ LƯƠNG CHI NHÁNH: '.$nhaHang->ten_nha_hang],
            ['Kỳ báo cáo', $range['label'], 'Phân nhóm lương', $periodGroup === 'tuan' ? 'Theo tuần' : 'Theo tháng'],
            [],
            ['I. CHỈ SỐ TỔNG QUAN CHI NHÁNH'],
            ['Chỉ tiêu', 'Giá trị'],
            ['Tổng ca làm việc đã tạo', $kpis['tong_ca_lam']],
            ['Tổng đơn ứng tuyển', $kpis['tong_don_ung_tuyen']],
            ['Số đơn đã nhận', $kpis['don_da_nhan'].' ('.$kpis['ty_le_duyet'].'%)'],
            ['Tổng giờ công đã duyệt (giờ)', $kpis['tong_gio_lam']],
            ['Tổng quỹ lương chi nhánh (VNĐ)', number_format($kpis['tong_quy_luong'], 0, ',', '.')],
            ['Điểm đánh giá trung bình chi nhánh', $kpis['diem_danh_gia_tb'].' / 5.0 ('.$kpis['tong_luot_danh_gia'].' lượt)'],
            [],
            ['II. HIỆU SUẤT CA LÀM VIỆC'],
            ['Trạng thái', 'Số lượt', 'Tỷ lệ (%)'],
            ['Hoàn thành đúng giờ', $perf['dung_gio'], $perf['ty_le_dung_gio']],
            ['Đi muộn', $perf['di_muon'], $perf['ty_le_di_muon']],
            ['Hủy / Bùng ca', $perf['huy_bung_ca'], $perf['ty_le_huy_bung_ca']],
            [],
            ['III. BÁO CÁO QUỸ LƯƠNG THEO KỲ ('.($periodGroup === 'tuan' ? 'TUẦN' : 'THÁNG').')'],
            ['Kỳ lương', 'Số ca đã duyệt', 'Tổng giờ làm', 'Chi phí lương (VNĐ)'],
        ];

        foreach ($analytics['quy_luong_theo_ky'] as $row) {
            $rows[] = [
                $row['ky_label'],
                $row['so_ca'],
                $row['tong_gio'],
                number_format($row['chi_phi_luong'], 0, ',', '.'),
            ];
        }

        $rows[] = [];
        $rows[] = ['IV. TOP 5 ỨNG VIÊN XUẤT SẮC NHẤT'];
        $rows[] = ['Hạng', 'Họ tên', 'Email', 'SĐT', 'Số ca làm', 'Tổng giờ làm', 'Điểm ĐG TB', 'Tỷ lệ đúng giờ (%)', 'Điểm xuất sắc (/100)', 'Thu nhập (VNĐ)'];

        foreach ($analytics['top_ung_vien'] as $idx => $uv) {
            $rows[] = [
                $idx + 1,
                $uv['ho_ten'],
                $uv['email'],
                $uv['so_dien_thoai'],
                $uv['so_ca'],
                $uv['tong_gio_lam'],
                ($uv['so_luot_danh_gia'] ?? 0) > 0 ? $uv['diem_danh_gia_tb'] : 'Chưa có',
                $uv['ty_le_dung_gio'].'%',
                $uv['diem_xuat_sac'].' / 100',
                number_format($uv['tong_thu_nhap'], 0, ',', '.'),
            ];
        }

        $csvContent = $this->thongKeService->buildCsv($rows);
        $fileName = 'bao_cao_chi_nhanh_'.$nhaHang->ma_nha_hang.'_'.date('Ymd_His').'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Xác thực Quản lý nhà hàng và lấy đúng chi nhánh trực thuộc.
     */
    private function resolveManagerAndRestaurant(Request $request): array
    {
        $sessionUser = session('user') ?? auth()->user();
        if (! $sessionUser) {
            $msg = 'Vui lòng đăng nhập tài khoản Quản lý nhà hàng để xem thống kê.';
            if ($request->ajax() || $request->wantsJson()) {
                return ['response' => response()->json(['success' => false, 'message' => $msg], 401)];
            }

            return ['response' => redirect('/dang-nhap')->with('error', $msg)];
        }

        $role = is_array($sessionUser) ? ($sessionUser['vai_tro'] ?? '') : ($sessionUser->vai_tro ?? '');
        if ($role !== 'quan_ly_nha_hang') {
            $msg = 'Bạn không có quyền truy cập mục thống kê Quản lý chi nhánh!';
            if ($request->ajax() || $request->wantsJson()) {
                return ['response' => response()->json(['success' => false, 'message' => $msg], 403)];
            }

            return ['response' => redirect('/')->with('error', $msg)];
        }

        $userId = is_array($sessionUser) ? (int) ($sessionUser['ma_nguoi_dung'] ?? 0) : (int) ($sessionUser->ma_nguoi_dung ?? 0);
        $branchIdFromSession = is_array($sessionUser)
            ? (int) ($sessionUser['ma_nha_hang_truc_thuoc'] ?? 0)
            : (int) ($sessionUser->ma_nha_hang_truc_thuoc ?? 0);

        $managerModel = $userId > 0
            ? NguoiDung::with(['nhaHang.thuongHieu', 'nhaHangTrucThuoc.thuongHieu'])->find($userId)
            : null;

        $nhaHang = $managerModel ? ($managerModel->nhaHang ?? $managerModel->nhaHangTrucThuoc) : null;
        if (! $nhaHang && $branchIdFromSession > 0) {
            $nhaHang = NhaHang::with('thuongHieu')->find($branchIdFromSession);
        }

        if (! $nhaHang) {
            $msg = 'Tài khoản Quản lý của bạn chưa được phân công chi nhánh nhà hàng nào.';
            if ($request->ajax() || $request->wantsJson()) {
                return ['response' => response()->json(['success' => false, 'message' => $msg], 404)];
            }

            return ['response' => redirect('/')->with('warning', $msg)];
        }

        return ['nhaHang' => $nhaHang, 'manager' => $managerModel ?? $sessionUser];
    }
}
