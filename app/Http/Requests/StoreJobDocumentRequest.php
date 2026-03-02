<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DocumentCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobDocumentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', Rule::in(DocumentCategory::values())],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx'],
            'external_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'file.max' => 'Файл не должен превышать 10 МБ.',
            'file.mimes' => 'Допустимые форматы: jpg, jpeg, png, gif, pdf, doc, docx.',
        ];
    }

    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Contracts\Validation\Validator $validator): void {
            $hasFile = $this->hasFile('file');
            $hasUrl = $this->filled('external_url');

            if (! $hasFile && ! $hasUrl) {
                $validator->errors()->add('file', 'Необходимо прикрепить файл или указать ссылку.');
            }

            if ($hasFile && $hasUrl) {
                $validator->errors()->add('file', 'Нельзя одновременно загрузить файл и указать ссылку.');
            }
        });
    }
}
