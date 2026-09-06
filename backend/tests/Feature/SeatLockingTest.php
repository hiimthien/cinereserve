<?php

namespace Tests\Feature;

use App\Events\SeatStatusUpdated;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Services\SeatLockingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SeatLockingTest extends TestCase
{
    use RefreshDatabase;

    protected Showtime $showtime;
    protected Seat $seat1;
    protected Seat $seat2;
    protected SeatLockingService $seatLockingService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Event::fake([SeatStatusUpdated::class]);

        $this->seatLockingService = app(SeatLockingService::class);

        $movie = Movie::create([
            'title' => 'Avatar 3',
            'slug' => 'avatar-3',
            'duration' => 180,
            'release_date' => now()->subDays(10)->toDateString(),
            'description' => 'Sci-fi epic movie',
            'poster_url' => 'https://example.com/avatar3.jpg',
            'backdrop_url' => 'https://example.com/avatar3-bg.jpg',
            'age_rating' => 'T13',
            'status' => 'now_showing',
        ]);

        $cinema = Cinema::create([
            'name' => 'CineReserve Vincom',
            'address' => '72 Le Thanh Ton, Q1',
            'city' => 'TP. Hồ Chí Minh',
        ]);

        $room = Room::create([
            'cinema_id' => $cinema->id,
            'name' => 'Cinema Hall 01',
            'room_type' => 'IMAX Laser',
            'total_seats' => 20,
        ]);

        $this->seat1 = Seat::create([
            'room_id' => $room->id,
            'row' => 'E',
            'number' => 5,
            'type' => 'vip',
        ]);

        $this->seat2 = Seat::create([
            'room_id' => $room->id,
            'row' => 'E',
            'number' => 6,
            'type' => 'vip',
        ]);

        $this->showtime = Showtime::create([
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'room_id' => $room->id,
            'show_date' => now()->toDateString(),
            'start_time' => '19:00',
            'end_time' => '21:30',
            'base_price' => 100000,
        ]);
    }

    public function test_user_can_successfully_hold_available_seat(): void
    {
        $sessionId = 'session_user_a';

        $response = $this->postJson("/api/showtimes/{$this->showtime->id}/seats/{$this->seat1->id}/hold", [
            'session_id' => $sessionId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Giữ ghế thành công trong 10 phút.',
            ]);

        // Verify Redis key exists
        $lockKey = "cinereserve:showtime:{$this->showtime->id}:seat:{$this->seat1->id}:holder";
        $holdData = Cache::get($lockKey);

        $this->assertNotNull($holdData);
        $this->assertEquals($sessionId, $holdData['session_id']);

        // Verify event was broadcast
        Event::assertDispatched(SeatStatusUpdated::class, function ($event) use ($sessionId) {
            return $event->showtime_id === $this->showtime->id
                && $event->seat_id === $this->seat1->id
                && $event->status === 'holding'
                && $event->held_by === $sessionId;
        });
    }

    public function test_concurrent_user_is_prevented_from_holding_already_held_seat(): void
    {
        $sessionA = 'session_user_a';
        $sessionB = 'session_user_b';

        // User A holds seat first
        $this->seatLockingService->holdSeat($this->showtime->id, $this->seat1->id, $sessionA);

        // User B tries to hold the same seat -> Expect Conflict 422
        $response = $this->postJson("/api/showtimes/{$this->showtime->id}/seats/{$this->seat1->id}/hold", [
            'session_id' => $sessionB,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Ghế đang được giữ bởi người dùng khác.',
            ]);

        // Verify seat is still held by User A
        $lockKey = "cinereserve:showtime:{$this->showtime->id}:seat:{$this->seat1->id}:holder";
        $holdData = Cache::get($lockKey);
        $this->assertEquals($sessionA, $holdData['session_id']);
    }

    public function test_same_user_can_refresh_ttl_when_re_holding(): void
    {
        $sessionA = 'session_user_a';

        $this->seatLockingService->holdSeat($this->showtime->id, $this->seat1->id, $sessionA);

        $response = $this->postJson("/api/showtimes/{$this->showtime->id}/seats/{$this->seat1->id}/hold", [
            'session_id' => $sessionA,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Giữ ghế thành công trong 10 phút.',
            ]);
    }

    public function test_user_can_release_their_held_seat(): void
    {
        $sessionA = 'session_user_a';

        $this->seatLockingService->holdSeat($this->showtime->id, $this->seat1->id, $sessionA);

        $response = $this->postJson("/api/showtimes/{$this->showtime->id}/seats/{$this->seat1->id}/release", [
            'session_id' => $sessionA,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Đã giải phóng ghế.',
            ]);

        $lockKey = "cinereserve:showtime:{$this->showtime->id}:seat:{$this->seat1->id}:holder";
        $this->assertNull(Cache::get($lockKey));

        // Verify broadcast release
        Event::assertDispatched(SeatStatusUpdated::class, function ($event) {
            return $event->showtime_id === $this->showtime->id
                && $event->seat_id === $this->seat1->id
                && $event->status === 'available';
        });
    }

    public function test_user_b_cannot_release_seat_held_by_user_a(): void
    {
        $sessionA = 'session_user_a';
        $sessionB = 'session_user_b';

        $this->seatLockingService->holdSeat($this->showtime->id, $this->seat1->id, $sessionA);

        $response = $this->postJson("/api/showtimes/{$this->showtime->id}/seats/{$this->seat1->id}/release", [
            'session_id' => $sessionB,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Không thể giải phóng ghế.',
            ]);

        $lockKey = "cinereserve:showtime:{$this->showtime->id}:seat:{$this->seat1->id}:holder";
        $this->assertNotNull(Cache::get($lockKey));
    }
}
