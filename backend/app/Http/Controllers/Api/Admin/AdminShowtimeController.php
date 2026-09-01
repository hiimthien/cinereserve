<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminShowtimeRequest;
use App\Http\Resources\ShowtimeResource;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminShowtimeController extends Controller
{
    /**
     * Danh sách suất chiếu trong quản trị kèm Phân Trang & Bộ Lọc
     */
    public function index(Request $request): JsonResponse
    {
        $query = Showtime::with(['movie', 'cinema', 'room']);

        if ($request->filled('date')) {
            $query->whereDate('show_date', $request->input('date'));
        }

        if ($request->filled('cinema_id') && $request->input('cinema_id') !== 'all') {
            $query->where('cinema_id', (int) $request->input('cinema_id'));
        }

        if ($request->filled('movie_id') && $request->input('movie_id') !== 'all') {
            $query->where('movie_id', (int) $request->input('movie_id'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 100);
        $showtimes = $query->orderBy('show_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ShowtimeResource::collection($showtimes->items()),
            'pagination' => [
                'current_page' => $showtimes->currentPage(),
                'last_page' => $showtimes->lastPage(),
                'per_page' => $showtimes->perPage(),
                'total' => $showtimes->total(),
            ],
            'meta' => [
                'current_page' => $showtimes->currentPage(),
                'last_page' => $showtimes->lastPage(),
                'per_page' => $showtimes->perPage(),
                'total' => $showtimes->total(),
            ],
        ]);
    }

    /**
     * Tạo 1 suất chiếu đơn lẻ
     */
    public function store(AdminShowtimeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $room = Room::findOrFail($validated['room_id']);

        $showtime = Showtime::create([
            'movie_id' => $validated['movie_id'],
            'cinema_id' => $room->cinema_id,
            'room_id' => $room->id,
            'show_date' => $validated['show_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? date('H:i', strtotime($validated['start_time'] . ' + 120 minutes')),
            'base_price' => (float) $validated['base_price'],
            'price_vip' => isset($validated['price_vip']) ? (float)$validated['price_vip'] : ((float)$validated['base_price'] + 15000),
            'price_couple' => isset($validated['price_couple']) ? (float)$validated['price_couple'] : ((float)$validated['base_price'] * 2),
            'format' => $validated['format'],
            'status' => $validated['status'] ?? 'scheduled',
        ]);


        $showtime->load(['movie', 'cinema', 'room']);

        return response()->json([
            'success' => true,
            'message' => "Tạo suất chiếu {$showtime->start_time} ngày {$showtime->show_date} thành công!",
            'data' => new ShowtimeResource($showtime),
        ], 201);
    }

    /**
     * Tạo suất chiếu hàng loạt (Batch Generate cho nhiều rạp, nhiều ngày, nhiều khung giờ)
     */
    public function batchStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'cinema_ids' => 'required|array|min:1',
            'cinema_ids.*' => 'exists:cinemas,id',
            'start_date' => 'required|date',
            'days_count' => 'required|integer|min:1|max:30',
            'time_slots' => 'required|array|min:1',
            'time_slots.*' => 'string', // ['09:30', '13:45', '17:15', '20:30']
            'base_price' => 'required|numeric|min:45000',
            'price_vip' => 'nullable|numeric|min:45000',
            'price_couple' => 'nullable|numeric|min:45000',
            'format' => 'required|string',
            'status' => 'nullable|string|in:scheduled,early_premiere',
        ]);

        $movie = Movie::findOrFail($validated['movie_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $daysCount = (int) $validated['days_count'];
        $timeSlots = (array) $validated['time_slots'];
        $basePrice = (float) $validated['base_price'];
        $priceVip = isset($validated['price_vip']) ? (float)$validated['price_vip'] : ($basePrice + 15000);
        $priceCouple = isset($validated['price_couple']) ? (float)$validated['price_couple'] : ($basePrice * 2);
        $format = (string) $validated['format'];
        $status = (string) ($validated['status'] ?? 'scheduled');

        $cinemas = Cinema::with('rooms')->whereIn('id', $validated['cinema_ids'])->get();
        $createdCount = 0;

        DB::transaction(function () use ($cinemas, $movie, $startDate, $daysCount, $timeSlots, $basePrice, $priceVip, $priceCouple, $format, $status, &$createdCount) {
            for ($d = 0; $d < $daysCount; $d++) {
                $showDate = $startDate->copy()->addDays($d)->toDateString();

                foreach ($cinemas as $cinema) {
                    $room = $cinema->rooms->first();
                    if (!$room) continue;

                    foreach ($timeSlots as $slotTime) {
                        Showtime::updateOrCreate(
                            [
                                'movie_id' => $movie->id,
                                'cinema_id' => $cinema->id,
                                'room_id' => $room->id,
                                'show_date' => $showDate,
                                'start_time' => $slotTime,
                            ],
                            [
                                'end_time' => date('H:i', strtotime($slotTime . ' + 120 minutes')),
                                'base_price' => $basePrice,
                                'price_vip' => $priceVip,
                                'price_couple' => $priceCouple,
                                'format' => $format,
                                'status' => $status,
                            ]
                        );
                        $createdCount++;
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Đã tạo thành công {$createdCount} suất chiếu cho phim [{$movie->title}] trên " . count($validated['cinema_ids']) . " cụm rạp!",
        ], 201);
    }

    /**
     * Cập nhật thông tin 1 suất chiếu
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $showtime = Showtime::findOrFail($id);

        $validated = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'show_date' => 'sometimes|date',
            'start_time' => 'sometimes|string',
            'end_time' => 'nullable|string',
            'base_price' => 'sometimes|numeric|min:45000',
            'price_vip' => 'nullable|numeric|min:45000',
            'price_couple' => 'nullable|numeric|min:45000',
            'format' => 'sometimes|string',
            'status' => 'sometimes|string|in:scheduled,early_premiere,cancelled',
        ]);


        if (isset($validated['room_id'])) {
            $room = Room::findOrFail($validated['room_id']);
            $validated['cinema_id'] = $room->cinema_id;
        }

        $showtime->update($validated);
        $showtime->load(['movie', 'cinema', 'room']);

        return response()->json([
            'success' => true,
            'message' => "Cập nhật suất chiếu thành công!",
            'data' => new ShowtimeResource($showtime),
        ]);
    }

    /**
     * Xóa suất chiếu
     */
    public function destroy(int $id): JsonResponse
    {
        $showtime = Showtime::findOrFail($id);
        $showtime->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa suất chiếu thành công.',
        ]);
    }
}
