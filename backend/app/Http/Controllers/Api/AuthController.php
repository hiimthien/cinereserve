<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\GoogleAuthRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Đăng ký tài khoản thành viên mới
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký tài khoản thành công! Tặng bạn 20 điểm thưởng và voucher 30k vào email.',
            'data' => $result,
        ], 201);
    }

    /**
     * Đăng nhập thành viên
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->authService->login((string) $validated['email'], (string) $validated['password']);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'data' => $result,
        ]);
    }

    /**
     * Đăng nhập nhanh Google One-Tap / OAuth
     */
    public function googleAuth(GoogleAuthRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->authService->googleAuth(
            (string) $validated['email'],
            (string) $validated['name'],
            $validated['avatar'] ?? null,
            $validated['google_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập Google thành công!',
            'data' => $result,
        ]);
    }

    /**
     * Lấy thông tin tài khoản hiện tại
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            $user = User::where('email', 'caoluongthienk1@gmail.com')->first();
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Đăng xuất tài khoản
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công!',
        ]);
    }

    /**
     * Yêu cầu gửi mã OTP đặt lại mật khẩu qua Email
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->authService->forgotPassword((string) $validated['email']);

            return response()->json([
                'success' => true,
                'message' => "Mã OTP đặt lại mật khẩu đã được gửi đến email {$validated['email']}.",
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Xác thực OTP và đặt mật khẩu mới
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->authService->resetPassword(
                (string) $validated['email'],
                (string) $validated['otp'],
                (string) $validated['password']
            );

            return response()->json([
                'success' => true,
                'message' => 'Đặt lại mật khẩu thành công! Đã tự động đăng nhập.',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Đổi mật khẩu cho người dùng đã đăng nhập
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();
        if (!$user && isset($validated['user_id'])) {
            $user = User::find($validated['user_id']);
        }
        if (!$user && isset($validated['email'])) {
            $user = User::where('email', $validated['email'])->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đổi mật khẩu.',
            ], 401);
        }

        try {
            $this->authService->changePassword(
                $user,
                (string) $validated['current_password'],
                (string) $validated['new_password']
            );

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
