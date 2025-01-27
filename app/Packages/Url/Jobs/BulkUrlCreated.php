<?php

namespace App\Packages\Url\Jobs;

use App\Packages\Url\Events\UrlCreated;
use App\Packages\Url\Repositories\UrlRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

class BulkUrlCreated implements ShouldQueue
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
        foreach ($this->getUrlIds() as $urlId) {
            $url = $this->getRepository()->find($urlId);

            if ($url) {
                $this->getEventDispatcher()->dispatch(new UrlCreated($url));
            } else {
                $this->getLogger()->error("Unable to fire URL created event from higher order event. URL not found", [
                    'id' => $urlId,
                ]);
            }
        }
    }

    /**
     * @return array
     */
    public function getUrlIds(): array
    {
        return $this->urlIds;
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

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return app(LoggerInterface::class);
    }
}
