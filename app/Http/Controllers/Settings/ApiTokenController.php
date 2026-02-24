<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreApiTokenRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ApiTokenController extends Controller
{
    /**
     * Показать страницу управления API-токенами.
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return Inertia::render('settings/ApiTokens', [
            'tokens' => $user->tokens()->latest()
                ->get()
                ->map(fn ($token) => [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->toISOString(),
                    'created_at' => $token->created_at?->toISOString(),
                ]),
            'newToken' => $request->session()->get('newToken'),
        ]);
    }

    /**
     * Создать новый API-токен.
     */
    public function store(StoreApiTokenRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var string $name */
        $name = $request->validated('name');

        $token = $user->createToken($name);

        return to_route('api-tokens.index')->with('newToken', $token->plainTextToken);
    }

    /**
     * Удалить API-токен.
     */
    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->tokens()->where('id', $tokenId)->delete();

        return to_route('api-tokens.index');
    }
}
