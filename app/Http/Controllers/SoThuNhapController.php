<?php

namespace App\Http\Controllers;

use App\Models\LichSuChamCong;
use App\Models\SoThuNhap;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SoThuNhapController extends Controller
{
    /**
     * Giao diện Sổ Thu Nhập dành cho Ứng viên (/ung-vien/so-thu-nhap)
     */
    public function index(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return redirect('/dang-nhap')->with('warning', 'Vui lòng đăng nhập với tài khoản Ứng viên để xem Sổ Thu Nhập.');
        }

        $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);

        ChamCongController::processAutoApprovals();

        // 1. Danh sách lịch sử ca làm việc đã hoàn thành & đã xác nhận chấm công thực tế của ứng viên
        $lichSuQuery = LichSuChamCong::with([
            'caLamViec.nhaHang.thuongHieu',
            'caLamViec.nhaHang.quanLy',
            'nguoiXacNhan',
        ])
            ->where('ma_nguoi_dung', $maNguoiDung)
            ->whereIn('trang_thai_cham_cong', ['da_xac_nhan', 'tu_dong_duyet'])
            ->orderBy('thoi_gian_ra_thuc_te', 'desc');

        $allLichSuCaLam = (clone $lichSuQuery)->get();

        // 2. Tự động đồng bộ chuẩn xác bảng so_thu_nhap từ ca làm việc thực tế (tránh dữ liệu cứng/sai lệch)
        $this->syncSoThuNhapFromAttendance($maNguoiDung, $allLichSuCaLam);

        // Phân trang danh sách hiển thị (5 mục/trang) sau khi đã đồng bộ đủ tất cả kỳ lương
        $lichSuCaLam = $lichSuQuery->paginate(5)->withQueryString();

        // 3. Danh sách các kỳ sổ thu nhập sau khi đã đồng bộ
        $soThuNhapList = SoThuNhap::where('ma_nguoi_dung', $maNguoiDung)
            ->orderBy('ky_luong', 'desc')
            ->get();

        // Tính tổng lũy kế tất cả kỳ lương
        $tongSoCaLam = (int) $soThuNhapList->sum('tong_so_ca_lam');
        $tongGioLam = (float) $soThuNhapList->sum('tong_gio_lam');
        $tongLuongCoBan = (float) $soThuNhapList->sum('tong_tien_luong_co_ban');
        $tongThuong = (float) $soThuNhapList->sum('tong_tien_thuong');
        $tongPhat = (float) $soThuNhapList->sum('tong_tien_phat');
        $tongThucNhan = (float) $soThuNhapList->sum('thuc_nhan');

        // Lấy kỳ lương hiện tại
        $kyLuongHienTai = 'Tháng '.Carbon::now('Asia/Ho_Chi_Minh')->format('m/Y');
        $soThuNhapHienTai = $soThuNhapList->first(function ($item) use ($kyLuongHienTai) {
            return ($item->formatted_ky_luong ?? $item->ky_luong) === $kyLuongHienTai;
        });

        return view('ung_vien.so_thu_nhap', compact(
            'soThuNhapList',
            'soThuNhapHienTai',
            'tongSoCaLam',
            'tongGioLam',
            'tongLuongCoBan',
            'tongThuong',
            'tongPhat',
            'tongThucNhan',
            'kyLuongHienTai',
            'lichSuCaLam'
        ));
    }

    /**
     * Đồng bộ số liệu Sổ Thu Nhập theo đúng ca làm thực tế từ lịch sử chấm công
     */
    public function syncSoThuNhapFromAttendance(int $maNguoiDung, $lichSuCaLam): void
    {
        if ($lichSuCaLam->isEmpty()) {
            return;
        }

        // Nhóm các ca làm theo kỳ lương (Tháng MM/YYYY)
        $grouped = $lichSuCaLam->groupBy(function ($item) {
            $shift = $item->caLamViec;
            $date = $item->thoi_gian_ra_thuc_te
                ?? ($item->thoi_gian_vao_thuc_te
                ?? ($shift && $shift->ngay_lam_viec ? $shift->ngay_lam_viec : $item->ngay_tao));

            return 'Tháng '.Carbon::parse($date)->setTimezone('Asia/Ho_Chi_Minh')->format('m/Y');
        });

        $validKyLuongs = [];

        foreach ($grouped as $kyLuong => $items) {
            $validKyLuongs[] = $kyLuong;

            $soCa = $items->count();
            $soGio = round((float) $items->sum('so_gio_lam_thuc_te'), 1);
            $luongCoBan = 0;
            $thuong = 0;

            foreach ($items as $cc) {
                $shift = $cc->caLamViec;
                $mucLuong = (float) ($shift ? $shift->muc_luong_theo_gio : 0);
                $gio = (float) $cc->so_gio_lam_thuc_te;
                $luongCoBan += round($gio * $mucLuong);

                // Trích xuất tiền thưởng từ ghi chú nếu có (ví dụ: 'Thưởng thêm: +20.000 VNĐ')
                if (! empty($cc->ghi_chu_cham_cong) && preg_match('/Thưởng thêm:\s*\+?([\d\.]+)/u', $cc->ghi_chu_cham_cong, $m)) {
                    $thuong += (float) str_replace('.', '', $m[1]);
                }
            }

            $thucNhan = $luongCoBan + $thuong;

            // Cập nhật hoặc tạo mới bản ghi sổ thu nhập theo dữ liệu thực
            $stn = SoThuNhap::where('ma_nguoi_dung', $maNguoiDung)
                ->where(function ($q) use ($kyLuong) {
                    $q->where('ky_luong', $kyLuong);
                    if (preg_match('/^Tháng\s*(\d{2})\/(\d{4})$/u', $kyLuong, $m)) {
                        $q->orWhere('ky_luong', $m[2].'-'.$m[1]);
                    }
                })
                ->first();

            if ($stn) {
                $stn->ky_luong = $kyLuong;
                $stn->tong_so_ca_lam = $soCa;
                $stn->tong_gio_lam = $soGio;
                $stn->tong_tien_luong_co_ban = $luongCoBan;
                $stn->tong_tien_thuong = $thuong;
                $stn->tong_tien_phat = 0;
                $stn->thuc_nhan = $thucNhan;
                $stn->save();
            } else {
                SoThuNhap::create([
                    'ma_nguoi_dung' => $maNguoiDung,
                    'ky_luong' => $kyLuong,
                    'tong_so_ca_lam' => $soCa,
                    'tong_gio_lam' => $soGio,
                    'tong_tien_luong_co_ban' => $luongCoBan,
                    'tong_tien_thuong' => $thuong,
                    'tong_tien_phat' => 0,
                    'thuc_nhan' => $thucNhan,
                    'trang_thai_thanh_toan' => 'da_thanh_toan',
                    'ngay_tao' => $items->first()->ngay_tao ?? now(),
                ]);
            }
        }

        // Xóa những kỳ lương không có ca làm việc thực tế
        SoThuNhap::where('ma_nguoi_dung', $maNguoiDung)
            ->whereNotIn('ky_luong', $validKyLuongs)
            ->delete();
    }
}
