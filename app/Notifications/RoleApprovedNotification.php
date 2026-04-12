<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class RoleApprovedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            // Como no hay una propiedad asociada, enviamos null o 0
            'property_id' => null,
            'name' => '¡Felicidades!',
            'title' => 'Rol actualizado a Propietario',
            'message' => 'Tu solicitud ha sido aprobada,
                        Ahora puedes publicar tus propios inmuebles.
                        Cierre sesion e inicie nuevamente para acceder a los nuevos cambios',
        ];
    }

    // Lo que se envía por WebSockets (Pusher/Reverb)
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'property_id' => null,
            'name' => '¡Felicidades!',
            'title' => 'Rol actualizado a Propietario',
            'message' => 'Tu solicitud ha sido aprobada,
                        Ahora puedes publicar tus propios inmuebles.
                        Cierre sesion e inicie nuevamente para acceder a los nuevos cambios',
            'type' => 'role_approved'
        ]);
    }
}
