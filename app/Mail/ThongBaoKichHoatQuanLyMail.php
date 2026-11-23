<?php

namespace App\Mail;

use App\Models\NguoiDung;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ThongBaoKichHoatQuanLyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public ?string $tenNhaHang;

    public function __construct($user, ?string $tenNhaHang = null)
    {
        $this->user = $user;
        $this->tenNhaHang = $tenNhaHang;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tài khoản Quản lý Chi nhánh của bạn đã được PHÊ DUYỆT - Golden Shift',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.kich_hoat_quan_ly',
        );
    }
}
