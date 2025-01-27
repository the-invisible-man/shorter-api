<?php

namespace App\Packages\Url\Http\Controllers\V1;

use App\Http\V1\Controllers\Controller;
use App\Packages\Url\CsvBulkJobService;
use App\Packages\Url\Exceptions\MaxRowLimit;
use App\Packages\Url\Http\Requests\V1\CreateJob;
use App\Packages\Url\Http\Serializers\V1\BulkCsvJobSerializer;
use App\Packages\Url\Repositories\JobRepository;
use App\Packages\Url\UrlService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JobController extends Controller
{
    /**
     * @param CsvBulkJobService $service
     * @param JobRepository $repository
     */
    public function __construct(
        protected CsvBulkJobService $service,
        protected JobRepository $repository
    ) {
    }

    /**
     * @param CreateJob $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \League\Csv\Exception
     * @throws \League\Csv\UnavailableStream
     */
    public function create(CreateJob $request): JsonResponse
    {
        $this->validate($request);

        $file = $request->file('file')
                        ->store('csv');

        try {
            $job = $this->service->createBulkCsvJob($file);
        } catch (MaxRowLimit $e) {
            $this->throwValidationException('file', 'max_row_limit', $e->getMessage());
        }

        return $this->itemResponse($job, new BulkCsvJobSerializer, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function find(int $id): JsonResponse
    {
        $job = $this->repository->find($id);

        if (!$job) {
            $this->throwNotFoundException();
        }

        return $this->itemResponse($job, new BulkCsvJobSerializer, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function download(int $id): JsonResponse
    {
        $job = $this->repository->find($id);

        if (!$job) {
            $this->throwNotFoundException();
        }
    }
}
