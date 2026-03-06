<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Models\Job;
use Illuminate\Support\Str;

/**
 * Сгенерировать UUID v7 для публичного доступа к вакансии.
 */
final readonly class ShareJobAction
{
    public function execute(Job $job): string
    {
        if ($job->uuid !== null) {
            return $job->uuid;
        }

        $uuid = Str::uuid7()->toString();

        $job->update(['uuid' => $uuid]);

        return $uuid;
    }
}
