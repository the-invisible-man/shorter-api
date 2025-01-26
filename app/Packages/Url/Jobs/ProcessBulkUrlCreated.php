<?php

namespace App\Packages\Url\Jobs;

use App\Packages\Url\Events\UrlCreated;
use App\Packages\Url\Repositories\UrlRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessBulkUrlCreated implements ShouldQueue
{
    /**
     * @param array $urlIds
     */
    public function __construct(protected array $urlIds)
    {
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->urlIds as $urlId) {
            $url = $this->getRepository()->find($urlId);

            if ($url) {
                $this->getEventDispatcher()->dispatch(new UrlCreated($url));
            }
        }
    }

    /**
     * @return UrlRepository
     */
    protected function getRepository(): UrlRepository
    {
        return app(UrlRepository::class);
    }

    /**
     * @return Dispatcher
     */
    protected function getEventDispatcher(): Dispatcher
    {
        return app(Dispatcher::class);
    }
}
