<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Snack;
use Illuminate\Http\JsonResponse;

class SnackController extends Controller
{
    public function index(): JsonResponse
    {
        $snacks = Snack::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $snacks,
        ]);
    }
}
