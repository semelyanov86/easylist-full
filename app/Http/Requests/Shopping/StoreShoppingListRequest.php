<?php

declare(strict_types=1);

namespace App\Http\Requests\Shopping;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreShoppingListRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'folder_id' => ['nullable', 'integer', 'exists:folders,id'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }
}
