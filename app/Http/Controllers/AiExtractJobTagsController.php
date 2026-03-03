<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Ai\ExtractJobTagsAction;
use App\Exceptions\AiFormatterException;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AiExtractJobTagsController extends Controller
{
    /**
     * Извлечь теги навыков из вакансии с помощью ИИ.
     */
    public function __invoke(Request $request, Job $job, ExtractJobTagsAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($job->user_id !== $user->id) {
            abort(403);
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
