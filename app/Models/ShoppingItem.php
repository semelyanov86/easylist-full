<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Database\Factories\ShoppingItemFactory;

class ShoppingItem extends Model implements Sortable
{
    /** @use HasFactory<ShoppingItemFactory> */
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
        'shopping_list_id',
        'name',
        'description',
        'quantity',
        'quantity_type',
        'price',
        'is_starred',
        'is_done',
        'file',
        'order_column',
    ];

    /**
     * @return Builder<static>
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('shopping_list_id', $this->shopping_list_id);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ShoppingList, $this>
     */
    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'is_starred' => 'boolean',
            'is_done' => 'boolean',
            'quantity' => 'integer',
            'price' => 'integer',
        ];
    }
}
