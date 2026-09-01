<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketCheckInController extends Controller
{
    /**
     * Quét soát vé QR Code cho nhân viên tại rạp
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string',
            'staff_name' => 'nullable|string',
            'cinema_id' => 'nullable|integer',
        ]);

        $rawCode = trim($request->input('qr_code'));
        
        // Tách lấy mã vé (CR-XXXXXX hoặc CINERESERVE-CR-XXXXXX)
        $bookingCode = $rawCode;
        if (str_starts_with($rawCode, 'CINERESERVE-')) {
            $bookingCode = str_replace('CINERESERVE-', '', $rawCode);
        }

        /** @var Booking|null $booking */
        $booking = Booking::with([
            'showtime.movie',
            'showtime.cinema',
            'showtime.room',
            'bookingSeats.seat',
        ])->where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'status' => 'INVALID',
                'message' => "Mã vé [{$rawCode}] không tồn tại trong hệ thống CineReserve.",
            ], 404);
        }

        // 1. Kiểm tra trạng thái thanh toán
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'status' => 'UNPAID',
                'message' => "Vé này chưa được thanh toán thành công (Trạng thái: {$booking->status}).",
                'data' => $this->formatTicketData($booking),
            ], 422);
        }

        // 2. Chống quét vé 2 lần (Anti-fraud / Double Check-in Protection)
        if ($booking->check_in_status === 'checked_in') {
            $checkInTime = $booking->checked_in_at ? Carbon::parse($booking->checked_in_at)->format('H:i:s - d/m/Y') : 'trước đó';
            $staff = $booking->checked_in_by ?: 'Nhân viên cổng';

            return response()->json([
                'success' => false,
                'status' => 'ALREADY_USED',
                'message' => "CẢNH BÁO: Vé này ĐÃ ĐƯỢC SOÁT VÉ vào lúc {$checkInTime} bởi [{$staff}]. Tuyệt đối không cho vào phòng chiếu!",
                'data' => $this->formatTicketData($booking),
            ], 422);
        }

        // 3. Thực hiện Soát vé thành công
        $staffName = $request->input('staff_name', 'Nhân viên Soát vé');
        $now = now();

        $booking->check_in_status = 'checked_in';
        $booking->checked_in_at = $now;
        $booking->checked_in_by = $staffName;
        $booking->save();

        return response()->json([
            'success' => true,
            'status' => 'VALID',
            'message' => "HỢP LỆ! Soát vé thành công cho khách hàng {$booking->user_name}.",
            'data' => $this->formatTicketData($booking),
        ]);
    }

    /**
     * Xem thông tin vé trước khi soát vé
     */
    public function verify(string $code): JsonResponse
    {
        $bookingCode = str_starts_with($code, 'CINERESERVE-') ? str_replace('CINERESERVE-', '', $code) : $code;

        $booking = Booking::with([
            'showtime.movie',
            'showtime.cinema',
            'showtime.room',
            'bookingSeats.seat',
        ])->where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vé trong hệ thống.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatTicketData($booking),
        ]);
    }

    /**
     * Format dữ liệu vé chi tiết trả về cho máy quét nhân viên
     */
    private function formatTicketData(Booking $booking): array
    {
        $seats = $booking->bookingSeats->map(function ($bs) {
            $s = $bs->seat;
            return $s ? "{$s->row}{$s->number}" : 'Ghế';
        })->toArray();

        return [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'user_name' => $booking->user_name,
            'user_phone' => $booking->user_phone,
            'user_email' => $booking->user_email,
            'movie_title' => $booking->showtime?->movie?->title ?? 'Phim',
            'movie_poster' => $booking->showtime?->movie?->poster_url,
            'cinema_name' => $booking->showtime?->cinema?->name ?? 'CGV Cinema',
            'room_name' => $booking->showtime?->room?->name ?? 'Phòng chiếu',
            'show_date' => $booking->showtime?->date ? Carbon::parse($booking->showtime->date)->format('d/m/Y') : '',
            'start_time' => $booking->showtime?->start_time ?? '',
            'seats' => $seats,
            'combos' => $booking->combos ?? [],
            'total_amount' => $booking->total_amount,
            'check_in_status' => $booking->check_in_status,
            'checked_in_at' => $booking->checked_in_at ? Carbon::parse($booking->checked_in_at)->format('H:i:s d/m/Y') : null,
            'checked_in_by' => $booking->checked_in_by,
        ];
    }
}
