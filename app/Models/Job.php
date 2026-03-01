<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    /** @use HasFactory<\Database\Factories\JobFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'job_listings';

    protected $fillable = [
        'user_id',
        'uuid',
        'job_status_id',
        'job_category_id',
        'title',
        'description',
        'company_name',
        'location_city',
        'salary',
        'job_url',
        'resume_version_url',
        'is_favorite',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<JobStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(JobStatus::class, 'job_status_id');
    }

    /**
     * @return BelongsTo<JobCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    /**
     * @return HasMany<JobComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(JobComment::class);
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'salary' => 'integer',
        ];
    }
}
