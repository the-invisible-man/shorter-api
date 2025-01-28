<?php

namespace App\Packages\Analytics;

use App\Packages\Analytics\Repositories\EloquentUrlMetricRepository;
use App\Packages\Analytics\Repositories\UrlMetricRepository;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(EloquentUrlMetricRepository::class, UrlMetricRepository::class);
    }
}
