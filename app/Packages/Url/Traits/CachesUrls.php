<?php

namespace App\Packages\Url\Traits;

use App\Packages\Url\Models\Url;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

trait CachesUrls
{
    /**
     * @return CacheInterface
     */
    abstract public function getCache(): CacheInterface;

    /**
     * @return LoggerInterface
     */
    abstract public function getLogger(): LoggerInterface;

    /**
     * @param string $path
     *
     * @return string
     */
    protected function makeCacheKey(string $path): string
    {
        return "short_url:{$path}";
    }

    /**
     * @param Url $url
     */
    protected function cacheUrl(Url $url): void
    {
        try {
            $this->getCache()->set($this->makeCacheKey($url->short_url), $url->toCache(), config('cache.url_cache_ttl'));
        } catch (\Exception $e) {
            $this->getLogger()->error('There was an error puting URL in cache', [
                'path' => $url->short_url,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        }
    }
}
