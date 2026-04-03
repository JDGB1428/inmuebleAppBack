<?php

namespace App\Notifications;

use App\Models\Properties;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PropertyStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Properties $property;

    public function __construct(Properties $property)
    {
        $this->property = $property;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        $estado = $this->property->state === 'rented' ? 'ya ha sido rentado' : 'vuelve a estar disponible';

        return [
            'property_id' => $this->property->id,
            'title'       => $this->property->title,
            'message'     => "El inmueble que te gusta, {$this->property->title}, {$estado}.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
