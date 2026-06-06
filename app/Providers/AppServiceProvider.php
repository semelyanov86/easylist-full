<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AiCompanyAnalyzerContract;
use App\Contracts\AiContactFinderContract;
use App\Contracts\AiFormatterContract;
use App\Contracts\AiTagExtractorContract;
use App\Models\User;
use App\Services\AiClientService;
use App\Services\AiCompanyAnalyzerService;
use App\Services\AiContactFinderService;
use App\Services\AiCoverLetterService;
use App\Services\AiFormatterService;
use App\Services\AiTagExtractorService;
use App\Services\Polza\PolzaChatClient;
use App\Services\Polza\PolzaCompanyAnalyzerService;
use App\Services\Polza\PolzaContactFinderService;
use App\Services\Polza\PolzaCoverLetterService;
use App\Services\Polza\PolzaFormatterService;
use App\Services\Polza\PolzaTagExtractorService;
use App\Services\Polza\PromptRepository;
use App\Services\TickTickClientService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Pulse\Facades\Pulse;
use App\Contracts\AiCoverLetterContract;
use App\Contracts\TickTickClientContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->registerAiTransports();
        $this->registerAiProvider();
        $this->registerTickTick();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        Pulse::user(fn (User $user) => [
            'name' => $user->name,
            'extra' => $user->email,
        ]);
        Gate::define('viewPulse', fn (User $user) => $user->email === 'se@sergeyem.ru');
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    /**
     * Зарегистрировать общие транспорты ИИ-провайдеров.
     */
    private function registerAiTransports(): void
    {
        $this->app->singleton(function (): AiClientService {
            /** @var string $url */
            $url = config('ai.claude.url');

            /** @var string $token */
            $token = config('ai.claude.token');

            /** @var int $timeout */
            $timeout = config('ai.claude.timeout');

            return new AiClientService(url: $url, token: $token, timeout: $timeout);
        });

        $this->app->singleton(function (): PolzaChatClient {
            /** @var string $apiKey */
            $apiKey = config('ai.polza.api_key');

            /** @var string $baseUrl */
            $baseUrl = config('ai.polza.base_url');

            /** @var int $timeout */
            $timeout = config('ai.polza.timeout');

            return new PolzaChatClient(apiKey: $apiKey, baseUrl: $baseUrl, timeout: $timeout);
        });

        $this->app->singleton(PromptRepository::class, fn (): PromptRepository => new PromptRepository());
    }

    /**
     * Привязать реализации ИИ-контрактов к выбранному провайдеру.
     */
    private function registerAiProvider(): void
    {
        /** @var string $provider */
        $provider = config('ai.provider', 'polza');

        if ($provider === 'polza') {
            $this->bindPolzaProvider();

            return;
        }

        $this->bindClaudeProvider();
    }

    /**
     * Привязки провайдера polza.ai (промпты формируются в приложении).
     */
    private function bindPolzaProvider(): void
    {
        $this->app->singleton(AiFormatterContract::class, fn (): AiFormatterContract => new PolzaFormatterService(
            $this->app->make(PolzaChatClient::class),
            $this->app->make(PromptRepository::class),
        ));

        $this->app->singleton(AiTagExtractorContract::class, fn (): AiTagExtractorContract => new PolzaTagExtractorService(
            $this->app->make(PolzaChatClient::class),
            $this->app->make(PromptRepository::class),
        ));

        $this->app->singleton(AiCompanyAnalyzerContract::class, fn (): AiCompanyAnalyzerContract => new PolzaCompanyAnalyzerService(
            $this->app->make(PolzaChatClient::class),
            $this->app->make(PromptRepository::class),
        ));

        $this->app->singleton(AiContactFinderContract::class, fn (): AiContactFinderContract => new PolzaContactFinderService(
            $this->app->make(PolzaChatClient::class),
            $this->app->make(PromptRepository::class),
        ));

        $this->app->singleton(AiCoverLetterContract::class, fn (): AiCoverLetterContract => new PolzaCoverLetterService(
            $this->app->make(PolzaChatClient::class),
            $this->app->make(PromptRepository::class),
        ));
    }

    /**
     * Привязки прежнего провайдера (skill-сервер ask.sergeyem.ru).
     */
    private function bindClaudeProvider(): void
    {
        $this->app->singleton(AiFormatterContract::class, fn (): AiFormatterContract => new AiFormatterService(
            client: $this->app->make(AiClientService::class),
        ));

        $this->app->singleton(AiTagExtractorContract::class, fn (): AiTagExtractorContract => new AiTagExtractorService(
            client: $this->app->make(AiClientService::class),
        ));

        $this->app->singleton(AiCompanyAnalyzerContract::class, fn (): AiCompanyAnalyzerContract => new AiCompanyAnalyzerService(
            client: $this->app->make(AiClientService::class),
        ));

        $this->app->singleton(AiContactFinderContract::class, fn (): AiContactFinderContract => new AiContactFinderService(
            client: $this->app->make(AiClientService::class),
        ));

        $this->app->singleton(function (): AiCoverLetterContract {
            /** @var string $url */
            $url = config('ai.claude.url');

            /** @var string $token */
            $token = config('ai.claude.token');

            /** @var int $timeout */
            $timeout = config('ai.claude.timeout');

            /** @var string $baseUrl */
            $baseUrl = config('ai.claude.base_url');

            return new AiCoverLetterService(url: $url, token: $token, timeout: $timeout, baseUrl: $baseUrl);
        });
    }

    /**
     * Регистрация клиента TickTick.
     */
    private function registerTickTick(): void
    {
        $this->app->singleton(function (): TickTickClientContract {
            /** @var string $baseUrl */
            $baseUrl = config('services.ticktick.base_url');

            /** @var int $timeout */
            $timeout = config('services.ticktick.timeout');

            return new TickTickClientService(
                baseUrl: $baseUrl,
                timeout: $timeout,
            );
        });
    }
}
