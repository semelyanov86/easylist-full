<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Enums\JobsViewMode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobsViewModeRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'view_mode' => ['required', 'string', Rule::in(JobsViewMode::values())],
        ];
    }
}
