<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['role', 'membership_tier', 'search']);
        $perPage = (int) $request->input('per_page', 10);

        $users = $this->userService->getPaginatedUsers($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(AdminUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Đã tạo tài khoản [{$user->name}] thành công!",
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findUser($id);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    public function update(AdminUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->updateUser($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật thông tin tài khoản [{$user->name}]!",
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tài khoản người dùng thành công.',
        ]);
    }

    public function updateRole(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|in:admin,staff,user',
        ]);

        $user = $this->userService->updateRole($id, $validated['role']);

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật vai trò người dùng thành [{$user->role}]!",
            'data' => new UserResource($user),
        ]);
    }

    public function updatePoints(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'delta' => 'required|integer',
        ]);

        $user = $this->userService->adjustPoints($id, (int) $validated['delta']);

        return response()->json([
            'success' => true,
            'message' => "Đã điều chỉnh điểm CinePoints thành công. Tổng điểm hiện tại: {$user->points}!",
            'data' => new UserResource($user),
        ]);
    }
}
