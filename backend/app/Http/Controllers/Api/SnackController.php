<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SnackResource;
use App\Services\SnackService;
use Illuminate\Http\JsonResponse;

class SnackController extends Controller
{
    public function __construct(
        protected SnackService $snackService
    ) {}

    public function index(): JsonResponse
    {
        $snacks = $this->snackService->getFilteredSnacks();

        return response()->json([
            'success' => true,
            'data' => SnackResource::collection($snacks),
        ]);
    }
}
