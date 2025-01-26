<?php

namespace App\Http\V1\Controllers;

use App\Http\V1\Requests\LawFirms\CreateRequest;
use App\Http\V1\Requests\LawFirms\UpdateRequest;
use App\Http\V1\Serializers\LawFirmSerializer;
use App\Http\V1\Traits\FindsLawFirms;
use App\Packages\Organizations\LawFirmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LawFirmsController extends Controller
{
    use FindsLawFirms;

    public function __construct(protected LawFirmsService $service)
    {
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

        $lawFirm = $this->service->create($this->entity()->getOrganization(), $data);

        return $this->itemResponse($lawFirm, new LawFirmSerializer, Response::HTTP_CREATED);
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

        $lawFirm = $this->findLawFirmOrNotFound($id);

        $this->service->update($lawFirm, $data);

        return $this->itemResponse($lawFirm, new LawFirmSerializer);
    }
}
