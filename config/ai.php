<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Провайдер ИИ по умолчанию
    |--------------------------------------------------------------------------
    |
    | Поддерживаемые значения: "polza", "claude".
    | "polza" — прямые запросы к polza.ai (OpenAI-совместимый API), промпты
    | формируются в приложении. "claude" — старый skill-сервер ask.sergeyem.ru.
    |
    */

    'provider' => env('AI_PROVIDER', 'polza'),

    /*
    |--------------------------------------------------------------------------
    | Polza.ai
    |--------------------------------------------------------------------------
    |
    | OpenAI-совместимый агрегатор. Для каждой задачи задаётся своя модель,
    | флаг веб-поиска (plugins:[{id:"web"}]) и температура. Структурированный
    | JSON обеспечивается промптом + App\Support\JsonExtractor (без response_format),
    | чтобы оставаться совместимым с любой моделью. Любую модель можно
    | переопределить через .env.
    |
    */

    'polza' => [
        'api_key' => env('POLZA_API_KEY', ''),
        'base_url' => env('POLZA_BASE_URL', 'https://polza.ai/api/v1'),
        'timeout' => (int) env('POLZA_TIMEOUT', 600),

        'tasks' => [
            'format' => [
                'model' => env('POLZA_MODEL_FORMAT', 'google/gemini-3.1-flash-lite'),
                'web' => false,
                'temperature' => 0.2,
            ],
            'tags' => [
                'model' => env('POLZA_MODEL_TAGS', 'google/gemini-3.1-flash-lite'),
                'web' => false,
                'temperature' => 0.0,
            ],
            'company' => [
                'model' => env('POLZA_MODEL_COMPANY', 'x-ai/grok-4.3'),
                'web' => true,
                'temperature' => 0.2,
            ],
            'contacts' => [
                'model' => env('POLZA_MODEL_CONTACTS', 'x-ai/grok-4.3'),
                'web' => true,
                'temperature' => 0.2,
            ],
            'cover_letter' => [
                'model' => env('POLZA_MODEL_COVER_LETTER', 'google/gemini-3.1-pro-preview'),
                'web' => false,
                'temperature' => 0.6,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Claude skill-сервер (ask.sergeyem.ru)
    |--------------------------------------------------------------------------
    |
    | Прежний провайдер: приложение отправляет slash-команду, промпты живут
    | в скиллах на сервере. Оставлен для возможности переключения обратно.
    |
    */

    'claude' => [
        'url' => env('AI_FORMATTER_URL', 'https://ask.sergeyem.ru/api/claude/json'),
        'token' => env('AI_FORMATTER_TOKEN', ''),
        'timeout' => (int) env('AI_FORMATTER_TIMEOUT', 600),
        'base_url' => env('AI_CLAUDE_BASE_URL', 'https://ask.sergeyem.ru'),
    ],

];
