<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_so_thu_nhap
 * @property int $ma_nguoi_dung
 * @property string $ky_luong
 * @property int $tong_so_ca_lam
 * @property float $tong_gio_lam
 * @property float $tong_tien_luong_co_ban
 * @property float $tong_tien_thuong
 * @property float $tong_tien_phat
 * @property float $thuc_nhan
 * @property string $trang_thai_thanh_toan
 * @property \DateTime|null $ngay_thanh_toan
 * @property string|null $ghi_chu
 * @property \DateTime $ngay_tao
 * @property NguoiDung|null $nguoiDung
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class SoThuNhap extends Model
{
    use HasFactory;

    protected $table = 'so_thu_nhap';

    protected $primaryKey = 'ma_so_thu_nhap';

    public $timestamps = false; // Bảng dùng ngay_tao

    protected $fillable = [
        'ma_nguoi_dung',
        'ky_luong',
        'tong_so_ca_lam',
        'tong_gio_lam',
        'tong_tien_luong_co_ban',
        'tong_tien_thuong',
        'tong_tien_phat',
        'thuc_nhan',
        'trang_thai_thanh_toan',
        'ngay_thanh_toan',
        'ghi_chu',
        'ngay_tao',
    ];

    protected $casts = [
        'tong_so_ca_lam' => 'integer',
        'tong_gio_lam' => 'float',
        'tong_tien_luong_co_ban' => 'float',
        'tong_tien_thuong' => 'float',
        'tong_tien_phat' => 'float',
        'thuc_nhan' => 'float',
        'ngay_thanh_toan' => 'datetime',
        'ngay_tao' => 'datetime',
    ];

    /**
     * Chuẩn hóa hiển thị kỳ lương về 1 định dạng duy nhất: 'Tháng MM/YYYY'
     */
    public function getFormattedKyLuongAttribute(): string
    {
        $val = trim((string) $this->ky_luong);
        if (preg_match('/^(\d{4})-(\d{2})$/', $val, $m)) {
            return 'Tháng '.$m[2].'/'.$m[1];
        }
        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $val, $m)) {
            return 'Tháng '.str_pad($m[1], 2, '0', STR_PAD_LEFT).'/'.$m[2];
        }
        if (preg_match('/^Tháng\s*(\d{1,2})\/(\d{4})$/ui', $val, $m)) {
            return 'Tháng '.str_pad($m[1], 2, '0', STR_PAD_LEFT).'/'.$m[2];
        }

        return $val;
    }

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung', 'ma_nguoi_dung');
    }
}
