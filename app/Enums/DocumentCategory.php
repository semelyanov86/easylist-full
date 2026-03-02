<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentCategory: string
{
    case Resume = 'resume';
    case Portfolio = 'portfolio';
    case Recommendation = 'recommendation';
    case Article = 'article';
    case Certificate = 'certificate';
    case Other = 'other';

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
            self::Resume => 'Резюме',
            self::Portfolio => 'Портфолио',
            self::Recommendation => 'Рекомендация',
            self::Article => 'Статья',
            self::Certificate => 'Сертификат',
            self::Other => 'Прочее',
        };
    }
}
