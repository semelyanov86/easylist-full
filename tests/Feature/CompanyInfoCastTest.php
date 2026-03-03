<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\CompanyInfoDetailsData;
use App\Data\CompanyLinksData;
use App\Data\CompanyNewsItemData;
use App\Data\CompanyReviewsData;
use App\Models\CompanyInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyInfoCastTest extends TestCase
{
    use RefreshDatabase;

    public function test_info_cast_to_data_object_on_read(): void
    {
        $model = CompanyInfo::factory()->create([
            'info' => [
                'overview' => 'Описание компании',
                'industry' => 'IT',
            ],
        ]);

        $fresh = $this->findCompanyInfo($model->id);

        /** @var CompanyInfoDetailsData $info */
        $info = $fresh->info;
        $this->assertInstanceOf(CompanyInfoDetailsData::class, $info);
        $this->assertSame('Описание компании', $info->overview);
        $this->assertSame('IT', $info->industry);
    }

    public function test_info_null_stays_null(): void
    {
        $model = CompanyInfo::factory()->create(['info' => null]);

        $fresh = $this->findCompanyInfo($model->id);

        $this->assertNull($fresh->info);
    }

    public function test_info_with_all_fields(): void
    {
        $infoArray = [
            'overview' => 'Крупный банк',
            'industry' => 'Banking & Financial Services',
            'founded' => '1870',
            'employees' => '~36,000',
            'revenue' => '€11,106 million',
            'funding' => 'Publicly listed',
            'hq' => 'Frankfurt am Main',
            'local_office' => 'Hamburg',
            'tech_stack' => ['AI', 'Cloud', 'e-Banking'],
            'reviews' => [
                'source' => 'Glassdoor',
                'rating' => 4.0,
                'total_reviews' => 1812,
                'pros' => ['Good work-life balance', 'Friendly colleagues'],
                'cons' => ['Below-market salaries'],
            ],
            'recent_news' => [
                ['title' => 'Strong 2024 results', 'date' => '2025-02-13', 'url' => 'https://example.com/news'],
                ['title' => '2025 outlook', 'date' => '2025-02-13', 'url' => 'https://example.com/outlook'],
            ],
            'links' => [
                'website' => 'https://example.com',
                'glassdoor' => 'https://glassdoor.com/reviews',
                'kununu' => 'https://kununu.com/de/test',
                'linkedin' => 'https://linkedin.com/company/test',
            ],
        ];

        $model = CompanyInfo::factory()->create(['info' => $infoArray]);

        /** @var CompanyInfoDetailsData $info */
        $info = $this->findCompanyInfo($model->id)->info;
        $this->assertInstanceOf(CompanyInfoDetailsData::class, $info);
        $this->assertSame('Крупный банк', $info->overview);
        $this->assertSame('Banking & Financial Services', $info->industry);
        $this->assertSame('1870', $info->founded);
        $this->assertSame('~36,000', $info->employees);
        $this->assertSame('€11,106 million', $info->revenue);
        $this->assertSame('Publicly listed', $info->funding);
        $this->assertSame('Frankfurt am Main', $info->hq);
        $this->assertSame('Hamburg', $info->local_office);
        $this->assertSame(['AI', 'Cloud', 'e-Banking'], $info->tech_stack);
    }

    public function test_info_reviews_cast(): void
    {
        $model = CompanyInfo::factory()->create([
            'info' => [
                'reviews' => [
                    'source' => 'Glassdoor',
                    'rating' => 4.0,
                    'total_reviews' => 500,
                    'pros' => ['Хороший коллектив'],
                    'cons' => ['Низкая зарплата', 'Медленные процессы'],
                ],
            ],
        ]);

        /** @var CompanyInfoDetailsData $info */
        $info = $this->findCompanyInfo($model->id)->info;
        $this->assertInstanceOf(CompanyInfoDetailsData::class, $info);

        $reviews = $info->reviews;
        $this->assertInstanceOf(CompanyReviewsData::class, $reviews);
        $this->assertSame('Glassdoor', $reviews->source);
        $this->assertSame(4.0, $reviews->rating);
        $this->assertSame(500, $reviews->total_reviews);
        $this->assertSame(['Хороший коллектив'], $reviews->pros);
        $this->assertNotNull($reviews->cons);
        $this->assertCount(2, $reviews->cons);
    }

    public function test_info_recent_news_cast(): void
    {
        $model = CompanyInfo::factory()->create([
            'info' => [
                'recent_news' => [
                    ['title' => 'Новость 1', 'date' => '2025-01-01', 'url' => 'https://example.com/1'],
                    ['title' => 'Новость 2', 'date' => '2025-02-01', 'url' => null],
                ],
            ],
        ]);

        /** @var CompanyInfoDetailsData $info */
        $info = $this->findCompanyInfo($model->id)->info;
        $this->assertInstanceOf(CompanyInfoDetailsData::class, $info);
        $this->assertNotNull($info->recent_news);
        $this->assertCount(2, $info->recent_news);

        /** @var CompanyNewsItemData $firstNews */
        $firstNews = $info->recent_news[0];
        $this->assertInstanceOf(CompanyNewsItemData::class, $firstNews);
        $this->assertSame('Новость 1', $firstNews->title);
        $this->assertSame('2025-01-01', $firstNews->date);
        $this->assertSame('https://example.com/1', $firstNews->url);

        /** @var CompanyNewsItemData $secondNews */
        $secondNews = $info->recent_news[1];
        $this->assertNull($secondNews->url);
    }

    public function test_info_links_cast(): void
    {
        $model = CompanyInfo::factory()->create([
            'info' => [
                'links' => [
                    'website' => 'https://example.com',
                    'glassdoor' => null,
                    'kununu' => 'https://kununu.com/test',
                    'linkedin' => null,
                ],
            ],
        ]);

        /** @var CompanyInfoDetailsData $info */
        $info = $this->findCompanyInfo($model->id)->info;
        $this->assertInstanceOf(CompanyInfoDetailsData::class, $info);

        $links = $info->links;
        $this->assertInstanceOf(CompanyLinksData::class, $links);
        $this->assertSame('https://example.com', $links->website);
        $this->assertNull($links->glassdoor);
        $this->assertSame('https://kununu.com/test', $links->kununu);
        $this->assertNull($links->linkedin);
    }

    public function test_info_partial_fields_nullable(): void
    {
        $model = CompanyInfo::factory()->create([
            'info' => [
                'overview' => 'Только описание',
            ],
        ]);

        /** @var CompanyInfoDetailsData $info */
        $info = $this->findCompanyInfo($model->id)->info;
        $this->assertInstanceOf(CompanyInfoDetailsData::class, $info);
        $this->assertSame('Только описание', $info->overview);
        $this->assertNull($info->industry);
        $this->assertNull($info->founded);
        $this->assertNull($info->employees);
        $this->assertNull($info->tech_stack);
        $this->assertNull($info->reviews);
        $this->assertNull($info->recent_news);
        $this->assertNull($info->links);
    }

    public function test_info_serializes_to_json_correctly(): void
    {
        $model = CompanyInfo::factory()->create([
            'info' => [
                'overview' => 'Тестовая компания',
                'reviews' => [
                    'source' => 'Glassdoor',
                    'rating' => 3.5,
                    'total_reviews' => 10,
                    'pros' => ['Плюс'],
                    'cons' => ['Минус'],
                ],
            ],
        ]);

        $fresh = $this->findCompanyInfo($model->id);
        /** @var array<string, mixed> $json */
        $json = $fresh->toArray();

        $this->assertIsArray($json['info']);
        /** @var array<string, mixed> $infoArray */
        $infoArray = $json['info'];
        $this->assertSame('Тестовая компания', $infoArray['overview']);

        /** @var array<string, mixed> $reviewsArray */
        $reviewsArray = $infoArray['reviews'];
        $this->assertSame('Glassdoor', $reviewsArray['source']);
    }

    public function test_info_data_object_can_be_assigned_on_create(): void
    {
        $data = CompanyInfoDetailsData::from([
            'overview' => 'Через Data объект',
            'industry' => 'Tech',
        ]);

        $model = CompanyInfo::create([
            'name' => 'Тест ООО',
            'city' => 'Москва',
            'info' => $data,
        ]);

        /** @var CompanyInfoDetailsData $info */
        $info = $this->findCompanyInfo($model->id)->info;
        $this->assertInstanceOf(CompanyInfoDetailsData::class, $info);
        $this->assertSame('Через Data объект', $info->overview);
        $this->assertSame('Tech', $info->industry);
    }

    private function findCompanyInfo(int $id): CompanyInfo
    {
        $model = CompanyInfo::find($id);
        $this->assertNotNull($model);

        return $model;
    }
}
