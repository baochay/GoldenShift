<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ma_thuong_hieu
 * @property string $ten_thuong_hieu
 * @property string|null $ma_dinh_danh
 *
 * @mixin \Eloquent
 * @mixin Builder
 */
class ThuongHieu extends Model
{
    use HasFactory;

    protected $table = 'thuong_hieu';

    protected $primaryKey = 'ma_thuong_hieu';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ten_thuong_hieu',
        'ma_dinh_danh',
        'bieu_tuong',
        'anh_bia',
        'mo_ta_ngan',
        'gioi_thieu_chi_tiet',
        'loai_hinh_am_thuc',
        'trang_thai',
    ];

    public function nhaHang()
    {
        return $this->hasMany(NhaHang::class, 'ma_thuong_hieu', 'ma_thuong_hieu');
    }

    /**
     * Trả về URL ảnh logo đầy đủ nếu giá trị bieu_tuong là đường dẫn file ảnh hợp lệ.
     */
    public static function resolveLogoUrl(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '' || str_starts_with($trimmed, 'fa-')) {
            return null;
        }

        if (file_exists(public_path($trimmed))) {
            return asset($trimmed);
        }

        $storageRelative = ltrim($trimmed, '/');
        if (file_exists(public_path('storage/'.$storageRelative))) {
            return asset('storage/'.$storageRelative);
        }

        return null;
    }

    /**
     * Trả về class icon Font Awesome dự phòng khi thương hiệu chưa tải lên ảnh logo.
     */
    public static function resolveFallbackIcon(?string $value): string
    {
        $trimmed = trim((string) $value);
        if ($trimmed !== '' && str_starts_with($trimmed, 'fa-')) {
            return $trimmed;
        }

        return 'fa-utensils';
    }
}
