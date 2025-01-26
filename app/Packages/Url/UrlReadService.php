<?php

namespace App\Packages\Url;

use App\Packages\Url\Models\Url;
use App\Packages\Url\Repositories\UrlRepository;
use App\Packages\Url\Traits\CachesUrls;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class UrlReadService
{
    use CachesUrls;

    /**
     * @param UrlRepository $repository
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected UrlRepository $repository,
        protected CacheInterface $cache,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @param string $path
     *
     * @return Url|null
     * @throws InvalidArgumentException
     */
    public function findByShortUrl(string $path): ?Url
    {
        $key = $this->makeCacheKey($path);

        if ($url = $this->fetchFromCache($key, $path)) {
            return $url;
        }

        $url = $this->repository->findByShortUrl($path);

        if ($url) {
            $this->cacheUrl($url);
        }

        return $url;
    }

    /**
     * @param string $key
     * @param string $path
     *
     * @return Url|null
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function fetchFromCache(string $key, string $path): ?Url
    {
        try {
            if ($data = $this->cache->get($key)) {
                return $this->repository->hydrateFromCache($data);
            }
        } catch (\Exception $e) {
            $this->logger->error("There was an error fetching URL from cache", [
                'key' => $key,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return null;
        }

        // Using "debug" instead to avoid overwhelming the logs
        // during high traffic and a large number of urls.
        $this->logger->debug("Cache miss", [
            'path' => $path,
        ]);

        return null;
    }

    /**
     * @return CacheInterface
     */
    protected function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
