<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\AiFormatterException;
use App\Support\JsonExtractor;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class JsonExtractorTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function invalidProvider(): array
    {
        return [
            'plain text' => ['это просто текст без json'],
            'empty' => [''],
            'broken' => ['{"a": '],
        ];
    }

    public function test_decodes_plain_json_object(): void
    {
        $result = JsonExtractor::decode('{"tags": ["PHP", "Laravel"]}');

        $this->assertSame(['PHP', 'Laravel'], $result['tags']);
    }

    public function test_decodes_json_wrapped_in_code_fences(): void
    {
        $content = "```json\n{\"data\": \"value\"}\n```";

        $result = JsonExtractor::decode($content);

        $this->assertSame('value', $result['data']);
    }

    public function test_decodes_json_with_surrounding_prose(): void
    {
        $content = 'Вот результат: {"result": "перевод"} надеюсь, помог.';

        $result = JsonExtractor::decode($content);

        $this->assertSame('перевод', $result['result']);
    }

    public function test_decodes_bare_json_array(): void
    {
        $result = JsonExtractor::decode('[{"name": "Anna"}]');

        $this->assertSame('Anna', data_get($result, '0.name'));
    }

    #[DataProvider('invalidProvider')]
    public function test_throws_on_unparseable_content(string $content): void
    {
        $this->expectException(AiFormatterException::class);

        JsonExtractor::decode($content);
    }
}
