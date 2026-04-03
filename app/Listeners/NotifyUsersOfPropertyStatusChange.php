<?php

namespace App\Listeners;

use App\Events\PropertyStatusChanged;
use App\Notifications\PropertyStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;

class NotifyUsersOfPropertyStatusChange
{
    public function handle(PropertyStatusChanged $event): void
    {
        $users = $event->property->likes()->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new PropertyStatusUpdatedNotification($event->property));
        }
    }
}
