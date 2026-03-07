<?php

declare(strict_types=1);

namespace App\Http\Traits;

/**
 * Извлечение атрибутов из JSON:API запроса.
 *
 * Валидация работает по ключам data.attributes.*, а этот метод
 * возвращает плоский массив атрибутов для передачи в Action.
 */
trait ExtractsJsonApiAttributes
{
    /**
     * Получить валидированные атрибуты из data.attributes.
     *
     * @return array<string, mixed>
     */
    public function validatedAttributes(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        /** @var array{attributes?: array<string, mixed>} $data */
        $data = $validated['data'] ?? [];

        return $data['attributes'] ?? [];
    }
}
