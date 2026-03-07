<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\JobStatusData;
use Illuminate\Http\Request;

/**
 * @property JobStatusData $resource
 */
final class JobStatusResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'job-statuses';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'color' => $this->resource->color->value,
            'order_column' => $this->resource->order_column,
        ];
    }
}
