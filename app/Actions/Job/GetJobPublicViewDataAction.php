<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Data\CompanyInfoDetailsData;
use App\Data\JobPublicViewData;
use App\Data\PublicContactData;
use App\Data\SkillData;
use App\Models\CompanyInfo;
use App\Models\Job;

/**
 * Загрузить данные вакансии для публичной страницы.
 */
final readonly class GetJobPublicViewDataAction
{
    public function execute(Job $job): JobPublicViewData
    {
        $job->load(['category', 'skills', 'contacts']);

        $companyInfo = CompanyInfo::query()
            ->where('name', $job->company_name)
            ->where('city', $job->location_city)
            ->first();

        return new JobPublicViewData(
            title: $job->title,
            company_name: $job->company_name,
            description: $job->description,
            job_url: $job->job_url,
            location_city: $job->location_city,
            salary: $job->salary,
            currency_symbol: $job->category?->currency_symbol,
            created_at: $job->created_at?->toISOString() ?? '',
            skills: array_values(SkillData::collect($job->skills)->all()),
            contacts: array_values(
                $job->contacts
                    ->map(fn ($contact): PublicContactData => new PublicContactData(
                        first_name: $contact->first_name,
                        last_name: $contact->last_name,
                        position: $contact->position,
                        city: $contact->city,
                        email: $contact->email,
                        phone: $contact->phone,
                        linkedin_url: $contact->linkedin_url,
                    ))
                    ->all(),
            ),
            company_info: $companyInfo !== null
                ? CompanyInfoDetailsData::from($companyInfo->info)
                : null,
        );
    }
}
