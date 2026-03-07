<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\JobPublicViewData;
use App\Data\PublicContactData;
use App\Data\SkillData;
use Illuminate\Http\Request;

/**
 * @property JobPublicViewData $resource
 */
final class JobPublicResource extends BaseJsonApiResource
{
    /**
     * Построить included ресурсы.
     *
     * @return list<array<string, mixed>>
     */
    public function buildIncluded(Request $request): array
    {
        $included = [];

        foreach ($this->resource->skills as $skill) {
            $included[] = [
                'type' => 'skills',
                'id' => (string) $skill->id,
                'attributes' => ['title' => $skill->title],
            ];
        }

        foreach ($this->resource->contacts as $i => $contact) {
            $included[] = [
                'type' => 'contacts',
                'id' => (string) $i,
                'attributes' => [
                    'first_name' => $contact->first_name,
                    'last_name' => $contact->last_name,
                    'position' => $contact->position,
                    'city' => $contact->city,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                    'linkedin_url' => $contact->linkedin_url,
                ],
            ];
        }

        if ($this->resource->company_info !== null) {
            $included[] = [
                'type' => 'company-infos',
                'id' => '0',
                'attributes' => $this->resource->company_info->toArray(),
            ];
        }

        return $included;
    }

    protected function resourceType(): string
    {
        return 'jobs';
    }

    #[\Override]
    protected function resourceId(): string
    {
        return '0';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'title' => $this->resource->title,
            'company_name' => $this->resource->company_name,
            'description' => $this->resource->description,
            'location_city' => $this->resource->location_city,
            'salary' => $this->resource->salary,
            'job_url' => $this->resource->job_url,
            'currency_symbol' => $this->resource->currency_symbol,
            'created_at' => $this->resource->created_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->resource->skills !== []) {
            $relationships['skills'] = $this->toManyRelationship(
                'skills',
                array_map(fn (SkillData $s): int => $s->id, $this->resource->skills),
            );
        }

        if ($this->resource->contacts !== []) {
            $relationships['contacts'] = [
                'data' => array_map(
                    fn (PublicContactData $c, int $i): array => ['type' => 'contacts', 'id' => (string) $i],
                    $this->resource->contacts,
                    array_keys($this->resource->contacts),
                ),
            ];
        }

        return $relationships;
    }
}
