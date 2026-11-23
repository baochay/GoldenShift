<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $hoTen;

    public function __construct(string $otp, string $hoTen)
    {
        $this->otp = $otp;
        $this->hoTen = $hoTen;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mã xác thực OTP đăng ký tài khoản Golden Shift',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp_register',
        );
    }
}
