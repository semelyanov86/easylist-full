<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Contracts\AiCompanyAnalyzerContract;
use App\Data\CompanyInfoData;
use App\Models\CompanyInfo;
use App\Models\Job;

final readonly class AnalyzeCompanyAction
{
    public function __construct(
        private AiCompanyAnalyzerContract $analyzer,
    ) {}

    /**
     * Запросить анализ компании через ИИ и сохранить результат.
     */
    public function execute(Job $job): CompanyInfoData
    {
        $result = $this->analyzer->analyze(
            $job->company_name,
            $job->location_city,
        );

        $companyInfo = CompanyInfo::updateOrCreate(
            [
                'name' => $job->company_name,
                'city' => $job->location_city,
            ],
            [
                'info' => $result,
            ],
        );

        return CompanyInfoData::from($companyInfo);
    }
}
