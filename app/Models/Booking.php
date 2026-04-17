<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['property_id', 'user_id', 'check_in', 'check_out', 'guests_count', 'total_price'];


    public function scopeOverlapping($query, $propertyId, $checkIn, $checkOut)
    {
        return $query->where('property_id', $propertyId)
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn);
    }

    public function property()
    {
        return $this->belongsTo(Properties::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
