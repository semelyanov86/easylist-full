<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class AiFormatterException extends RuntimeException
{
    public static function requestFailed(string $reason): self
    {
        return new self("Ошибка ИИ-форматирования: {$reason}");
    }

    public static function tagExtractionFailed(string $reason): self
    {
        return new self("Ошибка извлечения тегов: {$reason}");
    }
}
