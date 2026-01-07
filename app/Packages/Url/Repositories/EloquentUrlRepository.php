<?php

namespace App\Packages\Url\Repositories;

use App\Packages\Url\Models\Url;

class EloquentUrlRepository implements UrlRepository
{
    /**
     * @param string $shortUrl
     *
     * @return Url|null
     */
    public function findByShortUrl(string $shortUrl): ?Url
    {
        return Url::where('short_url', $shortUrl)->first();
    }

    /**
     * @param int $id
     *
     * @return Url|null
     */
    public function find(int $id): ?Url
    {
        return Url::find($id);
    }

    /**
     * @param string      $longUrl
     * @param string|null $shortUrl
     *
     * @return Url
     */
    public function create(string $longUrl, string $shortUrl = null): Url
    {
        $url = new Url;

        $url->long_url = $longUrl;
        $url->short_url = $shortUrl;

        $url->save();

        return $url;
    }

    /**
     * @param Url $url
     * @param string $shortUrl
     * @param bool $flagged
     *
     * @return void
     */
    public function update(Url $url, string $shortUrl, bool $flagged = false): void
    {
        $url->short_url = $shortUrl;
        $url->flagged = $flagged;

        $url->save();
    }

    /**
     * @param array $data
     *
     * @return Url
     */
    public function hydrateFromCache(array $data): Url
    {
        $url = new Url;

        foreach ($data as $key => $value) {
            $url->{$key} = $value;
        }

        $url->exists = true;

        return $url;
    }

}
