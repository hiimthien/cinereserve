<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\VNPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected VNPayService $vnPayService
    ) {}

    /**
     * Khởi tạo URL chuyển hướng VNPay Sandbox
     */
    public function createVNPayUrl(Request $request): JsonResponse
    {
        $request->validate([
            'booking_code' => 'required|string',
            'return_url' => 'nullable|string',
        ]);

        $booking = Booking::where('booking_code', $request->input('booking_code'))->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng.',
            ], 404);
        }

        $returnUrl = $request->input('return_url', 'http://localhost:5173/ticket/confirmation');
        $paymentUrl = $this->vnPayService->createPaymentUrl($booking, $returnUrl, $request->ip() ?: '127.0.0.1');

        return response()->json([
            'success' => true,
            'payment_url' => $paymentUrl,
        ]);
    }

    /**
     * Webhook IPN xử lý từ server VNPay (Background Server-to-Server)
     */
    public function handleVNPayIpn(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $result = $this->vnPayService->processIpn($inputData);

        return response()->json($result);
    }

    /**
     * Xử lý khi khách hàng hoàn tất thanh toán và được VNPay chuyển hướng về website
     */
    public function handleVNPayReturn(Request $request): RedirectResponse
    {
        $inputData = $request->all();
        $bookingCode = $inputData['vnp_TxnRef'] ?? '';
        $responseCode = $inputData['vnp_ResponseCode'] ?? '';

        if ($responseCode === '00') {
            return redirect("http://localhost:5173/ticket/confirmation?code={$bookingCode}&status=success");
        }

        return redirect("http://localhost:5173/checkout?code={$bookingCode}&status=failed");
    }
}
