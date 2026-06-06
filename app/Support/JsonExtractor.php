<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\AiFormatterException;

/**
 * Устойчивый разбор JSON из текстового ответа LLM.
 *
 * Снимает markdown-ограждения и при необходимости вырезает первый
 * сбалансированный JSON-блок ({...} или [...]) из ответа, в котором
 * модель добавила лишний текст вокруг.
 */
final class JsonExtractor
{
    /**
     * Декодировать JSON-ответ модели в массив.
     *
     * @return array<int|string, mixed>
     *
     * @throws AiFormatterException
     */
    public static function decode(string $content): array
    {
        $normalized = self::stripFences($content);

        $decoded = json_decode($normalized, true);

        if (! is_array($decoded)) {
            $extracted = self::extractJsonBlock($normalized);

            if ($extracted !== null) {
                $decoded = json_decode($extracted, true);
            }
        }

        if (! is_array($decoded)) {
            throw AiFormatterException::requestFailed(
                'Не удалось разобрать JSON в ответе модели'
            );
        }

        return $decoded;
    }

    /**
     * Снять обрамляющие markdown-ограждения (```json ... ``` / ```latex ... ```).
     */
    public static function stripFences(string $content): string
    {
        $content = trim($content);

        if (! str_starts_with($content, '```')) {
            return $content;
        }

        $content = preg_replace('/^```[a-zA-Z]*\s*/', '', $content) ?? $content;
        $content = preg_replace('/\s*```\s*$/', '', $content) ?? $content;

        return trim($content);
    }

    /**
     * Вырезать первый сбалансированный JSON-блок из строки.
     *
     * Учитывает вложенность скобок и игнорирует скобки внутри строк,
     * поэтому корректно работает даже если модель добавила текст после JSON.
     */
    private static function extractJsonBlock(string $content): ?string
    {
        $start = null;
        $opener = null;

        foreach (['{', '['] as $char) {
            $pos = strpos($content, $char);

            if ($pos !== false && ($start === null || $pos < $start)) {
                $start = $pos;
                $opener = $char;
            }
        }

        if ($start === null) {
            return null;
        }

        $closer = $opener === '{' ? '}' : ']';
        $depth = 0;
        $inString = false;
        $escaped = false;
        $length = strlen($content);

        for ($i = $start; $i < $length; $i++) {
            $char = $content[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
            } elseif ($char === $opener) {
                $depth++;
            } elseif ($char === $closer) {
                $depth--;

                if ($depth === 0) {
                    return substr($content, $start, $i - $start + 1);
                }
            }
        }

        return null;
    }
}
