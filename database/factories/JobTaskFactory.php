<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Job;
use App\Models\JobTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobTask>
 */
class JobTaskFactory extends Factory
{
    protected $model = JobTask::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'external_id' => null,
            'deadline' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'completed_at' => null,
        ];
    }

    /**
     * Задача выполнена.
     */
    public function completed(): static
    {
        return $this->state(fn (): array => [
            'completed_at' => now(),
        ]);
    }
}
