<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Трейт для формирования ответов в формате JSON:API.
 */
trait JsonApiResponses
{
    private const string CONTENT_TYPE = 'application/vnd.api+json';

    /**
     * Распарсить параметр include из запроса.
     *
     * @return list<string>
     */
    protected function parseIncludes(Request $request): array
    {
        $include = $request->query('include');

        return is_string($include) && $include !== '' ? explode(',', $include) : [];
    }

    /**
     * Проверить допустимость запрошенных include.
     *
     * @param  list<string>  $includes
     * @param  list<string>  $allowed
     */
    protected function validateIncludes(array $includes, array $allowed): void
    {
        $invalid = array_diff($includes, $allowed);

        if ($invalid !== []) {
            abort(400, 'Недопустимые include: ' . implode(', ', $invalid));
        }
    }

    /**
     * Ответ с одним ресурсом.
     *
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $included
     */
    protected function jsonApiSingle(array $data, array $included = [], int $status = 200): JsonResponse
    {
        $response = ['data' => $data];

        if ($included !== []) {
            $response['included'] = $included;
        }

        return response()
            ->json($response, $status)
            ->header('Content-Type', self::CONTENT_TYPE);
    }

    /**
     * Ответ со списком ресурсов (без пагинации).
     *
     * @param  list<array<string, mixed>>  $data
     * @param  list<array<string, mixed>>  $included
     */
    protected function jsonApiList(array $data, array $included = []): JsonResponse
    {
        $response = ['data' => $data];

        if ($included !== []) {
            $response['included'] = $included;
        }

        return response()
            ->json($response)
            ->header('Content-Type', self::CONTENT_TYPE);
    }

    /**
     * Ответ с пагинированным списком ресурсов.
     *
     * @param  list<array<string, mixed>>  $data
     * @param  list<array<string, mixed>>  $included
     * @phpstan-param  LengthAwarePaginator<int, mixed>  $paginator
     */
    protected function jsonApiPaginated(
        LengthAwarePaginator $paginator,
        array $data,
        array $included = [],
    ): JsonResponse {
        $response = ['data' => $data];

        if ($included !== []) {
            $response['included'] = $included;
        }

        $response['meta'] = [
            'current_page' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ];

        $response['links'] = [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];

        return response()
            ->json($response)
            ->header('Content-Type', self::CONTENT_TYPE);
    }

    /**
     * Ответ 201 Created с ресурсом.
     *
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $included
     */
    protected function jsonApiCreated(array $data, array $included = []): JsonResponse
    {
        return $this->jsonApiSingle($data, $included, 201);
    }

    /**
     * Ответ 204 No Content.
     */
    protected function jsonApiNoContent(): JsonResponse
    {
        /** @var JsonResponse */
        return response()
            ->json(null, 204)
            ->header('Content-Type', self::CONTENT_TYPE);
    }

    /**
     * Получить размер страницы из запроса JSON:API.
     */
    protected function getPageSize(Request $request, int $default = 15, int $max = 100): int
    {
        /** @var string|int $raw */
        $raw = $request->input('page.size', $default);
        $size = (int) $raw;

        return min(max($size, 1), $max);
    }

    /**
     * Получить номер страницы из запроса JSON:API.
     */
    protected function getPageNumber(Request $request): int
    {
        /** @var string|int $raw */
        $raw = $request->input('page.number', 1);

        return max((int) $raw, 1);
    }
}
