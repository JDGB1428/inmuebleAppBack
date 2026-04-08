<?php

namespace App\Events;

use App\Models\Properties;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyLiked
{
    use Dispatchable, SerializesModels;

    public Properties $property;
    public User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Properties $property, User $user)
    {
        $this->property = $property;
        $this->user = $user;
    }


}
