<?php

namespace Http\V1\Controllers;

use App\Packages\Url\Events\UrlCreated;
use App\Packages\Url\Models\Url;
use Illuminate\Http\Response;
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
                'short_url' => 'http://localhost/r/100gMAe',
            ]
        ]);

        self::assertDatabaseHas('urls', [
            'id' => 1,
            'long_url' => $longUrl,
            'short_url' => '100gMAe',
        ]);
    }

    public function testRouteFound(): void
    {
        Event::fake();

        $this->createUrl('https://www.wemod.com/features', 'ndf8nq3');

        $response = $this->call('GET', route('v1::router::route', ['path' => 'ndf8nq3']));

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function testRouteNotFound(): void
    {
        Event::fake();

        $response = $this->call('GET', route('v1::router::route', ['path' => 'ndf8nq3']));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
