<?php

namespace Tests\Http\V1\Controllers;

use Illuminate\Http\Request;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    public function testFind(): void
    {
        $metric = $this->createMetric('nd93m0s', 90);

        $response = $this->call(Request::METHOD_GET, route('analytics::v1::metric.get', [
            'path' => $metric->path,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $metric->id,
                'path' => $metric->path,
                'count' => $metric->count,
            ]
        ]);
    }

    public function testFindNotFound(): void
    {
        $response = $this->call(Request::METHOD_GET, route('analytics::v1::metric.get', [
            'path' => 'random',
        ]));

        $response->assertStatus(404);
    }
}
