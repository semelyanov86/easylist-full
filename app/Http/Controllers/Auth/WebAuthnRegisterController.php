<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;
use Symfony\Component\HttpFoundation\Response;

class WebAuthnRegisterController extends Controller
{
    /**
     * Возвращает challenge для регистрации нового ключа.
     */
    public function challenge(AttestationRequest $request): Response
    {
        return $request->toCreate()->toResponse($request);
    }

    /**
     * Сохраняет новый WebAuthn credential.
     */
    public function store(AttestedRequest $request): JsonResponse
    {
        $credentialId = $request->save([
            'alias' => $request->input('alias'),
        ]);

        return response()->json([
            'id' => $credentialId,
            'alias' => $request->input('alias'),
        ], 201);
    }

    /**
     * Удаляет WebAuthn credential текущего пользователя.
     */
    public function destroy(Request $request, string $credentialId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $deleted = $user->webAuthnCredentials()
            ->where('id', $credentialId)
            ->delete();

        if ($deleted === 0) {
            return response()->json(['message' => 'Ключ не найден.'], 404);
        }

        return response()->json(['message' => 'Ключ удалён.']);
    }
}
