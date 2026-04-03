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
        $this->app->singleton(function (): AiClientService {
            /** @var string $url */
            $url = config('services.ai_formatter.url');

            /** @var string $token */
            $token = config('services.ai_formatter.token');

            /** @var int $timeout */
            $timeout = config('services.ai_formatter.timeout');

            return new AiClientService(
                url: $url,
                token: $token,
                timeout: $timeout,
            );
        });

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
            $url = config('services.ai_formatter.url');

            /** @var string $token */
            $token = config('services.ai_formatter.token');

            /** @var int $timeout */
            $timeout = config('services.ai_formatter.timeout');

            return new AiCoverLetterService(
                url: $url,
                token: $token,
                timeout: $timeout,
                baseUrl: 'https://ask.sergeyem.ru',
            );
        });

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
}
