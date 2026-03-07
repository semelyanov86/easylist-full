<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\JobCategoryData;
use Illuminate\Http\Request;

/**
 * @property JobCategoryData $resource
 */
final class JobCategoryResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'job-categories';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'currency' => $this->resource->currency->value,
            'currency_symbol' => $this->resource->currency_symbol,
            'order_column' => $this->resource->order_column,
        ];
    }
}
