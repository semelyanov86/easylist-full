<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shopping;

use App\Actions\ShoppingList\CreateShoppingListAction;
use App\Actions\ShoppingList\DeleteShoppingListAction;
use App\Actions\ShoppingList\ReorderShoppingListsAction;
use App\Actions\ShoppingList\UpdateShoppingListAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shopping\ReorderRequest;
use App\Http\Requests\Shopping\StoreShoppingListRequest;
use App\Http\Requests\Shopping\UpdateShoppingListRequest;
use App\Models\ShoppingList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;

final class ShoppingListController extends Controller
{
    /**
     * Создать список покупок.
     */
    public function store(StoreShoppingListRequest $request, CreateShoppingListAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{name: string, folder_id: int, icon?: string|null, is_public?: bool} $data */
        $data = $request->validated();

        $list = $action->execute($user, $data);

        return redirect()->to(route('shopping.index', ['folder_id' => $list->folder_id, 'list_id' => $list->id]));
    }

    /**
     * Обновить список покупок.
     */
    public function update(UpdateShoppingListRequest $request, ShoppingList $shoppingList, UpdateShoppingListAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        /** @var array{name?: string, folder_id?: int, icon?: string|null, is_public?: bool} $data */
        $data = $request->validated();

        $action->execute($user, $shoppingList, $data);

        return back();
    }

    /**
     * Удалить список покупок.
     */
    public function destroy(Request $request, ShoppingList $shoppingList, DeleteShoppingListAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        $action->execute($shoppingList);

        return back();
    }

    /**
     * Изменить порядок списков.
     */
    public function reorder(ReorderRequest $request, ReorderShoppingListsAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array<int, int> $ids */
        $ids = $request->validated('ids');

        $action->execute($user, $ids);

        return back();
    }
}
