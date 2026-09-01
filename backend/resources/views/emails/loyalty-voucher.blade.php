<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectTitle ?? 'Quà Tặng Voucher CineReserve' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0b0f19;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #e2e8f0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #151c2c;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .header {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            padding: 28px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 1px;
        }
        .header p {
            margin: 6px 0 0 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }
        .content {
            padding: 28px 24px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
        }
        .message {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .voucher-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 2px dashed #f59e0b;
            border-radius: 20px;
            padding: 24px;
            margin: 0 auto 24px auto;
            position: relative;
        }
        .voucher-title {
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 1px;
            color: #f59e0b;
            margin-bottom: 6px;
        }
        .voucher-code {
            font-size: 28px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: 3px;
            background-color: rgba(255, 255, 255, 0.05);
            padding: 10px 20px;
            border-radius: 12px;
            display: inline-block;
            margin: 8px 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .voucher-desc {
            font-size: 13px;
            color: #cbd5e1;
            margin: 6px 0 14px 0;
        }
        .qr-image {
            width: 140px;
            height: 140px;
            background-color: #ffffff;
            padding: 8px;
            border-radius: 12px;
            margin: 0 auto;
            display: block;
        }
        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 14px;
            margin-top: 10px;
            box-shadow: 0 10px 20px -5px rgba(225, 29, 72, 0.4);
        }
        .footer {
            background-color: #0b0f19;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🎬 CINERESERVE LOYALTY CLUB</h1>
            <p>{{ $badgeText ?? 'Đặc Quyền Thành Viên VIP' }}</p>
        </div>

        <div class="content">
            <div class="greeting">Xin chào, {{ $userName ?? 'Quý Khách' }}! 👋</div>
            <div class="message">
                {{ $emailMessage ?? 'Cảm ơn bạn đã đồng hành cùng CineReserve. Dưới đây là mã voucher ưu đãi dành riêng cho bạn:' }}
            </div>

            <!-- Voucher Card -->
            <div class="voucher-card">
                <div class="voucher-title">{{ $voucherTitle ?? 'Voucher Ưu Đãi Độc Quyền' }}</div>
                <div class="voucher-code">{{ $voucherCode ?? 'CINERESERVE' }}</div>
                <div class="voucher-desc">{{ $voucherDescription ?? 'Giảm giá trực tiếp khi đặt vé xem phim trực tuyến tại CineReserve' }}</div>
                
                <!-- QR Code Voucher -->
                <img 
                    class="qr-image" 
                    src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($voucherCode ?? 'CINERESERVE') }}" 
                    alt="Mã QR Voucher"
                />
                <p style="font-size: 11px; color: #94a3b8; margin: 10px 0 0 0;">
                    Đưa mã QR cho nhân viên tại quầy hoặc nhập mã khi thanh toán online
                </p>
            </div>

            <!-- CTA Button -->
            <a href="http://localhost:5173" target="_blank" class="cta-btn">
                🍿 Đặt Vé & Sử Dụng Voucher Ngay
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 6px 0;">CineReserve Cinema • Hệ thống rạp chiếu phim hàng đầu Việt Nam</p>
            <p style="margin: 0;">Hotline: 1900 6868 • Hạng hiện tại: {{ $tierName ?? 'CineMember' }}</p>
        </div>
    </div>
</body>
</html>
