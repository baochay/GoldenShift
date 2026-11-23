<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_vi_pham
 * @property int $ma_nguoi_vi_pham
 * @property int $ma_nguoi_bao_cao
 * @property int|null $ma_ca_lam
 * @property string $loai_vi_pham
 * @property string $muc_do_nghiem_trong
 * @property string $mo_ta_chi_tiet
 * @property string|null $hinh_anh_minh_chung
 * @property float $so_tien_phat_du_kien
 * @property string $trang_thai_xu_ly
 * @property string|null $hinh_thuc_xu_ly
 * @property string|null $ghi_chu_admin
 * @property \DateTime $ngay_lap_bao_cao
 * @property NguoiDung|null $nguoiViPham
 * @property NguoiDung|null $nguoiBaoCao
 * @property CaLamViec|null $caLamViec
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class BaoCaoViPham extends Model
{
    use HasFactory;

    protected $table = 'bao_cao_vi_pham';

    protected $primaryKey = 'ma_vi_pham';

    const CREATED_AT = 'ngay_lap_bao_cao';

    const UPDATED_AT = null;

    protected $fillable = [
        'ma_nguoi_vi_pham',
        'ma_nguoi_bao_cao',
        'ma_ca_lam',
        'loai_vi_pham',
        'muc_do_nghiem_trong',
        'mo_ta_chi_tiet',
        'hinh_anh_minh_chung',
        'so_tien_phat_du_kien',
        'trang_thai_xu_ly',
        'hinh_thuc_xu_ly',
        'ghi_chu_admin',
        'ngay_lap_bao_cao',
    ];

    protected $casts = [
        'so_tien_phat_du_kien' => 'decimal:2',
        'ngay_lap_bao_cao' => 'datetime',
    ];

    /**
     * Danh sách lý do vi phạm phân theo 2 phân hệ (Quản lý báo cáo Ứng viên & Ứng viên báo cáo Quản lý)
     */
    public static $danhSachLyDoManager = [
        'bung_ca' => 'Bùng ca / Tự ý bỏ ca không báo trước',
        'di_muon_ve_som' => 'Đi muộn / Về sớm nhiều lần',
        'thai_do_khong_phu_hop' => 'Thái độ làm việc kém / Không tuân thủ nội quy',
        'gay_thiet_hai' => 'Gây hư hỏng / Thiệt hại tài sản nhà hàng',
        'ly_do_khac' => 'Lý do khác',
    ];

    public static $danhSachLyDoCandidate = [
        'sai_luong_thu_nhap' => 'Gian lận giờ làm / Không thanh toán đúng mức lương',
        'mo_ta_khong_dung' => 'Phân công công việc không đúng như mô tả ca làm',
        'thai_do_chen_ep' => 'Quản lý có thái độ chèn ép / Quấy rối',
        'moi_truong_khong_an_toan' => 'Môi trường làm việc không đảm bảo an toàn',
        'ly_do_khac' => 'Lý do khác',
    ];

    /**
     * Tổng hợp tất cả lý do vi phạm
     */
    public static $danhSachLyDo = [
        // Quản lý báo cáo Ứng viên
        'bung_ca' => 'Bùng ca / Tự ý bỏ ca không báo trước',
        'bo_ca_khong_bao' => 'Bùng ca / Tự ý bỏ ca',
        'di_muon_ve_som' => 'Đi muộn / Về sớm nhiều lần',
        'di_muon' => 'Đi muộn / Về sớm',
        'thai_do_khong_phu_hop' => 'Thái độ làm việc kém / Không tuân thủ nội quy',
        'thai_do_kem' => 'Thái độ không phù hợp',
        'gay_thiet_hai' => 'Gây hư hỏng / Thiệt hại tài sản nhà hàng',
        'lam_hong_tai_san' => 'Làm hư hỏng tài sản',
        'vi_pham_dong_phuc' => 'Sai tác phong / Vi phạm trang phục',

        // Ứng viên báo cáo Quản lý
        'sai_luong_thu_nhap' => 'Gian lận giờ làm / Không thanh toán đúng mức lương',
        'khong_thanh_toan' => 'Không thanh toán đúng hạn',
        'mo_ta_khong_dung' => 'Phân công công việc không đúng như mô tả ca làm',
        'thai_do_chen_ep' => 'Quản lý có thái độ chèn ép / Quấy rối',
        'vi_pham_noi_quy' => 'Quấy rối / Vi phạm nội quy nghiêm trọng',
        'moi_truong_khong_an_toan' => 'Môi trường làm việc không đảm bảo an toàn',

        // Chung
        'ly_do_khac' => 'Lý do khác',
        'khac' => 'Lý do khác',
    ];

    /**
     * Lấy tên tiếng Việt của loại vi phạm
     */
    public function getTenLoaiViPhamAttribute()
    {
        $key = $this->loai_vi_pham;

        return self::$danhSachLyDo[$key] ?? ($key ?: 'Chưa phân loại');
    }

    /**
     * Trạng thái chuẩn hóa (cho_xuly, da_xuly, tu_choi)
     */
    public function getTrangThaiChuanAttribute()
    {
        $st = $this->trang_thai_xu_ly;
        if (in_array($st, ['cho_xu_ly', 'cho_xuly'])) {
            return 'cho_xuly';
        }
        if (in_array($st, ['da_xac_nhan', 'da_xuly'])) {
            return 'da_xuly';
        }
        if (in_array($st, ['da_huy', 'tu_choi'])) {
            return 'tu_choi';
        }

        return $st ?: 'cho_xuly';
    }

    /**
     * Quan hệ tới Người gửi báo cáo
     */
    public function nguoiBaoCao()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_bao_cao', 'ma_nguoi_dung');
    }

    /**
     * Quan hệ tới Người bị báo cáo (vi phạm)
     */
    public function nguoiViPham()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nguoi_vi_pham', 'ma_nguoi_dung');
    }

    /**
     * Quan hệ tới Ca làm việc liên quan (nếu có)
     */
    public function caLamViec()
    {
        return $this->belongsTo(CaLamViec::class, 'ma_ca_lam', 'ma_ca_lam');
    }
}
