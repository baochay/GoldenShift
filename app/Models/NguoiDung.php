<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $ma_nguoi_dung
 * @property string $ho_ten
 * @property string $email
 * @property string|null $so_dien_thoai
 * @property string $mat_khau
 * @property string $vai_tro
 * @property string|null $anh_dai_dien
 * @property int|null $ma_nha_hang_truc_thuoc
 * @property string $trang_thai
 * @property string|null $ly_do_tu_choi
 * @property string|null $remember_token
 * @property \DateTime|null $ngay_duyet
 * @property NhaHang|null $nhaHang
 * @property NhaHang|null $nhaHangTrucThuoc
 * @property HoSoUngVien|null $hoSoUngVien
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'nguoi_dung';

    protected $primaryKey = 'ma_nguoi_dung';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ho_ten',
        'email',
        'so_dien_thoai',
        'mat_khau',
        'vai_tro',
        'anh_dai_dien',
        'ma_nha_hang_truc_thuoc',
        'trang_thai',
        'ly_do_tu_choi',
        'ngay_duyet',
        'remember_token',
    ];

    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    public function getAuthPasswordName()
    {
        return 'mat_khau';
    }

    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    /**
     * Quan hệ 1-1 ngược lại từ Quản lý sang Nhà hàng phụ trách (theo khóa ngoại ma_quan_ly)
     */
    public function nhaHang()
    {
        return $this->hasOne(NhaHang::class, 'ma_quan_ly', 'ma_nguoi_dung');
    }

    /**
     * Quan hệ Nhà hàng trực thuộc mà tài khoản đăng ký / liên kết (theo ma_nha_hang_truc_thuoc)
     */
    public function nhaHangTrucThuoc()
    {
        return $this->belongsTo(NhaHang::class, 'ma_nha_hang_truc_thuoc', 'ma_nha_hang');
    }

    /**
     * Quan hệ với danh sách hồ sơ ứng tuyển của ứng viên
     */
    public function hoSoUngTuyen()
    {
        return $this->hasMany(HoSoUngTuyen::class, 'ma_nguoi_dung', 'ma_nguoi_dung');
    }

    /**
     * Quan hệ 1-1 với hồ sơ chi tiết ứng viên (CV/kinh nghiệm)
     */
    public function hoSoUngVien()
    {
        return $this->hasOne(HoSoUngVien::class, 'ma_nguoi_dung', 'ma_nguoi_dung');
    }

    /**
     * Danh sách đánh giá mà người này đã nhận được
     */
    public function danhGiaNhanDuoc()
    {
        return $this->hasMany(DanhGiaCaLam::class, 'ma_nguoi_duoc_danh_gia', 'ma_nguoi_dung');
    }

    /**
     * Danh sách đánh giá mà người này đã gửi đi
     */
    public function danhGiaDaGui()
    {
        return $this->hasMany(DanhGiaCaLam::class, 'ma_nguoi_danh_gia', 'ma_nguoi_dung');
    }

    /**
     * Danh sách báo cáo vi phạm mà người này bị báo cáo
     */
    public function baoCaoBiToCao()
    {
        return $this->hasMany(BaoCaoViPham::class, 'ma_nguoi_vi_pham', 'ma_nguoi_dung');
    }

    /**
     * Danh sách báo cáo vi phạm mà người này đã gửi
     */
    public function baoCaoDaGui()
    {
        return $this->hasMany(BaoCaoViPham::class, 'ma_nguoi_bao_cao', 'ma_nguoi_dung');
    }
}
