<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vé Điện Tử CineReserve</title>
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
        .header p {
            margin: 4px 0 0 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
        }
        .content {
            padding: 24px;
        }
        .movie-card {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.15);
            padding-bottom: 20px;
        }
        .movie-poster {
            display: table-cell;
            width: 90px;
            vertical-align: top;
        }
        .movie-poster img {
            width: 90px;
            border-radius: 12px;
            border: 1px solid #374151;
        }
        .movie-info {
            display: table-cell;
            vertical-align: top;
            padding-left: 16px;
        }
        .movie-title {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 6px 0;
        }
        .badge {
            display: inline-block;
            background-color: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.4);
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .info-row {
            margin: 4px 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .info-row strong {
            color: #f8fafc;
        }
        .ticket-box {
            background-color: #1e293b;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 24px;
        }
        .qr-image {
            width: 170px;
            height: 170px;
            background-color: #ffffff;
            padding: 8px;
            border-radius: 14px;
            margin: 0 auto 10px auto;
            display: block;
        }
        .booking-code {
            font-size: 22px;
            font-weight: 900;
            color: #f59e0b;
            letter-spacing: 2px;
            margin: 6px 0;
        }
        .qr-hint {
            font-size: 12px;
            color: #94a3b8;
            margin: 0;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .summary-table td {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            color: #94a3b8;
        }
        .summary-table td.text-right {
            text-align: right;
            color: #f8fafc;
            font-weight: 600;
        }
        .total-row td {
            font-size: 16px;
            font-weight: 800;
            color: #10b981 !important;
            border-bottom: none;
            padding-top: 14px;
        }
        .footer {
            background-color: #0b0f19;
            padding: 18px;
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
            <h1>🎬 CINERESERVE VIỆT NAM</h1>
            <p>Xác Nhận Đặt Vé & Thanh Toán Thành Công</p>
        </div>

        <div class="content">
            <!-- Movie Info -->
            <div class="movie-card">
                <div class="movie-poster">
                    <img src="{{ $booking->showtime?->movie?->poster_url ?? 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600' }}" alt="{{ $booking->showtime?->movie?->title }}">
                </div>
                <div class="movie-info">
                    <span class="badge">TMDb ★ {{ $booking->showtime?->movie?->rating ?? 8.5 }}</span>
                    <h2 class="movie-title">{{ $booking->showtime?->movie?->title ?? 'Phim Chiếu Rạp' }}</h2>
                    <div class="info-row"><strong>Rạp:</strong> {{ $booking->showtime?->cinema?->name ?? 'CGV Vincom Landmark 81' }}</div>
                    <div class="info-row"><strong>Địa chỉ:</strong> {{ $booking->showtime?->cinema?->address ?? 'TP. Hồ Chí Minh' }}</div>
                    <div class="info-row">
                        <strong>Phòng chiếu:</strong> 
                        {{ $booking->showtime?->room?->name ?? 'Phòng Chiếu 1' }}
                        @php
                            $roomName = $booking->showtime?->room?->name ?? '';
                            $roomType = $booking->showtime?->room?->room_type ?? '';
                        @endphp
                        @if($roomType && !str_contains($roomName, $roomType))
                            ({{ $roomType }})
                        @endif
                    </div>
                    <div class="info-row"><strong>Suất chiếu:</strong> {{ $booking->showtime?->start_time ?? '09:00' }} • {{ $booking->showtime?->show_date ? date('d/m/Y', strtotime(explode(' ', (string)$booking->showtime->show_date)[0])) : date('d/m/Y') }}</div>
                </div>
            </div>

            <!-- QR Code Ticket Pass -->
            <div class="ticket-box">
                <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #f8fafc;">MÃ VÉ ĐIỆN TỬ CHECK-IN</p>
                <img class="qr-image" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($booking->qr_code ?? $booking->booking_code) }}" alt="QR Code">
                <div class="booking-code">{{ $booking->booking_code }}</div>
                <p class="qr-hint">Vui lòng đưa mã QR này cho nhân viên rạp quét khi vào phòng chiếu.</p>
            </div>

            <!-- Booking Summary -->
            <table class="summary-table">
                <tr>
                    <td>Khách hàng</td>
                    <td class="text-right"><strong>{{ $booking->user_name }}</strong></td>
                </tr>
                <tr>
                    <td>Số điện thoại</td>
                    <td class="text-right">{{ $booking->user_phone }}</td>
                </tr>
                <tr>
                    <td>Email nhận vé</td>
                    <td class="text-right">{{ $booking->user_email }}</td>
                </tr>
                <tr>
                    <td>Danh sách ghế</td>
                    <td class="text-right">
                        @php
                            $seatList = $booking->seats;
                            if ((!$seatList || $seatList->isEmpty()) && $booking->bookingSeats) {
                                $seatList = $booking->bookingSeats->map(fn($bs) => $bs->seat)->filter();
                            }
                        @endphp
                        @if($seatList && $seatList->count() > 0)
                            <strong style="color: #10b981;">{{ $seatList->map(fn($s) => $s->row . $s->number . ' (' . strtoupper($s->type ?? 'Standard') . ')')->join(', ') }}</strong>
                        @else
                            Ghế tiêu chuẩn
                        @endif
                    </td>
                </tr>
                @if(!empty($booking->combos))
                <tr>
                    <td>Combo bắp nước</td>
                    <td class="text-right">
                        @foreach($booking->combos as $cb)
                            🍿 {{ $cb['name'] ?? 'Combo' }} (x{{ $cb['quantity'] }})<br>
                        @endforeach
                    </td>
                </tr>
                @endif
                @if($booking->discount_amount > 0)
                <tr>
                    <td>Voucher giảm giá ({{ $booking->voucher_code }})</td>
                    <td class="text-right" style="color: #10b981; font-weight: bold;">
                        -{{ number_format($booking->discount_amount, 0, ',', '.') }} đ
                    </td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TỔNG THANH TOÁN</td>
                    <td class="text-right">
                        {{ number_format($booking->total_amount > 0 ? $booking->total_amount : 115000, 0, ',', '.') }} đ
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 6px 0;">Cảm ơn bạn đã lựa chọn CineReserve Cinema!</p>
            <p style="margin: 0;">Hotline hỗ trợ: 1900 6868 • Website: cinereserve.vn</p>
        </div>
    </div>
</body>
</html>
