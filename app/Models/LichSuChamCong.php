<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_cham_cong
 * @property int $ma_ung_tuyen
 * @property int $ma_nguoi_dung
 * @property int $ma_ca_lam
 * @property Carbon|null $thoi_gian_vao_thuc_te
 * @property Carbon|null $thoi_gian_ra_thuc_te
 * @property float $so_gio_lam_thuc_te
 * @property string $trang_thai_cham_cong
 * @property string $hinh_thuc_xac_thuc
 * @property string|null $ghi_chu_cham_cong
 * @property int|null $nguoi_xac_nhan
 * @property CaLamViec|null $caLamViec
 * @property HoSoUngTuyen|null $hoSoUngTuyen
 * @property NguoiDung|null $nguoiXacNhan
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class LichSuChamCong extends Model
{
    use HasFactory;

    protected $table = 'lich_su_cham_cong';

    protected $primaryKey = 'ma_cham_cong';

    public $timestamps = false; // Bảng dùng ngay_tao

    protected $fillable = [
        'ma_ung_tuyen',
        'ma_nguoi_dung',
        'ma_ca_lam',
        'thoi_gian_vao_thuc_te',
        'thoi_gian_ra_thuc_te',
        'so_gio_lam_thuc_te',
        'trang_thai_cham_cong',
        'hinh_thuc_xac_thuc',
        'ghi_chu_cham_cong',
        'nguoi_xac_nhan',
        'ngay_tao',
    ];

    protected $casts = [
        'thoi_gian_vao_thuc_te' => 'datetime',
        'thoi_gian_ra_thuc_te' => 'datetime',
        'so_gio_lam_thuc_te' => 'float',
        'ngay_tao' => 'datetime',
    ];

    public function hoSoUngTuyen()
    {
        return $this->belongsTo(HoSoUngTuyen::class, 'ma_ung_tuyen', 'ma_ung_tuyen');
    }

    public function caLamViec()
    {
        return $this->belongsTo(CaLamViec::class, 'ma_ca_lam', 'ma_ca_lam');
    }

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung', 'ma_nguoi_dung');
    }

    public function nguoiXacNhan()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_xac_nhan', 'ma_nguoi_dung');
    }
}
