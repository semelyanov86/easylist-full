<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class JobDocumentData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $category,
        public readonly string $category_label,
        public readonly ?string $file_path,
        public readonly ?string $original_filename,
        public readonly ?string $mime_type,
        public readonly ?int $file_size,
        public readonly ?string $external_url,
        public readonly string $author_name,
        public readonly string $created_at,
    ) {}
}
