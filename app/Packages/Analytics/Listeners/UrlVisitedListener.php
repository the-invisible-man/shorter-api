<?php

namespace App\Packages\Analytics\Listeners;

use App\Packages\Analytics\AnalyticsService;
use App\Packages\Url\Events\UrlVisited;

class UrlVisitedListener
{
    /**
     * @param AnalyticsService $analyticsService
     */
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    /**
     * @param UrlVisited $event
     *
     * @throws \RedisException
     */
    public function handle(UrlVisited $event): void
    {
        $this->analyticsService->increaseMemoryCount($event->url->short_url);
    }
}
