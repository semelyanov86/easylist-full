<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CoverLetter\GenerateCoverLetterAction;
use App\Exceptions\AiFormatterException;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AiCoverLetterController extends Controller
{
    /**
     * Сгенерировать сопроводительное письмо через ИИ.
     */
    public function __invoke(Request $request, Job $job, GenerateCoverLetterAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($job->user_id !== $user->id) {
            abort(403);
        }

        if (! $user->is_premium) {
            abort(403, 'Данная функция доступна только для Premium аккаунтов');
        }

        try {
            $action->execute($user, $job);

            return back();
        } catch (AiFormatterException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
