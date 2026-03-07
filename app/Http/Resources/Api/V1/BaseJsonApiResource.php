<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Базовый ресурс для формата JSON:API.
 */
abstract class BaseJsonApiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        $data = [
            'type' => $this->resourceType(),
            'id' => $this->resourceId(),
            'attributes' => $this->resourceAttributes($request),
        ];

        $relationships = $this->resourceRelationships($request);
        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return $data;
    }

    /**
     * Идентификатор ресурса.
     */
    protected function resourceId(): string
    {
        /** @var object{id: int|string} $resource */
        $resource = $this->resource;

        return (string) $resource->id;
    }

    /**
     * Связи ресурса (linkage data).
     *
     * @return array<string, mixed>
     */
    protected function resourceRelationships(Request $request): array
    {
        return [];
    }

    /**
     * Построить linkage для связи to-one.
     *
     * @return array{data: array{type: string, id: string}|null}
     */
    protected function toOneRelationship(string $type, int|string|null $id): array
    {
        return [
            'data' => $id !== null
                ? ['type' => $type, 'id' => (string) $id]
                : null,
        ];
    }

    /**
     * Построить linkage для связи to-many.
     *
     * @param  list<int|string>  $ids
     * @return array{data: list<array{type: string, id: string}>}
     */
    protected function toManyRelationship(string $type, array $ids): array
    {
        return [
            'data' => array_map(
                fn (int|string $id): array => ['type' => $type, 'id' => (string) $id],
                $ids,
            ),
        ];
    }

    /**
     * Тип ресурса в JSON:API.
     */
    abstract protected function resourceType(): string;

    /**
     * Атрибуты ресурса.
     *
     * @return array<string, mixed>
     */
    abstract protected function resourceAttributes(Request $request): array;
}
