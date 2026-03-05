<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiContactFinderContract;

final readonly class AiContactFinderService implements AiContactFinderContract
{
    public function __construct(
        private AiClientService $client,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function find(string $companyName, ?string $city): array
    {
        $location = $city !== null && $city !== '' ? ", {$city}" : '';

        return $this->client->send("/hiring-contact-finder {$companyName}{$location}");
    }
}
