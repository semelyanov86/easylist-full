<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use Symfony\Component\HttpFoundation\Response;

class WebAuthnChallengeController extends Controller
{
    /**
     * Возвращает challenge для аутентификации через WebAuthn.
     */
    public function challenge(AssertionRequest $request): Response
    {
        $user = $this->resolveUser($request);

        return $request->toVerify($user)->toResponse($request);
    }

    /**
     * Верифицирует assertion и логинит пользователя.
     */
    public function verify(AssertedRequest $request): JsonResponse
    {
        $user = $request->login(
            remember: (bool) $request->session()->pull('login.remember', false),
        );

        if ($user === null) {
            return response()->json(['message' => 'Не удалось выполнить аутентификацию.'], 422);
        }

        // Очищаем session-ключи login flow
        $request->session()->forget(['login.id', 'login.remember', 'login.2fa_methods']);

        $redirectUrl = $request->session()->pull('url.intended', '/dashboard');

        return response()->json([
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Определяет пользователя из login flow (session) или текущей аутентификации.
     */
    private function resolveUser(Request $request): ?User
    {
        // Login flow: пользователь ещё не залогинен, ID в session
        /** @var int|string|null $loginId */
        $loginId = $request->session()->get('login.id');

        if ($loginId !== null) {
            return User::find($loginId);
        }

        // Re-validation flow: пользователь уже залогинен
        /** @var User|null */
        return Auth::user();
    }
}
