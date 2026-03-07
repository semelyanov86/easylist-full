<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\JobDocumentData;
use Illuminate\Http\Request;

/**
 * @property JobDocumentData $resource
 */
final class JobDocumentResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'documents';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'category' => $this->resource->category,
            'category_label' => $this->resource->category_label,
            'original_filename' => $this->resource->original_filename,
            'mime_type' => $this->resource->mime_type,
            'file_size' => $this->resource->file_size,
            'external_url' => $this->resource->external_url,
            'author_name' => $this->resource->author_name,
            'created_at' => $this->resource->created_at,
        ];
    }
}
