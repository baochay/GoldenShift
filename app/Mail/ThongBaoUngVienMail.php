<?php

namespace App\Mail;

use App\Models\NguoiDung;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ThongBaoUngVienMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public string $tieuDe;
    public string $noiDung;
    public ?string $duongDan;

    public function __construct($user, string $tieuDe, string $noiDung, ?string $duongDan = null)
    {
        $this->user = $user;
        $this->tieuDe = $tieuDe;
        $this->noiDung = $noiDung;
        $this->duongDan = $duongDan;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Golden Shift] ' . $this->tieuDe,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.thong_bao_ung_vien',
        );
    }
}
