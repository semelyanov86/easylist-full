<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\FolderData;
use Illuminate\Http\Request;

/**
 * @property FolderData $resource
 */
final class FolderResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'folders';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'icon' => $this->resource->icon,
            'order_column' => $this->resource->order_column,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->resource->lists !== null) {
            /** @var list<int> $ids */
            $ids = array_map(fn ($list): int => $list->id, $this->resource->lists);
            $relationships['lists'] = $this->toManyRelationship('lists', $ids);
        }

        return $relationships;
    }
}
