<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Traits\ExtractsJsonApiAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFolderRequest extends FormRequest
{
    use ExtractsJsonApiAttributes;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.id' => ['required', 'string'],
            'data.type' => ['required', 'string', 'in:folders'],
            'data.attributes.name' => ['sometimes', 'string', 'max:255'],
            'data.attributes.icon' => ['nullable', 'string', 'max:255'],
            'data.attributes.order_column' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
