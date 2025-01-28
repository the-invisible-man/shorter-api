<?php

namespace App\Packages\Url\Jobs;

use App\Packages\Url\Models\BulkCsvJob;
use App\Packages\Url\UrlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBulkCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param BulkCsvJob $jobRecord
     * @param string     $origin
     * @param string     $destination
     * @param string     $totalRows
     */
    public function __construct(
        protected BulkCsvJob $jobRecord,
        protected string $origin,
        protected string $destination,
        protected string $totalRows,
    ) {
    }

    /**
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
        return $this->jobRecord->id;
    }

    /**
     * @return BulkCsvJob
     */
    public function getJobRecord(): BulkCsvJob
    {
        return $this->jobRecord;
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
