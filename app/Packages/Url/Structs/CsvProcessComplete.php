<?php

namespace App\Packages\Url\Structs;

use App\Packages\Url\Jobs\ProcessBulkCsv;

class CsvProcessComplete
{
    /**
     * @param ProcessBulkCsv $job
     * @param int            $totalProcessed
     * @param int            $totalFailures
     * @param int[]          $urlIds
     */
    public function __construct(
        public ProcessBulkCsv $job,
        public int $totalProcessed,
        public int $totalFailures,
        public array $urlIds,
    ) {

    }

    /**
     * @return ProcessBulkCsv
     */
    public function getJob(): ProcessBulkCsv
    {
        return $this->job;
    }

    /**
     * @return int
     */
    public function getTotalProcessed(): int
    {
        return $this->totalProcessed;
    }

    /**
     * @return int
     */
    public function getTotalFailures(): int
    {
        return $this->totalFailures;
    }

    /**
     * @return int[]
     */
    public function getUrlIds(): array
    {
        return $this->urlIds;
    }
}
