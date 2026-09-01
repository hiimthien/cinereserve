<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\TicketConfirmationMail;
use App\Models\Booking;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VNPayService
{
    private string $tmnCode;
    private string $hashSecret;
    private string $vnpUrl;

    public function __construct()
    {
        $this->tmnCode = config('services.vnpay.tmn_code', 'CINERESERVE');
        $this->hashSecret = config('services.vnpay.hash_secret', 'RAKUGNOKPUWETKHYDRAKUGNOKPUWETKH');
        $this->vnpUrl = config('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
    }

    /**
     * Tạo URL chuyển hướng sang cổng thanh toán VNPay Sandbox
     */
    public function createPaymentUrl(Booking $booking, string $returnUrl, string $ipAddr = '127.0.0.1'): string
    {
        $vnp_TxnRef = $booking->booking_code;
        $vnp_OrderInfo = "Thanh toan ve CineReserve - " . $booking->booking_code;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = (int) ($booking->total_amount * 100); // VNPay yêu cầu nhân 100
        $vnp_Locale = 'vn';
        $vnp_CreateDate = date('YmdHis');
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_Amount' => $vnp_Amount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => $vnp_CreateDate,
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $ipAddr,
            'vnp_Locale' => $vnp_Locale,
            'vnp_OrderInfo' => $vnp_OrderInfo,
            'vnp_OrderType' => $vnp_OrderType,
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $vnp_TxnRef,
            'vnp_ExpireDate' => $vnp_ExpireDate,
        ];

        ksort($inputData);
        $query = '';
        $i = 0;
        $hashdata = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashdata .= urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
            $query .= urlencode((string)$key) . "=" . urlencode((string)$value) . '&';
        }

        $vnp_SecureHash = hash_hmac('sha512', $hashdata, $this->hashSecret);
        $vnpUrl = $this->vnpUrl . "?" . $query . 'vnp_SecureHash=' . $vnp_SecureHash;

        return $vnpUrl;
    }

    /**
     * Xử lý Webhook (IPN) với kiểm tra chữ ký HMAC-SHA512 và Idempotency
     */
    public function processIpn(array $inputData): array
    {
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);
        $hashData = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashData .= urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->hashSecret);

        // 1. Kiểm tra chữ ký bảo mật HMAC-SHA512
        if (hash_equals($secureHash, $vnp_SecureHash) === false) {
            Log::warning('VNPay IPN: Chữ ký không hợp lệ', ['received' => $vnp_SecureHash, 'calculated' => $secureHash]);
            return [
                'RspCode' => '97',
                'Message' => 'Invalid Signature',
            ];
        }

        $bookingCode = $inputData['vnp_TxnRef'] ?? '';
        $vnpAmount = ((float)($inputData['vnp_Amount'] ?? 0)) / 100;
        $responseCode = $inputData['vnp_ResponseCode'] ?? '';
        $transactionNo = $inputData['vnp_TransactionNo'] ?? null;
        $bankCode = $inputData['vnp_BankCode'] ?? null;

        /** @var Booking|null $booking */
        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return [
                'RspCode' => '01',
                'Message' => 'Order not found',
            ];
        }

        // 2. Kiểm tra số tiền
        if (abs($booking->total_amount - $vnpAmount) > 100) {
            return [
                'RspCode' => '04',
                'Message' => 'Invalid amount',
            ];
        }

        // 3. Cơ chế Idempotency: Nếu đơn hàng đã được xác nhận trước đó thì trả về thành công ngay
        if ($booking->status === 'confirmed') {
            return [
                'RspCode' => '02',
                'Message' => 'Order already confirmed',
            ];
        }

        // 4. Xử lý kết quả giao dịch
        if ($responseCode === '00') {
            DB::transaction(function () use ($booking, $transactionNo, $bankCode) {
                $booking->status = 'confirmed';
                $booking->vnp_transaction_no = $transactionNo;
                $booking->vnp_bank_code = $bankCode;
                $booking->save();
            });

            // Gửi mail vé điện tử qua Queue Job (Background Worker)
            try {
                $booking->load(['showtime.movie', 'showtime.cinema', 'showtime.room', 'bookingSeats.seat']);
                if (!empty($booking->user_email)) {
                    \App\Jobs\SendTicketEmailJob::dispatch($booking);
                }
            } catch (Exception $e) {
                Log::error('Lỗi dispatch Queue Job gửi vé sau khi VNPay IPN: ' . $e->getMessage());
            }

            return [
                'RspCode' => '00',
                'Message' => 'Confirm Success',
            ];
        } else {
            $booking->status = 'failed';
            $booking->save();

            return [
                'RspCode' => '00',
                'Message' => 'Transaction Failed Recorded',
            ];
        }
    }
}
