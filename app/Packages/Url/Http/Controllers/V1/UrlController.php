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
use Illuminate\View\View;
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
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     *
     * @return JsonResponse
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
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return RedirectResponse|View
     */
    public function route(string $path): RedirectResponse|View
    {
        $url = $this->urlService->visitUrl($path);

        abort_unless(isset($url), Response::HTTP_NOT_FOUND);

        if ($url->isFlagged()) {
            return view('flagged', ['url' => $url]);
        }

        return redirect($url->long_url, Response::HTTP_FOUND);
    }
}
