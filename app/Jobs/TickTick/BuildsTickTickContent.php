<?php

declare(strict_types=1);

namespace App\Jobs\TickTick;

use App\Models\Job;

trait BuildsTickTickContent
{
    /**
     * Сформировать контент задачи для TickTick.
     */
    private function buildContent(Job $job): string
    {
        $url = route('jobs.show', $job);

        return implode("\n", array_filter([
            "Вакансия: {$job->title}",
            "Компания: {$job->company_name}",
            $job->location_city ? "Город: {$job->location_city}" : null,
            "Ссылка: {$url}",
        ]));
    }
}
