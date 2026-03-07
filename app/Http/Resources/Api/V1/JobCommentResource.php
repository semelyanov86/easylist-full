<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\JobCommentData;
use Illuminate\Http\Request;

/**
 * @property JobCommentData $resource
 */
final class JobCommentResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'comments';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'body' => $this->resource->body,
            'author_name' => $this->resource->author_name,
            'user_id' => $this->resource->user_id,
            'created_at' => $this->resource->created_at,
        ];
    }
}
