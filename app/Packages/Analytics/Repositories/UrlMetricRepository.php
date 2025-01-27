<?php

namespace App\Packages\Analytics\Repositories;

use App\Packages\Analytics\Models\UrlMetric;

interface UrlMetricRepository
{
    /**
     * @param string $path
     * @return UrlMetric|null
     */
    public function findByPath(string $path): ?UrlMetric;

    /**
     * @param string $path
     * @param int $count
     * @return UrlMetric
     */
    public function create(string $path, int $count = 0): UrlMetric;

    /**
     * @param UrlMetric $metric
     * @param int $incrementBy
     * @return void
     */
    public function atomicIncrement(UrlMetric $metric, int $incrementBy): void;
}
