<?php

namespace App\Notifications;

use App\Models\Properties;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPropertyNotification extends Notification
{
    use Queueable;
    public Properties $property;

    public function __construct(Properties $property)
    {
        $this->property = $property;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'property_id' => $this->property->id,
            'name' => '¡Nuevo inmueble disponible!',
            'title' => $this->property->title,
            'direction' => $this->property->direction ?? 'Nueva dirección'
        ];
    }
}
