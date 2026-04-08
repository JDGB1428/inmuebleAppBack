<?php

namespace App\Notifications;

use App\Models\Properties;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PropertyLikedNotification extends Notification
{
    use Queueable;

    public Properties $property;
    public User $user;


    public function __construct(Properties $property, User $user)
    {
        $this->property = $property;
        $this->user = $user;
    }


    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'property_id' => $this->property->id,
            'name' => '¡A ' . $this->user->name . ' le gusta tu inmueble!',
            'title' => $this->property->title,
            'direction' => $this->property->direction ?? 'Nueva interacción',
            'message' => 'Alguien ha guardado tu propiedad en sus favoritos.' // Opcional para tu Angular
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'property_id' => $this->property->id,
            'name' => '¡A ' . $this->user->name . ' le gusta tu inmueble!',
            'title' => $this->property->title,
            'direction' => $this->property->direction ?? 'Nueva interacción',
            'message' => 'Alguien ha guardado tu propiedad en sus favoritos.'
        ]);
    }
}
