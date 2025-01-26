<?php

namespace App\Packages\Analytics\Commands;

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
     * @return void
     * @throws \RedisException
     */
    public function handle(): void
    {
        $keys = $this->getRedis()->keys('url:*:access_count');

        foreach ($keys as $key) {
            $urlId = explode(':', $key)[1];
            $count = $this->getRedis()->get($key);

            $this->getAnalyticsService()->increaseDbCount($urlId, $count);

            $this->getRedis()->del($key);
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
