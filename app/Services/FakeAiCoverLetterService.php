<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiCoverLetterContract;
use App\Exceptions\AiFormatterException;

final class FakeAiCoverLetterService implements AiCoverLetterContract
{
    private string $response = '\documentclass{article}\begin{document}Fake Cover Letter\end{document}';

    private bool $shouldFail = false;

    /**
     * Установить ответ, который вернёт фейк.
     */
    public function withResponse(string $content): self
    {
        $this->response = $content;

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
    public function generate(string $prompt): string
    {
        if ($this->shouldFail) {
            throw AiFormatterException::requestFailed('Сервис недоступен');
        }

        return $this->response;
    }
}
