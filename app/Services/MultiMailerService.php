<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MultiMailerService
{
    /**
     * Lưu trữ lỗi gửi mail gần nhất để hiển thị ra flash message
     */
    public static ?string $lastError = null;

    /**
     * Xác định Mailer phù hợp theo email người nhận:
     * - Email nội bộ Quản lý (@goldenshift.vn) -> Sử dụng mailer 'mailtrap'
     * - Email Ứng viên thật (@gmail.com hoặc domain khác) -> Sử dụng mailer 'gmail'
     */
    public static function getMailerForEmail(string $email): string
    {
        $normalizedEmail = strtolower(trim($email));
        if (str_ends_with($normalizedEmail, '@goldenshift.vn')) {
            return 'mailtrap';
        }

        return 'gmail';
    }

    /**
     * Gửi email tự động phân luồng đa kênh (Multi-mailer routing)
     * Trả về true nếu thành công, false nếu thất bại (kèm $errorMessage truyền qua tham chiếu)
     */
    public static function send(string $toEmail, Mailable $mailable, ?string &$errorMessage = null): bool
    {
        self::$lastError = null;
        $toEmail = trim($toEmail);
        if (empty($toEmail)) {
            $err = 'Địa chỉ email người nhận không được để trống.';
            self::$lastError = $err;
            $errorMessage = $err;
            Log::error("Lỗi gửi mail: " . $err);
            return false;
        }

        $mailer = self::getMailerForEmail($toEmail);

        try {
            // Khi đang chạy testing trong PHPUnit, gửi qua Mail::to để tương thích trọn vẹn với Mail::fake()
            if (app()->environment('testing')) {
                Mail::to($toEmail)->send($mailable);
                return true;
            }

            // Gửi qua Mailer được định tuyến chuyên biệt (gmail / mailtrap)
            Mail::mailer($mailer)->to($toEmail)->send($mailable);
            return true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            self::$lastError = $msg;
            $errorMessage = $msg;
            Log::error("Lỗi gửi mail: " . $msg);

            // Fallback gửi qua 'log' mailer để ghi nhận nội dung mail phục vụ debug
            try {
                Mail::mailer('log')->to($toEmail)->send($mailable);
            } catch (\Throwable $logEx) {
                // Ignore log transport error
            }

            return false;
        }
    }

    /**
     * Gửi trực tiếp qua một mailer cụ thể (ví dụ: 'gmail' hoặc 'mailtrap')
     */
    public static function sendDirect(string $mailer, string $toEmail, Mailable $mailable, ?string &$errorMessage = null): bool
    {
        self::$lastError = null;
        $toEmail = trim($toEmail);
        if (empty($toEmail)) {
            $err = 'Địa chỉ email người nhận không được để trống.';
            self::$lastError = $err;
            $errorMessage = $err;
            Log::error("Lỗi gửi mail: " . $err);
            return false;
        }

        try {
            if (app()->environment('testing')) {
                Mail::to($toEmail)->send($mailable);
                return true;
            }

            Mail::mailer($mailer)->to($toEmail)->send($mailable);
            return true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            self::$lastError = $msg;
            $errorMessage = $msg;
            Log::error("Lỗi gửi mail: " . $msg);
            return false;
        }
    }

    /**
     * Lấy thông báo lỗi gửi mail gần nhất
     */
    public static function getLastError(): ?string
    {
        return self::$lastError;
    }
}
