<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhắc Giờ Chiếu Phim - CineReserve</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0b0f19;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #e2e8f0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #111827;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #1f293d;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .header {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 50%, #881337 100%);
            padding: 28px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #fecdd3;
            font-weight: 600;
        }
        .body-content {
            padding: 28px 24px;
        }
        .reminder-banner {
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.4);
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 24px;
            text-align: center;
        }
        .reminder-banner strong {
            color: #fbbf24;
            font-size: 14px;
            display: block;
            margin-bottom: 4px;
        }
        .reminder-banner span {
            color: #d1d5db;
            font-size: 12px;
        }
        .movie-card {
            display: table;
            width: 100%;
            background: #1a2234;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 24px;
            border: 1px solid #243048;
        }
        .movie-poster-cell {
            display: table-cell;
            width: 100px;
            vertical-align: top;
        }
        .movie-poster {
            width: 100px;
            height: 145px;
            border-radius: 10px;
            object-fit: cover;
            display: block;
        }
        .movie-info-cell {
            display: table-cell;
            vertical-align: top;
            padding-left: 18px;
        }
        .movie-title {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 6px 0;
        }
        .movie-meta {
            font-size: 12px;
            color: #94a3b8;
            margin: 0 0 10px 0;
        }
        .details-grid {
            background: #161f30;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            border: 1px solid #222f46;
        }
        .detail-row {
            display: table;
            width: 100%;
            padding: 6px 0;
            border-bottom: 1px solid #1f2a3f;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            display: table-cell;
            width: 40%;
            font-size: 12px;
            color: #94a3b8;
        }
        .detail-value {
            display: table-cell;
            width: 60%;
            font-size: 12px;
            font-weight: 700;
            color: #f1f5f9;
            text-align: right;
        }
        .qr-section {
            text-align: center;
            background: #161f30;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px dashed #334155;
        }
        .qr-code {
            width: 140px;
            height: 140px;
            background: #ffffff;
            padding: 8px;
            border-radius: 12px;
            margin: 0 auto 12px;
            display: inline-block;
        }
        .booking-code {
            font-family: monospace;
            font-size: 16px;
            font-weight: 900;
            color: #fb7185;
            letter-spacing: 2px;
            display: block;
        }
        .cta-button {
            display: block;
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            color: #ffffff !important;
            text-align: center;
            padding: 14px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 14px;
            text-decoration: none;
            margin: 20px 0;
            box-shadow: 0 10px 15px -3px rgba(225, 29, 72, 0.4);
        }
        .footer {
            text-align: center;
            padding: 20px 24px;
            background: #0d131f;
            border-top: 1px solid #1a2333;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎬 NHẮC NHỞ SUẤT CHIẾU SẮP DIỄN RA</h1>
            <p>Suất chiếu của bạn sẽ bắt đầu trong vòng 2 giờ tới!</p>
        </div>

        <!-- Body -->
        <div class="body-content">
            <!-- Warning Banner -->
            <div class="reminder-banner">
                <strong>⏰ Vui lòng có mặt trước 15 phút</strong>
                <span>Để nhận bắp nước tại quầy và làm thủ tục soát vé vào phòng chiếu trước khi phim bắt đầu.</span>
            </div>

            <!-- Movie Information Card -->
            <div class="movie-card">
                <div class="movie-poster-cell">
                    <img src="{{ $booking->movie?->poster_url ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=300' }}" alt="Poster" class="movie-poster">
                </div>
                <div class="movie-info-cell">
                    <h2 class="movie-title">{{ $booking->movie?->title }}</h2>
                    <p class="movie-meta">
                        Thời lượng: {{ $booking->movie?->duration ?: 120 }} phút • Định dạng: {{ $booking->showtime?->format ?: '2D Standard' }}
                    </p>
                    <p style="margin: 0; font-size: 13px; color: #fbbf24; font-weight: 700;">
                        🏛️ {{ $booking->cinema?->name }}
                    </p>
                    <p style="margin: 4px 0 0; font-size: 11px; color: #94a3b8;">
                        📍 {{ $booking->cinema?->address }}
                    </p>
                </div>
            </div>

            <!-- Details Table -->
            <div class="details-grid">
                <div class="detail-row">
                    <div class="detail-label">Mã Đặt Vé:</div>
                    <div class="detail-value" style="color: #fb7185; font-family: monospace;">{{ $booking->booking_code }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Giờ Bắt Đầu:</div>
                    <div class="detail-value" style="color: #38bdf8; font-size: 14px;">{{ $booking->showtime?->start_time }} (Hôm Nay)</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phòng Chiếu:</div>
                    <div class="detail-value">{{ $booking->room?->name ?: 'Phòng Chiếu 1' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Ghế Ngồi:</div>
                    <div class="detail-value" style="color: #4ade80;">
                        @if($booking->seats && count($booking->seats) > 0)
                            {{ $booking->seats->pluck('seat_number')->implode(', ') }}
                        @else
                            {{ $booking->seat_numbers ?: 'Ghế đã chọn' }}
                        @endif
                    </div>
                </div>
                @if($booking->combos && count($booking->combos) > 0)
                <div class="detail-row">
                    <div class="detail-label">Bắp Nước Đã Đặt:</div>
                    <div class="detail-value" style="color: #f59e0b;">
                        @foreach($booking->combos as $cb)
                            {{ $cb['quantity'] }}x {{ $cb['name'] }}@if(!$loop->last), @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- QR Code Check-in Section -->
            <div class="qr-section">
                <p style="margin: 0 0 10px 0; font-size: 12px; color: #cbd5e1; font-weight: 600;">
                    Đưa mã QR này tại cổng soát vé hoặc quầy Fast Track F&B:
                </p>
                <img 
                    src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $booking->qr_code ?: $booking->booking_code }}" 
                    alt="Mã QR Soát Vé" 
                    class="qr-code"
                >
                <span class="booking-code">{{ $booking->booking_code }}</span>
            </div>

            <!-- CTA Button -->
            <a href="http://localhost:5173/my-tickets" class="cta-button">
                🎟️ Mở Vé Điện Tử Trực Tuyến
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 4px 0;">Cảm ơn bạn đã lựa chọn trải nghiệm điện ảnh tại <strong>CineReserve</strong>.</p>
            <p style="margin: 0;">Hotline hỗ trợ: 1900 6868 • Email: hotro@cinereserve.vn</p>
        </div>
    </div>
</body>
</html>
