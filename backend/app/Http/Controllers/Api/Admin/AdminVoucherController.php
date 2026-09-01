<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminVoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminVoucherController extends Controller
{
    public function __construct(
        protected VoucherService $voucherService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $vouchers = $this->voucherService->getFilteredVouchers($filters);

        return response()->json([
            'success' => true,
            'data' => VoucherResource::collection($vouchers),
        ]);
    }

    public function store(AdminVoucherRequest $request): JsonResponse
    {
        $voucher = $this->voucherService->createVoucher($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tạo mã voucher thành công!',
            'data' => new VoucherResource($voucher),
        ], 201);
    }

    public function update(AdminVoucherRequest $request, int $id): JsonResponse
    {
        $voucher = $this->voucherService->updateVoucher($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật voucher thành công!',
            'data' => new VoucherResource($voucher),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->voucherService->deleteVoucher($id);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa voucher thành công!',
        ]);
    }
}
