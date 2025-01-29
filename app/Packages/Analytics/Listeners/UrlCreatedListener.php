<?php

namespace App\Packages\Analytics\Listeners;

use App\Packages\Analytics\AnalyticsService;
use App\Packages\Url\Events\UrlCreated;

class UrlCreatedListener
{
    /**
     * @param AnalyticsService $analyticsService
     */
    public function __construct(protected AnalyticsService $analyticsService)
    {

    }

    /**
     * Create the metric record for this URL to ensure that the user
     * see at least "0" if they pull analytics for a URL before it's
     * visited.
     *
     * @param UrlCreated $event
     */
    public function handle(UrlCreated $event): void
    {
        $this->analyticsService->increaseDbCount($event->url->short_url, 0);
    }
}
