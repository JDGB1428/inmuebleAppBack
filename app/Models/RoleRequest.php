<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleRequest extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'status'
    ];

    public function user():BelongsTo{
        return $this->belongsTo(User::class)->select('id','name','email');
    }
}
