<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Thực Đổi Mật Khẩu - Golden Shift</title>
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
                            <p style="margin: 4px 0 0; color: #cbd5e1; font-size: 12px; letter-spacing: 2px;">BẢO MẬT TÀI KHOẢN</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <h2 style="margin: 0 0 14px; color: #0f172a; font-size: 20px; font-weight: 700;">Chào bạn {{ $hoTen }},</h2>
                            <p style="margin: 0 0 20px; font-size: 14.5px; line-height: 1.6; color: #475569;">
                                Hệ thống nhận được yêu cầu cập nhật mật khẩu mới trong trang Hồ sơ cá nhân của bạn. Để đảm bảo an toàn tuyệt đối, vui lòng nhập mã xác thực OTP sau:
                            </p>
                            <!-- OTP Box -->
                            <div style="background-color: #fffdf5; border: 2px dashed #f59e0b; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0;">
                                <span style="font-size: 12px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 6px;">Mã OTP Xác Nhận</span>
                                <span style="font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #b45309; display: inline-block;">{{ $otp }}</span>
                                <p style="margin: 8px 0 0; font-size: 12.5px; color: #64748b;">Mã này có hiệu lực trong vòng <strong>5 phút</strong>.</p>
                            </div>

                            <p style="margin: 0 0 14px; font-size: 13.5px; line-height: 1.5; color: #64748b;">
                                ⚠️ <em>Nếu bạn không thực hiện hành động này, rất có thể tài khoản của bạn đang bị truy cập trái phép. Vui lòng liên hệ ban quản trị ngay lập tức.</em>
                            </p>
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
