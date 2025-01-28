<?php

namespace Tests\Http\V1\Controllers;

use App\Console\Commands\FlushUrlCount;
use Illuminate\Support\Facades\Artisan;
use Mockery as m;
use Tests\TestCase;

class FlushUrlCountTest extends TestCase
{
    public function testHandleExisting(): void
    {
        $metric = $this->createMetric('kfj930w', 5);

        $redisMock = m::mock(\Redis::class);

        $this->app->instance(\Redis::class, $redisMock);

        $redisMock->shouldReceive('keys')
            ->with('url:*:access_count')
            ->andReturn([
                'url:kfj930w:access_count',
            ]);

        $redisMock->shouldReceive('get')
            ->with('url:kfj930w:access_count')
            ->andReturn(49);

        $redisMock->shouldReceive('del')
            ->with('url:kfj930w:access_count');

        Artisan::call(FlushUrlCount::class);

        self::assertDatabaseHas('url_metrics', [
            'id' => $metric->id,
            'count' => 54,
        ]);
    }

    public function testHandleNew(): void
    {
        $redisMock = m::mock(\Redis::class);

        $this->app->instance(\Redis::class, $redisMock);

        $redisMock->shouldReceive('keys')
            ->with('url:*:access_count')
            ->andReturn([
                'url:kfj930w:access_count',
            ]);

        $redisMock->shouldReceive('get')
            ->with('url:kfj930w:access_count')
            ->andReturn(49);

        $redisMock->shouldReceive('del')
            ->with('url:kfj930w:access_count');

        Artisan::call(FlushUrlCount::class);

        self::assertDatabaseHas('url_metrics', [
            'path' => 'kfj930w',
            'count' => 49,
        ]);
    }
}
