<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_ca_lam
 * @property int $ma_nha_hang
 * @property string $ten_vi_tri_tuyen_dung
 * @property string $ngay_lam_viec
 * @property string $gio_bat_dau
 * @property string $gio_ket_thuc
 * @property float $muc_luong_theo_gio
 * @property int $so_luong_tuyen
 * @property int $so_luong_da_tuyen
 * @property string $trang_thai_ca
 * @property string|null $ly_do_huy
 * @property string|null $yeu_cau_cong_viec
 * @property string|null $quyen_loi_ca_lam
 * @property NhaHang|null $nhaHang
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class CaLamViec extends Model
{
    use HasFactory;

    protected $table = 'ca_lam_viec';

    protected $primaryKey = 'ma_ca_lam';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    /**
     * Thuộc tính có thể gán hàng loạt.
     * Lưu ý: Loại bỏ hoàn toàn ma_dinh_danh_ca theo yêu cầu nghiệp vụ.
     */
    protected $fillable = [
        'ma_nha_hang',
        'ten_vi_tri_tuyen_dung',
        'ngay_lam_viec',
        'gio_bat_dau',
        'gio_ket_thuc',
        'muc_luong_theo_gio',
        'so_luong_tuyen',
        'so_luong_da_tuyen',
        'yeu_cau_cong_viec',
        'quyen_loi_ca_lam',
        'trang_thai_ca',
        'ly_do_huy',
    ];

    /**
     * Relationship: CaLamViec thuộc về Nhà Hàng
     */
    public function nhaHang()
    {
        return $this->belongsTo(NhaHang::class, 'ma_nha_hang', 'ma_nha_hang');
    }

    /**
     * Relationship: CaLamViec có nhiều Hồ Sơ Ứng Tuyển
     */
    public function hoSoUngTuyen()
    {
        return $this->hasMany(HoSoUngTuyen::class, 'ma_ca_lam', 'ma_ca_lam');
    }

    /**
     * Relationship: CaLamViec có nhiều Lịch Sử Chấm Công
     */
    public function lichSuChamCong()
    {
        return $this->hasMany(LichSuChamCong::class, 'ma_ca_lam', 'ma_ca_lam');
    }

    /**
     * Relationship: CaLamViec có nhiều Đánh giá
     */
    public function danhGiaCaLam()
    {
        return $this->hasMany(DanhGiaCaLam::class, 'ma_ca_lam', 'ma_ca_lam');
    }

    /**
     * Relationship: CaLamViec có nhiều Báo cáo vi phạm
     */
    public function baoCaoViPham()
    {
        return $this->hasMany(BaoCaoViPham::class, 'ma_ca_lam', 'ma_ca_lam');
    }

    /**
     * Kiểm tra ca làm có được phép chỉnh sửa hay không:
     * - Chỉ cho phép sửa trước và trong ngày có ca làm (ngay_lam_viec >= today)
     * - Không cho phép sửa nếu ca đã hủy (da_huy) hoặc sau ngày có ca làm
     */
    public function canEdit(): bool
    {
        $today = date('Y-m-d');
        if ($this->trang_thai_ca === 'da_huy') {
            return false;
        }

        return $this->ngay_lam_viec >= $today;
    }

    /**
     * Xác định trạng thái hiển thị chuẩn xác
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->trang_thai_ca === 'da_huy') {
            return 'da_huy';
        }

        $today = date('Y-m-d');
        if ($this->ngay_lam_viec < $today || $this->trang_thai_ca === 'hoan_thanh') {
            return 'hoan_thanh';
        }

        $soLuongTuyen = (int) $this->so_luong_tuyen;
        $soLuongDaTuyen = (int) $this->so_luong_da_tuyen;

        // Chỉ khi số lượng đã tuyển >= chỉ tiêu tuyển mới tính là đã đủ người
        if ($soLuongTuyen > 0 && $soLuongDaTuyen >= $soLuongTuyen) {
            return 'da_du_nguoi';
        }

        return 'dang_tuyen';
    }

    /**
     * Tự động khóa trạng thái ca làm việc sau ngày có ca làm thành "Đã hoàn thành"
     */
    public static function autoClosePastShifts(): int
    {
        try {
            $today = date('Y-m-d');

            return self::where('ngay_lam_viec', '<', $today)
                ->where('trang_thai_ca', '!=', 'da_huy')
                ->where('trang_thai_ca', '!=', 'hoan_thanh')
                ->update(['trang_thai_ca' => 'hoan_thanh']);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
