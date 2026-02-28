<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Enums\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreJobCategoryRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('job_categories')->where('user_id', $user->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency' => ['required', 'string', Rule::in(Currency::values())],
        ];
    }
}
