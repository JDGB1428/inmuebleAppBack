<?php

namespace App\Notifications;



use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPropertyNotification extends Notification
{
    use Queueable;
    public $property;

    public function __construct($property)
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
            'title' => '¡Nuevo inmueble disponible!',
            'address' => $this->property->address ?? 'Nueva dirección'
        ];
    }
}
