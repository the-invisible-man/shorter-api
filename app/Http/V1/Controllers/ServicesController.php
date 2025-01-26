<?php

namespace App\Http\V1\Controllers;

use App\Http\V1\Requests\Services\CreateRequest;
use App\Http\V1\Requests\Services\IndexRequest;
use App\Http\V1\Requests\Services\UpdateRequest;
use App\Http\V1\Serializers\ServiceSerializer;
use App\Http\V1\Traits\FindsProviders;
use App\Http\V1\Traits\FindsServices;
use App\Http\V1\Traits\ScopesQueries;
use App\Packages\Organizations\Models\Service;
use App\Packages\Organizations\ServicesService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ServicesController extends Controller
{
    use FindsProviders;
    use FindsServices;
    use ScopesQueries;

    /**
     * @var ServicesService
     */
    protected ServicesService $service;

    /**
     * @param ServicesService $service
     */
    public function __construct(ServicesService $service)
    {
        $this->service = $service;
    }

    /**
     * @param string  $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function get(string $id, Request $request): JsonResponse
    {
        $service = $this->findsServiceOrNotFound($id);

        $requestExpand = explode(',', $request->get('expand'));
        $expand = array_intersect($requestExpand, ['provider']);

        $serializer = new ServiceSerializer;
        $serializer->expand($expand);

        return $this->itemResponse($service, $serializer);
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

        [$organization, $provider] = $this->fetchCreateReqs($data);

        $service = $this->service->create($organization, $provider, $data);

        $requestExpand = explode(',', $request->get('expand'));
        $expand = array_intersect($requestExpand, ['provider']);

        $serializer = new ServiceSerializer;
        $serializer->expand($expand);

        return $this->itemResponse($service, $serializer, ResponseAlias::HTTP_CREATED);
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
        $query = $this->scopeQuery(Service::query());

        $this->buildSearchScopes($query, $data);

        $paginator = $this->paginate($query, $data['per_page']);

        $requestExpand = explode(',', $request->get('expand'));
        $expand = array_intersect($requestExpand, ['provider']);

        $serializer = new ServiceSerializer;
        $serializer->expand($expand);

        return $this->paginatedResponse($paginator, $serializer);
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

        $service = $this->findsServiceOrNotFound($id);
        $this->service->update($service, $data);

        $serializer = new ServiceSerializer;

        return $this->itemResponse($service, $serializer);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function fetchCreateReqs(array $params): array
    {
        $provider = $this->findProvider($params['provider_id']);

        if (! $provider) {
            $this->throwValidationException('provider_id', 'invalid_provider_id', "No such provider with id {$params['provider_id']} exists.");
        }

        $organization = $this->entity()->getOrganization();

        return [$organization, $provider];
    }

    /**
     * @param Builder $query
     * @param array   $request
     */
    protected function buildSearchScopes(Builder $query, array $request): void
    {
        if (isset($request['provider_id'])) {
            $query->where('provider_id', $request['provider_id']);
        }
    }
}
