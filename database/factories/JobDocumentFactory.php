<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DocumentCategory;
use App\Models\Job;
use App\Models\JobDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobDocument>
 */
class JobDocumentFactory extends Factory
{
    protected $model = JobDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'category' => fake()->randomElement(DocumentCategory::values()),
        ];
    }

    public function withFile(): static
    {
        return $this->state(fn (): array => [
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
            'original_filename' => fake()->word() . '.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 10485760),
        ]);
    }

    public function withLink(): static
    {
        return $this->state(fn (): array => [
            'external_url' => fake()->url(),
        ]);
    }
}
