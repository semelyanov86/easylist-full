<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\AiFormatterContract;
use App\Models\User;
use App\Services\FakeAiFormatterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiFormatTest extends TestCase
{
    use RefreshDatabase;

    private FakeAiFormatterService $fakeFormatter;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeFormatter = new FakeAiFormatterService();
        $this->app->instance(AiFormatterContract::class, $this->fakeFormatter);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->postJson(route('ai.format-text'), [
            'text' => 'Тестовый текст',
        ]);

        $response->assertUnauthorized();
    }

    public function test_non_premium_user_gets_403(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('ai.format-text'), [
            'text' => 'Тестовый текст',
        ]);

        $response->assertForbidden();
    }

    public function test_text_field_is_required(): void
    {
        $user = User::factory()->premium()->create();

        $response = $this->actingAs($user)->postJson(route('ai.format-text'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('text');
    }

    public function test_text_cannot_exceed_max_length(): void
    {
        $user = User::factory()->premium()->create();

        $response = $this->actingAs($user)->postJson(route('ai.format-text'), [
            'text' => str_repeat('a', 10001),
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('text');
    }

    public function test_successful_formatting_returns_json(): void
    {
        $user = User::factory()->premium()->create();
        $this->fakeFormatter->withResponse('**Красиво отформатировано**');

        $response = $this->actingAs($user)->postJson(route('ai.format-text'), [
            'text' => 'Сырой текст вакансии',
        ]);

        $response->assertOk();
        $response->assertJson(['formatted' => '**Красиво отформатировано**']);
    }

    public function test_service_failure_returns_502(): void
    {
        $user = User::factory()->premium()->create();
        $this->fakeFormatter->shouldFail();

        $response = $this->actingAs($user)->postJson(route('ai.format-text'), [
            'text' => 'Текст для форматирования',
        ]);

        $response->assertStatus(502);
        $response->assertJsonStructure(['message']);
    }
}
