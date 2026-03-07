<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Ai\AnalyzeCompanyAction;
use App\Models\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Фоновый запуск ИИ-анализа компании.
 */
final class AnalyzeCompanyJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $jobId,
    ) {}

    public function handle(AnalyzeCompanyAction $action): void
    {
        $job = Job::findOrFail($this->jobId);

        $action->execute($job);
    }
}
