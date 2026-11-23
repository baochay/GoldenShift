<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use App\Models\ThuongHieu;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class BrandController extends Controller
{
    /**
     * Danh sách thương hiệu (dùng chung với index của categories)
     */
    public function index(Request $request)
    {
        $currentTab = $request->input('tab', 'thuong_hieu');
        $searchBrand = trim($request->input('search_brand', ''));
        $searchRestaurant = trim($request->input('search_restaurant', ''));
        $filterBrand = (int) $request->input('filter_brand', 0);
        $filterCity = trim($request->input('filter_city', ''));

        try {
            // Danh sách thương hiệu
            $queryBrands = ThuongHieu::withCount('nhaHang');
            if (! empty($searchBrand)) {
                $queryBrands->where(function ($q) use ($searchBrand) {
                    $q->where('ten_thuong_hieu', 'LIKE', "%{$searchBrand}%")
                        ->orWhere('ma_dinh_danh', 'LIKE', "%{$searchBrand}%")
                        ->orWhere('loai_hinh_am_thuc', 'LIKE', "%{$searchBrand}%");
                });
            }
            $brandCount = (clone $queryBrands)->count();
            $activeBrands = (clone $queryBrands)->where('trang_thai', 'hoat_dong')->count();
            $brands = $queryBrands->orderBy('ten_thuong_hieu')
                ->paginate(5, ['*'], 'brands_page')
                ->appends(array_merge($request->except('brands_page'), ['tab' => 'thuong_hieu']));

            // Danh sách thương hiệu đầy đủ cho dropdown lọc nhà hàng
            $brandsForFilter = ThuongHieu::orderBy('ten_thuong_hieu')->get();

            // Danh sách nhà hàng
            $queryRestaurants = NhaHang::with(['quanLy', 'thuongHieu']);
            if ($filterBrand > 0) {
                $queryRestaurants->where('ma_thuong_hieu', $filterBrand);
            }
            if (! empty($filterCity)) {
                $queryRestaurants->where('tinh_thanh_pho', 'LIKE', "%{$filterCity}%");
            }
            if (! empty($searchRestaurant)) {
                $queryRestaurants->where(function ($q) use ($searchRestaurant) {
                    $q->where('ten_nha_hang', 'LIKE', "%{$searchRestaurant}%")
                        ->orWhere('so_dien_thoai_lien_he', 'LIKE', "%{$searchRestaurant}%")
                        ->orWhere('dia_chi_chi_tiet', 'LIKE', "%{$searchRestaurant}%");
                });
            }
            $restaurantCount = (clone $queryRestaurants)->count();
            $activeRestaurants = (clone $queryRestaurants)->where('trang_thai_hoat_dong', 'dang_hoat_dong')->count();
            $cities = NhaHang::query()->pluck('tinh_thanh_pho')->filter()->unique()->sort()->values();
            $restaurants = $queryRestaurants->orderBy('ten_nha_hang')
                ->paginate(5, ['*'], 'restaurants_page')
                ->appends(array_merge($request->except('restaurants_page'), ['tab' => 'nha_hang']));

        } catch (\Throwable $e) {
            // Fallback mock nếu DB chưa kết nối
            $allMockBrands = collect([
                (object) [
                    'ma_thuong_hieu' => 1,
                    'ten_thuong_hieu' => 'Golden Hotpot',
                    'ma_dinh_danh' => 'golden-hotpot',
                    'bieu_tuong' => null,
                    'loai_hinh_am_thuc' => 'Lẩu Nướng cao cấp',
                    'trang_thai' => 'hoat_dong',
                    'nha_hang_count' => 3,
                    'mo_ta_ngan' => 'Chuỗi lẩu nướng cao cấp phong cách Nhật Bản',
                ],
                (object) [
                    'ma_thuong_hieu' => 2,
                    'ten_thuong_hieu' => 'Golden Stars BBQ',
                    'ma_dinh_danh' => 'golden-stars-bbq',
                    'bieu_tuong' => null,
                    'loai_hinh_am_thuc' => 'BBQ Hàn Quốc',
                    'trang_thai' => 'hoat_dong',
                    'nha_hang_count' => 2,
                    'mo_ta_ngan' => 'Nhà hàng BBQ phong cách Hàn Quốc hiện đại',
                ],
            ]);
            $allMockRestaurants = collect([
                (object) [
                    'ma_nha_hang' => 1,
                    'ma_thuong_hieu' => 1,
                    'ten_nha_hang' => 'Golden Hotpot - Chi nhánh Đồng Khởi',
                    'so_dien_thoai_lien_he' => '0283123456',
                    'email_chi_nhanh' => 'dongkhoi@goldenshift.vn',
                    'dia_chi_chi_tiet' => '123 Đồng Khởi',
                    'phuong_xa' => 'Phường Bến Nghé',
                    'quan_huyen' => 'Quận 1',
                    'tinh_thanh_pho' => 'TP. Hồ Chí Minh',
                    'trang_thai_hoat_dong' => 'dang_hoat_dong',
                    'thuongHieu' => (object) ['ten_thuong_hieu' => 'Golden Hotpot'],
                    'quanLy' => (object) ['ho_ten' => 'Nguyễn Thị Bảo Châu'],
                ],
            ]);

            $brandCount = $allMockBrands->count();
            $activeBrands = $allMockBrands->where('trang_thai', 'hoat_dong')->count();
            $brandsForFilter = $allMockBrands;
            $restaurantCount = $allMockRestaurants->count();
            $activeRestaurants = $allMockRestaurants->where('trang_thai_hoat_dong', 'dang_hoat_dong')->count();
            $cities = $allMockRestaurants->pluck('tinh_thanh_pho')->filter()->unique()->sort()->values();

            $brandsPage = max(1, (int) $request->input('brands_page', 1));
            $brands = (new LengthAwarePaginator(
                $allMockBrands->forPage($brandsPage, 5)->values(),
                $brandCount,
                5,
                $brandsPage,
                ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'brands_page']
            ))->appends(array_merge($request->except('brands_page'), ['tab' => 'thuong_hieu']));

            $restaurantsPage = max(1, (int) $request->input('restaurants_page', 1));
            $restaurants = (new LengthAwarePaginator(
                $allMockRestaurants->forPage($restaurantsPage, 5)->values(),
                $restaurantCount,
                5,
                $restaurantsPage,
                ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'restaurants_page']
            ))->appends(array_merge($request->except('restaurants_page'), ['tab' => 'nha_hang']));
        }

        // Danh sách Quản lý để gán cho nhà hàng
        try {
            $managers = NguoiDung::where('vai_tro', 'quan_ly_nha_hang')
                ->whereIn('trang_thai', ['hoat_dong', 'da_duyet'])
                ->orderBy('ho_ten')
                ->get(['ma_nguoi_dung', 'ho_ten', 'email', 'so_dien_thoai']);
        } catch (\Throwable $e) {
            $managers = collect([
                (object) ['ma_nguoi_dung' => 2, 'ho_ten' => 'Nguyễn Thị Quản Lý', 'email' => 'quanly@goldenshift.vn', 'so_dien_thoai' => '0901234567'],
            ]);
        }

        return view('admin.categories.index', compact(
            'currentTab', 'brands', 'restaurants',
            'brandCount', 'activeBrands', 'restaurantCount', 'activeRestaurants',
            'managers', 'cities', 'brandsForFilter',
            'searchBrand', 'searchRestaurant', 'filterBrand', 'filterCity'
        ));
    }

    // =============================================
    // CRUD THƯƠNG HIỆU
    // =============================================

    /**
     * Lưu file ảnh logo thương hiệu vào public/uploads/brands/.
     */
    private function uploadBrandLogo(Request $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        $file = $request->file('logo');
        $dir = public_path('uploads/brands');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = 'brand_'.time().'_'.uniqid().'.'.$ext;
        $file->move($dir, $filename);

        return 'uploads/brands/'.$filename;
    }

    /**
     * Xóa file ảnh logo cũ nếu nằm trong thư mục public/uploads/brands/.
     */
    private function deleteUploadedLogo(?string $path): void
    {
        if (! empty($path) && str_starts_with($path, 'uploads/brands/') && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }

    /**
     * Lưu thương hiệu mới (POST)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_thuong_hieu' => 'required|string|max:150',
            'ma_dinh_danh' => 'required|string|max:60|unique:thuong_hieu,ma_dinh_danh',
            'loai_hinh_am_thuc' => 'nullable|string|max:100',
            'mo_ta_ngan' => 'nullable|string|max:255',
            'gioi_thieu_chi_tiet' => 'nullable|string',
            'trang_thai' => 'in:hoat_dong,tam_dung',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ], [
            'ten_thuong_hieu.required' => 'Tên thương hiệu là bắt buộc.',
            'ma_dinh_danh.required' => 'Mã định danh là bắt buộc.',
            'ma_dinh_danh.unique' => 'Mã định danh đã tồn tại, vui lòng chọn mã khác.',
            'logo.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'logo.mimes' => 'Logo thương hiệu chỉ chấp nhận định dạng: jpeg, png, jpg, webp, gif.',
            'logo.max' => 'Kích thước logo thương hiệu không được vượt quá 2MB.',
        ]);

        unset($validated['logo']);

        try {
            $validated['trang_thai'] = $validated['trang_thai'] ?? 'hoat_dong';
            if ($logoPath = $this->uploadBrandLogo($request)) {
                $validated['bieu_tuong'] = $logoPath;
            }
            ThuongHieu::create($validated);

            return redirect()->route('admin.categories.index', ['tab' => 'thuong_hieu'])
                ->with('success', 'Thêm thương hiệu "'.$validated['ten_thuong_hieu'].'" thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi thêm thương hiệu: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật thương hiệu (POST)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ten_thuong_hieu' => 'required|string|max:150',
            'loai_hinh_am_thuc' => 'nullable|string|max:100',
            'mo_ta_ngan' => 'nullable|string|max:255',
            'gioi_thieu_chi_tiet' => 'nullable|string',
            'trang_thai' => 'in:hoat_dong,tam_dung',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ], [
            'ten_thuong_hieu.required' => 'Tên thương hiệu là bắt buộc.',
            'logo.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'logo.mimes' => 'Logo thương hiệu chỉ chấp nhận định dạng: jpeg, png, jpg, webp, gif.',
            'logo.max' => 'Kích thước logo thương hiệu không được vượt quá 2MB.',
        ]);

        unset($validated['logo']);

        try {
            $brand = ThuongHieu::findOrFail($id);
            $oldLogo = $brand->bieu_tuong;

            if ($logoPath = $this->uploadBrandLogo($request)) {
                $validated['bieu_tuong'] = $logoPath;
            }

            $brand->update($validated);

            if (isset($validated['bieu_tuong'])) {
                $this->deleteUploadedLogo($oldLogo);
            }

            return redirect()->route('admin.categories.index', ['tab' => 'thuong_hieu'])
                ->with('success', 'Cập nhật thương hiệu "'.$brand->ten_thuong_hieu.'" thành công!');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Không tìm thấy thương hiệu cần sửa.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi cập nhật: '.$e->getMessage());
        }
    }

    /**
     * Chuyển trạng thái thương hiệu hoat_dong <-> tam_dung (POST)
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $brand = ThuongHieu::findOrFail($id);
            $newStatus = ($brand->trang_thai === 'hoat_dong') ? 'tam_dung' : 'hoat_dong';
            $brand->update(['trang_thai' => $newStatus]);

            $label = ($newStatus === 'hoat_dong') ? 'Hoạt động' : 'Tạm dừng';

            return redirect()->route('admin.categories.index', ['tab' => 'thuong_hieu'])
                ->with('success', 'Đã chuyển trạng thái "'.$brand->ten_thuong_hieu.'" sang ['.$label.'].');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi: '.$e->getMessage());
        }
    }

    /**
     * Xóa thương hiệu — CHỈ xóa nếu không còn nhà hàng chi nhánh (POST)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $brand = ThuongHieu::withCount('nhaHang')->findOrFail($id);
            if ($brand->nha_hang_count > 0) {
                return redirect()->route('admin.categories.index', ['tab' => 'thuong_hieu'])
                    ->with('error', 'KHÔNG THỂ XÓA thương hiệu "'.$brand->ten_thuong_hieu.'" vì đang có '
                        .$brand->nha_hang_count.' chi nhánh nhà hàng trực thuộc. '
                        .'Vui lòng chuyển trạng thái sang "Tạm dừng" thay thế.');
            }
            $name = $brand->ten_thuong_hieu;
            $oldLogo = $brand->bieu_tuong;
            $brand->delete();
            $this->deleteUploadedLogo($oldLogo);

            return redirect()->route('admin.categories.index', ['tab' => 'thuong_hieu'])
                ->with('success', 'Đã xóa thương hiệu "'.$name.'" thành công!');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Không tìm thấy thương hiệu cần xóa.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa: '.$e->getMessage());
        }
    }
}
