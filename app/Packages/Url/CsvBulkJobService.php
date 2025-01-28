<?php

namespace App\Packages\Url;

use App\Packages\Url\Exceptions\InvalidUrlException;
use App\Packages\Url\Exceptions\MaxRowLimit;
use App\Packages\Url\Jobs\ProcessBulkCsv;
use App\Packages\Url\Models\BulkCsvJob;
use App\Packages\Url\Repositories\JobRepository;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

class CsvBulkJobService
{
    public function __construct(
        protected JobRepository $jobRepository,
        protected Broadcaster $broadcaster
    ) {
    }

    /**
     * @param string $file
     * @param bool   $enqueue
     *
     * @throws Exception
     * @throws InvalidUrlException
     * @throws MaxRowLimit
     * @throws UnavailableStream
     *
     * @return BulkCsvJob
     */
    public function createBulkCsvJob(string $file, bool $enqueue = true): BulkCsvJob
    {
        if (! file_exists($file)) {
            throw new \RuntimeException("File does not exist: {$file}");
        }

        $rowLimit = config('services')[self::class]['max_row_limit'];

        $totalRows = $this->validateCsv($file, $rowLimit);
        $destination = $this->getDestinationPath($file);

        $job = $this->jobRepository->create($file, $destination, $totalRows);

        if ($enqueue) {
            dispatch(new ProcessBulkCsv($job, $file, $destination, $totalRows));
        }

        return $job;
    }

    /**
     * @param BulkCsvJob $job
     * @param string     $status
     * @param int        $processed
     */
    public function updateJobStatus(BulkCsvJob $job, string $status, int $processed): void
    {
        $this->jobRepository->update($job, $status);
        $this->broadcastJobProgress($job, $processed);
    }

    /**
     * @param BulkCsvJob $job
     * @param int        $processed
     */
    public function broadcastJobProgress(BulkCsvJob $job, int $processed): void
    {
        $this->broadcaster->broadcast(["jobs.{$job->id}"], 'job.progress', [
            'status' => $job->status,
            'processed' => $processed,
            'total_rows' => $job->total_rows,
        ]);
    }

    /**
     * @param string $file
     * @param int    $limit
     *
     * @throws Exception
     * @throws InvalidUrlException
     * @throws MaxRowLimit
     * @throws UnavailableStream
     *
     * @return int
     */
    protected function validateCsv(string $file, int $limit): int
    {
        $total = 0;

        $csv = Reader::createFromPath($file);

        foreach ($csv->getRecords() as $record) {
            $total++;

            // We have to iterate through all rows so that we can determine
            // if the file exceeds the max rows we allow per job, so while
            // we're at it, we'll also check that each URL is valid.
            if (! $this->isValidUrl($record[0])) {
                throw new InvalidUrlException($record[0]);
            }

            if ($total > $limit) {
                throw new MaxRowLimit("The CSV exceeds the row limit of {$limit}.");
            }
        }

        return $total;
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    protected function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getDestinationPath(string $file): string
    {
        $filename = basename($file);

        return storage_path("app/processed/{$filename}");
    }
}
