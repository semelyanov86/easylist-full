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
use Database\Factories\ShoppingListFactory;

class ShoppingList extends Model implements Sortable
{
    /** @use HasFactory<ShoppingListFactory> */
    use HasFactory;

    use SortableTrait;

    /** @var array<string, string|bool> */
    public array $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'folder_id',
        'name',
        'icon',
        'link',
        'order_column',
        'is_public',
    ];

    /**
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
     * @return BelongsTo<Folder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * @return HasMany<ShoppingItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShoppingItem::class);
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
