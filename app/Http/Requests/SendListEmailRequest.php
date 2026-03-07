<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Traits\ExtractsJsonApiAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendListEmailRequest extends FormRequest
{
    use ExtractsJsonApiAttributes;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.type' => ['required', 'string', 'in:emails'],
            'data.attributes.email' => ['required', 'string', 'email', 'max:255'],
        ];
    }
}
