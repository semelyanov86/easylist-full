<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property DocumentCategory $category
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property string|null $file_path
 * @property string|null $original_filename
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property string|null $external_url
 */
class JobDocument extends Model
{
    /** @use HasFactory<\Database\Factories\JobDocumentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_id',
        'user_id',
        'title',
        'description',
        'category',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'external_url',
    ];

    /**
     * @return BelongsTo<Job, $this>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFile(): bool
    {
        return $this->file_path !== null;
    }

    public function isLink(): bool
    {
        return $this->external_url !== null;
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'category' => DocumentCategory::class,
            'file_size' => 'integer',
        ];
    }
}
