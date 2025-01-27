<?php

namespace Tests\Http\V1\Controllers;

use App\Packages\Url\CsvBulkJobService;
use App\Packages\Url\Jobs\ProcessBulkCsv;
use App\Packages\Url\Models\BulkCsvJob;
use App\Packages\Url\Repositories\JobRepository;
use App\Packages\Url\UrlService;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Tests\TestCase;
use Mockery as m;

class UrlServiceTest extends TestCase
{
    public function testCreateFromCsv(): void
    {
        $origin = base_path('storage/app/csv/happy.csv');
        $destination = base_path('storage/app/processed/happy.csv');

        $jobRecord = new BulkCsvJob;

        $jobRecord->original_csv_path = $origin;
        $jobRecord->destination_csv_path = $destination;
        $jobRecord->status = 'in-progress';
        $jobRecord->total_rows = 9;

        $jobRecord->save();

        $job = new ProcessBulkCsv($jobRecord, $origin, $destination, 9);

        $csvBulkJobServiceSpy = m::spy(CsvBulkJobService::class);
        $jobRepositorySpy = m::spy(JobRepository::class);
        $broadcasterMock = m::mock(Broadcaster::class);

        $this->app->instance(CsvBulkJobService::class, $csvBulkJobServiceSpy);
        $this->app->instance(JobRepository::class, $jobRepositorySpy);
        $this->app->instance(Broadcaster::class, $broadcasterMock);

        // Job Starts
        $csvBulkJobServiceSpy->shouldReceive('updateJobStatus')
            ->with($jobRecord, 'in-progress', 0);
        $jobRepositorySpy->shouldReceive('update')
            ->with($jobRecord, 'in-progress');
        $broadcasterMock->shouldReceive('broadcast')
            ->with($jobRecord, 0);

        // Row iteration begins
        

        $urlService = $this->app->make(UrlService::class);

        self::assertTrue($urlService->createFromCsv($job));
        self::assertDatabaseHas(BulkCsvJob::class, [
            'id' => $jobRecord->id,
            'status' => 'complete',
        ]);
    }
}
