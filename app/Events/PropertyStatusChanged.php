<?php

namespace App\Events;

use App\Models\Properties;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyStatusChanged
{
    use Dispatchable, SerializesModels;

    public Properties $property;

    public function __construct(Properties $property)
    {
        $this->property = $property;
    }
}
