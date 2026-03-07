<?php

declare(strict_types=1);

namespace App\Http\Requests\Shopping;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateShoppingItemRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'quantity_type' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'integer', 'min:0'],
            'is_starred' => ['sometimes', 'boolean'],
            'is_done' => ['sometimes', 'boolean'],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
