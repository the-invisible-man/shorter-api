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
     * @param string $path
     * @param int $increment
     * @return void
     */
    public function increaseDbCount(string $path, int $increment): void
    {
        if (!($urlMetric = $this->repository->findByPath($path))) {
            $this->repository->create($path, $increment);
            return;
        }

        $this->repository->atomicIncrement($urlMetric, $increment);
    }

    /**
     * @param string $path
     *
     * @return void
     * @throws \RedisException
     */
    public function increaseMemoryCount(string $path): void
    {
        $key = $this->makeUrlKey($path);

        // Redis supports atomic counters which handles
        // race conditions (two people visiting the same URL
        // at the same time).
        $this->redis->incr($key);
    }

    /**
     * @param string $path
     * @return string
     */
    protected function makeUrlKey(string $path): string
    {
        return "url:{$path}:access_count";
    }
}
