<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Contracts\AiTagExtractorContract;
use App\Data\SkillData;
use App\Models\Job;
use App\Models\User;

final readonly class ExtractJobTagsAction
{
    public function __construct(
        private AiTagExtractorContract $extractor,
    ) {}

    /**
     * Извлечь теги навыков из вакансии через ИИ и привязать их.
     *
     * @return list<SkillData>
     */
    public function execute(User $user, Job $job): array
    {
        /** @var list<string> $existingTags */
        $existingTags = $user->skills()->pluck('title')->all();

        $tags = $this->extractor->extract([
            'title' => $job->title,
            'company_name' => $job->company_name,
            'description' => $job->description,
            'existing_tags' => $existingTags,
        ]);

        if ($tags === []) {
            return [];
        }

        $skillIds = [];
        foreach ($tags as $tag) {
            $skill = $user->skills()->firstOrCreate(['title' => $tag]);
            $skillIds[] = $skill->id;
        }

        $job->skills()->syncWithoutDetaching($skillIds);

        return array_values(
            $job->skills()
                ->orderBy('title')
                ->get()
                ->map(fn ($skill): SkillData => SkillData::from($skill))
                ->all()
        );
    }
}
