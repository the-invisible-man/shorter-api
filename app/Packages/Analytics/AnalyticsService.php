<?php

namespace App\Packages\Analytics;

use App\Packages\Analytics\Repositories\UrlMetricRepository;
use Psr\Log\LoggerInterface;

class AnalyticsService
{
    /**
     * @param UrlMetricRepository $repository
     * @param LoggerInterface $logger
     * @param \Redis $redis
     */
    public function __construct(
        protected UrlMetricRepository $repository,
        protected LoggerInterface $logger,
        protected \Redis $redis
    ) {
    }

    /**
     * @param int $urlId
     * @param int $increment
     * @return void
     */
    public function increaseDbCount(int $urlId, int $increment): void
    {
        if ($urlMetric = $this->repository->findByUrlId($urlId)) {
            $this->repository->create($urlId, $increment);
            return;
        }

        $this->repository->atomicIncrement($urlMetric, $increment);
    }

    /**
     * @param int $urlId
     *
     * @return void
     * @throws \RedisException
     */
    public function increaseMemoryCount(int $urlId): void
    {
        $key = $this->makeUrlKey($urlId);

        $this->redis->incr($key);
    }

    /**
     * @param int $urlId
     * @return string
     */
    protected function makeUrlKey(int $urlId): string
    {
        return "url:{$urlId}:access_count";
    }
}
