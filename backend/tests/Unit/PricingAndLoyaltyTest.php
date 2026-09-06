<?php

namespace Tests\Unit;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingAndLoyaltyTest extends TestCase
{
    use RefreshDatabase;

    protected PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
    }

    public function test_dynamic_pricing_happy_wednesday_sets_discounted_prices(): void
    {
        // Mock a Wednesday showtime
        $wednesday = Carbon::parse('next Wednesday')->toDateString();

        $showtime = new Showtime([
            'show_date' => $wednesday,
            'start_time' => '19:00',
            'base_price' => 100000,
        ]);

        $pricing = $this->pricingService->calculateDynamicPricing($showtime);

        $this->assertTrue($pricing['is_happy_wednesday']);
        $this->assertEquals(55000.0, $pricing['price_standard']);
        $this->assertEquals(70000.0, $pricing['price_vip']);
        $this->assertEquals(130000.0, $pricing['price_couple']);
    }

    public function test_dynamic_pricing_calculates_seat_prices_correctly(): void
    {
        // Mock a regular Monday afternoon showtime
        $monday = Carbon::parse('next Monday')->toDateString();

        $showtime = new Showtime([
            'show_date' => $monday,
            'start_time' => '14:00',
            'base_price' => 90000,
        ]);

        $stdSeat = new Seat(['type' => 'standard']);
        $vipSeat = new Seat(['type' => 'vip']);
        $coupleSeat = new Seat(['type' => 'couple']);

        $this->assertEquals(90000.0, $this->pricingService->getSeatPrice($showtime, $stdSeat));
        $this->assertEquals(105000.0, $this->pricingService->getSeatPrice($showtime, $vipSeat));
        $this->assertEquals(180000.0, $this->pricingService->getSeatPrice($showtime, $coupleSeat));
    }

    public function test_user_loyalty_point_earning_and_tier_upgrade(): void
    {
        $user = User::create([
            'name' => 'Nguyen Loyalty Tester',
            'email' => 'loyaltytester@example.com',
            'password' => bcrypt('secret123'),
            'points' => 0,
            'membership_tier' => 'member',
            'total_spent' => 0,
            'total_tickets_bought' => 0,
        ]);

        $this->assertEquals(0.05, $user->getPointMultiplier());

        // 1. First Booking: Spent 600,000 VND (3 tickets) -> Should upgrade to VIP (spent >= 500k)
        $result = $user->processBookingLoyalty(600000, 3);

        $this->assertTrue($result['upgraded']);
        $this->assertEquals('vip', $user->membership_tier);
        $this->assertEquals(0.10, $user->getPointMultiplier()); // VIP multiplier 10%
        $this->assertEquals(30, $user->points); // 600 * 0.05 = 30 points

        // 2. Second Booking: Spent 1,500,000 VND (8 tickets) -> Total spent: 2.1M -> Should upgrade to Diamond (spent >= 2M)
        $result2 = $user->processBookingLoyalty(1500000, 8);

        $this->assertTrue($result2['upgraded']);
        $this->assertEquals('diamond', $user->membership_tier);
        $this->assertEquals(0.15, $user->getPointMultiplier()); // Diamond multiplier 15%
        $this->assertEquals(180, $user->points); // 30 + (1500 * 0.10) = 180 points
    }
}
