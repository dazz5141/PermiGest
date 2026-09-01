<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Notification;

abstract class PermiGestNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public int $tries = 4;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }
}
