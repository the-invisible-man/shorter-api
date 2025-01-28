<?php

namespace App\Console\Commands;

use App\Packages\Analytics\AnalyticsService;
use Illuminate\Console\Command;

class FlushUrlCount extends Command
{
    /**
     * @var string
     */
    protected $signature = 'analytics:flush-url-count';

    /**
     * @var string
     */
    protected $description = 'Sync Redis URL visit counter to the DB.';

    /**
     * @throws \RedisException
     */
    public function handle(): void
    {
        // Fetch all access_count keys. This will return the
        // current counts for all URLs.
        $keys = $this->getRedis()->keys('url:*:access_count');

        foreach ($keys as $key) {
            $path = explode(':', $key)[1];
            $count = (int)$this->getRedis()->get($key);

            $this->getAnalyticsService()->increaseDbCount($path, $count);

            $this->getRedis()->del($key);

            $this->getRedis()->set('h', 4);
        }
    }

    /**
     * @return \Redis
     */
    protected function getRedis(): \Redis
    {
        return app(\Redis::class);
    }

    /**
     * @return AnalyticsService
     */
    protected function getAnalyticsService(): AnalyticsService
    {
        return app(AnalyticsService::class);
    }
}
