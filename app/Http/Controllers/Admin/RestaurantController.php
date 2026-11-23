<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhGiaCaLam;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * Lưu nhà hàng mới (POST)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_thuong_hieu' => 'required|integer',
            'ma_quan_ly' => 'nullable|integer',
            'ten_nha_hang' => 'required|string|max:200',
            'so_dien_thoai_lien_he' => ['required', 'regex:/^0[0-9]{9}$/'],
            'email_chi_nhanh' => 'nullable|email|max:150',
            'dia_chi_chi_tiet' => 'required|string|max:255',
            'phuong_xa' => 'nullable|string|max:100',
            'quan_huyen' => 'required|string|max:100',
            'tinh_thanh_pho' => 'required|string|max:100',
            'trang_thai_hoat_dong' => 'in:dang_hoat_dong,dang_sua_chua,tam_ngung',
        ], [
            'ma_thuong_hieu.required' => 'Vui lòng chọn Thương hiệu.',
            'ten_nha_hang.required' => 'Tên nhà hàng là bắt buộc.',
            'so_dien_thoai_lien_he.required' => 'SĐT hotline nhà hàng là bắt buộc.',
            'so_dien_thoai_lien_he.regex' => 'SĐT hotline phải đúng 10 chữ số và bắt đầu bằng số 0.',
            'dia_chi_chi_tiet.required' => 'Địa chỉ chi tiết là bắt buộc.',
            'quan_huyen.required' => 'Quận/Huyện là bắt buộc.',
            'tinh_thanh_pho.required' => 'Tỉnh/Thành phố là bắt buộc.',
        ]);

        try {
            $newManagerId = ! empty($validated['ma_quan_ly']) ? (int) $validated['ma_quan_ly'] : null;

            if ($newManagerId) {
                $manager = NguoiDung::where('ma_nguoi_dung', $newManagerId)
                    ->where('vai_tro', 'quan_ly_nha_hang')
                    ->first();

                if (! $manager) {
                    return redirect()->back()->withInput()
                        ->with('error', 'Người dùng được chọn không phải là tài khoản Quản lý nhà hàng hợp lệ.');
                }

                // Mỗi quản lý chỉ phụ trách 1 chi nhánh tại một thời điểm (khớp quan hệ hasOne)
                NhaHang::where('ma_quan_ly', $newManagerId)->update(['ma_quan_ly' => null]);
            }

            $validated['trang_thai_hoat_dong'] = $validated['trang_thai_hoat_dong'] ?? 'dang_hoat_dong';
            $validated['ma_quan_ly'] = $newManagerId;
            $restaurant = NhaHang::create($validated);

            if ($newManagerId) {
                NguoiDung::where('ma_nguoi_dung', $newManagerId)
                    ->update(['ma_nha_hang_truc_thuoc' => $restaurant->ma_nha_hang]);
            }

            return redirect()->route('admin.categories.index', ['tab' => 'nha_hang'])
                ->with('success', 'Thêm nhà hàng "'.$validated['ten_nha_hang'].'" thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi thêm nhà hàng: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật nhà hàng (POST)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ma_thuong_hieu' => 'required|integer',
            'ma_quan_ly' => 'nullable|integer',
            'ten_nha_hang' => 'required|string|max:200',
            'so_dien_thoai_lien_he' => ['required', 'regex:/^0[0-9]{9}$/'],
            'email_chi_nhanh' => 'nullable|email|max:150',
            'dia_chi_chi_tiet' => 'required|string|max:255',
            'phuong_xa' => 'nullable|string|max:100',
            'quan_huyen' => 'required|string|max:100',
            'tinh_thanh_pho' => 'required|string|max:100',
            'trang_thai_hoat_dong' => 'in:dang_hoat_dong,dang_sua_chua,tam_ngung',
        ], [
            'ma_thuong_hieu.required' => 'Vui lòng chọn Thương hiệu.',
            'ten_nha_hang.required' => 'Tên nhà hàng là bắt buộc.',
            'so_dien_thoai_lien_he.required' => 'SĐT hotline nhà hàng là bắt buộc.',
            'so_dien_thoai_lien_he.regex' => 'SĐT hotline phải đúng 10 chữ số và bắt đầu bằng số 0.',
            'dia_chi_chi_tiet.required' => 'Địa chỉ chi tiết là bắt buộc.',
            'quan_huyen.required' => 'Quận/Huyện là bắt buộc.',
            'tinh_thanh_pho.required' => 'Tỉnh/Thành phố là bắt buộc.',
        ]);

        try {
            $restaurant = NhaHang::findOrFail($id);
            $oldManagerId = $restaurant->ma_quan_ly ? (int) $restaurant->ma_quan_ly : null;
            $newManagerId = ! empty($validated['ma_quan_ly']) ? (int) $validated['ma_quan_ly'] : null;

            if ($newManagerId) {
                $manager = NguoiDung::where('ma_nguoi_dung', $newManagerId)
                    ->where('vai_tro', 'quan_ly_nha_hang')
                    ->first();

                if (! $manager) {
                    return redirect()->back()->withInput()
                        ->with('error', 'Người dùng được chọn không phải là tài khoản Quản lý nhà hàng hợp lệ.');
                }

                // Gỡ phân công ở chi nhánh cũ nếu quản lý này từng đứng tên chi nhánh khác
                NhaHang::where('ma_quan_ly', $newManagerId)
                    ->where('ma_nha_hang', '!=', $restaurant->ma_nha_hang)
                    ->update(['ma_quan_ly' => null]);
            }

            $validated['ma_quan_ly'] = $newManagerId;
            $restaurant->update($validated);

            // Đồng bộ ngược lại ma_nha_hang_truc_thuoc trên bảng nguoi_dung
            if ($oldManagerId && $oldManagerId !== $newManagerId) {
                NguoiDung::where('ma_nguoi_dung', $oldManagerId)
                    ->where('ma_nha_hang_truc_thuoc', $restaurant->ma_nha_hang)
                    ->update(['ma_nha_hang_truc_thuoc' => null]);
            }

            if ($newManagerId) {
                NguoiDung::where('ma_nguoi_dung', $newManagerId)
                    ->update(['ma_nha_hang_truc_thuoc' => $restaurant->ma_nha_hang]);
            }

            return redirect()->route('admin.categories.index', ['tab' => 'nha_hang'])
                ->with('success', 'Cập nhật nhà hàng "'.$restaurant->ten_nha_hang.'" thành công!');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Không tìm thấy nhà hàng cần sửa.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi cập nhật: '.$e->getMessage());
        }
    }

    /**
     * Chuyển trạng thái nhà hàng (POST)
     */
    public function toggleStatus(Request $request, $id)
    {
        $newStatus = trim($request->input('trang_thai_hoat_dong', ''));
        $validStatuses = ['dang_hoat_dong', 'dang_sua_chua', 'tam_ngung'];

        try {
            $restaurant = NhaHang::findOrFail($id);

            if (! in_array($newStatus, $validStatuses)) {
                // Xoay vòng trạng thái
                $cycle = ['dang_hoat_dong' => 'dang_sua_chua', 'dang_sua_chua' => 'tam_ngung', 'tam_ngung' => 'dang_hoat_dong'];
                $newStatus = $cycle[$restaurant->trang_thai_hoat_dong] ?? 'dang_hoat_dong';
            }

            $restaurant->update(['trang_thai_hoat_dong' => $newStatus]);
            $statusLabels = ['dang_hoat_dong' => 'Đang hoạt động', 'dang_sua_chua' => 'Đang sửa chữa', 'tam_ngung' => 'Tạm ngừng'];
            $label = $statusLabels[$newStatus] ?? $newStatus;

            return redirect()->route('admin.categories.index', ['tab' => 'nha_hang'])
                ->with('success', 'Đã chuyển trạng thái "'.$restaurant->ten_nha_hang.'" sang ['.$label.'].');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi: '.$e->getMessage());
        }
    }

    /**
     * Xóa nhà hàng — CHỈ xóa nếu chưa có ca làm việc nào (POST)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $restaurant = NhaHang::withCount('caLamViec')->findOrFail($id);
            if ($restaurant->ca_lam_viec_count > 0) {
                return redirect()->route('admin.categories.index', ['tab' => 'nha_hang'])
                    ->with('error', 'KHÔNG THỂ XÓA nhà hàng "'.$restaurant->ten_nha_hang.'" vì đã phát sinh '
                        .$restaurant->ca_lam_viec_count.' ca làm việc. '
                        .'Vui lòng chuyển trạng thái sang "Tạm ngừng" thay thế.');
            }
            $name = $restaurant->ten_nha_hang;
            $restaurant->delete();

            return redirect()->route('admin.categories.index', ['tab' => 'nha_hang'])
                ->with('success', 'Đã xóa nhà hàng "'.$name.'" thành công!');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Không tìm thấy nhà hàng cần xóa.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa: '.$e->getMessage());
        }
    }

    /**
     * Xem danh sách đánh giá chi tiết của một chi nhánh nhà hàng (GET)
     */
    public function showDanhGia(Request $request, $ma_nha_hang)
    {
        $nhaHang = NhaHang::with(['thuongHieu', 'quanLy'])->find($ma_nha_hang);
        if (! $nhaHang) {
            return redirect()->route('admin.categories.index', ['tab' => 'nha_hang'])
                ->with('error', 'Không tìm thấy chi nhánh nhà hàng cần xem đánh giá.');
        }

        // Chỉ lấy đánh giá từ ứng viên gửi cho nhà hàng thuộc các ca làm của nhà hàng này
        $baseQuery = DanhGiaCaLam::query()
            ->whereHas('caLamViec', function ($q) use ($nhaHang) {
                $q->where('ma_nha_hang', $nhaHang->ma_nha_hang);
            })
            ->where('loai_danh_gia', 'ung_vien_danh_gia_nha_hang');

        // Thống kê tổng quan (luôn tính trên toàn bộ đánh giá của nhà hàng)
        $starCountsRaw = (clone $baseQuery)
            ->selectRaw('so_sao, COUNT(*) as total')
            ->groupBy('so_sao')
            ->pluck('total', 'so_sao');

        $starCounts = [
            5 => (int) ($starCountsRaw[5] ?? 0),
            4 => (int) ($starCountsRaw[4] ?? 0),
            3 => (int) ($starCountsRaw[3] ?? 0),
            2 => (int) ($starCountsRaw[2] ?? 0),
            1 => (int) ($starCountsRaw[1] ?? 0),
        ];

        $tongLuot = array_sum($starCounts);
        $weightedSum = ($starCounts[5] * 5)
            + ($starCounts[4] * 4)
            + ($starCounts[3] * 3)
            + ($starCounts[2] * 2)
            + ($starCounts[1] * 1);
        $diemTrungBinh = $tongLuot > 0 ? round($weightedSum / $tongLuot, 1) : 0.0;

        // Tham số lọc theo số sao & sắp xếp
        $soSaoInput = (string) $request->input('so_sao', '');
        $soSao = in_array($soSaoInput, ['1', '2', '3', '4', '5'], true) ? $soSaoInput : '';

        $sortInput = (string) $request->input('sort', 'all');
        $sort = in_array($sortInput, ['all', 'cao_den_thap', 'thap_den_cao'], true) ? $sortInput : 'all';

        $danhGiaQuery = (clone $baseQuery)->with([
            'nguoiDanhGia',
            'caLamViec.lichSuChamCong',
        ]);

        if ($soSao !== '') {
            $danhGiaQuery->where('so_sao', (int) $soSao);
        }

        if ($sort === 'cao_den_thap') {
            $danhGiaQuery->orderBy('so_sao', 'desc')->orderBy('ngay_danh_gia', 'desc');
        } elseif ($sort === 'thap_den_cao') {
            $danhGiaQuery->orderBy('so_sao', 'asc')->orderBy('ngay_danh_gia', 'desc');
        } else {
            $danhGiaQuery->orderBy('ngay_danh_gia', 'desc');
        }

        $danhGiaList = $danhGiaQuery->paginate(5)->appends($request->query());

        return view('admin.nha_hang.danh_gia', compact(
            'nhaHang',
            'danhGiaList',
            'diemTrungBinh',
            'tongLuot',
            'starCounts',
            'sort',
            'soSao'
        ));
    }
}
