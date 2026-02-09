<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Properties extends Model
{
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
        'images'
    ];


    public function categories(): BelongsTo {
        return $this->belongsTo(Categories::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function propertyImages(){
        return $this->hasMany(PropertyImage::class);
    }

}
