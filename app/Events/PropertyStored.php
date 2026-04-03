<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyStored
{
    use Dispatchable, SerializesModels;

    public $property;
    public $userId;

    public function __construct($property, $userId){
        $this->property = $property;
        $this->userId = $userId;
    }

}
