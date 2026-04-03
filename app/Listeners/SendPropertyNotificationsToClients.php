<?php

namespace App\Listeners;

use app\Events\PropertyStored;
use App\Models\User;
use App\Notifications\NewPropertyNotification;
use Illuminate\Support\Facades\Notification;

class SendPropertyNotificationsToClients
{
    public function handle(PropertyStored $event)
    {
        $users = User::role('client')
            ->where('id', '!=', $event->userId)
            ->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new NewPropertyNotification($event->property));
        }
    }

}
