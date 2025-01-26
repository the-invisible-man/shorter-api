<?php

namespace App\Http\V1\Controllers;

use App\Http\V1\Requests\Providers\CreateRequest;
use App\Http\V1\Requests\Providers\IndexRequest;
use App\Http\V1\Requests\Providers\UpdateRequest;
use App\Http\V1\Serializers\ProviderSerializer;
use App\Http\V1\Traits\FindsProviders;
use App\Http\V1\Traits\ScopesQueries;
use App\Packages\Organizations\Models\Provider;
use App\Packages\Organizations\ProvidersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProvidersController extends Controller
{
    use FindsProviders;
    use ScopesQueries;

    /**
     * @var ProvidersService
     */
    protected ProvidersService $service;

    /**
     * @param ProvidersService $service
     */
    public function __construct(ProvidersService $service)
    {
        $this->service = $service;
    }

    /**
     * @param CreateRequest $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return JsonResponse
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $this->validate($request);

        $data = $request->data();

        $provider = $this->service->create($this->entity()->getOrganization(), $data);

        return $this->itemResponse($provider, new ProviderSerializer, Response::HTTP_CREATED);
    }

    /**
     * @param IndexRequest $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return JsonResponse
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $this->validate($request);

        $data = $request->data();
        $query = $this->scopeQuery(Provider::query());

        $paginator = $this->paginate($query, $data['per_page']);

        return $this->paginatedResponse($paginator, new ProviderSerializer);
    }

    /**
     * @param string $id
     *
     * @return JsonResponse
     */
    public function get(string $id): JsonResponse
    {
        $provider = $this->findProviderOrNotFound($id);

        return $this->itemResponse($provider, new ProviderSerializer);
    }

    /**
     * @param string        $id
     * @param UpdateRequest $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return JsonResponse
     */
    public function update(string $id, UpdateRequest $request): JsonResponse
    {
        $this->validate($request);

        $data = $request->data();

        $provider = $this->findProviderOrNotFound($id);

        $this->service->update($provider, $data);

        return $this->itemResponse($provider, new ProviderSerializer);
    }
}


$names = ['Reymer', 'Shannon', 'Jenny', 'Pedro'];

foreach ($names as $p) {

}

