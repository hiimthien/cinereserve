<?php

namespace Tests\Feature;

use App\Events\SeatStatusUpdated;
use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Voucher;
use App\Services\SeatLockingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BookingCheckoutTest extends TestCase
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
            'title' => 'Dune: Part Two',
            'slug' => 'dune-part-two',
            'duration' => 166,
            'release_date' => now()->subDays(5)->toDateString(),
            'description' => 'Epic sci-fi masterpiece',
            'poster_url' => 'https://example.com/dune2.jpg',
            'backdrop_url' => 'https://example.com/dune2-bg.jpg',
            'age_rating' => 'T16',
            'status' => 'now_showing',
        ]);

        $cinema = Cinema::create([
            'name' => 'CineReserve Landmark 81',
            'address' => '720A Dien Bien Phu, Binh Thanh',
            'city' => 'TP. Hồ Chí Minh',
        ]);

        $room = Room::create([
            'cinema_id' => $cinema->id,
            'name' => 'IMAX Laser Hall',
            'room_type' => 'IMAX Laser',
            'total_seats' => 50,
        ]);

        $this->seat1 = Seat::create([
            'room_id' => $room->id,
            'row' => 'F',
            'number' => 8,
            'type' => 'standard',
        ]);

        $this->seat2 = Seat::create([
            'room_id' => $room->id,
            'row' => 'F',
            'number' => 9,
            'type' => 'vip',
        ]);

        $this->showtime = Showtime::create([
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'room_id' => $room->id,
            'show_date' => now()->toDateString(),
            'start_time' => '20:00',
            'end_time' => '22:46',
            'base_price' => 110000,
        ]);
    }

    public function test_user_can_successfully_checkout_held_seats(): void
    {
        $sessionId = 'session_checkout_user';

        // 1. Hold seats first
        $this->seatLockingService->holdSeat($this->showtime->id, $this->seat1->id, $sessionId);

        // 2. Perform checkout
        $response = $this->postJson('/api/bookings/checkout', [
            'showtime_id' => $this->showtime->id,
            'seat_ids' => [$this->seat1->id],
            'session_id' => $sessionId,
            'user_name' => 'Nguyen Van A',
            'user_email' => 'nguyenvana@gmail.com',
            'user_phone' => '0901234567',
            'payment_method' => 'vnpay',
            'combos' => [
                [
                    'id' => 1,
                    'name' => 'Combo Bắp Nước Sweet',
                    'price' => 75000,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Đặt vé và thanh toán thành công!',
            ]);

        // 3. Verify Database records
        $this->assertDatabaseHas('bookings', [
            'showtime_id' => $this->showtime->id,
            'user_email' => 'nguyenvana@gmail.com',
            'status' => 'confirmed',
        ]);

        $booking = Booking::where('user_email', 'nguyenvana@gmail.com')->first();
        $this->assertNotNull($booking);
        $this->assertDatabaseHas('booking_seats', [
            'booking_id' => $booking->id,
            'seat_id' => $this->seat1->id,
        ]);

        // 4. Verify Redis Lock is released
        $lockKey = "cinereserve:showtime:{$this->showtime->id}:seat:{$this->seat1->id}:holder";
        $this->assertNull(Cache::get($lockKey));
    }

    public function test_checkout_fails_if_seat_is_already_booked_by_someone_else(): void
    {
        $sessionA = 'session_user_a';
        $sessionB = 'session_user_b';

        // User A books the seat first
        $this->seatLockingService->holdSeat($this->showtime->id, $this->seat1->id, $sessionA);
        $this->postJson('/api/bookings/checkout', [
            'showtime_id' => $this->showtime->id,
            'seat_ids' => [$this->seat1->id],
            'session_id' => $sessionA,
            'user_name' => 'User A',
            'user_email' => 'usera@example.com',
            'user_phone' => '0911111111',
            'payment_method' => 'cash',
        ]);

        // User B tries to checkout the same seat
        $response = $this->postJson('/api/bookings/checkout', [
            'showtime_id' => $this->showtime->id,
            'seat_ids' => [$this->seat1->id],
            'session_id' => $sessionB,
            'user_name' => 'User B',
            'user_email' => 'userb@example.com',
            'user_phone' => '0922222222',
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_checkout_applies_voucher_discount_and_increments_used_count(): void
    {
        $sessionId = 'session_voucher_user';

        $voucher = Voucher::create([
            'code' => 'CINERESERVE20K',
            'title' => 'Giảm 20.000đ cho mọi đơn vé',
            'discount_type' => 'fixed',
            'discount_value' => 20000,
            'min_spend' => 50000,
            'used_count' => 0,
            'usage_limit' => 100,
            'expires_at' => now()->addDays(30),
        ]);

        $this->seatLockingService->holdSeat($this->showtime->id, $this->seat1->id, $sessionId);

        $response = $this->postJson('/api/bookings/checkout', [
            'showtime_id' => $this->showtime->id,
            'seat_ids' => [$this->seat1->id],
            'session_id' => $sessionId,
            'user_name' => 'Voucher User',
            'user_email' => 'voucheruser@example.com',
            'user_phone' => '0933333333',
            'payment_method' => 'vnpay',
            'voucher_code' => 'CINERESERVE20K',
            'discount_amount' => 20000,
        ]);

        $response->assertStatus(200);

        // Verify voucher count incremented
        $this->assertEquals(1, $voucher->fresh()->used_count);

        // Verify booking recorded discount
        $this->assertDatabaseHas('bookings', [
            'user_email' => 'voucheruser@example.com',
            'voucher_code' => 'CINERESERVE20K',
            'discount_amount' => 20000,
        ]);
    }
}
