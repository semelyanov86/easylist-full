<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\AiFormatterException;
use App\Services\AiCoverLetterService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiCoverLetterServiceTest extends TestCase
{
    private AiCoverLetterService $service;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AiCoverLetterService(
            url: 'https://ai.example.com/api/generate',
            token: 'test-token',
            timeout: 30,
            baseUrl: 'https://ai.example.com',
        );
    }

    public function test_connection_timeout_throws_ai_formatter_exception(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('cURL error 28: Operation timed out');
        });

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessageMatches('/Таймаут соединения/');

        $this->service->generate('test prompt');
    }

    public function test_failed_http_response_throws_ai_formatter_exception(): void
    {
        Http::fake([
            'ai.example.com/*' => Http::response('Server Error', 500),
        ]);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessageMatches('/HTTP 500/');

        $this->service->generate('test prompt');
    }

    public function test_missing_url_in_response_throws_ai_formatter_exception(): void
    {
        Http::fake([
            'ai.example.com/*' => Http::response(['data' => 'no url field'], 200),
        ]);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessageMatches('/отсутствует url файла/');

        $this->service->generate('test prompt');
    }

    public function test_successful_generation_returns_tex_content(): void
    {
        $texContent = '\documentclass{article}\begin{document}Hello\end{document}';

        Http::fake([
            'ai.example.com/api/generate' => Http::response(['result' => ['url' => '/files/result.tex']], 200),
            'ai.example.com/files/result.tex' => Http::response($texContent, 200),
        ]);

        $result = $this->service->generate('test prompt');

        $this->assertSame($texContent, $result);
    }

    public function test_file_download_timeout_throws_ai_formatter_exception(): void
    {
        Http::fake([
            'ai.example.com/api/generate' => Http::response(['result' => ['url' => '/files/result.tex']], 200),
            'ai.example.com/files/result.tex' => function (): void {
                throw new ConnectionException('cURL error 28: Operation timed out');
            },
        ]);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessageMatches('/Таймаут скачивания файла/');

        $this->service->generate('test prompt');
    }

    public function test_file_download_failure_throws_ai_formatter_exception(): void
    {
        Http::fake([
            'ai.example.com/api/generate' => Http::response(['result' => ['url' => '/files/result.tex']], 200),
            'ai.example.com/files/result.tex' => Http::response('Not Found', 404),
        ]);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessageMatches('/Не удалось скачать файл/');

        $this->service->generate('test prompt');
    }
}
