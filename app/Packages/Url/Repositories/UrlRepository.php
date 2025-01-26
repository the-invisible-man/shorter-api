<?php

namespace App\Packages\Url\Repositories;

use App\Packages\Url\Models\Url;

interface UrlRepository
{
    /**
     * @param string $shortUrl
     *
     * @return Url|null
     */
    public function findByShortUrl(string $shortUrl): ?Url;

    /**
     * @param int $id
     * @return Url|null
     */
    public function find(int $id): ?Url;

    /**
     * @param string $longUrl
     *
     * @return Url
     */
    public function create(string $longUrl): Url;

    /**
     * @param Url $url
     * @param string $shortUrl
     *
     * @return void
     */
    public function update(Url $url, string $shortUrl): void;

    /**
     * @param array $data
     *
     * @return Url
     */
    public function hydrateFromCache(array $data): Url;
}
