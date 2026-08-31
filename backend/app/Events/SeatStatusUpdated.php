<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $showtime_id,
        public int $seat_id,
        public string $status, // available, holding, booked
        public ?string $held_by = null,
        public ?int $held_until = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("showtime.{$this->showtime_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SeatStatusUpdated';
    }
}
