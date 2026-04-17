<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Properties;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $property = Properties::where('id', $data['property_id'])
                                ->lockForUpdate()
                                ->firstOrFail();

             $maxCapacity = $property->room * 2;

            if (isset($data['guests_count']) && $data['guests_count'] > $maxCapacity) {
                throw ValidationException::withMessages([
                    'guests_count' => "El inmueble solo permite un máximo de {$maxCapacity} huéspedes."
                ]);
            }

            $isOverlapping = Booking::overlapping(
                $data['property_id'],
                $data['check_in'],
                $data['check_out']
            )->exists();

            if ($isOverlapping) {
                throw ValidationException::withMessages([
                    'fechas' => 'El inmueble ya ha sido reservado para estas fechas por otro usuario.'
                ]);
            }

            $data['total_price'] = $property->calculateTotalPrice($data['check_in'], $data['check_out']);
            return Booking::create($data);
        });

    }
}
