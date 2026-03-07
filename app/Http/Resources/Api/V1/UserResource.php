<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\UserData;
use Illuminate\Http\Request;

/**
 * @property UserData $resource
 */
final class UserResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'users';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'is_premium' => $this->resource->is_premium,
            'about_me' => $this->resource->about_me,
            'created_at' => $this->resource->created_at,
        ];
    }
}
