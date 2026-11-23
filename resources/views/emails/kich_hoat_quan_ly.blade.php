<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài Khoản Quản Lý Chi Nhánh Đã Được Kích Hoạt - Golden Shift</title>
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
                            <p style="margin: 4px 0 0; color: #cbd5e1; font-size: 12px; letter-spacing: 2px;">BAN QUẢN TRỊ HỆ THỐNG</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <div style="display: inline-block; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 4px 10px; margin-bottom: 14px;">
                                <span style="font-size: 12px; font-weight: 700; color: #059669;">✓ ĐÃ PHÊ DUYỆT TÀI KHOẢN</span>
                            </div>
                            <h2 style="margin: 0 0 14px; color: #0f172a; font-size: 20px; font-weight: 700;">Chúc mừng Quản lý {{ is_array($user) ? ($user['ho_ten'] ?? '') : ($user->ho_ten ?? '') }},</h2>
                            <p style="margin: 0 0 18px; font-size: 14.5px; line-height: 1.6; color: #475569;">
                                Đơn đăng ký tài khoản Quản lý nhà hàng của bạn đã được <strong>Ban Quản trị Golden Shift chính thức phê duyệt</strong>. Tài khoản của bạn hiện đã được kích hoạt ở trạng thái <strong>[ĐÃ DUYỆT / HOẠT ĐỘNG]</strong>.
                            </p>

                            <!-- Restaurant Info Box -->
                            <div style="background-color: #fffdf5; border: 1px solid #fde68a; border-radius: 12px; padding: 18px 20px; margin: 20px 0;">
                                <h3 style="margin: 0 0 10px; font-size: 14px; font-weight: 700; color: #b45309; text-transform: uppercase;">Thông Tin Chi Nhánh Trực Thuộc</h3>
                                <p style="margin: 0 0 6px; font-size: 14px; color: #334155;">
                                    <strong>Nhà hàng:</strong> {{ $tenNhaHang ?? 'Chi nhánh đã phân công' }}
                                </p>
                                <p style="margin: 0 0 6px; font-size: 14px; color: #334155;">
                                    <strong>Email đăng nhập:</strong> {{ is_array($user) ? ($user['email'] ?? '') : ($user->email ?? '') }}
                                </p>
                                <p style="margin: 0; font-size: 14px; color: #334155;">
                                    <strong>Vai trò hệ thống:</strong> Quản lý Chi Nhánh (Store Manager)
                                </p>
                            </div>

                            <p style="margin: 0 0 22px; font-size: 14px; line-height: 1.6; color: #475569;">
                                Bây giờ bạn đã có toàn quyền truy cập để <strong>Đăng ca tuyển dụng, Duyệt ứng viên, Quản lý chấm công qua Dynamic QR 30s và Đánh giá nhân sự</strong>.
                            </p>

                            <div style="text-align: center; margin: 26px 0 10px;">
                                <a href="{{ url('/dang-nhap') }}" style="display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; text-decoration: none; padding: 13px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);">
                                    Đăng Nhập Quản Lý Ngay
                                </a>
                            </div>
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
