<?php

namespace App\Packages\Analytics\Repositories;

use App\Packages\Analytics\Models\UrlMetric;

class EloquentUrlMetricRepository implements UrlMetricRepository
{
    /**
     * @param int $urlId
     * @return UrlMetric|null
     */
    public function findByUrlId(int $urlId): ?UrlMetric
    {
        return UrlMetric::where('url_id', $urlId)->first();
    }

    /**
     * @param int $urlId
     * @param int $count
     * @return UrlMetric
     */
    public function create(int $urlId, int $count = 0): UrlMetric
    {
        $metric = new UrlMetric;

        $metric->url_id = $urlId;
        $metric->count = $count;

        $metric->save();

        return $metric;
    }

    /**
     * @param UrlMetric $metric
     * @param int $incrementBy
     * @return void
     */
    public function atomicIncrement(UrlMetric $metric, int $incrementBy): void
    {
        UrlMetric::query()
            ->where('id', $metric->id)
            ->increment('count', $incrementBy);
    }
}
