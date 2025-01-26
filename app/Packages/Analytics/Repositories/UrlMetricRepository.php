<?php

namespace App\Packages\Analytics\Repositories;

use App\Packages\Analytics\Models\UrlMetric;

interface UrlMetricRepository
{
    public function findByUrlId(int $urlId): ?UrlMetric;

    public function create(int $urlId, int $count = 0): UrlMetric;

    public function atomicIncrement(UrlMetric $metric, int $increment);
}
