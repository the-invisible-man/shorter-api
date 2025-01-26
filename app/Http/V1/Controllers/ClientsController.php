<?php

namespace App\Http\V1\Controllers;

use App\Http\V1\Requests\Clients\CreateRequest;
use App\Http\V1\Requests\Clients\UpdateRequest;
use App\Http\V1\Serializers\ClientSerializer;
use App\Http\V1\Traits\FindsClients;
use App\Packages\Organizations\ClientsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ClientsController extends Controller
{
    use FindsClients;

    /**
     * @var ClientsService
     */
    protected ClientsService $service;

    /**
     * @param ClientsService $service
     */
    public function __construct(ClientsService $service)
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

        $client = $this->service->create($this->entity()->getOrganization(), $data);

        return $this->itemResponse($client, new ClientSerializer, Response::HTTP_CREATED);
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

        $client = $this->findClientOrNotFound($id);

        $this->service->update($client, $data);

        return $this->itemResponse($client, new ClientSerializer);
    }

    /**
     * @param string $id
     *
     * @return JsonResponse
     */
    public function get(string $id): JsonResponse
    {
        $client = $this->findClientOrNotFound($id);

        return $this->itemResponse($client, new ClientSerializer);
    }
}
