<?php

namespace App\Helpers;

use Carbon\Carbon;

class HolidayHelper
{
    /**
     * Danh sách ngày lễ cố định theo Dương lịch (tháng-ngày)
     *
     * @var array<string, string>
     */
    protected static array $solarHolidays = [
        '01-01' => 'Tết Dương Lịch',
        '04-30' => 'Ngày Giải phóng miền Nam (30/4)',
        '05-01' => 'Quốc tế Lao động (1/5)',
        '09-02' => 'Quốc khánh (2/9)',
        '09-03' => 'Nghỉ Quốc khánh (3/9)',
    ];

    /**
     * Danh sách các ngày Tết Âm lịch và Giỗ Tổ Hùng Vương (10/3 Âm lịch) theo năm Dương lịch (Y-m-d)
     * Hỗ trợ từ năm 2024 đến năm 2030+
     *
     * @var array<string, string>
     */
    protected static array $lunarHolidays = [
        // Năm 2024
        '2024-02-08' => 'Tết Âm Lịch (29 Tết)',
        '2024-02-09' => 'Tết Âm Lịch (30 Tết)',
        '2024-02-10' => 'Tết Âm Lịch (Mùng 1 Tết)',
        '2024-02-11' => 'Tết Âm Lịch (Mùng 2 Tết)',
        '2024-02-12' => 'Tết Âm Lịch (Mùng 3 Tết)',
        '2024-02-13' => 'Tết Âm Lịch (Mùng 4 Tết)',
        '2024-02-14' => 'Tết Âm Lịch (Mùng 5 Tết)',
        '2024-04-18' => 'Giỗ Tổ Hùng Vương (10/3 Âm lịch)',

        // Năm 2025
        '2025-01-28' => 'Tết Âm Lịch (29 Tết)',
        '2025-01-29' => 'Tết Âm Lịch (Mùng 1 Tết)',
        '2025-01-30' => 'Tết Âm Lịch (Mùng 2 Tết)',
        '2025-01-31' => 'Tết Âm Lịch (Mùng 3 Tết)',
        '2025-02-01' => 'Tết Âm Lịch (Mùng 4 Tết)',
        '2025-02-02' => 'Tết Âm Lịch (Mùng 5 Tết)',
        '2025-04-07' => 'Giỗ Tổ Hùng Vương (10/3 Âm lịch)',

        // Năm 2026
        '2026-02-16' => 'Tết Âm Lịch (29 Tết)',
        '2026-02-17' => 'Tết Âm Lịch (Mùng 1 Tết)',
        '2026-02-18' => 'Tết Âm Lịch (Mùng 2 Tết)',
        '2026-02-19' => 'Tết Âm Lịch (Mùng 3 Tết)',
        '2026-02-20' => 'Tết Âm Lịch (Mùng 4 Tết)',
        '2026-02-21' => 'Tết Âm Lịch (Mùng 5 Tết)',
        '2026-04-26' => 'Giỗ Tổ Hùng Vương (10/3 Âm lịch)',

        // Năm 2027
        '2027-02-05' => 'Tết Âm Lịch (29 Tết)',
        '2027-02-06' => 'Tết Âm Lịch (Mùng 1 Tết)',
        '2027-02-07' => 'Tết Âm Lịch (Mùng 2 Tết)',
        '2027-02-08' => 'Tết Âm Lịch (Mùng 3 Tết)',
        '2027-02-09' => 'Tết Âm Lịch (Mùng 4 Tết)',
        '2027-02-10' => 'Tết Âm Lịch (Mùng 5 Tết)',
        '2027-04-16' => 'Giỗ Tổ Hùng Vương (10/3 Âm lịch)',

        // Năm 2028
        '2028-01-25' => 'Tết Âm Lịch (29 Tết)',
        '2028-01-26' => 'Tết Âm Lịch (Mùng 1 Tết)',
        '2028-01-27' => 'Tết Âm Lịch (Mùng 2 Tết)',
        '2028-01-28' => 'Tết Âm Lịch (Mùng 3 Tết)',
        '2028-01-29' => 'Tết Âm Lịch (Mùng 4 Tết)',
        '2028-01-30' => 'Tết Âm Lịch (Mùng 5 Tết)',
        '2028-04-04' => 'Giỗ Tổ Hùng Vương (10/3 Âm lịch)',

        // Năm 2029
        '2029-02-12' => 'Tết Âm Lịch (29 Tết)',
        '2029-02-13' => 'Tết Âm Lịch (Mùng 1 Tết)',
        '2029-02-14' => 'Tết Âm Lịch (Mùng 2 Tết)',
        '2029-02-15' => 'Tết Âm Lịch (Mùng 3 Tết)',
        '2029-02-16' => 'Tết Âm Lịch (Mùng 4 Tết)',
        '2029-02-17' => 'Tết Âm Lịch (Mùng 5 Tết)',
        '2029-04-22' => 'Giỗ Tổ Hùng Vương (10/3 Âm lịch)',

        // Năm 2030
        '2030-02-02' => 'Tết Âm Lịch (29 Tết)',
        '2030-02-03' => 'Tết Âm Lịch (Mùng 1 Tết)',
        '2030-02-04' => 'Tết Âm Lịch (Mùng 2 Tết)',
        '2030-02-05' => 'Tết Âm Lịch (Mùng 3 Tết)',
        '2030-02-06' => 'Tết Âm Lịch (Mùng 4 Tết)',
        '2030-02-07' => 'Tết Âm Lịch (Mùng 5 Tết)',
        '2030-04-12' => 'Giỗ Tổ Hùng Vương (10/3 Âm lịch)',
    ];

    /**
     * Kiểm tra một ngày có phải là ngày lễ Việt Nam hay không.
     *
     * @param  string|\DateTimeInterface  $date  Ngày làm việc (Y-m-d hoặc đối tượng DateTime/Carbon)
     * @return string|null Tên ngày lễ nếu trùng, hoặc null nếu là ngày thường
     */
    public static function isVietnameseHoliday($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            $carbonDate = ($date instanceof Carbon) ? $date : Carbon::parse($date);
        } catch (\Throwable $e) {
            return null;
        }

        $fullDate = $carbonDate->format('Y-m-d');
        $monthDay = $carbonDate->format('m-d');

        // 1. Kiểm tra ngày lễ cố định Dương lịch hàng năm
        if (isset(self::$solarHolidays[$monthDay])) {
            return self::$solarHolidays[$monthDay];
        }

        // 2. Kiểm tra ngày Tết Âm lịch & Giỗ Tổ Hùng Vương
        if (isset(self::$lunarHolidays[$fullDate])) {
            return self::$lunarHolidays[$fullDate];
        }

        return null;
    }

    /**
     * Lấy toàn bộ danh sách ngày lễ cố định Dương lịch
     *
     * @return array<string, string>
     */
    public static function getSolarHolidays(): array
    {
        return self::$solarHolidays;
    }

    /**
     * Lấy toàn bộ danh sách ngày Tết Âm lịch & Giỗ Tổ
     *
     * @return array<string, string>
     */
    public static function getLunarHolidays(): array
    {
        return self::$lunarHolidays;
    }

    /**
     * Trả về mảng JSON chứa các ngày lễ theo định dạng tiện lợi cho JavaScript client-side kiểm tra
     *
     * @return string JSON string
     */
    public static function getHolidaysJson(): string
    {
        return json_encode([
            'solar' => self::$solarHolidays,
            'lunar' => self::$lunarHolidays,
        ], JSON_UNESCAPED_UNICODE);
    }
}
