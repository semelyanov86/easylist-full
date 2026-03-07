<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ShoppingItem> */
class ShoppingItemFactory extends Factory
{
    protected $model = ShoppingItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shopping_list_id' => ShoppingList::factory(),
            'name' => fake()->word(),
            'description' => fake()->optional()->sentence(),
            'quantity' => fake()->numberBetween(1, 10),
            'quantity_type' => fake()->optional()->randomElement(['шт', 'кг', 'л', 'уп']),
            'price' => fake()->optional()->numberBetween(10, 5000),
            'is_starred' => false,
            'is_done' => false,
        ];
    }

    public function done(): static
    {
        return $this->state(fn (): array => ['is_done' => true]);
    }

    public function starred(): static
    {
        return $this->state(fn (): array => ['is_starred' => true]);
    }
}
