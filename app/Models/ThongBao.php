<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_thong_bao
 * @property int $ma_nguoi_dung
 * @property string $tieu_de
 * @property string $noi_dung
 * @property string|null $duong_dan_lien_ket
 * @property int $da_doc
 * @property \DateTime $ngay_tao
 * @property NguoiDung|null $nguoiDung
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class ThongBao extends Model
{
    protected $table = 'thong_bao';

    protected $primaryKey = 'ma_thong_bao';

    public $timestamps = false; // chỉ dùng created_at thủ công

    protected $fillable = [
        'ma_nguoi_dung',
        'tieu_de',
        'noi_dung',
        'duong_dan_lien_ket',
        'da_doc',
        'created_at',
    ];

    protected $casts = [
        'da_doc' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Quan hệ với NguoiDung
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung', 'ma_nguoi_dung');
    }

    /**
     * Tạo thông báo nhanh cho 1 người dùng
     */
    public static function taoBaoTri(int $maNguoiDung, string $tieuDe, string $noiDung, ?string $duongDan = null): ?self
    {
        try {
            $thongBao = self::create([
                'ma_nguoi_dung' => $maNguoiDung,
                'tieu_de' => $tieuDe,
                'noi_dung' => $noiDung,
                'duong_dan_lien_ket' => $duongDan,
                'da_doc' => 0,
                'created_at' => now(),
            ]);

            // Tự động gửi 1 bản sao Email thông báo (ThongBaoUngVienMail) tới Email Ứng viên
            try {
                $user = NguoiDung::find($maNguoiDung);
                if ($user && $user->vai_tro === 'ung_vien' && ! empty($user->email)) {
                    \App\Services\MultiMailerService::send(
                        $user->email,
                        new \App\Mail\ThongBaoUngVienMail($user, $tieuDe, $noiDung, $duongDan)
                    );
                }
            } catch (\Throwable $mailEx) {
                // Không làm gián đoạn lưu thông báo web
            }

            return $thongBao;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Tạo thông báo cho tất cả Admin (vai_tro = 'quan_tri_vien')
     */
    public static function taoBaoTatCaAdmin(string $tieuDe, string $noiDung, ?string $duongDan = null): void
    {
        try {
            $admins = NguoiDung::where('vai_tro', 'quan_tri_vien')
                ->where('trang_thai', 'hoat_dong')
                ->get();
            foreach ($admins as $admin) {
                self::taoBaoTri($admin->ma_nguoi_dung, $tieuDe, $noiDung, $duongDan);
            }
        } catch (\Throwable $e) {
            // Bỏ qua nếu DB chưa sẵn sàng
        }
    }

    /**
     * Lấy danh sách thông báo của 1 user (mới nhất 20 cái)
     */
    public static function layDanhSach(int $maNguoiDung, int $limit = 20)
    {
        return self::where('ma_nguoi_dung', $maNguoiDung)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Đếm thông báo chưa đọc
     */
    public static function demChuaDoc(int $maNguoiDung): int
    {
        return self::where('ma_nguoi_dung', $maNguoiDung)
            ->where('da_doc', 0)
            ->count();
    }
}
