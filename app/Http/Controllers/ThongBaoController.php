<?php

namespace App\Http\Controllers;

use App\Models\ThongBao;
use Illuminate\Http\Request;

class ThongBaoController extends Controller
{
    /**
     * Lấy danh sách thông báo (AJAX - dùng cho dropdown Navbar)
     */
    public function danhSach(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
        }

        $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);

        try {
            $danhSach = ThongBao::layDanhSach($maNguoiDung, 15);
            $chuaDoc = ThongBao::demChuaDoc($maNguoiDung);

            return response()->json([
                'success' => true,
                'chua_doc' => $chuaDoc,
                'danh_sach' => $danhSach->map(function ($tb) {
                    return [
                        'ma_thong_bao' => $tb->ma_thong_bao,
                        'tieu_de' => $tb->tieu_de,
                        'noi_dung' => $tb->noi_dung,
                        'duong_dan_lien_ket' => $tb->duong_dan_lien_ket,
                        'da_doc' => (int) $tb->da_doc,
                        'created_at' => $tb->created_at ? $tb->created_at->copy()->setTimezone('Asia/Ho_Chi_Minh')->diffForHumans() : '',
                        'created_at_full' => $tb->created_at ? $tb->created_at->copy()->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') : '',
                    ];
                })->values(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'chua_doc' => 0,
                'danh_sach' => [],
            ]);
        }
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc, sau đó redirect về link
     */
    public function docVaChuyenHuong(Request $request, int $id)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return redirect('/dang-nhap');
        }

        $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);

        try {
            $tb = ThongBao::where('ma_thong_bao', $id)
                ->where('ma_nguoi_dung', $maNguoiDung)
                ->first();

            if ($tb) {
                $tb->da_doc = 1;
                $tb->save();
                $link = $tb->duong_dan_lien_ket;
                if ($link) {
                    return redirect($link);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return redirect()->back();
    }

    /**
     * Đánh dấu tất cả là đã đọc (AJAX)
     */
    public function docTatCa(Request $request)
    {
        $user = session('user') ?? auth()->user();
        if (! $user) {
            return response()->json(['success' => false], 401);
        }

        $maNguoiDung = is_array($user) ? (int) ($user['ma_nguoi_dung'] ?? 0) : (int) ($user->ma_nguoi_dung ?? 0);

        try {
            ThongBao::where('ma_nguoi_dung', $maNguoiDung)
                ->where('da_doc', 0)
                ->update(['da_doc' => 1]);
        } catch (\Throwable $e) {
            // ignore
        }

        return response()->json(['success' => true]);
    }
}
