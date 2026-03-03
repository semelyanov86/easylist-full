<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiFormatterContract;
use App\Exceptions\AiFormatterException;

final class FakeAiFormatterService implements AiFormatterContract
{
    private string $response = '**Отформатированный текст**';

    private bool $shouldFail = false;

    /**
     * Установить ответ, который вернёт фейк.
     */
    public function withResponse(string $response): self
    {
        $this->response = $response;

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
    public function format(string $text): string
    {
        if ($this->shouldFail) {
            throw AiFormatterException::requestFailed('Сервис недоступен');
        }

        return $this->response;
    }
}
