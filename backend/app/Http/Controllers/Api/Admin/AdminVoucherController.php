<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminVoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminVoucherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Voucher::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $vouchers = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => VoucherResource::collection($vouchers),
        ]);
    }

    public function store(AdminVoucherRequest $request): JsonResponse
    {
        $voucher = Voucher::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tạo mã voucher thành công!',
            'data' => new VoucherResource($voucher),
        ], 201);
    }

    public function update(AdminVoucherRequest $request, int $id): JsonResponse
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật voucher thành công!',
            'data' => new VoucherResource($voucher),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa voucher thành công!',
        ]);
    }
}
