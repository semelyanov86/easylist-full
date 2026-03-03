<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CompanyInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyInfo>
 */
class CompanyInfoFactory extends Factory
{
    protected $model = CompanyInfo::class;

    /** @return array<string, mixed> */
    #[\Override]
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'city' => fake()->city(),
            'info' => [
                'overview' => fake()->paragraph(),
                'industry' => fake()->word(),
                'founded' => (string) fake()->year(),
                'employees' => '~' . fake()->numberBetween(10, 10000),
                'hq' => fake()->city(),
                'tech_stack' => fake()->words(3),
                'links' => [
                    'website' => fake()->url(),
                ],
            ],
        ];
    }

    /** Без расширенной информации. */
    public function withoutInfo(): static
    {
        return $this->state(fn (): array => ['info' => null]);
    }
}
