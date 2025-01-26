<?php

namespace App\Http\V1\Controllers;

use App\Http\V1\Requests\Visits\AggregateReportRequest;
use App\Http\V1\Requests\Visits\ClientVisitsAggregateRequest;
use App\Http\V1\Requests\Visits\CreateRequest;
use App\Http\V1\Requests\Visits\IndexRequest;
use App\Http\V1\Requests\Visits\LawFirmVisitsAggregateRequest;
use App\Http\V1\Requests\Visits\ProviderVisitsAggregateRequest;
use App\Http\V1\Serializers\AggregateByClientSerializer;
use App\Http\V1\Serializers\AggregateByLawFirmSerializer;
use App\Http\V1\Serializers\AggregateByProviderSerializer;
use App\Http\V1\Serializers\VisitSerializer;
use App\Http\V1\Traits\ExpandsRelationships;
use App\Http\V1\Traits\FindsClients;
use App\Http\V1\Traits\FindsLawFirms;
use App\Http\V1\Traits\FindsOrganizationEntities;
use App\Http\V1\Traits\FindsServices;
use App\Http\V1\Traits\FindsVisits;
use App\Http\V1\Traits\ScopesQueries;
use App\Packages\Analytics\Enums\SearchFetchMethod;
use App\Packages\Analytics\Presenters\ClientsPresenter;
use App\Packages\Analytics\Presenters\LawFirmsPresenter;
use App\Packages\Analytics\Presenters\ProvidersPresenter;
use App\Packages\Analytics\Presenters\VisitsPresenter;
use App\Packages\Analytics\ReportGenerator;
use App\Packages\Analytics\SearchService;
use App\Packages\Transactions\Models\Visit;
use App\Packages\Transactions\VisitsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\Logger;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitsController extends Controller
{
    use FindsServices;
    use FindsClients;
    use FindsVisits;
    use FindsLawFirms;
    use ScopesQueries;
    use ExpandsRelationships;
    use FindsOrganizationEntities;

    /**
     * @param VisitsService   $service
     * @param SearchService   $searchService
     * @param ReportGenerator $reportGenerator
     * @param Logger          $logger
     */
    public function __construct(
        protected VisitsService $service,
        protected SearchService $searchService,
        protected ReportGenerator $reportGenerator,
        protected Logger $logger
    ) {
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

        $this->logger->info('Received request to create visit', $data);

        if (config('feature-gate.case-migration')) {
            [$service, $case] = $this->fetchEntitiesOrFail([
                'service_id' => $data['service_id'],
                'case_id' => $data['case_id'],
            ]);

            $visit = $this->service->create($service, $case, $data);
        } else {
            [$client, $service, $lawFirm] = $this->fetchEntitiesOrFail([
                'client_id' => $data['client_id'],
                'service_id' => $data['service_id'],
                'law_firm_id' => $data['law_firm_id'],
            ]);

            $visit = $this->service->createV1($client, $service, $lawFirm, $data);
        }

        $serializer = new VisitSerializer;

        if (config('feature-gate.case-migration')) {
            $relationships = $this->filterRelationships($request->get('expand'), $serializer->getExpandable());
        } else {
            $relationships = $this->filterRelationships($request->get('expand'), [
                'provider', 'service', 'client', 'law_firm',
            ]);
        }

        $serializer->expand($relationships);

        return $this->itemResponse($visit, $serializer, Response::HTTP_CREATED);
    }

    /**
     * @param string  $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function get(string $id, Request $request): JsonResponse
    {
        $visit = $this->findVisitOrNotFound($id);

        $serializer = new VisitSerializer;
        $relationships = $this->filterRelationships($request->get('expand'), $serializer->getExpandable());

        $serializer->expand($relationships);

        return $this->itemResponse($visit, $serializer);
    }

    /**
     * This is a temporary endpoint used for importing and backfilling data as part
     * of the launch of the platform. This is not meant to stay much beyond the launch.
     *
     * @param string  $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getByCsvId(string $id, Request $request): JsonResponse
    {
        $visit = Visit::where('csv_id', $id)->first();

        if (! $visit) {
            $this->throwNotFoundException();
        }

        $serializer = new VisitSerializer;
        $relationships = $this->filterRelationships($request->get('expand'), $serializer->getExpandable());

        $serializer->expand($relationships);

        return $this->itemResponse($visit, $serializer);
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

        $query = $this->searchService->buildVisitsQuery($data, $this->entity()->getOrganization());
        $metadata = [];

        $paginator = $this->paginate($query, $data['per_page']);

        $serializer = new VisitSerializer;
        $relationships = $this->filterRelationships($request->get('expand'), $serializer->getExpandable());

        // I think this is okay. For now
        if ($this->shouldIncludeAggregate($data)) {
            $metadata = $this->searchService->fetchAggregateTotals($data);
        }

        // This is making the index request very expensive as retrieving multiple relationships
        // for multiple results is resulting in an n+1 query. Because the use of this system is limited
        // to just employees of iReclaim, and their workforce is relatively small, we are okay with this.
        $serializer->expand($relationships);

        return $this->paginatedResponse($paginator, $serializer, pageParams: $data, metadata: $metadata);
    }

    /**
     * A very temporary solution, not intended for long term use.
     * This will be replaced by a search endpoint. The queries a pretty expensive.
     *
     * @param ClientVisitsAggregateRequest $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return JsonResponse
     */
    public function aggByClient(ClientVisitsAggregateRequest $request): JsonResponse
    {
        $this->validate($request);

        $data = $request->data();

        $query = $this->searchService->buildClientAggregateQuery($data, $this->entity()->getOrganization());
        $metadata = $this->searchService->fetchAggregateTotals($data);

        $paginator = $this->paginate($query, $data['per_page']);

        return $this->paginatedResponse($paginator, new AggregateByClientSerializer, metadata: $metadata);
    }

    /**
     * Same comments as aggByClientId()
     *
     * @param LawFirmVisitsAggregateRequest $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return JsonResponse
     */
    public function aggByLawFirm(LawFirmVisitsAggregateRequest $request): JsonResponse
    {
        $this->validate($request);

        $data = $request->data();

        $query = $this->searchService->buildLawFirmAggregateQuery($data, $this->entity()->getOrganization());
        $metadata = $this->searchService->fetchAggregateTotals($data);

        $paginator = $this->paginate($query, $data['per_page']);

        return $this->paginatedResponse($paginator, new AggregateByLawFirmSerializer, metadata: $metadata);
    }

    /**
     * @param ProviderVisitsAggregateRequest $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return JsonResponse
     */
    public function aggByProvider(ProviderVisitsAggregateRequest $request): JsonResponse
    {
        $this->validate($request);

        $data = $request->data();

        $query = $this->searchService->buildProviderAggregateQuery($data, $this->entity()->getOrganization());
        $metadata = $this->searchService->fetchAggregateTotals($data);

        $paginator = $this->paginate($query, $data['per_page']);

        return $this->paginatedResponse($paginator, new AggregateByProviderSerializer, metadata: $metadata);
    }

    /**
     * @param string                 $type
     * @param AggregateReportRequest $request
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     *
     * @return StreamedResponse
     */
    public function aggregateReport(string $type, AggregateReportRequest $request): StreamedResponse
    {
        $this->validate($request);

        $data = $request->data();

        $this->logger->info('Received request', $data);

        $this->validateDateRange($type, $data);

        $organization = $this->entity()->getOrganization();

        [$query, $presenter] = match($type) {
            'visits' => [
                $this->searchService->buildVisitsQuery($data, $organization, SearchFetchMethod::QUERY),
                new VisitsPresenter,
            ],
            'client' => [
                $this->searchService->buildClientAggregateQuery($data, $organization),
                new ClientsPresenter,
            ],
            'provider' => [
                $this->searchService->buildProviderAggregateQuery($data, $organization),
                new ProvidersPresenter,
            ],
            'law-firm' => [
                $this->searchService->buildLawFirmAggregateQuery($data, $organization),
                new LawFirmsPresenter,
            ],
            default => [false, null],
        };

        if (! $query) {
            $this->throwNotFoundException("Report type \"{$type}\" is invalid. Please use: client, provider, or law-firm");
        }

        $columns = isset($data['columns']) ? explode(',', $data['columns']) : [];

        $report = $this->reportGenerator->toCsv($query, $columns, $presenter);

        $fileName = "export-{$type}.csv";

        return response()->streamDownload(function () use ($report) {
            echo $report;
        }, $fileName, [
            'content-type' => 'application/csv',
            'content-disposition' => "attachment; filename={$fileName}",
            'pragma' => 'no-cache',
        ]);
    }

    /**
     * @param string $type
     * @param array  $request
     */
    protected function validateDateRange(string $type, array $request): void
    {
        $requiredFor = ['client', 'provider', 'law-firm'];

        if (in_array($type, $requiredFor)) {
            if (! isset($request['year'])) {
                $this->throwValidationException('year', 'year_required', 'The year field is required for this report');
            }

            if (! isset($request['month'])) {
                $this->throwValidationException('month', 'month_required', 'The month field is required for this report');
            }
        }
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function fetchCreateReqs(array $params): array
    {
        // I'm having second thoughts about having to query all the relationships
        // from the database everytime we create a visit record.
        $service = $this->findService($params['service_id']);

        if (! $service) {
            $this->throwValidationException('service_id', 'invalid_service_id', "No such service with id {$params['service_id']} exists.");
        }

        $client = $this->findClient($params['client_id']);

        if (! $client) {
            $this->throwValidationException('client_id', 'invalid_client_id', "No such client with id {$params['client_id']} exists.");
        }

        if (isset($params['law_firm_id'])) {
            $lawFirm = $this->findLawFirm($params['law_firm_id']);

            if (! $lawFirm) {
                $this->throwValidationException('law_firm_id', 'invalid_law_firm_id', "No such law firm with id {$params['law_firm_id']} exists.");
            }
        } else {
            $lawFirm = null;
        }

        return [$service, $client, $lawFirm];
    }

    /**
     * @param array $request
     *
     * @return bool
     */
    protected function shouldIncludeAggregate(array $request): bool
    {
        return isset($request['aggregate']) && $request['aggregate'];
    }
}
