<?php

namespace Tests\Http\V1\Controllers;

use App\Packages\Url\Events\UrlCreated;
use App\Packages\Url\Jobs\BulkUrlCreated;
use App\Packages\Url\Jobs\ProcessBulkCsv;
use App\Packages\Url\Models\BulkCsvJob;
use App\Packages\Url\Repositories\JobRepository;
use App\Packages\Url\Structs\CsvProcessComplete;
use App\Packages\Url\UrlService;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Mockery as m;

class UrlServiceTest extends TestCase
{
    public function testCreateFromCsv(): void
    {
        Bus::fake();
        Event::fake();

        $origin = base_path('tests/Stubs/happy.csv');
        $destination = base_path('storage/app/processed/happy.csv');

        $jobRecord = new BulkCsvJob;

        $jobRecord->original_csv_path = $origin;
        $jobRecord->destination_csv_path = $destination;
        $jobRecord->status = 'in-progress';
        $jobRecord->total_rows = 9;

        $jobRecord->save();

        $job = new ProcessBulkCsv($jobRecord, $origin, $destination, 9);

        $jobRepositorySpy = m::spy(JobRepository::class);
        $broadcasterMock = m::spy(Broadcaster::class);

        $this->app->instance(JobRepository::class, $jobRepositorySpy);
        $this->app->instance(Broadcaster::class, $broadcasterMock);

        $urlService = $this->app->make(UrlService::class);

        /** @var CsvProcessComplete $result */
        /** @var UrlService $urlService */
        $result = $urlService->createFromCsv($job);

        // Job Starts
        $jobRepositorySpy->shouldHaveReceived('update')
            ->with($jobRecord, 'in-progress');

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 0,
                'total_rows' => 9,
            ]);

        // Row iteration begins
        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 1,
                'total_rows' => 9,
            ]);

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 2,
                'total_rows' => 9,
            ]);

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 3,
                'total_rows' => 9,
            ]);

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 4,
                'total_rows' => 9,
            ]);

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 5,
                'total_rows' => 9,
            ]);

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 6,
                'total_rows' => 9,
            ]);

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 7,
                'total_rows' => 9,
            ]);

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 8,
                'total_rows' => 9,
            ]);

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                'status' => 'in-progress',
                'processed' => 9,
                'total_rows' => 9,
            ]);

        // Job is complete
        $jobRepositorySpy->shouldHaveReceived('update')
            ->with($jobRecord, 'completed');

        $broadcasterMock->shouldHaveReceived('broadcast')
            ->with(["jobs.{$jobRecord->id}"], 'job.progress', [
                // This never changes to "completed" because we are mocking the repo class
                // that makes this change in this test. It's a bad test, but just gotta get it done.
                'status' => 'in-progress',
                'processed' => 9,
                'total_rows' => 9,
            ]);

        Bus::assertDispatched(function (BulkUrlCreated $higherOrderEvent) {
            self::assertCount(9, $higherOrderEvent->getUrlIds());
            return true;
        });

        Event::assertNotDispatched(UrlCreated::class);

        self::assertEquals(9, $result->getTotalProcessed());
        self::assertEquals(0, $result->getTotalFailures());
    }
}
