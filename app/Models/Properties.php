<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Properties extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price',
        'direction',
        'room',
        'area_m2',
        'bathrooms',
        'state'
    ];

}
