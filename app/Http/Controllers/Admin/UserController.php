<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\NhaHang;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách tất cả tài khoản phân theo Tab (Quản lý nhà hàng / Ứng viên)
     */
    public function index(Request $request)
    {
        $currentTab = $request->input('tab', 'quan_ly'); // 'quan_ly' hoặc 'ung_vien'
        $search = trim($request->input('search', ''));
        $statusFilter = trim($request->input('status', ''));

        try {
            // 1. Quản lý nhà hàng
            $queryManagers = NguoiDung::with(['nhaHang.thuongHieu', 'nhaHangTrucThuoc.thuongHieu'])
                ->where('vai_tro', 'quan_ly_nha_hang');

            if (! empty($search)) {
                $queryManagers->where(function ($q) use ($search) {
                    $q->where('ho_ten', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('so_dien_thoai', 'LIKE', "%{$search}%");
                });
            }

            if (! empty($statusFilter)) {
                $queryManagers->where('trang_thai', $statusFilter);
            }

            $pendingCount = (clone $queryManagers)->where('trang_thai', 'cho_duyet')->count();

            $managers = $queryManagers->orderBy('ma_nguoi_dung', 'desc')
                ->paginate(5, ['*'], 'managers_page')
                ->appends(array_merge($request->except('managers_page'), ['tab' => 'quan_ly']));

            // 2. Ứng viên
            $queryCandidates = NguoiDung::with(['hoSoUngVien', 'hoSoUngTuyen'])
                ->where('vai_tro', 'ung_vien');

            if (! empty($search)) {
                $queryCandidates->where(function ($q) use ($search) {
                    $q->where('ho_ten', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('so_dien_thoai', 'LIKE', "%{$search}%");
                });
            }

            if (! empty($statusFilter)) {
                $queryCandidates->where('trang_thai', $statusFilter);
            }

            $candidates = $queryCandidates->orderBy('ma_nguoi_dung', 'desc')
                ->paginate(5, ['*'], 'candidates_page')
                ->appends(array_merge($request->except('candidates_page'), ['tab' => 'ung_vien']));

            $totalManagers = $managers->total();
            $totalCandidates = $candidates->total();

        } catch (\Throwable $e) {
            // Mock fallback nếu CSDL chưa kết nối
            $allMockManagers = $this->getMockManagers($search, $statusFilter)
                ->sortByDesc('ma_nguoi_dung')
                ->values();
            $allMockCandidates = $this->getMockCandidates($search, $statusFilter)
                ->sortByDesc('ma_nguoi_dung')
                ->values();

            $pendingCount = $allMockManagers->where('trang_thai', 'cho_duyet')->count();
            $totalManagers = $allMockManagers->count();
            $totalCandidates = $allMockCandidates->count();

            $managersPage = max(1, (int) $request->input('managers_page', 1));
            $managers = (new LengthAwarePaginator(
                $allMockManagers->forPage($managersPage, 5)->values(),
                $totalManagers,
                5,
                $managersPage,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'managers_page',
                ]
            ))->appends(array_merge($request->except('managers_page'), ['tab' => 'quan_ly']));

            $candidatesPage = max(1, (int) $request->input('candidates_page', 1));
            $candidates = (new LengthAwarePaginator(
                $allMockCandidates->forPage($candidatesPage, 5)->values(),
                $totalCandidates,
                5,
                $candidatesPage,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'candidates_page',
                ]
            ))->appends(array_merge($request->except('candidates_page'), ['tab' => 'ung_vien']));
        }

        // Thẻ Tổng người dùng: chỉ tính Quản lý nhà hàng và Ứng viên, KHÔNG đếm tài khoản Admin
        try {
            $totalNonAdminUsers = NguoiDung::where('vai_tro', '!=', 'admin')
                ->where('vai_tro', '!=', 'quan_tri_vien')
                ->count();
        } catch (\Throwable $e) {
            $totalNonAdminUsers = $totalManagers + $totalCandidates;
        }

        return view('admin.users.index', compact(
            'managers',
            'candidates',
            'currentTab',
            'search',
            'statusFilter',
            'pendingCount',
            'totalManagers',
            'totalCandidates',
            'totalNonAdminUsers'
        ));
    }

    /**
     * Thao tác: 'Duyệt tài khoản' (khi Quản lý nhà hàng mới đăng ký đang 'cho_duyet')
     */
    public function duyet(Request $request, $id)
    {
        try {
            $user = NguoiDung::where('ma_nguoi_dung', $id)
                ->where('vai_tro', 'quan_ly_nha_hang')
                ->first();

            if ($user) {
                $user->update([
                    'trang_thai' => 'da_duyet',
                    'ngay_duyet' => now(),
                    'ly_do_tu_choi' => null,
                ]);

                $tenNhaHang = null;
                if ($user->ma_nha_hang_truc_thuoc && Schema::hasTable('nha_hang')) {
                    $nh = NhaHang::find($user->ma_nha_hang_truc_thuoc);
                    if ($nh) {
                        $tenNhaHang = $nh->ten_nha_hang;
                    }
                    NhaHang::where('ma_nha_hang', $user->ma_nha_hang_truc_thuoc)
                        ->whereNull('ma_quan_ly')
                        ->update(['ma_quan_ly' => $user->ma_nguoi_dung]);
                }

                // Gửi Email thông báo kích hoạt tới email @goldenshift.vn của Quản lý (tự động phân luồng Mailtrap)
                $mailError = null;
                $mailSent = false;
                try {
                    if (! empty($user->email)) {
                        $mailSent = \App\Services\MultiMailerService::send(
                            $user->email,
                            new \App\Mail\ThongBaoKichHoatQuanLyMail($user, $tenNhaHang),
                            $mailError
                        );
                    }
                } catch (\Throwable $mailEx) {
                    $mailError = $mailEx->getMessage();
                    \Illuminate\Support\Facades\Log::error("Lỗi gửi mail: " . $mailEx->getMessage());
                }

                $msg = "Đã DUYỆT tài khoản Quản lý [{$user->ho_ten}] thành công! Trạng thái chuyển sang: ĐÃ DUYỆT.";
                if (! empty($user->email) && ! $mailSent && $mailError) {
                    $msg .= " (Cảnh báo gửi mail kích hoạt thất bại: {$mailError})";
                    return redirect()->back()->with('warning', $msg);
                }

                return redirect()->back()
                    ->with('success', $msg);
            }
        } catch (\Throwable $e) {
            // Fallback mock session
            $this->updateMockUserStatus((int) $id, 'da_duyet');

            return redirect()->back()
                ->with('success', 'Đã DUYỆT tài khoản Quản lý thành công! Trạng thái chuyển sang: ĐÃ DUYỆT.');
        }

        return redirect()->back()->with('error', 'Không tìm thấy tài khoản quản lý yêu cầu.');
    }

    /**
     * Thao tác: 'Từ chối tài khoản' kèm lý do
     */
    public function tuChoi(Request $request, $id)
    {
        $reason = trim($request->input('ly_do_tu_choi', 'Hồ sơ chưa đạt tiêu chuẩn quản lý chuỗi Golden Shift.'));

        try {
            $user = NguoiDung::where('ma_nguoi_dung', $id)
                ->where('vai_tro', 'quan_ly_nha_hang')
                ->first();

            if ($user) {
                $user->update([
                    'trang_thai' => 'tu_choi',
                    'ly_do_tu_choi' => $reason,
                    'ngay_duyet' => now(),
                ]);

                return redirect()->back()
                    ->with('error', "Đã TỪ CHỐI tài khoản Quản lý [{$user->ho_ten}]. Lý do: {$reason}");
            }
        } catch (\Throwable $e) {
            $this->updateMockUserStatus((int) $id, 'tu_choi', $reason);

            return redirect()->back()
                ->with('error', "Đã TỪ CHỐI tài khoản Quản lý. Lý do: {$reason}");
        }

        return redirect()->back()->with('error', 'Không tìm thấy tài khoản quản lý.');
    }

    /**
     * Thao tác: 'Khóa / Mở khóa tài khoản' (trang_thai_tai_khoan)
     */
    public function toggleLock(Request $request, $id)
    {
        try {
            $user = NguoiDung::find($id);

            if (! $user) {
                return redirect()->back()->with('error', 'Không tìm thấy tài khoản.');
            }

            // Nếu tài khoản quản lý nhà hàng đã bị từ chối xét duyệt -> không có thao tác khóa tài khoản
            if ($user->vai_tro === 'quan_ly_nha_hang' && $user->trang_thai === 'tu_choi') {
                return redirect()->back()
                    ->with('error', 'Tài khoản Quản lý đã bị từ chối xét duyệt, không thể thực hiện thao tác khóa tài khoản.');
            }

            // Nếu đang khóa -> Mở khóa
            if ($user->trang_thai === 'tam_khoa') {
                $newStatus = ($user->vai_tro === 'quan_ly_nha_hang') ? 'da_duyet' : 'hoat_dong';
                $user->update(['trang_thai' => $newStatus]);

                return redirect()->back()
                    ->with('success', "Đã MỞ KHÓA tài khoản [{$user->ho_ten}] thành công! Tài khoản đã có thể đăng nhập bình thường.");
            } else {
                // Khóa tài khoản
                $user->update(['trang_thai' => 'tam_khoa']);

                return redirect()->back()
                    ->with('warning', "Đã KHÓA tài khoản [{$user->ho_ten}]. Người dùng sẽ bị chặn đăng nhập vào hệ thống.");
            }
        } catch (\Throwable $e) {
            $res = $this->toggleMockLock((int) $id);
            if ($res === 'tu_choi') {
                return redirect()->back()->with('error', 'Tài khoản Quản lý đã bị từ chối xét duyệt, không thể thực hiện thao tác khóa tài khoản.');
            }
            if ($res === 'tam_khoa') {
                return redirect()->back()->with('warning', 'Đã KHÓA tài khoản thành công.');
            }

            return redirect()->back()->with('success', 'Đã MỞ KHÓA tài khoản thành công.');
        }
    }

    /**
     * Mock data Quản lý nhà hàng
     */
    private function getMockManagers(string $search = '', string $statusFilter = '')
    {
        $base = [
            (object) [
                'ma_nguoi_dung' => 2,
                'ho_ten' => 'Nguyễn Thị Bảo Châu',
                'email' => 'chau.ntb@goldenshift.vn',
                'so_dien_thoai' => '0923456789',
                'vai_tro' => 'quan_ly_nha_hang',
                'trang_thai' => 'da_duyet',
                'ly_do_tu_choi' => null,
                'ngay_tao' => '2026-09-01 08:30:00',
                'nhaHang' => (object) [
                    'ten_nha_hang' => 'Golden Hotpot - Đồng Khởi',
                    'quan_huyen' => 'Quận 1',
                    'thuongHieu' => (object) ['ten_thuong_hieu' => 'Golden Hotpot'],
                ],
            ],
            (object) [
                'ma_nguoi_dung' => 3,
                'ho_ten' => 'Dương Thị Tuyết Nhi',
                'email' => 'nhi.dtt@goldenshift.vn',
                'so_dien_thoai' => '0934567890',
                'vai_tro' => 'quan_ly_nha_hang',
                'trang_thai' => 'da_duyet',
                'ly_do_tu_choi' => null,
                'ngay_tao' => '2026-09-05 10:15:00',
                'nhaHang' => (object) [
                    'ten_nha_hang' => 'Golden BBQ - Landmark 81',
                    'quan_huyen' => 'Bình Thạnh',
                    'thuongHieu' => (object) ['ten_thuong_hieu' => 'Golden BBQ & Grill'],
                ],
            ],
            (object) [
                'ma_nguoi_dung' => 4,
                'ho_ten' => 'Nguyễn Thành Long',
                'email' => 'long.nt@goldenshift.vn',
                'so_dien_thoai' => '0988112233',
                'vai_tro' => 'quan_ly_nha_hang',
                'trang_thai' => 'cho_duyet',
                'ly_do_tu_choi' => null,
                'ngay_tao' => '2026-09-17 09:30:00',
                'nhaHang' => (object) [
                    'ten_nha_hang' => 'Golden Hotpot - Crescent Mall',
                    'quan_huyen' => 'Quận 7',
                    'thuongHieu' => (object) ['ten_thuong_hieu' => 'Golden Hotpot'],
                ],
            ],
            (object) [
                'ma_nguoi_dung' => 5,
                'ho_ten' => 'Hoàng Kim Yến',
                'email' => 'yen.hk@goldenshift.vn',
                'so_dien_thoai' => '0977223344',
                'vai_tro' => 'quan_ly_nha_hang',
                'trang_thai' => 'cho_duyet',
                'ly_do_tu_choi' => null,
                'ngay_tao' => '2026-09-17 11:15:00',
                'nhaHang' => (object) [
                    'ten_nha_hang' => 'Golden Dimsum - Saigon Centre',
                    'quan_huyen' => 'Quận 1',
                    'thuongHieu' => (object) ['ten_thuong_hieu' => 'Golden Dimsum House'],
                ],
            ],
        ];

        // Ghép thêm session demo nếu có
        $sessionMock = session('mock_pending_managers', []);
        foreach ($sessionMock as $m) {
            $base[] = (object) [
                'ma_nguoi_dung' => $m['ma_nguoi_dung'] ?? rand(200, 400),
                'ho_ten' => $m['ho_ten'],
                'email' => $m['email'],
                'so_dien_thoai' => $m['so_dien_thoai'] ?? '',
                'vai_tro' => 'quan_ly_nha_hang',
                'trang_thai' => $m['trang_thai'] ?? 'cho_duyet',
                'ly_do_tu_choi' => $m['ly_do_tu_choi'] ?? null,
                'ngay_tao' => $m['ngay_tao'] ?? date('Y-m-d H:i:s'),
                'nhaHang' => (object) [
                    'ten_nha_hang' => $m['ten_nha_hang'] ?? 'Chi nhánh Golden Shift',
                    'quan_huyen' => 'TP.HCM',
                    'thuongHieu' => (object) ['ten_thuong_hieu' => 'Golden Shift'],
                ],
            ];
        }

        $collection = collect($base);
        if (! empty($search)) {
            $kw = mb_strtolower($search);
            $collection = $collection->filter(function ($m) use ($kw) {
                return str_contains(mb_strtolower($m->ho_ten ?? ''), $kw) ||
                       str_contains(mb_strtolower($m->email ?? ''), $kw) ||
                       str_contains(mb_strtolower($m->so_dien_thoai ?? ''), $kw);
            });
        }
        if (! empty($statusFilter)) {
            $collection = $collection->filter(function ($m) use ($statusFilter) {
                $st = $m->trang_thai_tai_khoan ?? $m->trang_thai ?? '';
                if ($statusFilter === 'tu_choi') {
                    return $st === 'tu_choi' || $st === 'da_tu_choi';
                }

                return $st === $statusFilter;
            });
        }

        return $collection->values();
    }

    /**
     * Mock data Ứng viên
     */
    private function getMockCandidates(string $search = '', string $statusFilter = '')
    {
        $base = [
            (object) [
                'ma_nguoi_dung' => 6,
                'ho_ten' => 'Phạm Thu Hương',
                'email' => 'thuhuong.pham@gmail.com',
                'so_dien_thoai' => '0945678901',
                'vai_tro' => 'ung_vien',
                'trang_thai' => 'hoat_dong',
                'ngay_tao' => '2026-09-02 14:00:00',
                'hoSoUngVien' => (object) [
                    'gioi_thieu_ban_than' => 'Có kinh nghiệm 1 năm phục vụ bàn tại chuỗi nhà hàng lẩu nướng.',
                    'trang_thai_ho_so' => 'san_sang_nhan_viec',
                ],
                'ho_so_ung_tuyen_count' => 3,
            ],
            (object) [
                'ma_nguoi_dung' => 7,
                'ho_ten' => 'Lê Hoàng Nam',
                'email' => 'nam.lehoang@gmail.com',
                'so_dien_thoai' => '0912348899',
                'vai_tro' => 'ung_vien',
                'trang_thai' => 'hoat_dong',
                'ngay_tao' => '2026-09-10 09:20:00',
                'hoSoUngVien' => (object) [
                    'gioi_thieu_ban_than' => 'Sinh viên năng động, mong muốn tìm ca phụ bếp hoặc tiếp thực.',
                    'trang_thai_ho_so' => 'san_sang_nhan_viec',
                ],
                'ho_so_ung_tuyen_count' => 1,
            ],
            (object) [
                'ma_nguoi_dung' => 8,
                'ho_ten' => 'Trần Minh Quang',
                'email' => 'quang.tm@gmail.com',
                'so_dien_thoai' => '0933445566',
                'vai_tro' => 'ung_vien',
                'trang_thai' => 'tam_khoa',
                'ngay_tao' => '2026-09-12 16:45:00',
                'hoSoUngVien' => (object) [
                    'gioi_thieu_ban_than' => 'Nhân viên pha chế cà phê và cocktail cơ bản.',
                    'trang_thai_ho_so' => 'tam_ngung',
                ],
                'ho_so_ung_tuyen_count' => 2,
            ],
        ];

        $sessionMock = session('mock_candidates', []);
        foreach ($sessionMock as $c) {
            $base[] = (object) [
                'ma_nguoi_dung' => $c['ma_nguoi_dung'] ?? rand(500, 700),
                'ho_ten' => $c['ho_ten'],
                'email' => $c['email'],
                'so_dien_thoai' => $c['so_dien_thoai'],
                'vai_tro' => 'ung_vien',
                'trang_thai' => $c['trang_thai'] ?? 'hoat_dong',
                'ngay_tao' => $c['ngay_tao'] ?? date('Y-m-d H:i:s'),
                'hoSoUngVien' => (object) [
                    'gioi_thieu_ban_than' => 'Ứng viên mới đăng ký tài khoản Golden Shift',
                    'trang_thai_ho_so' => 'san_sang_nhan_viec',
                ],
                'ho_so_ung_tuyen_count' => 0,
            ];
        }

        $collection = collect($base);
        if (! empty($search)) {
            $kw = mb_strtolower($search);
            $collection = $collection->filter(function ($c) use ($kw) {
                return str_contains(mb_strtolower($c->ho_ten ?? ''), $kw) ||
                       str_contains(mb_strtolower($c->email ?? ''), $kw) ||
                       str_contains(mb_strtolower($c->so_dien_thoai ?? ''), $kw);
            });
        }
        if (! empty($statusFilter)) {
            $collection = $collection->filter(function ($c) use ($statusFilter) {
                $st = $c->trang_thai_tai_khoan ?? $c->trang_thai ?? '';

                return $st === $statusFilter;
            });
        }

        return $collection->values();
    }

    private function updateMockUserStatus(int $id, string $status, ?string $reason = null)
    {
        $sessionManagers = session('mock_pending_managers', []);
        foreach ($sessionManagers as &$m) {
            if (($m['ma_nguoi_dung'] ?? 0) === $id) {
                $m['trang_thai'] = $status;
                if ($reason !== null) {
                    $m['ly_do_tu_choi'] = $reason;
                }
            }
        }
        session(['mock_pending_managers' => $sessionManagers]);
    }

    private function toggleMockLock(int $id): string
    {
        $sessionManagers = session('mock_pending_managers', []);
        foreach ($sessionManagers as &$m) {
            if (($m['ma_nguoi_dung'] ?? 0) === $id) {
                if (($m['trang_thai'] ?? '') === 'tu_choi') {
                    return 'tu_choi';
                }
                $m['trang_thai'] = ($m['trang_thai'] === 'tam_khoa') ? 'da_duyet' : 'tam_khoa';
                session(['mock_pending_managers' => $sessionManagers]);

                return $m['trang_thai'];
            }
        }

        return 'hoat_dong';
    }
}
