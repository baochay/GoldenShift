<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tieuDe }} - Golden Shift</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 580px; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 26px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #f59e0b; font-size: 24px; font-weight: 800; letter-spacing: 1px;">GOLDEN SHIFT</h1>
                            <p style="margin: 4px 0 0; color: #cbd5e1; font-size: 12px; letter-spacing: 2px;">THÔNG BÁO HOẠT ĐỘNG CA LÀM</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <div style="display: inline-block; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 4px 10px; margin-bottom: 14px;">
                                <span style="font-size: 12px; font-weight: 700; color: #059669;">Thông Báo Mới</span>
                            </div>
                            <h2 style="margin: 0 0 16px; color: #0f172a; font-size: 19px; font-weight: 700;">{{ $tieuDe }}</h2>
                            <div style="background-color: #f8fafc; border-left: 4px solid #f59e0b; border-radius: 4px; padding: 16px; margin: 0 0 24px;">
                                <p style="margin: 0; font-size: 14.5px; line-height: 1.6; color: #334155;">
                                    {!! nl2br(e($noiDung)) !!}
                                </p>
                            </div>

                            @if (!empty($duongDan))
                            <div style="text-align: center; margin: 28px 0 10px;">
                                <a href="{{ url($duongDan) }}" style="display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; font-size: 14.5px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);">
                                    Xem Chi Tiết Ngay
                                </a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 12px; color: #94a3b8;">
                            <p style="margin: 0 0 4px;">© 2026 Golden Shift F&B Recruitment Portal. All rights reserved.</p>
                            <p style="margin: 0;">Hotline: 1900 8899 | Email: contact@goldenshift.vn</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
