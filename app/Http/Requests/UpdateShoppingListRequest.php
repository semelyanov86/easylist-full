<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Traits\ExtractsJsonApiAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShoppingListRequest extends FormRequest
{
    use ExtractsJsonApiAttributes;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.id' => ['required', 'string'],
            'data.type' => ['required', 'string', 'in:lists'],
            'data.attributes.name' => ['sometimes', 'string', 'max:255'],
            'data.attributes.icon' => ['nullable', 'string', 'max:255'],
            'data.attributes.folder_id' => ['sometimes', 'integer', 'exists:folders,id'],
            'data.attributes.is_public' => ['sometimes', 'boolean'],
            'data.attributes.order_column' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
