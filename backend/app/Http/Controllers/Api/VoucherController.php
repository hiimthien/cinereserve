<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function __construct(
        protected VoucherService $voucherService
    ) {}

    /**
     * Danh sách voucher công khai đang hoạt động
     */
    public function index(): JsonResponse
    {
        $vouchers = $this->voucherService->getFilteredVouchers();

        return response()->json([
            'success' => true,
            'data' => VoucherResource::collection($vouchers),
        ]);
    }

    /**
     * Xác thực và áp dụng voucher
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'seats_total' => 'required|numeric|min:0',
            'snack_total' => 'nullable|numeric|min:0',
        ]);

        $code = (string) $request->input('code');
        $seatsTotal = (float) $request->input('seats_total', 0);
        $snackTotal = (float) $request->input('snack_total', 0);

        $result = $this->voucherService->applyVoucher($code, $seatsTotal, $snackTotal);

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        $voucher = $result['voucher'];

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'code' => $voucher->code,
                'title' => $voucher->title,
                'discount_amount' => $result['discount'],
                'target' => $voucher->target,
            ],
        ]);
    }
}
