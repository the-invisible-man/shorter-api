<?php

namespace App\Packages\Url\Http\Controllers\V1;

use App\Http\V1\Controllers\Controller;
use App\Packages\Url\CsvBulkJobService;
use App\Packages\Url\Exceptions\InvalidUrlException;
use App\Packages\Url\Exceptions\MaxRowLimit;
use App\Packages\Url\Http\Requests\V1\CreateJob;
use App\Packages\Url\Http\Serializers\V1\BulkCsvJobSerializer;
use App\Packages\Url\Repositories\JobRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobController extends Controller
{
    /**
     * @param CsvBulkJobService $service
     * @param JobRepository     $repository
     */
    public function __construct(
        protected CsvBulkJobService $service,
        protected JobRepository $repository
    ) {
    }

    /**
     * @param CreateJob $request
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \League\Csv\Exception
     * @throws \League\Csv\UnavailableStream
     *
     * @return JsonResponse
     */
    public function create(CreateJob $request): JsonResponse
    {
        $this->validate($request);

        $filename = Str::uuid() . '.csv';
        $file = $request->file('file')
                        ->storeAs('csv', $filename);

        try {
            $job = $this->service->createBulkCsvJob(storage_path("app/{$file}"));
        } catch (MaxRowLimit $e) {
            $this->throwValidationException('file', 'max_row_limit', $e->getMessage());
        } catch (InvalidUrlException $e) {
            $this->throwValidationException('file', 'invalid_url', "The URL \"{$e->getMessage()}\" is invalid.");
        }

        return $this->itemResponse($job, new BulkCsvJobSerializer, Response::HTTP_CREATED);
    }

    /**
     * @param int $jobId
     *
     * @return JsonResponse
     */
    public function find(int $jobId): JsonResponse
    {
        $job = $this->repository->find($jobId);

        if (! $job || ! $job->isComplete()) {
            $this->throwNotFoundException();
        }

        return $this->itemResponse($job, new BulkCsvJobSerializer, Response::HTTP_CREATED);
    }

    /**
     * @param string $jobId
     *
     * @return StreamedResponse
     */
    public function download(string $jobId): StreamedResponse
    {
        $job = $this->repository->find($jobId);

        if (! $job) {
            $this->throwNotFoundException();
        }

        $fileName = 'processed-'.basename($job->destination_csv_path);

        return response()->streamDownload(function () use ($job) {
            echo file_get_contents($job->destination_csv_path);
        }, $fileName, [
            'content-type' => 'application/csv',
            'content-disposition' => "attachment; filename={$fileName}",
            'pragma' => 'no-cache',
        ]);
    }
}
