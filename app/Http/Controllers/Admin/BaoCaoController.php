<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoViPham;
use App\Models\ThongBao;
use Illuminate\Http\Request;

class BaoCaoController extends Controller
{
    /**
     * Danh sách Báo cáo vi phạm với bộ lọc trạng thái
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'cho_xuly'); // 'all', 'cho_xuly', 'da_xuly', 'tu_choi'
        $search = trim($request->input('search', ''));

        $query = BaoCaoViPham::with([
            'nguoiBaoCao',
            'nguoiViPham',
            'caLamViec.nhaHang.thuongHieu',
        ]);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('mo_ta_chi_tiet', 'LIKE', "%{$search}%")
                    ->orWhere('loai_vi_pham', 'LIKE', "%{$search}%")
                    ->orWhereHas('nguoiBaoCao', function ($sub) use ($search) {
                        $sub->where('ho_ten', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('nguoiViPham', function ($sub) use ($search) {
                        $sub->where('ho_ten', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($statusFilter === 'cho_xuly') {
            $query->whereIn('trang_thai_xu_ly', ['cho_xuly', 'cho_xu_ly']);
        } elseif ($statusFilter === 'da_xuly') {
            $query->whereIn('trang_thai_xu_ly', ['da_xuly', 'da_xac_nhan']);
        } elseif ($statusFilter === 'tu_choi') {
            $query->whereIn('trang_thai_xu_ly', ['tu_choi', 'da_huy']);
        }

        $reports = $query->orderBy('ma_vi_pham', 'desc')->paginate(15)->appends($request->query());

        // Đếm theo từng trạng thái để làm badge tab
        $countPending = BaoCaoViPham::whereIn('trang_thai_xu_ly', ['cho_xuly', 'cho_xu_ly'])->count();
        $countProcessed = BaoCaoViPham::whereIn('trang_thai_xu_ly', ['da_xuly', 'da_xac_nhan'])->count();
        $countRejected = BaoCaoViPham::whereIn('trang_thai_xu_ly', ['tu_choi', 'da_huy'])->count();
        $countTotal = BaoCaoViPham::count();

        return view('admin.bao_cao.index', compact(
            'reports',
            'statusFilter',
            'search',
            'countPending',
            'countProcessed',
            'countRejected',
            'countTotal'
        ));
    }

    /**
     * Admin xử lý Báo cáo vi phạm (Cảnh cáo, Tạm khóa tài khoản, Từ chối)
     */
    public function xuLy(Request $request, $id)
    {
        $report = BaoCaoViPham::with(['nguoiViPham', 'nguoiBaoCao', 'caLamViec'])->findOrFail($id);

        $hinhThuc = $request->input('hinh_thuc'); // 'canh_cao', 'tam_khoa', 'tu_choi'
        $ghiChuAdmin = trim($request->input('ghi_chu_admin', ''));

        if (! in_array($hinhThuc, ['canh_cao', 'tam_khoa', 'tu_choi'])) {
            return redirect()->back()->with('error', 'Hình thức xử lý không hợp lệ.');
        }

        $nguoiViPham = $report->nguoiViPham;
        $nguoiBaoCao = $report->nguoiBaoCao;
        $tenNguoiViPham = $nguoiViPham ? $nguoiViPham->ho_ten : 'Người dùng';

        if ($hinhThuc === 'canh_cao') {
            // 1. Cảnh cáo: Cập nhật trạng thái 'da_xuly', gửi thông báo tới người vi phạm
            $hinhThucText = 'Cảnh cáo tài khoản vi phạm nội quy';
            $report->update([
                'trang_thai_xu_ly' => 'da_xuly',
                'hinh_thuc_xu_ly' => $hinhThucText.($ghiChuAdmin ? ': '.$ghiChuAdmin : ''),
                'ghi_chu_admin' => $ghiChuAdmin,
            ]);

            if ($nguoiViPham) {
                try {
                    ThongBao::taoBaoTri(
                        $nguoiViPham->ma_nguoi_dung,
                        'CẢNH CÁO VI PHẠM NỘI QUY HỆ THỐNG',
                        "Ban Quản Trị Golden Shift đã xem xét báo cáo vi phạm liên quan đến bạn. Quyết định: Cảnh cáo chính thức. Ghi chú: {$ghiChuAdmin}. Vui lòng tuân thủ nghiêm ngặt quy định để không bị khóa tài khoản vĩnh viễn.",
                        '/ho-so-ca-nhan'
                    );
                } catch (\Throwable $e) {
                }
            }

            // Báo lại cho người gửi báo cáo
            if ($nguoiBaoCao) {
                try {
                    ThongBao::taoBaoTri(
                        $nguoiBaoCao->ma_nguoi_dung,
                        'Báo cáo vi phạm của bạn đã được xử lý',
                        "Ban Quản Trị đã tiến hành xác minh và ban hành lệnh Cảnh cáo chính thức đối với đối tượng [{$tenNguoiViPham}].",
                        '/thong-tin-ca-nhan'
                    );
                } catch (\Throwable $e) {
                }
            }

            return redirect()->back()->with('success', "Đã áp dụng biện pháp [CẢNH CÁO] đối với thành viên [{$tenNguoiViPham}] thành công!");

        } elseif ($hinhThuc === 'tam_khoa') {
            // 2. Tạm khóa tài khoản: Tự động chuyển trang_thai = 'tam_khoa' trong bảng nguoi_dung
            $hinhThucText = 'Tạm khóa tài khoản người vi phạm';
            $report->update([
                'trang_thai_xu_ly' => 'da_xuly',
                'hinh_thuc_xu_ly' => $hinhThucText.($ghiChuAdmin ? ': '.$ghiChuAdmin : ''),
                'ghi_chu_admin' => $ghiChuAdmin,
            ]);

            if ($nguoiViPham) {
                $nguoiViPham->update([
                    'trang_thai' => 'tam_khoa',
                    'ly_do_tu_choi' => 'Tài khoản bị tạm khóa do vi phạm nội quy: '.($ghiChuAdmin ?: 'Vi phạm nghiêm trọng.'),
                ]);

                try {
                    ThongBao::taoBaoTri(
                        $nguoiViPham->ma_nguoi_dung,
                        'THÔNG BÁO TẠM KHÓA TÀI KHOẢN',
                        "Tài khoản của bạn đã bị Ban Quản Trị TẠM KHÓA do vi phạm quy định làm việc: {$ghiChuAdmin}. Mọi thắc mắc xin liên hệ quản trị viên.",
                        '#'
                    );
                } catch (\Throwable $e) {
                }
            }

            // Báo lại cho người gửi báo cáo
            if ($nguoiBaoCao) {
                try {
                    ThongBao::taoBaoTri(
                        $nguoiBaoCao->ma_nguoi_dung,
                        'Báo cáo vi phạm của bạn đã được xử lý',
                        "Ban Quản Trị đã tiến hành xác minh và ban hành quyết định TẠM KHÓA TÀI KHOẢN đối với đối tượng [{$tenNguoiViPham}].",
                        '/thong-tin-ca-nhan'
                    );
                } catch (\Throwable $e) {
                }
            }

            return redirect()->back()->with('success', "Đã TẠM KHÓA TÀI KHOẢN thành viên [{$tenNguoiViPham}] và chốt báo cáo thành công!");

        } elseif ($hinhThuc === 'tu_choi') {
            // 3. Từ chối / Bác bỏ báo cáo
            $hinhThucText = 'Bác bỏ / Từ chối báo cáo';
            $report->update([
                'trang_thai_xu_ly' => 'tu_choi',
                'hinh_thuc_xu_ly' => $hinhThucText.($ghiChuAdmin ? ': '.$ghiChuAdmin : ''),
                'ghi_chu_admin' => $ghiChuAdmin,
            ]);

            if ($nguoiBaoCao) {
                try {
                    ThongBao::taoBaoTri(
                        $nguoiBaoCao->ma_nguoi_dung,
                        'Thông báo kết quả xử lý báo cáo vi phạm',
                        "Báo cáo của bạn về đối tượng [{$tenNguoiViPham}] đã bị từ chối do không đủ minh chứng hoặc lý do không xác đáng: {$ghiChuAdmin}.",
                        '/thong-tin-ca-nhan'
                    );
                } catch (\Throwable $e) {
                }
            }

            return redirect()->back()->with('info', "Đã bác bỏ báo cáo vi phạm #{$report->ma_vi_pham}.");
        }
    }
}
