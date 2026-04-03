<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Ai\FormatTextAction;
use App\Exceptions\AiFormatterException;
use App\Http\Requests\AiFormatRequest;
use Illuminate\Http\JsonResponse;
use App\Models\User;

final class AiFormatController extends Controller
{
    /**
     * Отформатировать текст через ИИ-сервис.
     */
    public function __invoke(AiFormatRequest $request, FormatTextAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->is_premium) {
            abort(403, 'Данная функция доступна только для Premium аккаунтов');
        }

        try {
            /** @var string $text */
            $text = $request->validated('text');

            $formatted = $action->execute($text);

            return response()->json(['formatted' => $formatted]);
        } catch (AiFormatterException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                502,
            );
        }
    }
}
