<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case Rub = 'rub';
    case Usd = 'usd';
    case Eur = 'eur';

    /**
     * Список всех строковых значений.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Русская подпись для UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Rub => 'Рубль (₽)',
            self::Usd => 'Доллар ($)',
            self::Eur => 'Евро (€)',
        };
    }

    /**
     * Символ валюты.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::Rub => '₽',
            self::Usd => '$',
            self::Eur => '€',
        };
    }
}
