<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Session\Events\SessionCreated;
use Illuminate\Session\Events\SessionUpdated;

class SessionUserProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen([SessionCreated::class, SessionUpdated::class], function ($event) {
            if (auth()->check()) {
                $event->session->put('user_id', auth()->id());
            }
        });
    }
}
