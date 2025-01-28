<?php

namespace Tests\Http\V1\Controllers;

use App\Packages\Analytics\AnalyticsService;
use Tests\TestCase;
use Mockery as m;

class AnalyticsServiceTest extends TestCase
{
    public function testIncreaseMemoryCount(): void
    {
        $path = 'if93o0w';

        $redisMock = m::spy(\Redis::class);

        $this->app->instance(\Redis::class, $redisMock);

        $service = $this->app->make(AnalyticsService::class);

        $service->increaseMemoryCount($path);

        $redisMock->shouldHaveReceived('incr')
            ->with("url:{$path}:access_count");
    }
}
