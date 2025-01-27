<?php

namespace Http\V1\Controllers;

use App\Packages\Url\Events\UrlCreated;
use Illuminate\Support\Facades\Event;
use Psr\SimpleCache\CacheInterface;
use Tests\TestCase;
use Mockery as m;

class UrlControllerTest extends TestCase
{
    public function testCreate(): void
    {
        Event::fake();

        $longUrl = "https://stackoverflow.com/questions/45794683/how-to-create-aliases-in-laravel";

        $cacheMock = m::mock(CacheInterface::class);

        $cacheMock->shouldReceive('set')
            ->once()
            ->with("short_url:100gMAe", [
                'id' => 1,
                'long_url' => $longUrl,
                'short_url' => '100gMAe',
            ], 60);

        $this->app->instance(CacheInterface::class, $cacheMock);

        $response = $this->call('POST', route('shorten::v1::urls.create'), [
            'long_url' => $longUrl,
        ]);

        Event::assertDispatched(UrlCreated::class);

        $response->assertJson([
            'data' => [
                'path' => '100gMAe',
                'long_url' => $longUrl,
                'short_url' => 'http://localhost/100gMAe',
            ]
        ]);

        self::assertDatabaseHas('urls', [
            'id' => 1,
            'long_url' => $longUrl,
            'short_url' => '100gMAe',
        ]);
    }
}
