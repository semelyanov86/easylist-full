<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Traits\ExtractsJsonApiAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShoppingItemRequest extends FormRequest
{
    use ExtractsJsonApiAttributes;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.id' => ['required', 'string'],
            'data.type' => ['required', 'string', 'in:items'],
            'data.attributes.name' => ['sometimes', 'string', 'max:255'],
            'data.attributes.description' => ['nullable', 'string'],
            'data.attributes.quantity' => ['sometimes', 'integer', 'min:1'],
            'data.attributes.quantity_type' => ['nullable', 'string', 'max:50'],
            'data.attributes.price' => ['nullable', 'integer', 'min:0'],
            'data.attributes.is_starred' => ['sometimes', 'boolean'],
            'data.attributes.is_done' => ['sometimes', 'boolean'],
            'data.attributes.file' => ['nullable', 'string', 'max:255'],
            'data.attributes.order_column' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
