<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use App\Services\VNPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentWebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected Booking $booking;
    protected User $user;
    protected string $hashSecret;
    protected string $tmnCode;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->hashSecret = config('services.vnpay.hash_secret', 'RAKUGNOKPUWETKHYDRAKUGNOKPUWETKH');
        $this->tmnCode = config('services.vnpay.tmn_code', 'CINERESERVE');

        $this->user = User::create([
            'name' => 'Tran Van C',
            'email' => 'tranvanc@example.com',
            'password' => bcrypt('password123'),
            'loyalty_points' => 100,
            'membership_tier' => 'member',
        ]);

        $movie = Movie::create([
            'title' => 'Oppenheimer',
            'slug' => 'oppenheimer',
            'duration' => 180,
            'release_date' => now()->subMonths(1)->toDateString(),
            'description' => 'Historical drama',
            'poster_url' => 'https://example.com/oppenheimer.jpg',
            'backdrop_url' => 'https://example.com/oppenheimer-bg.jpg',
            'age_rating' => 'T18',
            'status' => 'now_showing',
        ]);

        $cinema = Cinema::create([
            'name' => 'CineReserve Hanoi',
            'address' => '54 Lieu Giai, Ba Dinh',
            'city' => 'Hà Nội',
        ]);

        $room = Room::create([
            'cinema_id' => $cinema->id,
            'name' => 'Screen 1',
            'room_type' => '2D',
            'total_seats' => 30,
        ]);

        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'cinema_id' => $cinema->id,
            'room_id' => $room->id,
            'show_date' => now()->toDateString(),
            'start_time' => '18:00',
            'end_time' => '21:00',
            'base_price' => 100000,
        ]);

        $this->booking = Booking::create([
            'booking_code' => 'CR-TESTPAY1',
            'showtime_id' => $showtime->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'user_phone' => '0987654321',
            'total_amount' => 200000,
            'status' => 'pending',
            'qr_code' => 'https://example.com/qr/CR-TESTPAY1',
        ]);
    }

    private function generateSignedIpnPayload(array $params): array
    {
        ksort($params);
        $hashData = '';
        $i = 0;
        foreach ($params as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashData .= urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
        }

        $params['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $this->hashSecret);
        return $params;
    }

    public function test_vnpay_ipn_processes_pending_order_and_confirms_successfully(): void
    {
        $payload = $this->generateSignedIpnPayload([
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_TxnRef' => $this->booking->booking_code,
            'vnp_Amount' => 200000 * 100,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionNo' => '14589234',
            'vnp_BankCode' => 'NCB',
            'vnp_PayDate' => now()->format('YmdHis'),
        ]);

        $response = $this->getJson('/api/payment/vnpay/ipn?' . http_build_query($payload));

        $response->assertStatus(200)
            ->assertJson([
                'RspCode' => '00',
                'Message' => 'Confirm Success',
            ]);

        // Verify booking status is now confirmed and transaction recorded
        $this->assertEquals('confirmed', $this->booking->fresh()->status);
        $this->assertEquals('14589234', $this->booking->fresh()->vnp_transaction_no);
        $this->assertEquals('NCB', $this->booking->fresh()->vnp_bank_code);
    }

    public function test_vnpay_ipn_is_idempotent_when_called_multiple_times(): void
    {
        $payload = $this->generateSignedIpnPayload([
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_TxnRef' => $this->booking->booking_code,
            'vnp_Amount' => 200000 * 100,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionNo' => '14589234',
            'vnp_BankCode' => 'NCB',
            'vnp_PayDate' => now()->format('YmdHis'),
        ]);

        // Call 1st time
        $response1 = $this->getJson('/api/payment/vnpay/ipn?' . http_build_query($payload));
        $response1->assertJson(['RspCode' => '00', 'Message' => 'Confirm Success']);
        $this->assertEquals('confirmed', $this->booking->fresh()->status);

        // Call 2nd time (duplicate callback retry from gateway)
        $response2 = $this->getJson('/api/payment/vnpay/ipn?' . http_build_query($payload));
        $response2->assertStatus(200)
            ->assertJson([
                'RspCode' => '02',
                'Message' => 'Order already confirmed',
            ]);
    }
}
