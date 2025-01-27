<?php

namespace App\Packages\Url;

use App\Packages\Url\Exceptions\MaxRowLimit;
use App\Packages\Url\Jobs\ProcessBulkCsv;
use App\Packages\Url\Models\BulkCsvJob;
use App\Packages\Url\Repositories\JobRepository;
use Illuminate\Support\Facades\Broadcast;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

class CsvBulkJobService
{
    public function __construct(protected JobRepository $jobRepository)
    {
    }

    /**
     * @param string $file
     * @return BulkCsvJob
     * @throws MaxRowLimit
     * @throws Exception
     * @throws UnavailableStream
     */
    public function fireBulkCsvJob(string $file): BulkCsvJob
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("File does not exist: {$file}");
        }

        $rowLimit = config('services')[CsvBulkJobService::class]['max_row_limit'];

        if (($totalRows = $this->getTotalRows($file, $rowLimit)) === false) {
            throw new MaxRowLimit("The CSV exceeds the row limit of {$rowLimit}. Maximum rows exceeded.");
        }

        $destination = $this->getDestinationPath($file);
        $job = $this->jobRepository->create($file, $destination);

        dispatch(new ProcessBulkCsv($job, $file, $destination, $totalRows));

        return $job;
    }

    /**
     * @param BulkCsvJob $job
     * @param string $status
     * @param int $processed
     *
     * @return void
     */
    public function updateJobStatus(BulkCsvJob $job, string $status, int $processed): void
    {
        $this->jobRepository->update($job, $status);
        $this->broadcastJobProgress($job, $processed);
    }

    /**
     * @param BulkCsvJob $job
     * @param int $processed
     *
     * @return void
     */
    public function broadcastJobProgress(BulkCsvJob $job, int $processed): void
    {
        Broadcast::on("jobs.{$job->id}")->with([
            'status' => $job->status,
            'processed' => $processed,
            'total_rows' => $job->total_rows,
        ])->send();
    }

    /**
     * @param string $file
     * @param int $limit
     * @return bool
     * @throws \League\Csv\Exception
     * @throws \League\Csv\UnavailableStream
     */
    protected function getTotalRows(string $file, int $limit): bool
    {
        $total = 0;

        $csv = Reader::createFromPath($file);

        foreach ($csv->getRecords() as $record) {
            $total++;

            if ($total > $limit) {
                return false;
            }
        }

        return $total;
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getDestinationPath(string $file): string
    {
        $filename = basename($file);

        return storage_path("app/processed/{$filename}");
    }
}
