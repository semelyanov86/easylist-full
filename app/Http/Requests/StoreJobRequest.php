<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'job_status_id' => ['required', 'integer', 'exists:job_statuses,id'],
            'job_category_id' => ['required', 'integer', 'exists:job_categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'job_url' => ['nullable', 'string', 'url', 'max:255'],
            'salary' => ['nullable', 'integer', 'min:0'],
            'location_city' => ['nullable', 'string', 'max:255'],
        ];
    }
}
