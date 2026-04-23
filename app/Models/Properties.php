<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Properties extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'direction',
        'room',
        'area_m2',
        'bathrooms',
        'state',
        'image',
        'city',
        'country',
        'features'
    ];

    protected $casts = [
        'image' => 'array',
        'features' => 'array',
    ];

    protected function totalPriceForTrip(): Attribute
    {
        return Attribute::make(
            get: function () {
                $checkIn = request('check_in');
                $checkOut = request('check_out');

                if ($checkIn && $checkOut) {
                    $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
                    return $nights * $this->price;
                }

                return null;
            }
        );
    }


    protected function totalNights(): Attribute
    {
        return Attribute::make(
            get: function () {
                $checkIn = request('check_in');
                $checkOut = request('check_out');

                if ($checkIn && $checkOut) {
                    return Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
                }

                return null;
            }
        );
    }

    public function calculateTotalPrice($checkIn, $checkOut)
    {
        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
        return $nights * $this->price;
    }

    public function scopeAvailable($query)
    {
        return $query->where('state', 'available');
    }

    public function scopeFilterByCity($query, $city)
    {
        if ($city) {
            $query->where('city', 'LIKE', "%{$city}%");
        }
    }

    public function scopeFilterByCapacity($query, $adults, $children)
    {
        $totalGuests = ($adults ?? 1) + ($children ?? 0);

        if ($totalGuests > 1) {
            $query->whereRaw('(room * 2) >= ?', [$totalGuests]);
        }
    }

    public function scopeAvailableForDates($query, $checkIn, $checkOut)
    {
        if ($checkIn && $checkOut) {
            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in', '<', $checkOut)
                  ->where('check_out', '>', $checkIn);
            });
        }
    }


    public function commentary(): HasMany
    {
        return $this->hasMany(Commentary::class, 'property_id')->latest();
    }


    public function categories(): BelongsTo
    {
        return $this->belongsTo(Categories::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function propertyImages()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes', 'property_id', 'user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'property_id');
    }
}
