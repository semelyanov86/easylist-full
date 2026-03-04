<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiCompanyAnalyzerContract;

final readonly class AiCompanyAnalyzerService implements AiCompanyAnalyzerContract
{
    public function __construct(
        private AiClientService $client,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function analyze(string $companyName, ?string $city): array
    {
        $location = $city !== null && $city !== '' ? ", {$city}" : '';

        return $this->client->send("/company {$companyName}{$location}");
    }
}
