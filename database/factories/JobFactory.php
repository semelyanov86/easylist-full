<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    protected $model = Job::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_status_id' => JobStatus::factory(),
            'job_category_id' => JobCategory::factory(),
            'title' => fake()->jobTitle(),
            'description' => fake()->optional()->paragraph(),
            'company_name' => fake()->company(),
            'location_city' => fake()->optional()->city(),
            'salary' => fake()->optional()->numberBetween(30000, 500000),
            'job_url' => fake()->optional()->url(),
            'is_favorite' => false,
        ];
    }

    /**
     * Пометить вакансию как избранную.
     */
    public function favorite(): static
    {
        return $this->state(fn (): array => [
            'is_favorite' => true,
        ]);
    }
}
