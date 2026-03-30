<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'features'
    ];

    protected $casts = [
        'image' => 'array',
        'features' => 'array',
    ];

    public function commentary(): HasMany
    {
        return $this->hasMany(Commentary::class)->lastest();
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
}
