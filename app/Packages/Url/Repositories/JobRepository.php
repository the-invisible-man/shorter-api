<?php

namespace App\Packages\Url\Repositories;

use App\Packages\Url\Models\BulkCsvJob;

interface JobRepository
{
    const STATUS = [
        'pending' => 'pending',
        'in-progress' => 'in-progress',
        'completed' => 'completed',
        'failed' => 'failed',
    ];

    /**
     * @param string $original_csv_path
     * @param string $destination_csv_path
     * @param string $status
     * @return BulkCsvJob
     */
    public function create(string $original_csv_path, string $destination_csv_path, string $status = self::STATUS['pending']): BulkCsvJob;

    /**
     * @param BulkCsvJob $job
     * @param string $status
     * @return void
     */
    public function update(BulkCsvJob $job, string $status): void;
}
