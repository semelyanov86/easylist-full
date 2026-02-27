<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_creates_default_job_statuses(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertCount(8, $user->jobStatuses);

        $statuses = $user->jobStatuses()->ordered()->get();

        $titles = $statuses->pluck('title')->toArray();
        $this->assertSame([
            'Отложено',
            'Подана заявка',
            'Первичное собеседование',
            'Техническое интервью',
            'Финальный процесс',
            'Оффер',
            'Отклонено',
            'Отклонено после собеседования',
        ], $titles);

        $expectedColors = [
            'Отложено' => 'gray',
            'Подана заявка' => 'blue',
            'Первичное собеседование' => 'purple',
            'Техническое интервью' => 'cyan',
            'Финальный процесс' => 'amber',
            'Оффер' => 'green',
            'Отклонено' => 'red',
            'Отклонено после собеседования' => 'pink',
        ];

        foreach ($statuses as $status) {
            $this->assertSame(
                $expectedColors[$status->title],
                $status->getRawOriginal('color'),
                "Цвет статуса «{$status->title}» не совпадает"
            );
        }
    }

    public function test_registration_creates_default_job_category(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'category@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'category@example.com')->first();

        $this->assertNotNull($user);
        $this->assertCount(1, $user->jobCategories);
        $this->assertSame('Общее', $user->jobCategories->first()?->title);
    }
}
