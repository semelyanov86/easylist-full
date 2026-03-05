<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiContactFinderContract;
use App\Exceptions\AiFormatterException;

final class FakeAiContactFinderService implements AiContactFinderContract
{
    /** @var array<int|string, mixed> */
    private array $response = [
        [
            'first_name' => 'Анна',
            'last_name' => 'Иванова',
            'position' => 'HR-менеджер',
            'city' => 'Берлин',
            'email' => 'anna@example.com',
            'phone' => '+49 170 1234567',
            'description' => 'Отвечает за подбор IT-специалистов',
            'linkedin_url' => 'https://linkedin.com/in/anna-ivanova',
            'whatsapp_url' => null,
        ],
    ];

    private bool $shouldFail = false;

    /**
     * Установить ответ, который вернёт фейк.
     *
     * @param  array<int|string, mixed>  $data
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
    public function find(string $companyName, ?string $city): array
    {
        if ($this->shouldFail) {
            throw AiFormatterException::requestFailed('Сервис недоступен');
        }

        return $this->response;
    }
}
