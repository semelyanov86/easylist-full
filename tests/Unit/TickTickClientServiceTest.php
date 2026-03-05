<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\TickTickException;
use App\Services\TickTickClientService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TickTickClientServiceTest extends TestCase
{
    private const string BASE_URL = 'https://api.ticktick.com/open/v1';

    private TickTickClientService $service;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TickTickClientService(
            baseUrl: self::BASE_URL,
            timeout: 30,
        );
    }

    public function test_create_task_sends_correct_request(): void
    {
        Http::fake([
            self::BASE_URL . '/task' => Http::response(['id' => 'tt-123']),
        ]);

        $result = $this->service->createTask('test-token', [
            'title' => 'Тестовая задача',
            'projectId' => 'list-1',
        ]);

        $this->assertSame(['id' => 'tt-123'], $result);

        Http::assertSent(fn (Request $request): bool => $request->url() === self::BASE_URL . '/task'
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request['title'] === 'Тестовая задача'
                && $request['projectId'] === 'list-1');
    }

    public function test_create_task_throws_on_http_error(): void
    {
        Http::fake([
            self::BASE_URL . '/task' => Http::response('Error', 500),
        ]);

        $this->expectException(TickTickException::class);
        $this->expectExceptionMessage('HTTP 500');

        $this->service->createTask('test-token', ['title' => 'Задача']);
    }

    public function test_update_task_sends_correct_request(): void
    {
        Http::fake([
            self::BASE_URL . '/task/tt-123' => Http::response(['id' => 'tt-123']),
        ]);

        $this->service->updateTask('test-token', 'tt-123', [
            'id' => 'tt-123',
            'projectId' => 'list-1',
            'title' => 'Обновлённая задача',
        ]);

        Http::assertSent(fn (Request $request): bool => $request->url() === self::BASE_URL . '/task/tt-123'
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request['title'] === 'Обновлённая задача');
    }

    public function test_update_task_throws_on_http_error(): void
    {
        Http::fake([
            self::BASE_URL . '/task/tt-123' => Http::response('Error', 422),
        ]);

        $this->expectException(TickTickException::class);
        $this->expectExceptionMessage('HTTP 422');

        $this->service->updateTask('test-token', 'tt-123', ['title' => 'Задача']);
    }

    public function test_complete_task_sends_correct_request(): void
    {
        Http::fake([
            self::BASE_URL . '/project/list-1/task/tt-123/complete' => Http::response(null, 200),
        ]);

        $this->service->completeTask('test-token', 'list-1', 'tt-123');

        Http::assertSent(fn (Request $request): bool => $request->url() === self::BASE_URL . '/project/list-1/task/tt-123/complete'
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_complete_task_throws_on_http_error(): void
    {
        Http::fake([
            self::BASE_URL . '/project/list-1/task/tt-123/complete' => Http::response('Error', 500),
        ]);

        $this->expectException(TickTickException::class);

        $this->service->completeTask('test-token', 'list-1', 'tt-123');
    }

    public function test_delete_task_sends_correct_request(): void
    {
        Http::fake([
            self::BASE_URL . '/project/list-1/task/tt-123' => Http::response(null, 200),
        ]);

        $this->service->deleteTask('test-token', 'list-1', 'tt-123');

        Http::assertSent(fn (Request $request): bool => $request->url() === self::BASE_URL . '/project/list-1/task/tt-123'
                && $request->method() === 'DELETE'
                && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_delete_task_throws_on_http_error(): void
    {
        Http::fake([
            self::BASE_URL . '/project/list-1/task/tt-123' => Http::response('Error', 500),
        ]);

        $this->expectException(TickTickException::class);

        $this->service->deleteTask('test-token', 'list-1', 'tt-123');
    }

    public function test_get_task_returns_task_data(): void
    {
        Http::fake([
            self::BASE_URL . '/project/list-1/task/tt-123' => Http::response([
                'id' => 'tt-123',
                'status' => 0,
                'title' => 'Задача',
            ]),
        ]);

        $result = $this->service->getTask('test-token', 'list-1', 'tt-123');

        $this->assertSame(0, $result['status']);

        Http::assertSent(fn (Request $request): bool => $request->url() === self::BASE_URL . '/project/list-1/task/tt-123'
                && $request->method() === 'GET'
                && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_get_task_throws_task_not_found_on_404(): void
    {
        Http::fake([
            self::BASE_URL . '/project/list-1/task/tt-123' => Http::response('Not Found', 404),
        ]);

        $this->expectException(TickTickException::class);
        $this->expectExceptionMessage('Задача TickTick не найдена: tt-123');

        $this->service->getTask('test-token', 'list-1', 'tt-123');
    }

    public function test_get_task_throws_on_other_http_error(): void
    {
        Http::fake([
            self::BASE_URL . '/project/list-1/task/tt-123' => Http::response('Error', 500),
        ]);

        $this->expectException(TickTickException::class);
        $this->expectExceptionMessage('HTTP 500');

        $this->service->getTask('test-token', 'list-1', 'tt-123');
    }
}
