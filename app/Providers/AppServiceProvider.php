<?php

namespace App\Providers;

use App\Events\PropertyLiked;
use App\Events\PropertyStatusChanged;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\PropertyStored;
use App\Listeners\NotifyUsersOfPropertyStatusChange;
use App\Listeners\SendPropertyLikedNotificationToOwner;
use App\Listeners\SendPropertyNotificationsToClients;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            PropertyStored::class,
            SendPropertyNotificationsToClients::class
        );

        // 2. Cuando CAMBIA DE ESTADO (Vendida, etc) -> Avisar a los que le dieron Like
        Event::listen(
            PropertyStatusChanged::class,
            NotifyUsersOfPropertyStatusChange::class
        );

        // 3. Cuando alguien le da LIKE -> Avisar al dueño (El que creamos hoy)
        Event::listen(
            PropertyLiked::class,
            SendPropertyLikedNotificationToOwner::class
        );
    }
}
