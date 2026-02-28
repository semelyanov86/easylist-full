<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class JobCategory extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\JobCategoryFactory> */
    use HasFactory;

    use SortableTrait;

    /** @var array<string, string|bool> */
    public array $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'order_column',
    ];

    /**
     * Группировка сортировки по пользователю.
     *
     * @return Builder<static>
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('user_id', $this->user_id);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Job, $this>
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
