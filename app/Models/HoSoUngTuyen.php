<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_ung_tuyen
 * @property int $ma_ca_lam
 * @property int $ma_nguoi_dung
 * @property string|null $loi_nhan_ung_vien
 * @property string $trang_thai_ung_tuyen
 * @property string|null $ghi_chu_nha_tuyen_dung
 * @property int|null $nguoi_duyet
 * @property \DateTime $ngay_nop_don
 * @property \DateTime $ngay_cap_nhat
 * @property CaLamViec|null $caLamViec
 * @property NguoiDung|null $nguoiDung
 * @property LichSuChamCong|null $chamCong
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class HoSoUngTuyen extends Model
{
    use HasFactory;

    protected $table = 'ho_so_ung_tuyen';

    protected $primaryKey = 'ma_ung_tuyen';

    const CREATED_AT = 'ngay_nop_don';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_ca_lam',
        'ma_nguoi_dung',
        'loi_nhan_ung_vien',
        'trang_thai_ung_tuyen',
        'ghi_chu_nha_tuyen_dung',
        'nguoi_duyet',
        'ngay_duyet',
    ];

    /**
     * Relationship: HoSoUngTuyen thuộc về CaLamViec
     */
    public function caLamViec()
    {
        return $this->belongsTo(CaLamViec::class, 'ma_ca_lam', 'ma_ca_lam');
    }

    /**
     * Relationship: HoSoUngTuyen thuộc về NguoiDung (Ứng viên)
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_dung', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Người duyệt hồ sơ
     */
    public function nguoiPheDuyet()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_duyet', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Lịch sử chấm công của hồ sơ ứng tuyển
     */
    public function chamCong()
    {
        return $this->hasOne(LichSuChamCong::class, 'ma_ung_tuyen', 'ma_ung_tuyen');
    }

    /**
     * Tính trạng thái hiển thị cho ứng viên (5 trạng thái):
     *  - "da_huy"      : Ca làm đã bị quản lý hủy (bất kể đã duyệt hay chưa)
     *  - "cho_duyet"   : Đang chờ quản lý xét duyệt (giữ nguyên dù ca đã qua ngày)
     *  - "tu_choi"     : Bị từ chối (giữ nguyên dù ca đã qua ngày)
     *  - "hoan_thanh"  : Đã được duyệt + ca làm không bị hủy + ngày ca đã kết thúc
     *  - "da_nhan"     : Đã được duyệt + ca làm chưa kết thúc
     *
     * @return string Một trong: 'da_huy'|'cho_duyet'|'tu_choi'|'hoan_thanh'|'da_nhan'
     */
    public function getDisplayStatus(): string
    {
        $trangThaiDon = $this->trang_thai_ung_tuyen ?? 'cho_duyet';
        $shift = $this->caLamViec;

        // Ưu tiên cao nhất: Ca làm đã bị hủy → hiển thị "Đã hủy" bất kể trạng thái đơn
        if ($shift && $shift->trang_thai_ca === 'da_huy') {
            return 'da_huy';
        }

        // Chờ duyệt / Từ chối: giữ nguyên trạng thái dù ca đã qua ngày hay chưa
        if ($trangThaiDon === 'cho_duyet') {
            return 'cho_duyet';
        }
        if ($trangThaiDon === 'tu_choi') {
            return 'tu_choi';
        }

        // Đã được duyệt (da_nhan): xét thêm ngày ca làm có đã qua chưa
        if (in_array($trangThaiDon, ['da_nhan', 'da_duyet'])) {
            if ($shift) {
                $today = date('Y-m-d');
                // Sau ngày ca làm → Đã hoàn thành
                if ($shift->ngay_lam_viec < $today) {
                    return 'hoan_thanh';
                }
            }

            return 'da_nhan';
        }

        return $trangThaiDon;
    }
}
