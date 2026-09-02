<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketCheckInRequest;
use App\Services\TicketCheckInService;
use Illuminate\Http\JsonResponse;

class TicketCheckInController extends Controller
{
    public function __construct(
        protected TicketCheckInService $ticketCheckInService
    ) {}

    /**
     * Quét soát vé QR Code cho nhân viên tại rạp
     */
    public function checkIn(TicketCheckInRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $rawCode = (string) $validated['qr_code'];
        $booking = $this->ticketCheckInService->findBookingForCheckIn($rawCode);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'status' => 'INVALID',
                'message' => "Mã vé [{$rawCode}] không tồn tại trong hệ thống CineReserve.",
            ], 404);
        }

        $result = $this->ticketCheckInService->processCheckIn(
            $booking,
            $validated['staff_name'] ?? null,
            isset($validated['cinema_id']) ? (int) $validated['cinema_id'] : null
        );

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }

    /**
     * Xem thông tin vé trước khi soát vé
     */
    public function verify(string $code): JsonResponse
    {
        $booking = $this->ticketCheckInService->findBookingForCheckIn($code);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vé trong hệ thống.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->ticketCheckInService->formatTicketData($booking),
        ]);
    }
}
