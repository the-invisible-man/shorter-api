<?php

namespace App\Packages\Analytics\Http\V1\Controllers;

use App\Http\V1\Controllers\Controller;
use App\Packages\Analytics\Http\V1\Serializers\UrlMetricSerializer;
use App\Packages\Analytics\Repositories\UrlMetricRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        protected UrlMetricRepository $repository,
    ) {

    }

    /**
     * @param string $path
     *
     * @return JsonResponse
     */
    public function find(string $path): JsonResponse
    {
        if (! ($path = $this->repository->findByPath($path))) {
            return $this->response([], Response::HTTP_NOT_FOUND);
        }

        return $this->itemResponse($path, new UrlMetricSerializer);
    }
}
