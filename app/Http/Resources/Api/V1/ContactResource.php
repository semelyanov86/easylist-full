<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\ContactData;
use Illuminate\Http\Request;

/**
 * @property ContactData $resource
 */
final class ContactResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'contacts';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'position' => $this->resource->position,
            'city' => $this->resource->city,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'description' => $this->resource->description,
            'linkedin_url' => $this->resource->linkedin_url,
            'facebook_url' => $this->resource->facebook_url,
            'whatsapp_url' => $this->resource->whatsapp_url,
            'created_at' => $this->resource->created_at,
        ];
    }
}
