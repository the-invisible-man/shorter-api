<?php

namespace Tests\Http\V1\Controllers;

use App\Packages\Url\CsvBulkJobService;
use App\Packages\Url\Jobs\ProcessBulkCsv;
use App\Packages\Url\Models\BulkCsvJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class JobControllerTest extends TestCase
{
    public function testCreate(): void
    {
        Bus::fake();

        $file = UploadedFile::fake()->createWithContent('happy.csv', file_get_contents(base_path('tests/Stubs/happy.csv')));

        $response = $this->call('POST', route('shorten::v1::urls.jobs.create'), [], [], [
            'file' => $file,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'status' => 'pending'
            ]
        ]);

        Bus::assertDispatched(function (ProcessBulkCsv $job) {
            self::assertInstanceOf(BulkCsvJob::class, $job->getJobRecord());
            self::assertEquals('pending', $job->getJobRecord()->status);

            return true;
        });
    }

    public function testCreateExceedsMaxLength(): void
    {
        $file = UploadedFile::fake()->createWithContent('happy.csv', file_get_contents(base_path('tests/Stubs/happy.csv')));

        config([
            'services.'.CsvBulkJobService::class.'.max_row_limit' => 3,
        ]);

        $response = $this->call('POST', route('shorten::v1::urls.jobs.create'), [], [], [
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => [
                'code' => 10100,
                'type' => 'validation_failed',
                'message' => 'Validation failed.',
                'errors' => [
                    'file' => [
                        [
                            'type' => 'max_row_limit',
                            'message' => 'The CSV exceeds the row limit of 3.'
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testCreateBadUrls(): void
    {
        $file = UploadedFile::fake()->createWithContent('happy.csv', file_get_contents(base_path('tests/Stubs/bad-urls.csv')));

        $response = $this->call('POST', route('shorten::v1::urls.jobs.create'), [], [], [
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => [
                'code' => 10100,
                'type' => 'validation_failed',
                'message' => 'Validation failed.',
                'errors' => [
                    'file' => [
                        [
                            'type' => 'invalid_url',
                            'message' => 'The URL "google.igloo" is invalid.'
                        ]
                    ]
                ]
            ]
        ]);
    }
}
