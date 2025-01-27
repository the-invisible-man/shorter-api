<?php

namespace App\Packages\Analytics\Repositories;

use App\Packages\Analytics\Models\UrlMetric;

interface UrlMetricRepository
{
    /**
     * @param int $urlId
     * @return UrlMetric|null
     */
    public function findByUrlId(int $urlId): ?UrlMetric;

    /**
     * @param int $urlId
     * @param int $count
     * @return UrlMetric
     */
    public function create(int $urlId, int $count = 0): UrlMetric;

    /**
     * @param UrlMetric $metric
     * @param int $incrementBy
     * @return void
     */
    public function atomicIncrement(UrlMetric $metric, int $incrementBy): void;
}
