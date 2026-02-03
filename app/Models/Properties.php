<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Properties extends Model
{
    protected $fillable = [
        'name',
        'title',
        'description',
        'price',
        'direction',
        'room',
        'area',
        'bathrooms',
        'state'
    ];
}
