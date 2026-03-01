<?php

declare(strict_types=1);

namespace App\Actions\Skill;

use App\Models\User;

final readonly class CreateDefaultSkillsAction
{
    /** @var list<string> */
    private const array DEFAULT_SKILLS = [
        'Golang',
        'Docker',
        'PHP',
        'Java',
        'JavaScript',
        'Laravel',
        'Vue.js',
        'React',
        'TypeScript',
        'Python',
        'PostgreSQL',
        'MySQL',
        'Redis',
        'Git',
        'Linux',
        'Kubernetes',
        'CI/CD',
        'REST API',
        'GraphQL',
        'AWS',
    ];

    /**
     * Идемпотентно создаёт дефолтные навыки для пользователя.
     */
    public function execute(User $user): void
    {
        foreach (self::DEFAULT_SKILLS as $title) {
            $user->skills()->firstOrCreate(['title' => $title]);
        }
    }
}
