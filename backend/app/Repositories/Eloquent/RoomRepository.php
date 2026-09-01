<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use App\Repositories\Contracts\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RoomRepository implements RoomRepositoryInterface
{
    public function getAllRoomsWithCinemas(): Collection
    {
        return Cinema::with(['rooms.seats'])->orderBy('name', 'asc')->get();
    }

    public function findById(int $id, array $relations = ['cinema', 'seats']): ?Room
    {
        return Room::with($relations)->findOrFail($id);
    }

    public function updateSeatMatrix(int $roomId, int $totalRows, int $seatsPerRow, array $vipRows, array $coupleRows): Room
    {
        $room = Room::findOrFail($roomId);
        $rowLetters = range('A', 'Z');

        DB::transaction(function () use ($room, $totalRows, $seatsPerRow, $vipRows, $coupleRows, $rowLetters) {
            Seat::where('room_id', $room->id)->delete();

            $newSeats = [];
            for ($r = 0; $r < $totalRows; $r++) {
                $rowLetter = $rowLetters[$r];
                $isCouple = in_array($rowLetter, $coupleRows);
                $isVip = in_array($rowLetter, $vipRows);

                $numSeats = $isCouple ? (int) floor($seatsPerRow / 2) : $seatsPerRow;

                for ($n = 1; $n <= $numSeats; $n++) {
                    $type = 'standard';
                    if ($isCouple) {
                        $type = 'couple';
                    } elseif ($isVip) {
                        $type = 'vip';
                    }

                    $newSeats[] = [
                        'room_id' => $room->id,
                        'row' => $rowLetter,
                        'number' => $n,
                        'type' => $type,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            Seat::insert($newSeats);
            $room->total_seats = count($newSeats);
            $room->save();
        });

        return $room->fresh(['seats', 'cinema']);
    }
}
