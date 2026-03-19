<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'address',
        'avatar',
        'phone',
        'whatsapp',
        'years_of_experience',
        'specialties',
        'social_links',
        'job_title',
        'license_number'
    ];

    protected $casts = [
        'specialties' => 'array',
        'social_links' => 'array',
        'years_of_experience' => 'integer',
    ];

    public function user(){
        return $this->belongsTo(User::class)->select('id','name','email');
    }
}
