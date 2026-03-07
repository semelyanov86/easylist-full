<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\CompanyInfoData;
use Illuminate\Http\Request;

/**
 * @property CompanyInfoData $resource
 */
final class CompanyInfoResource extends BaseJsonApiResource
{
    protected function resourceType(): string
    {
        return 'company-infos';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'city' => $this->resource->city,
            'info' => $this->resource->info,
        ];
    }
}
