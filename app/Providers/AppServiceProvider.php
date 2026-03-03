<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AiFormatterContract;
use App\Contracts\AiTagExtractorContract;
use App\Services\AiClientService;
use App\Services\AiFormatterService;
use App\Services\AiTagExtractorService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(function (): \App\Services\AiClientService {
            /** @var string $url */
            $url = config('services.ai_formatter.url');

            /** @var string $token */
            $token = config('services.ai_formatter.token');

            return new AiClientService(
                url: $url,
                token: $token,
            );
        });

        $this->app->singleton(AiFormatterContract::class, fn (): AiFormatterContract => new AiFormatterService(
            client: $this->app->make(AiClientService::class),
        ));

        $this->app->singleton(AiTagExtractorContract::class, fn (): AiTagExtractorContract => new AiTagExtractorService(
            client: $this->app->make(AiClientService::class),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
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
