<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSnackRequest;
use App\Http\Resources\SnackResource;
use App\Services\SnackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSnackController extends Controller
{
    public function __construct(
        protected SnackService $snackService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['category', 'search']);
        $snacks = $this->snackService->getFilteredSnacks($filters);

        return response()->json([
            'success' => true,
            'data' => SnackResource::collection($snacks),
        ]);
    }

    public function store(AdminSnackRequest $request): JsonResponse
    {
        $snack = $this->snackService->createSnack($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Thêm món ăn/combo mới thành công!',
            'data' => new SnackResource($snack),
        ], 201);
    }

    public function update(AdminSnackRequest $request, int $id): JsonResponse
    {
        $snack = $this->snackService->updateSnack($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật món ăn/combo thành công!',
            'data' => new SnackResource($snack),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->snackService->deleteSnack($id);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa món ăn/combo thành công!',
        ]);
    }
}
