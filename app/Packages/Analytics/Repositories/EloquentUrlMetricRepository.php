<?php

namespace App\Packages\Analytics\Repositories;

use App\Packages\Analytics\Models\UrlMetric;

class EloquentUrlMetricRepository implements UrlMetricRepository
{
    public function findByUrlId(int $urlId): ?UrlMetric
    {
        // TODO: Implement findByUrlId() method.
    }

    public function create(int $urlId, int $count = 0): UrlMetric
    {
        // TODO: Implement create() method.
    }

    public function atomicIncrement(UrlMetric $metric, int $increment): void
    {
        // TODO: Implement atomicIncrement() method.
    }

}
