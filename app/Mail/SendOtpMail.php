<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $tieuDe;
    public string $noiDung;

    public function __construct(string $otp, string $tieuDe = 'Mã xác thực OTP Golden Shift', string $noiDung = 'Vui lòng sử dụng mã xác thực bên dưới:')
    {
        $this->otp = $otp;
        $this->tieuDe = $tieuDe;
        $this->noiDung = $noiDung;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->tieuDe,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp_register',
            with: [
                'otp' => $this->otp,
                'hoTen' => 'Quý khách',
            ],
        );
    }
}
