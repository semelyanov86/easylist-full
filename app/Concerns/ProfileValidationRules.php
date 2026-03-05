<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'ticktick_token' => $this->ticktickTokenRules(),
            'ticktick_list_id' => $this->ticktickListIdRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['sometimes', 'required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        /** @var \Illuminate\Contracts\Validation\Rule $uniqueRule */
        $uniqueRule = $userId === null
            ? Rule::unique(User::class)
            : Rule::unique(User::class)->ignore($userId);

        return [
            'sometimes',
            'required',
            'string',
            'email',
            'max:255',
            $uniqueRule,
        ];
    }

    /**
     * Правила валидации для токена TickTick.
     *
     * @return array<int, string>
     */
    protected function ticktickTokenRules(): array
    {
        return ['nullable', 'string', 'max:500'];
    }

    /**
     * Правила валидации для идентификатора списка TickTick.
     *
     * @return array<int, string>
     */
    protected function ticktickListIdRules(): array
    {
        return ['nullable', 'string', 'max:255'];
    }
}
