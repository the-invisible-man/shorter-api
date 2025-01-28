<?php

namespace Tests;

use App\Packages\Analytics\Models\UrlMetric;
use App\Packages\Url\Models\BulkCsvJob;
use App\Packages\Url\Models\Url;
use App\Packages\Url\Repositories\JobRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * @param string $longUrl
     * @param string $shortUrl
     *
     * @return Url
     */
    protected function createUrl(string $longUrl, string $shortUrl): Url
    {
        $url = new Url;

        $url->long_url = $longUrl;
        $url->short_url = $shortUrl;

        $url->save();

        return $url;
    }

    /**
     * @param string $original_csv_path
     * @param string $destination_csv_path
     * @param int    $totalRows
     * @param string $status
     *
     * @return BulkCsvJob
     */
    protected function createJob(
        string $original_csv_path,
        string $destination_csv_path,
        int $totalRows,
        string $status = JobRepository::STATUS['pending']
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
     * @param string $path
     * @param int    $count
     *
     * @return UrlMetric
     */
    protected function createMetric(string $path, int $count): UrlMetric
    {
        $metric = new UrlMetric;

        $metric->path = $path;
        $metric->count = $count;

        $metric->save();

        return $metric;
    }
}
