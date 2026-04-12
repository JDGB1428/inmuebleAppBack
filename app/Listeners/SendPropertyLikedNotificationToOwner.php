<?php

namespace App\Listeners;

use App\Events\PropertyLiked;
use App\Models\User;
use App\Notifications\PropertyLikedNotification;

class SendPropertyLikedNotificationToOwner
{

    public function handle(PropertyLiked $event): void
    {
        $owner = User::find($event->property->user_id);

        if ($owner && $owner->id !== $event->user->id) {
            $owner->notify(new PropertyLikedNotification($event->property, $event->user));
        }
    }
}
