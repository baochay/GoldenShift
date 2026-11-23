<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm Tra Gửi Mail Trực Tiếp - Golden Shift</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-color: #0b1120;
            --card-bg: #111827;
            --border-color: #1f2937;
            --text-main: #f9fafb;
            --text-sub: #9ca3af;
            --gold: #f59e0b;
            --gold-light: #fbbf24;
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.12);
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .container {
            max-width: 900px;
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 35px;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .header h1 i {
            color: var(--gold);
        }
        .header p {
            color: var(--text-sub);
            font-size: 15px;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 18px;
        }
        .card-title {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .badge {
            font-size: 13px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .badge-success {
            background: var(--success-bg);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .badge-error {
            background: var(--danger-bg);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .info-list {
            list-style: none;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .info-list li {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px dashed rgba(255,255,255,0.06);
        }
        .info-label {
            color: var(--text-sub);
        }
        .info-val {
            font-family: monospace;
            font-weight: 600;
            color: #e5e7eb;
        }
        .result-box {
            border-radius: 10px;
            padding: 14px;
            font-size: 14px;
            margin-top: auto;
            word-break: break-word;
        }
        .result-box.success {
            background: var(--success-bg);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #34d399;
        }
        .result-box.error {
            background: var(--danger-bg);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
        }
        .result-box pre {
            margin-top: 8px;
            font-family: monospace;
            font-size: 12.5px;
            white-space: pre-wrap;
            background: rgba(0,0,0,0.3);
            padding: 8px;
            border-radius: 6px;
        }
        .form-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .form-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--gold-light);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 16px;
            align-items: flex-end;
        }
        @media (max-width: 768px) {
            .cards-grid { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-sub);
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            background: #1f2937;
            border: 1px solid #374151;
            color: #fff;
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--gold);
        }
        .btn-submit {
            background: linear-gradient(135deg, var(--gold) 0%, #d97706 100%);
            color: #000;
            font-weight: 700;
            border: none;
            padding: 11px 22px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-sub);
            text-decoration: none;
            font-size: 14px;
            margin-top: 16px;
        }
        .btn-back:hover {
            color: #fff;
        }
        .help-section {
            background: rgba(245, 158, 11, 0.05);
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 12px;
            padding: 18px;
            font-size: 13.5px;
            line-height: 1.6;
            color: #d1d5db;
        }
        .help-section h4 {
            color: var(--gold-light);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa-solid fa-envelope-circle-check"></i> Kiểm Tra Kết Nối Mailer Golden Shift</h1>
            <p>Hệ thống tự động kiểm tra gửi email đồng bộ (Not Queued) qua driver Gmail SMTP & Mailtrap SMTP</p>
        </div>

        {{-- Form điều chỉnh Email nhận test --}}
        <div class="form-section">
            <div class="form-title"><i class="fa-solid fa-sliders"></i> Tùy Chỉnh Địa Chỉ Nhận Thử Nghiệm</div>
            <form action="{{ url('/test-mail') }}" method="GET" class="form-grid">
                <div class="form-group">
                    <label for="gmail">Email nhận thử nghiệm Gmail thật:</label>
                    <input type="email" id="gmail" name="gmail" value="{{ $gmailTo }}" required placeholder="email.cua.ban@gmail.com">
                </div>
                <div class="form-group">
                    <label for="mailtrap">Email nhận thử nghiệm Mailtrap (Quản lý):</label>
                    <input type="email" id="mailtrap" name="mailtrap" value="{{ $mailtrapTo }}" required placeholder="manager@goldenshift.vn">
                </div>
                <div>
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-paper-plane"></i> Gửi Thử Lại
                    </button>
                </div>
            </form>
        </div>

        {{-- Kết quả 2 Mailer --}}
        <div class="cards-grid">
            {{-- 1. GMAIL CARD --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-brands fa-google text-gold" style="color: #ea4335;"></i> Gmail Mailer (Ứng viên)
                    </div>
                    @if ($gmailResult['status'] === 'success')
                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Gửi thành công!</span>
                    @else
                        <span class="badge badge-error"><i class="fa-solid fa-triangle-exclamation"></i> Thất bại</span>
                    @endif
                </div>

                <ul class="info-list">
                    <li><span class="info-label">Mailer Driver:</span> <span class="info-val">gmail (smtp)</span></li>
                    <li><span class="info-label">Host & Port:</span> <span class="info-val">{{ $gmailResult['host'] }}</span></li>
                    <li><span class="info-label">Encryption:</span> <span class="info-val">{{ strtoupper($gmailResult['encryption'] ?? 'TLS') }}</span></li>
                    <li><span class="info-label">Username:</span> <span class="info-val">{{ $gmailResult['username'] ?: '(chưa cấu hình)' }}</span></li>
                    <li><span class="info-label">Gửi tới:</span> <span class="info-val" style="color: #60a5fa;">{{ $gmailResult['to'] }}</span></li>
                </ul>

                @if ($gmailResult['status'] === 'success')
                    <div class="result-box success">
                        <i class="fa-solid fa-circle-check"></i> <strong>{{ $gmailResult['message'] }}</strong>
                        <p style="margin-top: 4px; font-size: 13px; opacity: 0.9;">Email OTP mẫu đã được gửi trực tiếp vào hộp thư đến của {{ $gmailResult['to'] }}.</p>
                    </div>
                @else
                    <div class="result-box error">
                        <i class="fa-solid fa-circle-exclamation"></i> <strong>Lỗi gửi mail:</strong>
                        <pre>{{ $gmailResult['message'] }}</pre>
                    </div>
                @endif
            </div>

            {{-- 2. MAILTRAP CARD --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-inbox text-gold" style="color: #10b981;"></i> Mailtrap Mailer (Quản lý)
                    </div>
                    @if ($mailtrapResult['status'] === 'success')
                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Gửi thành công!</span>
                    @else
                        <span class="badge badge-error"><i class="fa-solid fa-triangle-exclamation"></i> Thất bại</span>
                    @endif
                </div>

                <ul class="info-list">
                    <li><span class="info-label">Mailer Driver:</span> <span class="info-val">mailtrap (smtp)</span></li>
                    <li><span class="info-label">Host & Port:</span> <span class="info-val">{{ $mailtrapResult['host'] }}</span></li>
                    <li><span class="info-label">Encryption:</span> <span class="info-val">{{ strtoupper($mailtrapResult['encryption'] ?? 'TLS') }}</span></li>
                    <li><span class="info-label">Username:</span> <span class="info-val">{{ $mailtrapResult['username'] ?: '(chưa cấu hình)' }}</span></li>
                    <li><span class="info-label">Gửi tới:</span> <span class="info-val" style="color: #60a5fa;">{{ $mailtrapResult['to'] }}</span></li>
                </ul>

                @if ($mailtrapResult['status'] === 'success')
                    <div class="result-box success">
                        <i class="fa-solid fa-circle-check"></i> <strong>{{ $mailtrapResult['message'] }}</strong>
                        <p style="margin-top: 4px; font-size: 13px; opacity: 0.9;">Email đã được gửi thành công vào Mailtrap Sandbox Inbox.</p>
                    </div>
                @else
                    <div class="result-box error">
                        <i class="fa-solid fa-circle-exclamation"></i> <strong>Lỗi gửi mail:</strong>
                        <pre>{{ $mailtrapResult['message'] }}</pre>
                    </div>
                @endif
            </div>
        </div>

        {{-- Hướng dẫn nhanh khi có lỗi --}}
        <div class="help-section">
            <h4><i class="fa-solid fa-circle-info"></i> Hướng Dẫn Khắc Phục Khi Gặp Lỗi Kết Nối SMTP:</h4>
            <ul style="padding-left: 20px;">
                <li><strong>Gmail (535-5.7.8 Username and Password not accepted):</strong> Tài khoản Gmail cần bật <em>Xác minh 2 bước (2-Step Verification)</em>, sau đó vào <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: var(--gold-light); text-decoration: underline;">Google App Passwords</a> để sinh Mật khẩu ứng dụng 16 ký tự và điền vào <code>GMAIL_PASSWORD</code> trong file <code>.env</code>.</li>
                <li><strong>Lỗi Timeout kết nối Port 587 hoặc Port 2525:</strong> Kiểm tra phần mềm Antivirus hoặc Firewall trên máy tính có đang chặn cổng SMTP outbound hay không. Có thể thử đổi sang Port 465 (SSL) nếu 587 bị nhà mạng chặn.</li>
                <li><strong>Mailtrap:</strong> Đăng nhập tài khoản tại <a href="https://mailtrap.io" target="_blank" style="color: var(--gold-light); text-decoration: underline;">mailtrap.io</a> &rarr; Inboxes &rarr; SMTP Settings để lấy lại Username & Password mới.</li>
            </ul>
        </div>

        <div style="text-align: center;">
            <a href="{{ url('/') }}" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Quay lại Trang Chủ Golden Shift</a>
        </div>
    </div>
</body>
</html>
