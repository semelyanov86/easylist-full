<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Folder;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ShoppingList> */
class ShoppingListFactory extends Factory
{
    protected $model = ShoppingList::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'folder_id' => Folder::factory(),
            'name' => fake()->words(3, true),
            'icon' => fake()->optional()->emoji(),
            'is_public' => false,
        ];
    }

    public function withoutFolder(): static
    {
        return $this->state(fn (): array => [
            'folder_id' => null,
        ]);
    }

    public function shared(): static
    {
        return $this->state(fn (): array => [
            'is_public' => true,
            'link' => Str::uuid()->toString(),
        ]);
    }
}
