<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class RoleRejectNotification extends Notification
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
            'property_id' => null,
            'name' => 'Lo sentimos',
            'message' => 'Tu solicitud ha sido rechazada',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'property_id' => null,
            'name' => 'Lo sentimos',
            'message' => 'Tu solicitud ha sido rechazada',
            'type' => 'role_approved'
        ]);
    }
}
