<?php

namespace App\Listeners;

use App\Events\LinkHitEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClearStatisticKeyFromCache
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LinkHitEvent $event): void
    {
        $slug = $event->slug;
        Cache::forget(":link-statistics-{$slug}");
    }
}
