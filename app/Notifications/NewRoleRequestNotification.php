<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewRoleRequestNotification extends Notification
{
    use Queueable;

    public User $user; // 💡 Lo cambiamos a tipo User

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {

        return ['database', 'broadcast'];
    }

    /**
     * Datos que se guardan en la base de datos
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Nueva Solicitud de Propietario',
            'name' => $this->user->name,
            'message' => $this->user->name . ' ha enviado una solicitud para ser propietario.',
        ];
    }

    /**
     * Datos que se mandan en tiempo real a Angular
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Nueva Solicitud de Propietario',
            'name' => $this->user->name,
            'message' => $this->user->name . ' ha enviado una solicitud para ser propietario.',
        ]);
    }
}
