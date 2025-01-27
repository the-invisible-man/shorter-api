<?php

namespace App\Packages\Url;

use App\Packages\Url\Events\UrlCreated;
use App\Packages\Url\Models\Url;
use App\Packages\Url\Repositories\UrlRepository;
use App\Packages\Url\Traits\CachesUrls;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Illuminate\Contracts\Events\Dispatcher;
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
            $path = $this->toBase62($seed);

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
        // We need to start from at least this number to generate
        // base62 values that will be 7 characters in length.
        // The real number is 56,800,235,584, but that gives
        // us a base62 value of "1000000" which looks kinda
        // weird (esthetically). So for demo purposes, we're
        // starting even higher, so we can get something more
        // like a "100gMAd" type of url path.
        //
        // Another very important caveat about this operation is
        // that to reach the highest possible base62 value, which
        // will be "ZZZZZZZ", we need to run our application in
        // a 64-bit operating system to avoid an integer overflow.
        return 56804235589 + $id;
    }

    /**
     * This method generates a deterministic set of
     * characters given an integer seed.
     *
     * @param int $seed
     * @return string
     */
    protected function toBase62(int $seed): string
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
