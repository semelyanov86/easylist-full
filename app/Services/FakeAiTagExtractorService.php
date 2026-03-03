<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiTagExtractorContract;
use App\Exceptions\AiFormatterException;

final class FakeAiTagExtractorService implements AiTagExtractorContract
{
    /** @var list<string> */
    private array $response = ['PHP', 'Laravel', 'Vue.js'];

    private bool $shouldFail = false;

    /**
     * Установить ответ, который вернёт фейк.
     *
     * @param  list<string>  $tags
     */
    public function withResponse(array $tags): self
    {
        $this->response = $tags;

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
    public function extract(array $context): array
    {
        if ($this->shouldFail) {
            throw AiFormatterException::tagExtractionFailed('Сервис недоступен');
        }

        return $this->response;
    }
}
