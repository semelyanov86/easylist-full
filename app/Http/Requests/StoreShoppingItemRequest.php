<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Traits\ExtractsJsonApiAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreShoppingItemRequest extends FormRequest
{
    use ExtractsJsonApiAttributes;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:items'],
            'data.attributes.shopping_list_id' => ['required', 'integer', 'exists:shopping_lists,id'],
            'data.attributes.name' => ['required', 'string', 'max:255'],
            'data.attributes.description' => ['nullable', 'string'],
            'data.attributes.quantity' => ['nullable', 'integer', 'min:1'],
            'data.attributes.quantity_type' => ['nullable', 'string', 'max:50'],
            'data.attributes.price' => ['nullable', 'integer', 'min:0'],
            'data.attributes.is_starred' => ['nullable', 'boolean'],
            'data.attributes.is_done' => ['nullable', 'boolean'],
            'data.attributes.file' => ['nullable', 'string', 'max:255'],
        ];
    }
}
