<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Ai\ExtractJobTagsAction;
use App\Exceptions\AiFormatterException;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

final class AiExtractJobTagsController extends Controller
{
    /**
     * Извлечь теги навыков из вакансии с помощью ИИ.
     */
    public function __invoke(Request $request, Job $job, ExtractJobTagsAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($job->user_id !== $user->id) {
            abort(403);
        }

        if (! $user->is_premium) {
            abort(403, 'Данная функция доступна только для Premium аккаунтов');
        }

        try {
            $skills = $action->execute($user, $job);

            return response()->json(['skills' => $skills]);
        } catch (AiFormatterException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                502,
            );
        }
    }
}
