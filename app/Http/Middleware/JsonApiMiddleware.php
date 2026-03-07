<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для формата JSON:API.
 *
 * Устанавливает заголовки Content-Type и Accept,
 * трансформирует ошибки валидации в формат JSON:API.
 */
final class JsonApiMiddleware
{
    private const string CONTENT_TYPE = 'application/vnd.api+json';

    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        /** @var Response $response */
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->headers->set('Content-Type', self::CONTENT_TYPE);

            if ($response->getStatusCode() === 422) {
                $this->transformValidationErrors($response);
            }
        }

        return $response;
    }

    /**
     * Трансформировать ошибки валидации Laravel в формат JSON:API.
     */
    private function transformValidationErrors(JsonResponse $response): void
    {
        /** @var array<string, mixed> $data */
        $data = $response->getData(true);

        if (! isset($data['errors']) || ! is_array($data['errors'])) {
            return;
        }

        $jsonApiErrors = [];

        /** @var array<string, list<string>> $errors */
        $errors = $data['errors'];

        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                $jsonApiErrors[] = [
                    'status' => '422',
                    'title' => 'Ошибка валидации',
                    'detail' => $message,
                    'source' => ['pointer' => "/data/attributes/{$field}"],
                ];
            }
        }

        $response->setData(['errors' => $jsonApiErrors]);
    }
}
