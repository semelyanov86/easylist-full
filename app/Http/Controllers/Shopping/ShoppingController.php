<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shopping;

use App\Actions\Folder\GetUserFoldersAction;
use App\Actions\ShoppingList\GetShoppingListDataAction;
use App\Http\Controllers\Controller;
use App\Models\ShoppingList;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShoppingController extends Controller
{
    /**
     * Страница списков покупок.
     */
    public function index(
        Request $request,
        GetUserFoldersAction $getUserFolders,
        GetShoppingListDataAction $getShoppingListData,
    ): Response {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $selectedFolderId = $request->query('folder_id')
            ? (int) $request->query('folder_id')
            : null;

        $folders = $getUserFolders->execute($user);

        $listsQuery = $user->shoppingLists()->ordered();

        if ($selectedFolderId !== null) {
            $listsQuery->where('folder_id', $selectedFolderId);
        }

        $lists = $listsQuery->get()->map(
            fn (ShoppingList $list) => $getShoppingListData->execute($list),
        )->values();

        $selectedList = null;
        $selectedListId = $request->query('list_id')
            ? (int) $request->query('list_id')
            : null;

        if ($selectedListId !== null) {
            $listModel = ShoppingList::query()
                ->where('id', $selectedListId)
                ->where('user_id', $user->id)
                ->with(['items' => fn ($q) => $q->orderBy('order_column')]) // @phpstan-ignore method.nonObject
                ->first();

            if ($listModel !== null) {
                $selectedList = $getShoppingListData->execute($listModel, withItems: true);
            }
        }

        return Inertia::render('shopping/Index', [
            'folders' => $folders,
            'lists' => $lists,
            'selectedList' => $selectedList,
            'selectedFolderId' => $selectedFolderId,
        ]);
    }
}
