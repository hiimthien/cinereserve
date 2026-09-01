<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSnackRequest;
use App\Http\Resources\SnackResource;
use App\Models\Snack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSnackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Snack::query();

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $snacks = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => SnackResource::collection($snacks),
        ]);
    }

    public function store(AdminSnackRequest $request): JsonResponse
    {
        $snack = Snack::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Thêm món ăn/combo mới thành công!',
            'data' => new SnackResource($snack),
        ], 201);
    }

    public function update(AdminSnackRequest $request, int $id): JsonResponse
    {
        $snack = Snack::findOrFail($id);
        $snack->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật món ăn/combo thành công!',
            'data' => new SnackResource($snack),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $snack = Snack::findOrFail($id);
        $snack->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa món ăn/combo thành công!',
        ]);
    }
}
