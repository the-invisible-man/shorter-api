<?php

namespace App\Http\V1\Controllers;

use App\Http\V1\Exceptions\ForbiddenException;
use App\Http\V1\Exceptions\NotFoundException;
use App\Http\V1\Exceptions\ValidationFailedException;
use App\Http\V1\Requests\Request;
use App\Http\V1\Serializers\Serializer;
use App\Packages\Identity\Entities\Entity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ValidatesRequests {
        ValidatesRequests::validate as originalValidate;
        AuthorizesRequests::authorize as originalAuthorize;
    }

    public const PAGINATION_TOTAL_COUNT_STRATEGY = [
        'WINDOWED' => 1,
        'REDUNDANT' => 2,
    ];

    /**
     * Create a response from a single item.
     *
     * @param mixed                    $item
     * @param Serializer|callable|null $serializer
     * @param int                      $status
     * @param array                    $headers
     *
     * @return JsonResponse
     */
    public function itemResponse(mixed $item, Serializer|callable|null $serializer = null, int $status = 200, array $headers = []): JsonResponse
    {
        if ($serializer) {
            if (is_callable($serializer)) {
                $item = $serializer($item);
            } elseif ($serializer instanceof Serializer) {
                $item = $serializer->data($item);
            }
        }

        return $this->response(['data' => $item], $status, $headers);
    }

    /**
     * Create a response from a paginated collection.
     *
     * @param LengthAwarePaginator $paginator
     * @param Serializer           $serializer
     * @param int                  $status
     * @param array                $headers
     *
     * @return JsonResponse
     */
    public function paginatedResponse(LengthAwarePaginator $paginator, Serializer $serializer, int $status = 200, array $headers = [], array $pageParams = [], array $metadata = []): JsonResponse
    {
        $collection = $paginator->items();

        if (is_callable($serializer)) {
            $items = array_map($serializer, $collection);
        } else {
            $items = array_map([$serializer, 'data'], $collection);
        }

        $nextPageUrl = null;
        $prevPageUrl = null;

        $pageParameters = $this->buildPageParameters($pageParams);

        if ($paginator->hasMorePages()) {
            $nextPageUrl = "{$paginator->nextPageUrl()}&per_page={$paginator->perPage()}";

            if ($pageParameters) {
                $nextPageUrl = "{$nextPageUrl}&{$pageParameters}";
            }
        }

        if ($paginator->currentPage() > 1) {
            $prevPageUrl = "{$paginator->previousPageUrl()}&per_page={$paginator->perPage()}";

            if ($pageParameters) {
                $prevPageUrl = "{$prevPageUrl}&{$pageParameters}";
            }
        }

        $data = [
            'data' => $items,
            'pagination' => [
                'count' => count($paginator->items()),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'previous_url' => $prevPageUrl,
                'next_url' => $nextPageUrl,
            ],
        ];

        if ($metadata) {
            $data['metadata'] = $metadata;
        }

        return $this->response($data, $status, $headers);
    }

    /**
     * @param array $pageParams
     *
     * @return string|null
     */
    public function buildPageParameters(array $pageParams): ?string
    {
        if (isset($pageParams['page'])) {
            unset($pageParams['page']);
        }

        if (isset($pageParams['per_page'])) {
            unset($pageParams['per_page']);
        }

        return http_build_query($pageParams);
    }

    /**
     * Get the paginator for the given Eloquent query.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param int                                                                      $perPage
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($query, int $perPage = 100, int $totalCountMethod = self::PAGINATION_TOTAL_COUNT_STRATEGY['WINDOWED']): LengthAwarePaginator
    {
        $columns = ['*'];
        $pageName = 'page';
        $page = Paginator::resolveCurrentPage($pageName);

        // B/c we are adding a select statement below, we need to manually set the asterisk here
        if (($query instanceof Builder && $query->getQuery()->columns === null)
            || ($query instanceof Relation && $query->getBaseQuery()->columns === null)) {
            $query->select($query->getModel()->getTable().'.*');
        } elseif ($query instanceof \Illuminate\Database\Query\Builder && $query->columns === null) {
            $query->select($columns);
        } elseif ($query instanceof \Illuminate\Database\Query\Builder && $query->getColumns() !== null) {
            $columns = $query->getColumns();
        }

        if ($totalCountMethod === self::PAGINATION_TOTAL_COUNT_STRATEGY['REDUNDANT']) {
            $results = $query->forPage($page, $perPage)
                ->get($columns);

            // With the redundant strategy, we query the DB twice to get the total count
            // Use this when window functions are possible to use
            $total = $query->selectRaw('COUNT(DISTINCT v.client_id, law_firm_id)');
        } else {
            $results = $query
                // trick to get total found rows in the same query
                // https://www.postgresql.org/docs/current/tutorial-window.html
                ->selectRaw('count(*) over() as total_rows')
                ->forPage($page, $perPage)
                ->get($columns);

            $total = $results->first() ? $results->first()->total_rows : 0;
        }

        $results = $total > 0 ? $results : new Collection([]);

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * @param $items
     * @param $total
     * @param $perPage
     * @param $currentPage
     * @param $options
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return \Closure|mixed|object|null
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(\Illuminate\Pagination\LengthAwarePaginator::class, compact(
            'items',
            'total',
            'perPage',
            'currentPage',
            'options'
        ));
    }

    /**
     * Create a response from a collection.
     *
     * @param array|\ArrayIterator $collection
     * @param object|callable      $serializer
     * @param int                  $status
     * @param array                $headers
     *
     * @return JsonResponse
     */
    public function collectionResponse(array|\ArrayIterator $collection, object|callable $serializer, int $status = 200, array $headers = []): JsonResponse
    {
        if (is_callable($serializer)) {
            $items = array_map($serializer, $collection);
        } else {
            $items = array_map([$serializer, 'data'], $collection);
        }

        $data = [
            'data' => $items,
            'pagination' => [
                'total' => count($items),
            ],
        ];

        return $this->response($data, $status, $headers);
    }

    /**
     * Create a no content response.
     *
     * @return Response
     */
    protected function blankResponse(): Response
    {
        return new Response(null, ResponseAlias::HTTP_NO_CONTENT);
    }

    /**
     * Create a JSON response.
     *
     * @param string|array $data
     * @param int          $status
     * @param array        $headers
     *
     * @return JsonResponse
     */
    public function response(string|array $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @param Request $request
     * @param array   $rules
     * @param array   $messages
     * @param array   $customAttributes
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(Request $request, array $rules = [], array $messages = [], array $customAttributes = [])
    {
        if (empty($rules) && empty($messages) && empty($customAttributes)) {
            $validator = $request->getValidatorInstance();

            $this->validateWith($validator, $request);
        } else {
            $this->originalValidate($request, $rules, $messages, $customAttributes);
        }
    }

    /**
     * Get the authenticated entity.
     *
     * @return Entity
     */
    protected function entity(): Entity
    {
        return Auth::client();
    }

    /**
     * Throw a validation failed exception
     *
     * @param string $attribute
     * @param string $type
     * @param string $message
     * @param array  $data
     *
     * @throws ValidationFailedException
     */
    protected function throwValidationException(string $attribute, string $type, string $message, array $data = [])
    {
        $error = [
                'type' => $type,
                'message' => $message,
        ] + $data;

        throw (new ValidationFailedException)->withErrors([$attribute => [$error]]);
    }

    /**
     * Throw a forbidden exception
     *
     * @param null|mixed $message
     */
    protected function throwForbiddenException(?string $message = null)
    {
        throw new ForbiddenException($message);
    }

    /**
     * Throw a not found exception
     *
     * @param string|null $message
     */
    protected function throwNotFoundException(?string $message = null): void
    {
        throw new NotFoundException($message);
    }

    /**
     * @param string $ability
     * @param array  $arguments
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function authorize(string $ability, array $arguments = []): \Illuminate\Auth\Access\Response
    {
        try {
            return $this->originalAuthorize($ability, $arguments);
        } catch (AuthorizationException) {
            throw new ForbiddenException;
        }
    }
}
