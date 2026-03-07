<?php

declare(strict_types=1);

namespace App\Http\Requests\Shopping;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreShoppingItemRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shopping_list_id' => ['required', 'integer', 'exists:shopping_lists,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'quantity_type' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'integer', 'min:0'],
            'is_starred' => ['nullable', 'boolean'],
            'is_done' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
