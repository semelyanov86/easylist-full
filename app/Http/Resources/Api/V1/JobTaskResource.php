<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\JobTaskData;
use Illuminate\Http\Request;

/**
 * @property JobTaskData $resource
 */
final class JobTaskResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'tasks';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'title' => $this->resource->title,
            'external_id' => $this->resource->external_id,
            'deadline' => $this->resource->deadline,
            'completed_at' => $this->resource->completed_at,
            'created_at' => $this->resource->created_at,
        ];
    }
}
