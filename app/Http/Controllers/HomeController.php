<?php

namespace App\Http\Controllers;

use App\Models\CaLamViec;
use App\Models\ThuongHieu;

class HomeController extends Controller
{
    public function index()
    {
        $user = session('user') ?? auth()->user();
        $userRole = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');
        if ($userRole === 'quan_ly_nha_hang') {
            return redirect()->route('ca-lam-viec.index');
        } elseif ($userRole === 'quan_tri_vien') {
            return redirect()->route('admin.users.index');
        }

        // Tự động đóng các ca đã quá ngày
        CaLamViec::autoClosePastShifts();

        $today = date('Y-m-d');

        try {
            $thuongHieuList = ThuongHieu::withCount('nhaHang as so_nha_hang')->where('trang_thai', 'hoat_dong')->get();
            $caLamList = CaLamViec::with(['nhaHang.thuongHieu'])
                ->where('trang_thai_ca', 'dang_tuyen')
                ->where('ngay_lam_viec', '>=', $today)
                ->orderBy('ngay_lam_viec', 'asc')
                ->orderBy('gio_bat_dau', 'asc')
                ->take(4)
                ->get();

            $tongSoCa = CaLamViec::where('trang_thai_ca', 'dang_tuyen')->where('ngay_lam_viec', '>=', $today)->count();
            $tongSoThuongHieu = $thuongHieuList->count();
        } catch (\Throwable $e) {
            // Fallback an toàn khi DB chưa start
            $thuongHieuList = collect([
                (object) [
                    'ma_thuong_hieu' => 1,
                    'ten_thuong_hieu' => 'Golden Hotpot',
                    'ma_dinh_danh' => 'golden-hotpot',
                    'mo_ta_ngan' => 'Chuỗi nhà hàng lẩu tinh hoa châu Á',
                    'so_nha_hang' => 3,
                ],
                (object) [
                    'ma_thuong_hieu' => 2,
                    'ten_thuong_hieu' => 'Golden BBQ & Grill',
                    'ma_dinh_danh' => 'golden-bbq',
                    'mo_ta_ngan' => 'Ẩm thực nướng than hoa thượng hạng',
                    'so_nha_hang' => 2,
                ],
                (object) [
                    'ma_thuong_hieu' => 3,
                    'ten_thuong_hieu' => 'Golden Dimsum House',
                    'ma_dinh_danh' => 'golden-dimsum',
                    'mo_ta_ngan' => 'Hương vị ẩm thực Quảng Đông trứ danh',
                    'so_nha_hang' => 2,
                ],
            ]);

            $caLamList = collect([
                (object) [
                    'ma_ca_lam' => 1,
                    'ten_vi_tri_tuyen_dung' => 'Nhân viên Phục vụ',
                    'ngay_lam_viec' => date('Y-m-d', strtotime('+1 day')),
                    'gio_bat_dau' => '17:00:00',
                    'gio_ket_thuc' => '23:00:00',
                    'muc_luong_theo_gio' => 30000,
                    'so_luong_tuyen' => 3,
                    'so_luong_da_tuyen' => 1,
                    'trang_thai_ca' => 'dang_tuyen',
                    'computed_status' => 'dang_tuyen',
                    'nhaHang' => (object) [
                        'ten_nha_hang' => 'Golden Hotpot - Đồng Khởi',
                        'quan_huyen' => 'Quận 1',
                        'thuongHieu' => (object) ['ten_thuong_hieu' => 'Golden Hotpot'],
                    ],
                ],
                (object) [
                    'ma_ca_lam' => 2,
                    'ten_vi_tri_tuyen_dung' => 'Nhân viên Tiếp thực',
                    'ngay_lam_viec' => date('Y-m-d', strtotime('+1 day')),
                    'gio_bat_dau' => '18:00:00',
                    'gio_ket_thuc' => '23:00:00',
                    'muc_luong_theo_gio' => 28000,
                    'so_luong_tuyen' => 2,
                    'so_luong_da_tuyen' => 0,
                    'trang_thai_ca' => 'dang_tuyen',
                    'computed_status' => 'dang_tuyen',
                    'nhaHang' => (object) [
                        'ten_nha_hang' => 'Golden BBQ - Landmark 81',
                        'quan_huyen' => 'Bình Thạnh',
                        'thuongHieu' => (object) ['ten_thuong_hieu' => 'Golden BBQ & Grill'],
                    ],
                ],
            ]);

            $tongSoCa = 2;
            $tongSoThuongHieu = 3;
        }

        return view('home.index', [
            'thuongHieuList' => $thuongHieuList,
            'caLamList' => $caLamList,
            'tongSoCa' => $tongSoCa,
            'tongSoThuongHieu' => $tongSoThuongHieu,
        ]);
    }
}
