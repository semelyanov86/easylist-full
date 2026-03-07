<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\ShoppingListData;
use Illuminate\Http\Request;

/**
 * @property ShoppingListData $resource
 */
final class ShoppingListResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'lists';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'icon' => $this->resource->icon,
            'folder_id' => $this->resource->folder_id,
            'link' => $this->resource->link,
            'is_public' => $this->resource->is_public,
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

        if ($this->resource->folder !== null) {
            $relationships['folder'] = $this->toOneRelationship(
                'folders',
                $this->resource->folder_id,
            );
        }

        if ($this->resource->items !== null) {
            /** @var list<int> $ids */
            $ids = array_map(fn ($item): int => $item->id, $this->resource->items);
            $relationships['items'] = $this->toManyRelationship('items', $ids);
        }

        return $relationships;
    }
}
