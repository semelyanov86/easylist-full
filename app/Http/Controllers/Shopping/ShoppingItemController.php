<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shopping;

use App\Actions\ShoppingItem\CreateShoppingItemAction;
use App\Actions\ShoppingItem\DeleteAllItemsAction;
use App\Actions\ShoppingItem\DeleteShoppingItemAction;
use App\Actions\ShoppingItem\ReorderShoppingItemsAction;
use App\Actions\ShoppingItem\UncrossAllItemsAction;
use App\Actions\ShoppingItem\UpdateShoppingItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shopping\ReorderRequest;
use App\Http\Requests\Shopping\StoreShoppingItemRequest;
use App\Http\Requests\Shopping\UpdateShoppingItemRequest;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ShoppingItemController extends Controller
{
    /**
     * Создать товар.
     */
    public function store(StoreShoppingItemRequest $request, CreateShoppingItemAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array{shopping_list_id: int, name: string, description?: string|null, quantity?: int, quantity_type?: string|null, price?: int|null, is_starred?: bool, is_done?: bool} $data */
        $data = array_filter($request->validated(), fn (mixed $value): bool => $value !== null);

        if ($request->hasFile('file')) {
            $path = $request->file('file')?->store('shopping-items', 'public');
            $data['file'] = $path !== false ? $path : null;
        }

        $action->execute($user, $data);

        return back();
    }

    /**
     * Обновить товар.
     */
    public function update(UpdateShoppingItemRequest $request, ShoppingItem $shoppingItem, UpdateShoppingItemAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($shoppingItem->user_id !== $user->id, 403);

        /** @var array{name?: string, description?: string|null, quantity?: int, quantity_type?: string|null, price?: int|null, is_starred?: bool, is_done?: bool} $data */
        $data = array_filter($request->validated(), fn (mixed $value): bool => $value !== null);

        if ($request->hasFile('file')) {
            $path = $request->file('file')?->store('shopping-items', 'public');
            $data['file'] = $path !== false ? $path : null;
        }

        $action->execute($shoppingItem, $data);

        return back();
    }

    /**
     * Переключить is_done.
     */
    public function toggleDone(Request $request, ShoppingItem $shoppingItem, UpdateShoppingItemAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($shoppingItem->user_id !== $user->id, 403);

        $action->execute($shoppingItem, ['is_done' => ! $shoppingItem->is_done]);

        return back();
    }

    /**
     * Удалить товар.
     */
    public function destroy(Request $request, ShoppingItem $shoppingItem, DeleteShoppingItemAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($shoppingItem->user_id !== $user->id, 403);

        $action->execute($shoppingItem);

        return back();
    }

    /**
     * Изменить порядок товаров.
     */
    public function reorder(ReorderRequest $request, ReorderShoppingItemsAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array<int, int> $ids */
        $ids = $request->validated('ids');

        $action->execute($user, $ids);

        return back();
    }

    /**
     * Снять все отметки.
     */
    public function uncrossAll(Request $request, ShoppingList $shoppingList, UncrossAllItemsAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        $action->execute($shoppingList);

        return back();
    }

    /**
     * Удалить все товары из списка.
     */
    public function destroyAll(Request $request, ShoppingList $shoppingList, DeleteAllItemsAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        $action->execute($shoppingList);

        return back();
    }
}
