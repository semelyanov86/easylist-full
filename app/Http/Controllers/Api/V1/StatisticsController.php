<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Dashboard\GetDashboardActivityAction;
use App\Actions\Dashboard\GetDashboardFavoriteJobsAction;
use App\Actions\Dashboard\GetDashboardJobFunnelAction;
use App\Actions\Dashboard\GetDashboardPendingTasksAction;
use App\Actions\Dashboard\GetDashboardRecentJobsAction;
use App\Actions\Dashboard\GetDashboardResponseDynamicsAction;
use App\Actions\Dashboard\GetDashboardSkillsDemandAction;
use App\Http\Controllers\Controller;
use App\Http\Traits\JsonApiResponses;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Статистика дашборда — единый эндпоинт для API-клиентов.
 */
final class StatisticsController extends Controller
{
    use JsonApiResponses;

    public function __construct(
        private readonly GetDashboardActivityAction $getActivity,
        private readonly GetDashboardPendingTasksAction $getPendingTasks,
        private readonly GetDashboardJobFunnelAction $getJobFunnel,
        private readonly GetDashboardFavoriteJobsAction $getFavoriteJobs,
        private readonly GetDashboardRecentJobsAction $getRecentJobs,
        private readonly GetDashboardSkillsDemandAction $getSkillsDemand,
        private readonly GetDashboardResponseDynamicsAction $getResponseDynamics,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $funnelCategoryId = $request->integer('funnel_category_id') ?: null;

        /** @var list<array<string, mixed>> $recentActivities */
        $recentActivities = array_map(
            fn ($item): array => $item->toArray(),
            $this->getActivity->execute($user),
        );

        /** @var list<array<string, mixed>> $pendingTasks */
        $pendingTasks = array_map(
            fn ($item): array => $item->toArray(),
            $this->getPendingTasks->execute($user),
        );

        /** @var list<array<string, mixed>> $favoriteJobs */
        $favoriteJobs = array_map(
            fn ($item): array => $item->toArray(),
            $this->getFavoriteJobs->execute($user),
        );

        /** @var list<array<string, mixed>> $recentJobs */
        $recentJobs = array_map(
            fn ($item): array => $item->toArray(),
            $this->getRecentJobs->execute($user),
        );

        /** @var list<array<string, mixed>> $skillsDemand */
        $skillsDemand = array_map(
            fn ($item): array => $item->toArray(),
            $this->getSkillsDemand->execute($user),
        );

        /** @var list<array<string, mixed>> $responseDynamics */
        $responseDynamics = array_map(
            fn ($item): array => $item->toArray(),
            $this->getResponseDynamics->execute($user),
        );

        /** @var list<array<string, mixed>> $jobFunnel */
        $jobFunnel = $this->getJobFunnel->execute($user, $funnelCategoryId)
            ->map(fn ($item): array => $item->toArray())
            ->values()
            ->all();

        return $this->jsonApiSingle([
            'type' => 'statistics',
            'id' => (string) $user->id,
            'attributes' => [
                'recent_activities' => $recentActivities,
                'pending_tasks' => $pendingTasks,
                'favorite_jobs' => $favoriteJobs,
                'recent_jobs' => $recentJobs,
                'skills_demand' => $skillsDemand,
                'response_dynamics' => $responseDynamics,
                'job_funnel' => $jobFunnel,
            ],
        ]);
    }
}
