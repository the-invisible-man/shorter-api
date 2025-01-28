<?php

namespace Tests;

use App\Packages\Url\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * @param string $longUrl
     * @param string $shortUrl
     * @return Url
     */
    protected function createUrl(string $longUrl, string $shortUrl): Url
    {
        $url = new Url;

        $url->long_url = $longUrl;
        $url->short_url = $shortUrl;

        $url->save();

        return $url;
    }
}
