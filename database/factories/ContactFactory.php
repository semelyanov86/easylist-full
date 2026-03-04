<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'position' => fake()->jobTitle(),
            'city' => fake()->city(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'description' => fake()->sentence(),
            'linkedin_url' => 'https://linkedin.com/in/' . fake()->slug(),
            'facebook_url' => null,
            'whatsapp_url' => null,
        ];
    }
}
