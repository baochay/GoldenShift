<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_nha_hang
 * @property int $ma_thuong_hieu
 * @property int|null $ma_quan_ly
 * @property string $ten_nha_hang
 * @property string|null $so_dien_thoai_lien_he
 * @property string|null $email_chi_nhanh
 * @property string $dia_chi_chi_tiet
 * @property string|null $phuong_xa
 * @property string|null $quan_huyen
 * @property string|null $tinh_thanh_pho
 * @property string|null $gio_mo_cua
 * @property string|null $gio_dong_cua
 * @property string $trang_thai_hoat_dong
 * @property ThuongHieu|null $thuongHieu
 * @property NguoiDung|null $quanLy
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class NhaHang extends Model
{
    use HasFactory;

    protected $table = 'nha_hang';

    protected $primaryKey = 'ma_nha_hang';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_thuong_hieu',
        'ma_quan_ly',
        'ten_nha_hang',
        'so_dien_thoai_lien_he',
        'email_chi_nhanh',
        'dia_chi_chi_tiet',
        'phuong_xa',
        'quan_huyen',
        'tinh_thanh_pho',
        'gio_mo_cua',
        'gio_dong_cua',
        'trang_thai_hoat_dong',
    ];

    public function thuongHieu()
    {
        return $this->belongsTo(ThuongHieu::class, 'ma_thuong_hieu', 'ma_thuong_hieu');
    }

    public function quanLy()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_quan_ly', 'ma_nguoi_dung');
    }

    public function caLamViec()
    {
        return $this->hasMany(CaLamViec::class, 'ma_nha_hang', 'ma_nha_hang');
    }

    /**
     * Quan hệ các lượt đánh giá thông qua danh sách ca làm việc của nhà hàng.
     */
    public function danhGia()
    {
        return $this->hasManyThrough(
            DanhGiaCaLam::class,
            CaLamViec::class,
            'ma_nha_hang',
            'ma_ca_lam',
            'ma_nha_hang',
            'ma_ca_lam'
        );
    }
}
