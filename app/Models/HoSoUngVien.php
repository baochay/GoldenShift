<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_ho_so
 * @property int $ma_nguoi_dung
 * @property string|null $kinh_nghiem_lam_viec
 * @property string|null $ky_nang_chuyen_mon
 * @property string|null $chung_chi_bang_cap
 * @property string|null $gioi_thieu_ban_than
 * @property NguoiDung|null $nguoiDung
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class HoSoUngVien extends Model
{
    use HasFactory;

    protected $table = 'ho_so_ung_vien';

    protected $primaryKey = 'ma_ho_so';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_nguoi_dung',
        'ngay_sinh',
        'gioi_tinh',
        'so_cccd',
        'gioi_thieu_ban_than',
        'trang_thai_ho_so',
        'diem_danh_gia',
    ];

    protected $casts = [
        'diem_danh_gia' => 'float',
    ];

    public function getDiemDanhGiaAttribute($value): float
    {
        return $value !== null ? round((float) $value, 1) : 0.0;
    }

    /**
     * Relationship: Thuộc về NguoiDung
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung', 'ma_nguoi_dung');
    }
}
