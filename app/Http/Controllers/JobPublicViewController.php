<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Job\GetJobPublicViewDataAction;
use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Публичная страница просмотра вакансии (без авторизации).
 */
final class JobPublicViewController extends Controller
{
    public function __invoke(
        Request $request,
        string $uuid,
        GetJobPublicViewDataAction $action,
    ): Response {
        $job = Job::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        return Inertia::render('jobs/PublicView', [
            'job' => $action->execute($job),
        ]);
    }
}
