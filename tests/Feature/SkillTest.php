<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_user_skills(): void
    {
        $user = User::factory()->create();
        Skill::factory()->for($user)->create(['title' => 'PHP']);
        Skill::factory()->for($user)->create(['title' => 'Python']);

        $response = $this->actingAs($user)->getJson(route('skills.search', ['q' => 'PHP']));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'PHP']);
    }

    public function test_search_filters_by_substring(): void
    {
        $user = User::factory()->create();
        Skill::factory()->for($user)->create(['title' => 'JavaScript']);
        Skill::factory()->for($user)->create(['title' => 'TypeScript']);
        Skill::factory()->for($user)->create(['title' => 'Python']);

        $response = $this->actingAs($user)->getJson(route('skills.search', ['q' => 'Script']));

        $response->assertOk();
        $response->assertJsonCount(2);
    }

    public function test_search_does_not_return_other_users_skills(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Skill::factory()->for($user)->create(['title' => 'PHP']);
        Skill::factory()->for($otherUser)->create(['title' => 'PHP']);

        $response = $this->actingAs($user)->getJson(route('skills.search', ['q' => 'PHP']));

        $response->assertOk();
        $response->assertJsonCount(1);
    }

    public function test_store_creates_new_skill(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('skills.store'), [
            'title' => 'Rust',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['title' => 'Rust']);
        $this->assertDatabaseHas('skills', [
            'user_id' => $user->id,
            'title' => 'Rust',
        ]);
    }

    public function test_store_returns_existing_skill_if_duplicate(): void
    {
        $user = User::factory()->create();
        $existing = Skill::factory()->for($user)->create(['title' => 'PHP']);

        $response = $this->actingAs($user)->postJson(route('skills.store'), [
            'title' => 'PHP',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['id' => $existing->id]);
        $this->assertDatabaseCount('skills', 1);
    }

    public function test_store_validates_title_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('skills.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    }

    public function test_store_validates_title_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('skills.store'), [
            'title' => str_repeat('a', 51),
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    }

    public function test_unauthenticated_user_cannot_search_skills(): void
    {
        $response = $this->getJson(route('skills.search'));

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_create_skills(): void
    {
        $response = $this->postJson(route('skills.store'), [
            'title' => 'PHP',
        ]);

        $response->assertUnauthorized();
    }
}
