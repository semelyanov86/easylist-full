<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Database\Factories\JobFactory;

class Job extends Model
{
    /** @use HasFactory<JobFactory> */
    use HasFactory;

    use LogsActivity;
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
     * @return HasMany<JobDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(JobDocument::class);
    }

    /**
     * @return HasMany<Contact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * @return HasMany<JobTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(JobTask::class);
    }

    /**
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_skill');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'company_name',
                'description',
                'location_city',
                'salary',
                'job_url',
                'resume_version_url',
                'job_status_id',
                'job_category_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('job')
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'Вакансия создана',
                'updated' => 'Вакансия обновлена',
                'deleted' => 'Вакансия удалена',
                default => "Вакансия: {$eventName}",
            });
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
