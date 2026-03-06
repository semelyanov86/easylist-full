<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class JobShowData extends Data
{
    /**
     * @param  list<SkillData>  $skills
     * @param  list<JobCommentData>  $comments
     * @param  list<JobDocumentData>  $documents
     * @param  list<ActivityTimelineItemData>  $activities
     * @param  list<ContactData>  $contacts
     * @param  list<JobTaskData>  $tasks
     */
    public function __construct(
        public readonly int $id,
        public readonly ?string $uuid,
        public readonly string $title,
        public readonly string $company_name,
        public readonly ?string $description,
        public readonly ?string $job_url,
        public readonly ?string $location_city,
        public readonly ?int $salary,
        public readonly bool $is_favorite,
        public readonly int $job_status_id,
        public readonly int $job_category_id,
        public readonly string $created_at,
        public readonly JobStatusData $status,
        public readonly JobCategoryData $category,
        public readonly array $skills = [],
        public readonly array $comments = [],
        public readonly array $documents = [],
        public readonly array $activities = [],
        public readonly array $contacts = [],
        public readonly array $tasks = [],
        public readonly ?CompanyInfoData $company_info = null,
    ) {}
}
