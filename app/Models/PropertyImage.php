<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    protected $fillable = [
        'url',
        'property_id'
    ];

    public function property(){
        return $this->belongsTo(Properties::class);
    }
}
