<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\PropertyStored;
use App\Listeners\NotifyUsersOfPropertyStatusChange;

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
            NotifyUsersOfPropertyStatusChange::class
        );
    }
}
