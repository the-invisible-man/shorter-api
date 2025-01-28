<?php

namespace App\Packages\Url\Repositories;

use App\Packages\Url\Models\BulkCsvJob;

interface JobRepository
{
    public const STATUS = [
        'pending' => 'pending',
        'in-progress' => 'in-progress',
        'completed' => 'completed',
        'failed' => 'failed',
    ];

    /**
     * @param string $original_csv_path
     * @param string $destination_csv_path
     * @param int    $totalRows
     * @param string $status
     *
     * @return BulkCsvJob
     */
    public function create(string $original_csv_path, string $destination_csv_path, int $totalRows, string $status = self::STATUS['pending']): BulkCsvJob;

    /**
     * @param string $id
     *
     * @return BulkCsvJob|null
     */
    public function find(string $id): ?BulkCsvJob;

    /**
     * @param BulkCsvJob $job
     * @param string     $status
     */
    public function update(BulkCsvJob $job, string $status): void;
}
