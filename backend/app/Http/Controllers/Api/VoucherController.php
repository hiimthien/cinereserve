<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * List all public active vouchers
     */
    public function index(): JsonResponse
    {
        $vouchers = Voucher::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vouchers,
        ]);
    }

    /**
     * Validate and apply a voucher code
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'seats_total' => 'required|numeric|min:0',
            'snack_total' => 'nullable|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $seatsTotal = (float) $request->input('seats_total', 0);
        $snackTotal = (float) $request->input('snack_total', 0);

        $voucher = Voucher::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại hoặc đã hết hạn sử dụng.',
            ], 422);
        }

        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá này đã hết hạn.',
            ], 422);
        }

        if ($voucher->usage_limit > 0 && $voucher->used_count >= $voucher->usage_limit) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá này đã hết lượt sử dụng.',
            ], 422);
        }

        $totalOrder = $seatsTotal + $snackTotal;
        if ($voucher->min_order_amount > 0 && $totalOrder < $voucher->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng chưa đạt mức tối thiểu ' . number_format($voucher->min_order_amount, 0, ',', '.') . ' đ để áp dụng mã này.',
            ], 422);
        }

        if ($voucher->target === 'combo' && $snackTotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Mã ưu đãi này chỉ áp dụng khi bạn mua kèm Combo Bắp Nước.',
            ], 422);
        }

        $discount = $voucher->calculateDiscount($seatsTotal, $snackTotal);

        if ($discount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể áp dụng mã ưu đãi cho đơn hàng hiện tại.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Áp dụng thành công mã {$voucher->code}! Giảm " . number_format($discount, 0, ',', '.') . " đ",
            'data' => [
                'code' => $voucher->code,
                'title' => $voucher->title,
                'discount_amount' => $discount,
                'target' => $voucher->target,
            ],
        ]);
    }
}
