<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Actions\JobComment\GetJobCommentsAction;
use App\Actions\JobDocument\GetJobDocumentsAction;
use App\Data\CompanyInfoData;
use App\Data\JobCategoryData;
use App\Data\JobShowData;
use App\Data\JobStatusData;
use App\Data\SkillData;
use App\Models\CompanyInfo;
use App\Models\Job;

final readonly class GetJobShowDataAction
{
    public function __construct(
        private GetJobCommentsAction $getComments,
        private GetJobDocumentsAction $getDocuments,
        private GetJobActivityTimelineAction $getActivityTimeline,
    ) {}

    /**
     * Загрузить полные данные вакансии для страницы просмотра.
     */
    public function execute(Job $job): JobShowData
    {
        $job->load(['status', 'category', 'skills']);

        $companyInfo = CompanyInfo::query()
            ->where('name', $job->company_name)
            ->where('city', $job->location_city)
            ->first();

        return new JobShowData(
            id: $job->id,
            title: $job->title,
            company_name: $job->company_name,
            description: $job->description,
            job_url: $job->job_url,
            location_city: $job->location_city,
            salary: $job->salary,
            is_favorite: $job->is_favorite,
            job_status_id: $job->job_status_id,
            job_category_id: $job->job_category_id,
            created_at: $job->created_at?->toISOString() ?? '',
            status: JobStatusData::from($job->status),
            category: JobCategoryData::from($job->category),
            skills: array_values(SkillData::collect($job->skills)->all()),
            comments: $this->getComments->execute($job),
            documents: $this->getDocuments->execute($job),
            activities: $this->getActivityTimeline->execute($job),
            company_info: $companyInfo ? CompanyInfoData::from($companyInfo) : null,
        );
    }
}
