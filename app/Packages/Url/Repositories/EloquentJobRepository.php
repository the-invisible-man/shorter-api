<?php

namespace App\Packages\Url\Repositories;

use App\Packages\Url\Models\BulkCsvJob;

class EloquentJobRepository implements JobRepository
{
    /**
     * @param string $original_csv_path
     * @param string $destination_csv_path
     * @param string $status
     * @param int    $totalRows
     *
     * @return BulkCsvJob
     */
    public function create(
        string $original_csv_path,
        string $destination_csv_path,
        int $totalRows,
        string $status = self::STATUS['pending']
    ): BulkCsvJob {
        $job = new BulkCsvJob;

        $job->original_csv_path = $original_csv_path;
        $job->destination_csv_path = $destination_csv_path;
        $job->total_rows = $totalRows;
        $job->status = $status;

        $job->save();

        return $job;
    }

    /**
     * @param BulkCsvJob $job
     * @param string     $status
     */
    public function update(BulkCsvJob $job, string $status): void
    {
        $job->status = $status;
        $job->save();
    }

    /**
     * @param string $id
     *
     * @return BulkCsvJob|null
     */
    public function find(string $id): ?BulkCsvJob
    {
        return BulkCsvJob::find($id);
    }
}
