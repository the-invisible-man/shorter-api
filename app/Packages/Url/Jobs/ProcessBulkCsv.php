<?php

namespace App\Packages\Url\Jobs;

use App\Packages\Url\UrlService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessBulkCsv implements ShouldQueue
{
    /**
     * @param string $jobId
     * @param string $origin
     * @param string $destination
     * @param string $totalRows
     */
    public function __construct(
        protected string $jobId,
        protected string $origin,
        protected string $destination,
        protected string $totalRows,
    )
    {
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->getUrlService()->createFromCsv($this);
    }

    /**
     * @return string
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }

    /**
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @return string
     */
    public function getTotalRows(): string
    {
        return $this->totalRows;
    }

    /**
     * @return UrlService
     */
    protected function getUrlService(): UrlService
    {
        return app(UrlService::class);
    }
}
