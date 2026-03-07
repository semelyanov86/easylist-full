<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\ShoppingItemData;
use Illuminate\Http\Request;

/**
 * @property ShoppingItemData $resource
 */
final class ShoppingItemResource extends BaseJsonApiResource
{
    private bool $showListRelationship = false;

    /**
     * Показать связь list в relationships.
     */
    public function withListRelationship(): static
    {
        $this->showListRelationship = true;

        return $this;
    }

    protected function resourceType(): string
    {
        return 'items';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'quantity' => $this->resource->quantity,
            'quantity_type' => $this->resource->quantity_type,
            'price' => $this->resource->price,
            'is_starred' => $this->resource->is_starred,
            'is_done' => $this->resource->is_done,
            'file' => $this->resource->file,
            'shopping_list_id' => $this->resource->shopping_list_id,
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

        if ($this->showListRelationship) {
            $relationships['list'] = $this->toOneRelationship(
                'lists',
                $this->resource->shopping_list_id,
            );
        }

        return $relationships;
    }
}
