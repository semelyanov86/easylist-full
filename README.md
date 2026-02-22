# Easylist

Сервис для соискателей: учёт откликов на вакансии, отслеживание статусов и ведение пользовательских списков.

## Стек

| Слой | Технологии |
|------|------------|
| Backend | PHP 8.4, Laravel 12, Laravel Fortify |
| Frontend | Vue 3, TypeScript, Inertia.js v2, Tailwind CSS v4 |
| Сборка | Vite 7, SSR |
| Маршрутизация | Laravel Wayfinder (type-safe TS-функции для роутов) |
| Качество кода | PHPStan (max), ESLint + FSD-плагин, Prettier, Pint, Rector |
| Тесты | PHPUnit 13 |
| Production | Laravel Octane (Swoole), Supervisor, systemd |

## Архитектура

**Backend** — Domain-Driven Design (DDD): тонкие контроллеры, бизнес-логика в отдельных классах, Eloquent-модели только для инфраструктуры.

**Frontend** — Feature-Sliced Design (FSD):

```
resources/js/
├── app/           # Инициализация приложения
├── pages/         # Страницы (Inertia)
├── widgets/       # Сложные UI-блоки (AppShell, Sidebar, Header)
├── features/      # Пользовательские сценарии (auth, settings, two-factor)
├── entities/      # Бизнес-сущности (user)
└── shared/        # UI-компоненты, composables, утилиты, типы
```

Импорты строго по иерархии: `pages` → `widgets` → `features` → `entities` → `shared`.

## Требования

- PHP >= 8.4
- Node.js >= 20
- Composer
- SQLite / MySQL / PostgreSQL

## Установка

```bash
git clone <repo-url> easylist
cd easylist

# Установка зависимостей
composer install
npm install

# Конфигурация
cp .env.example .env
php artisan key:generate

# База данных
php artisan migrate --seed

# Wayfinder (генерация TS-функций для роутов)
php artisan wayfinder:generate

# Сборка фронтенда
npm run build
```

Или через Task-runner:

```bash
task install
```

## Разработка

```bash
# Запуск dev-сервера (Vite + Laravel)
composer run dev

# Или по отдельности
php artisan serve
npm run dev
```

## Команды качества

| Команда | Описание |
|---------|----------|
| `task phpstan` | Статический анализ (уровень max) |
| `task rector` | Проверка мёртвого кода |
| `task type-check` | Проверка TypeScript (`vue-tsc --noEmit`) |
| `task test` | Запуск тестов |
| `task lint` | ESLint |
| `task fixcs` | Форматирование PHP (Pint) |
| `task all` | Все проверки + тесты |

## Тестирование

```bash
# Все тесты
php artisan test --compact

# Конкретный файл
php artisan test --compact tests/Feature/Auth/LoginTest.php

# По имени
php artisan test --compact --filter=testLoginScreenCanBeRendered
```

## Деплой

```bash
task deploy
```

Используются systemd-сервисы и Supervisor для очередей.

## Лицензия

Проприетарная. Все права защищены.
