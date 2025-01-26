<?php

namespace App\Http\V1\Controllers;

use App\Http\V1\Requests\Cases\CreateRequest;
use App\Http\V1\Requests\Cases\UpdateRequest;
use App\Http\V1\Serializers\CaseSerializer;
use App\Http\V1\Traits\ExpandsRelationships;
use App\Http\V1\Traits\FindsOrganizationEntities;
use App\Packages\Cases\CasesService;
use App\Packages\Organizations\Models\Service;
use App\Packages\Transactions\Models\Visit;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CasesController extends Controller
{
    use FindsOrganizationEntities;
    use ExpandsRelationships;

    public function __construct(protected CasesService $service)
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

        if (! $this->entity()->getOrganization()->isIReclaim() && $request->has('id')) {
            // The id of the case is only allowed when the organization
            // is iReclaim. This is because we have decided that we want
            // the IDs of our entities to match in both systems to simplify
            $this->throwValidationException('id', 'field_not_allowed', 'You cannot supply an id for this record.');
        }

        [$client, $lawFirm] = $this->fetchEntitiesOrFail([
            'client_id' => $data['client_id'],
            'law_firm_id' => $data['law_firm_id'],
        ], [
            // The law firm is optional. If it isn't passed in the
            // request then it will be null in the $data array by default.
            'law_firm_id',
        ]);

        $case = $this->service->create($client, $lawFirm, $data);

        $serializer = new CaseSerializer;
        $relationships = $this->filterRelationships($request->get('expand'), $serializer->getExpandable());

        $serializer->expand($relationships);

        return $this->itemResponse($case, $serializer, Response::HTTP_CREATED);
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

        $case = $this->findCaseOrNotFound($id);

        $this->service->update($case, $data);

        return $this->itemResponse($case, new CaseSerializer);
    }

    /**
     * Temporary endpoint for the data import
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function getLawFirmForClient(string $id): JsonResponse
    {
        $visit = Visit::where('client_id', $id)
            ->whereNotNull('law_firm_id')
            ->first();

        if ($visit) {
            return $this->itemResponse([
                'law_firm_id' => $visit->law_firm_id,
            ]);
        }

        return $this->itemResponse([
            'law_firm_id' => null,
        ]);
    }

    /**
     * Temporary endpoint for the data import
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function getProviderFromService(string $id): JsonResponse
    {
        $service = Service::find($id);

        if ($service) {
            return $this->itemResponse([
                'provider_id' => $service->provider_id,
            ]);
        }

        return $this->itemResponse([
            'provider_id' => null,
        ]);
    }
}
