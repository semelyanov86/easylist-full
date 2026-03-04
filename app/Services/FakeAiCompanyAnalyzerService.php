<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiCompanyAnalyzerContract;
use App\Exceptions\AiFormatterException;

final class FakeAiCompanyAnalyzerService implements AiCompanyAnalyzerContract
{
    /** @var array<string, mixed> */
    private array $response = [
        'overview' => 'Тестовая компания',
        'industry' => 'IT',
        'founded' => '2020',
        'employees' => '~100',
        'hq' => 'Берлин',
        'tech_stack' => ['PHP', 'Laravel'],
        'links' => ['website' => 'https://example.com'],
    ];

    private bool $shouldFail = false;

    /**
     * Установить ответ, который вернёт фейк.
     *
     * @param  array<string, mixed>  $data
     */
    public function withResponse(array $data): self
    {
        $this->response = $data;

        return $this;
    }

    /**
     * Заставить фейк выбросить исключение.
     */
    public function shouldFail(): self
    {
        $this->shouldFail = true;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function analyze(string $companyName, ?string $city): array
    {
        if ($this->shouldFail) {
            throw AiFormatterException::requestFailed('Сервис недоступен');
        }

        return $this->response;
    }
}
