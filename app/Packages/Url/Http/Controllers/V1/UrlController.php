<?php

namespace App\Packages\Url\Http\Controllers\V1;

use App\Http\V1\Controllers\Controller;
use App\Packages\Url\Http\Requests\V1\CreateUrl;
use App\Packages\Url\Http\Serializers\V1\UrlSerializer;
use App\Packages\Url\UrlReadService;
use App\Packages\Url\UrlService;
use App\Packages\Url\UrlWriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends Controller
{
    public function __construct(
        protected UrlReadService $urlReadService,
        protected UrlWriteService $urlWriteService,
        protected UrlService $urlService,
    ) {
    }

    /**
     * @param CreateUrl $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function create(CreateUrl $request): JsonResponse
    {
        $this->validate($request);

        $data = $request->getValidated();

        $url = $this->urlWriteService->create($data['long_url']);

        return $this->itemResponse($url, new UrlSerializer, Response::HTTP_CREATED);
    }

    /**
     * @param string $path
     * @return RedirectResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function route(string $path): RedirectResponse
    {
        if ($url = $this->urlService->visitUrl($path)) {
            return redirect($url->long_url, Response::HTTP_FOUND);
        }

        abort(404);
    }
}
