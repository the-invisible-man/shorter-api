<?php

namespace App\Packages\Analytics\Repositories;

use App\Packages\Analytics\Models\UrlMetric;

class EloquentUrlMetricRepository implements UrlMetricRepository
{
    /**
     * @param int $urlId
     *
     * @return UrlMetric|null
     */
    public function findByUrlId(int $urlId): ?UrlMetric
    {
        return UrlMetric::where('url_id', $urlId)->first();
    }

    /**
     * @param string $path
     *
     * @return UrlMetric|null
     */
    public function findByPath(string $path): ?UrlMetric
    {
        return UrlMetric::where('path', $path)->first();
    }

    /**
     * @param string $path
     * @param int    $count
     *
     * @return UrlMetric
     */
    public function create(string $path, int $count = 0): UrlMetric
    {
        $metric = new UrlMetric;

        $metric->path = $path;
        $metric->count = $count;

        $metric->save();

        return $metric;
    }

    /**
     * @param UrlMetric $metric
     * @param int       $incrementBy
     */
    public function atomicIncrement(UrlMetric $metric, int $incrementBy): void
    {
        UrlMetric::query()
            ->where('id', $metric->id)
            ->increment('count', $incrementBy);
    }
}
