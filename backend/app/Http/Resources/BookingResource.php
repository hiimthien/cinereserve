<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showtime = $this->showtime;
        $movie = $showtime?->movie;
        $cinema = $showtime?->cinema;
        $room = $showtime?->room;

        $seatsList = [];
        if ($this->relationLoaded('bookingSeats') && $this->bookingSeats) {
            foreach ($this->bookingSeats as $bs) {
                if ($bs->seat) {
                    $seatsList[] = [
                        'id' => $bs->seat->id,
                        'row' => $bs->seat->row,
                        'number' => $bs->seat->number,
                        'type' => $bs->seat->type,
                        'price' => (float) $bs->price,
                    ];
                }
            }
        }

        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'showtime_id' => $this->showtime_id,
            'total_amount' => (float) $this->total_amount,
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'voucher_code' => $this->voucher_code,
            'combos' => $this->combos ?? [],
            'status' => $this->status,
            'checked_in_at' => $this->checked_in_at ? (string) $this->checked_in_at : null,
            'qr_code' => $this->qr_code ?? "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=CINERESERVE-{$this->booking_code}",
            'created_at' => $this->created_at?->toIso8601String(),
            'movie' => $movie ? [
                'id' => $movie->id,
                'title' => $movie->title,
                'poster_url' => $movie->poster_url,
                'backdrop_url' => $movie->backdrop_url,
                'duration' => $movie->duration,
                'genre' => $movie->genre,
                'rating' => (float) $movie->rating,
            ] : null,
            'showtime' => $showtime ? [
                'id' => $showtime->id,
                'show_date' => $showtime->show_date ? (string) $showtime->show_date : null,
                'start_time' => $showtime->start_time,
                'end_time' => $showtime->end_time,
                'cinema' => $cinema ? [
                    'id' => $cinema->id,
                    'name' => $cinema->name,
                    'address' => $cinema->address,
                    'city' => $cinema->city,
                ] : null,
                'room' => $room ? [
                    'id' => $room->id,
                    'name' => $room->name,
                    'room_type' => $room->room_type,
                ] : null,
            ] : null,
            'seats' => $seatsList,
        ];
    }
}
