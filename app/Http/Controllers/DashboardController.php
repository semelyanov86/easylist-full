<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardActivityAction;
use App\Actions\Dashboard\GetDashboardPendingTasksAction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        GetDashboardActivityAction $getActivity,
        GetDashboardPendingTasksAction $getPendingTasks,
    ): Response {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'recentActivities' => Inertia::defer(
                fn () => $getActivity->execute($user),
            ),
            'pendingTasks' => Inertia::defer(
                fn () => $getPendingTasks->execute($user),
            ),
        ]);
    }
}
