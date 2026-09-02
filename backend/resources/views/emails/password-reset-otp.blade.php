<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã Xác Nhận Đặt Lại Mật Khẩu • CineReserve</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0b0f19;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #e2e8f0;
        }
        .email-container {
            max-width: 540px;
            margin: 20px auto;
            background-color: #151c2c;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .header {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 1px;
        }
        .content {
            padding: 28px 24px;
            text-align: center;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }
        .message {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .otp-box {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 2px dashed #e11d48;
            border-radius: 20px;
            padding: 20px;
            margin: 0 auto 20px auto;
        }
        .otp-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 2px;
            color: #f43f5e;
            margin-bottom: 6px;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: 8px;
            font-family: 'Courier New', Courier, monospace;
            padding: 8px 16px;
            display: inline-block;
        }
        .expiry-note {
            font-size: 11px;
            color: #f59e0b;
            font-weight: 600;
            margin-top: 6px;
        }
        .warning-text {
            font-size: 11px;
            color: #64748b;
            line-height: 1.5;
            margin-top: 16px;
        }
        .footer {
            background-color: #0b0f19;
            padding: 16px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🎬 CINERESERVE SECURITY</h1>
            <p style="margin: 4px 0 0 0; color: rgba(255, 255, 255, 0.85); font-size: 12px;">Khôi Phục Mật Khẩu Tài Khoản</p>
        </div>

        <div class="content">
            <div class="greeting">Xin chào, {{ $userName ?? 'Quý Khách' }}! 👋</div>
            <div class="message">
                Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản CineReserve của bạn. Vui lòng sử dụng mã xác thực OTP dưới đây để tạo mật khẩu mới:
            </div>

            <!-- OTP Box -->
            <div class="otp-box">
                <div class="otp-label">MÃ XÁC THỰC OTP</div>
                <div class="otp-code">{{ $otpCode }}</div>
                <div class="expiry-note">⏳ Mã xác thực có hiệu lực trong vòng 10 phút</div>
            </div>

            <p class="warning-text">
                Nếu bạn không gửi yêu cầu này, vui lòng bỏ qua email. Mật khẩu của bạn vẫn được giữ an toàn tuyệt đối.
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0;">© 2026 CineReserve Cinema • Hotline hỗ trợ: 1900 6868</p>
        </div>
    </div>
</body>
</html>
