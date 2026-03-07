<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Traits\ExtractsJsonApiAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreShoppingListRequest extends FormRequest
{
    use ExtractsJsonApiAttributes;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:lists'],
            'data.attributes.folder_id' => ['nullable', 'integer', 'exists:folders,id'],
            'data.attributes.name' => ['required', 'string', 'max:255'],
            'data.attributes.icon' => ['nullable', 'string', 'max:255'],
            'data.attributes.is_public' => ['nullable', 'boolean'],
        ];
    }
}
