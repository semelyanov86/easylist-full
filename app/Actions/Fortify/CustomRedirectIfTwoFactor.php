<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\TwoFactorMethod;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Расширение стандартного Fortify pipeline: учитывает и TOTP, и WebAuthn credentials.
 */
class CustomRedirectIfTwoFactor extends RedirectIfTwoFactorAuthenticatable
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     */
    #[\Override]
    public function handle($request, $next): mixed
    {
        $user = $this->validateCredentials($request);

        if ($this->userRequiresTwoFactor($user)) {
            $methods = $this->getAvailableMethods($user);

            $request->session()->put('login.2fa_methods', array_map(
                static fn (TwoFactorMethod $method): string => $method->value,
                $methods,
            ));

            return $this->twoFactorChallengeResponse($request, $user);
        }

        return $next($request);
    }

    /**
     * Проверяет, нужна ли пользователю двухфакторная аутентификация.
     */
    private function userRequiresTwoFactor(mixed $user): bool
    {
        if ($user === null) {
            return false;
        }

        $hasTotpEnabled = $this->hasTotpEnabled($user);
        $hasWebAuthnCredentials = $this->hasWebAuthnCredentials($user);

        return $hasTotpEnabled || $hasWebAuthnCredentials;
    }

    /**
     * Определяет доступные методы 2FA для пользователя.
     *
     * @return list<TwoFactorMethod>
     */
    private function getAvailableMethods(mixed $user): array
    {
        $methods = [];

        if ($this->hasTotpEnabled($user)) {
            $methods[] = TwoFactorMethod::Totp;
        }

        if ($this->hasWebAuthnCredentials($user)) {
            $methods[] = TwoFactorMethod::WebAuthn;
        }

        return $methods;
    }

    /**
     * Проверяет, включён ли TOTP у пользователя.
     */
    private function hasTotpEnabled(mixed $user): bool
    {
        if (! is_object($user)) {
            return false;
        }

        if (! in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user), true)) {
            return false;
        }

        /** @var \App\Models\User $user */
        $hasSecret = ! empty($user->two_factor_secret);
        $isConfirmed = Fortify::confirmsTwoFactorAuthentication()
            ? $user->two_factor_confirmed_at !== null
            : true;

        return $hasSecret && $isConfirmed;
    }

    /**
     * Проверяет, есть ли активные WebAuthn credentials у пользователя.
     */
    private function hasWebAuthnCredentials(mixed $user): bool
    {
        if (! $user instanceof WebAuthnAuthenticatable) {
            return false;
        }

        return $user->webAuthnCredentials()->whereNull('disabled_at')->exists();
    }
}
