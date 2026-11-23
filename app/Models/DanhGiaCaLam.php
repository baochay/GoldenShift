<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_danh_gia
 * @property int $ma_ca_lam
 * @property int $ma_nguoi_danh_gia
 * @property int $ma_nguoi_duoc_danh_gia
 * @property string $loai_danh_gia
 * @property int $so_sao
 * @property int $tieu_chi_thai_do
 * @property int $tieu_chi_chuyen_can
 * @property int $tieu_chi_hieu_suat
 * @property string|null $noi_dung_nhan_xet
 * @property \DateTime $ngay_danh_gia
 * @property CaLamViec|null $caLamViec
 * @property NguoiDung|null $nguoiDanhGia
 * @property NguoiDung|null $nguoiDuocDanhGia
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class DanhGiaCaLam extends Model
{
    use HasFactory;

    protected $table = 'danh_gia_ca_lam';

    protected $primaryKey = 'ma_danh_gia';

    const CREATED_AT = 'ngay_danh_gia';

    const UPDATED_AT = null;

    protected $fillable = [
        'ma_ca_lam',
        'ma_nguoi_danh_gia',
        'ma_nguoi_duoc_danh_gia',
        'loai_danh_gia',
        'so_sao',
        'tieu_chi_thai_do',
        'tieu_chi_chuyen_can',
        'tieu_chi_hieu_suat',
        'noi_dung_nhan_xet',
        'ngay_danh_gia',
    ];

    protected $casts = [
        'so_sao' => 'integer',
        'tieu_chi_thai_do' => 'integer',
        'tieu_chi_chuyen_can' => 'integer',
        'tieu_chi_hieu_suat' => 'integer',
        'ngay_danh_gia' => 'datetime',
    ];

    /**
     * Alias getter/setter cho diem_danh_gia tương đương so_sao
     */
    public function getDiemDanhGiaAttribute()
    {
        return $this->so_sao;
    }

    public function setDiemDanhGiaAttribute($value)
    {
        $this->attributes['so_sao'] = $value;
    }

    /**
     * Alias getter/setter cho nhan_xet tương đương noi_dung_nhan_xet
     */
    public function getNhanXetAttribute()
    {
        return $this->noi_dung_nhan_xet;
    }

    public function setNhanXetAttribute($value)
    {
        $this->attributes['noi_dung_nhan_xet'] = $value;
    }

    /**
     * Quan hệ tới Ca làm việc
     */
    public function caLamViec()
    {
        return $this->belongsTo(CaLamViec::class, 'ma_ca_lam', 'ma_ca_lam');
    }

    /**
     * Quan hệ tới Người đánh giá
     */
    public function nguoiDanhGia()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_danh_gia', 'ma_nguoi_dung');
    }

    /**
     * Quan hệ tới Người được đánh giá
     */
    public function nguoiDuocDanhGia()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_duoc_danh_gia', 'ma_nguoi_dung');
    }

    /**
     * Tính điểm đánh giá trung bình của Ứng viên (do Nhà hàng chấm)
     */
    public static function tinhDiemTrungBinhUngVien($maNguoiDung)
    {
        $danhGiaList = self::where('ma_nguoi_duoc_danh_gia', $maNguoiDung)
            ->where('loai_danh_gia', 'nha_hang_danh_gia_ung_vien')
            ->get();

        $tongSo = $danhGiaList->count();
        if ($tongSo === 0) {
            return [
                'diem_tb' => 0.0,
                'tong_luot' => 0,
                'thai_do_tb' => 0.0,
                'chuyen_can_tb' => 0.0,
                'hieu_suat_tb' => 0.0,
            ];
        }

        return [
            'diem_tb' => round($danhGiaList->avg('so_sao'), 1),
            'tong_luot' => $tongSo,
            'thai_do_tb' => round($danhGiaList->avg('tieu_chi_thai_do'), 1),
            'chuyen_can_tb' => round($danhGiaList->avg('tieu_chi_chuyen_can'), 1),
            'hieu_suat_tb' => round($danhGiaList->avg('tieu_chi_hieu_suat'), 1),
        ];
    }

    /**
     * Tính điểm đánh giá trung bình của Nhà hàng (do Ứng viên chấm)
     */
    public static function tinhDiemTrungBinhNhaHang($maNhaHang)
    {
        $danhGiaList = self::whereHas('caLamViec', function ($q) use ($maNhaHang) {
            $q->where('ma_nha_hang', $maNhaHang);
        })
            ->where('loai_danh_gia', 'ung_vien_danh_gia_nha_hang')
            ->get();

        $tongSo = $danhGiaList->count();
        if ($tongSo === 0) {
            return [
                'diem_tb' => 0.0,
                'tong_luot' => 0,
            ];
        }

        return [
            'diem_tb' => round($danhGiaList->avg('so_sao'), 1),
            'tong_luot' => $tongSo,
        ];
    }

    /**
     * Tính điểm đánh giá trung bình của Thương hiệu
     */
    public static function tinhDiemTrungBinhThuongHieu($maThuongHieu)
    {
        $danhGiaList = self::whereHas('caLamViec.nhaHang', function ($q) use ($maThuongHieu) {
            $q->where('ma_thuong_hieu', $maThuongHieu);
        })
            ->where('loai_danh_gia', 'ung_vien_danh_gia_nha_hang')
            ->get();

        $tongSo = $danhGiaList->count();
        if ($tongSo === 0) {
            return [
                'diem_tb' => 0.0,
                'tong_luot' => 0,
            ];
        }

        return [
            'diem_tb' => round($danhGiaList->avg('so_sao'), 1),
            'tong_luot' => $tongSo,
        ];
    }
}
