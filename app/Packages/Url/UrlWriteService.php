<?php

namespace App\Packages\Url;

use App\Packages\Url\Events\UrlCreated;
use App\Packages\Url\Models\Url;
use App\Packages\Url\Repositories\UrlRepository;
use App\Packages\Url\Traits\CachesUrls;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;

class UrlWriteService
{
    use CachesUrls;

    /**
     * @param UrlRepository $repository
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     * @param Dispatcher $dispatcher
     * @param DatabaseManager $databaseManager
     */
    public function __construct(
        protected UrlRepository $repository,
        protected CacheInterface $cache,
        protected LoggerInterface $logger,
        protected Dispatcher $dispatcher,
        protected DatabaseManager $databaseManager,
    ) {

    }

    /**
     * @param string $longUrl
     * @param bool $shouldCache
     * @param bool $shouldDispatchEvent
     * @return Url
     * @throws \Throwable
     */
    public function create(string $longUrl, bool $shouldCache = true, bool $shouldDispatchEvent = true): Url
    {
        $url = $this->databaseManager->transaction(function () use ($longUrl) {
            $url = $this->repository->create($longUrl);

            $seed = $this->calcPathSeedValue($url->id);
            $path = $this->encodeBase62($seed);

            $this->repository->update($url, $path);

            return $url;
        });

        if ($shouldCache) {
            $this->cacheUrl($url);
        }

        if ($shouldDispatchEvent) {
            $this->dispatcher->dispatch(new UrlCreated($url));
        }

        return $url;
    }

    /**
     * @param int $id
     * @return int
     */
    protected function calcPathSeedValue(int $id): int
    {

    }

    /**
     * This method generates a deterministic set of
     * characters given an integer seed.
     *
     * @param int $seed
     * @return string
     */
    protected function encodeBase62(int $seed): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($characters);
        $result = '';

        while ($seed > 0) {
            $remainder = $seed % $base;
            $result = $characters[$remainder] . $result;
            $seed = intdiv($seed, $base);
        }

        return $result;
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
