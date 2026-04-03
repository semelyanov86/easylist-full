<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ShoppingItem\CreateShoppingItemAction;
use App\Actions\ShoppingItem\DeleteAllItemsAction;
use App\Actions\ShoppingItem\DeleteShoppingItemAction;
use App\Actions\ShoppingItem\GetListItemsAction;
use App\Actions\ShoppingItem\GetShoppingItemDataAction;
use App\Actions\ShoppingItem\GetUserItemsAction;
use App\Actions\ShoppingItem\UncrossAllItemsAction;
use App\Actions\ShoppingItem\UpdateShoppingItemAction;
use App\Actions\ShoppingList\GetShoppingListDataAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShoppingItemRequest;
use App\Http\Requests\UpdateShoppingItemRequest;
use App\Http\Resources\Api\V1\ShoppingItemResource;
use App\Http\Resources\Api\V1\ShoppingListResource;
use App\Http\Traits\JsonApiResponses;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

final class ShoppingItemController extends Controller
{
    use JsonApiResponses;

    private const array ALLOWED_INCLUDES = ['list'];

    /**
     * Получить все позиции пользователя.
     */
    public function index(Request $request, GetUserItemsAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $items = $action->execute($user);

        /** @var list<array<string, mixed>> $data */
        $data = $items->map(
            fn ($item): array => new ShoppingItemResource($item)->toArray($request),
        )->all();

        return $this->jsonApiList($data);
    }

    /**
     * Создать позицию.
     */
    public function store(
        StoreShoppingItemRequest $request,
        CreateShoppingItemAction $action,
        GetShoppingItemDataAction $getShoppingItemData,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{shopping_list_id: int, name: string, description?: string|null, quantity?: int, quantity_type?: string|null, price?: int|null, is_starred?: bool, is_done?: bool, file?: string|null} $attributes */
        $attributes = $request->validatedAttributes();

        $item = $action->execute($user, $attributes);
        $itemData = $getShoppingItemData->execute($item);
        $resource = new ShoppingItemResource($itemData);

        return $this->jsonApiCreated($resource->toArray($request));
    }

    /**
     * Показать позицию.
     */
    public function show(
        Request $request,
        ShoppingItem $shoppingItem,
        GetShoppingItemDataAction $getShoppingItemData,
        GetShoppingListDataAction $getShoppingListData,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        abort_if($shoppingItem->user_id !== $user->id, 403);

        $includes = $this->parseIncludes($request);
        $this->validateIncludes($includes, self::ALLOWED_INCLUDES);

        $withList = in_array('list', $includes, true);

        $itemData = $getShoppingItemData->execute($shoppingItem);
        $resource = new ShoppingItemResource($itemData);

        $included = [];
        if ($withList) {
            $resource->withListRelationship();
            $shoppingItem->load('shoppingList');

            if ($shoppingItem->shoppingList !== null) {
                $listData = $getShoppingListData->execute($shoppingItem->shoppingList);
                $included[] = new ShoppingListResource($listData)->toArray($request);
            }
        }

        return $this->jsonApiSingle($resource->toArray($request), $included);
    }

    /**
     * Обновить позицию.
     */
    public function update(
        UpdateShoppingItemRequest $request,
        ShoppingItem $shoppingItem,
        UpdateShoppingItemAction $action,
        GetShoppingItemDataAction $getShoppingItemData,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        abort_if($shoppingItem->user_id !== $user->id, 403);

        /** @var array{name?: string, description?: string|null, quantity?: int, quantity_type?: string|null, price?: int|null, is_starred?: bool, is_done?: bool, file?: string|null, order_column?: int} $attributes */
        $attributes = $request->validatedAttributes();

        $action->execute($shoppingItem, $attributes);
        $shoppingItem->refresh();

        $itemData = $getShoppingItemData->execute($shoppingItem);
        $resource = new ShoppingItemResource($itemData);

        return $this->jsonApiSingle($resource->toArray($request));
    }

    /**
     * Удалить позицию.
     */
    public function destroy(Request $request, ShoppingItem $shoppingItem, DeleteShoppingItemAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($shoppingItem->user_id !== $user->id, 403);

        $action->execute($shoppingItem);

        return $this->jsonApiNoContent();
    }

    /**
     * Получить позиции из списка.
     */
    public function fromList(Request $request, ShoppingList $shoppingList, GetListItemsAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        $items = $action->execute($shoppingList);

        /** @var list<array<string, mixed>> $data */
        $data = $items->map(
            fn ($item): array => new ShoppingItemResource($item)->toArray($request),
        )->all();

        return $this->jsonApiList($data);
    }

    /**
     * Снять все отметки (uncross) позиций в списке.
     */
    public function uncrossAll(Request $request, ShoppingList $shoppingList, UncrossAllItemsAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        $action->execute($shoppingList);

        return $this->jsonApiNoContent();
    }

    /**
     * Удалить все позиции из списка.
     */
    public function destroyAll(Request $request, ShoppingList $shoppingList, DeleteAllItemsAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        $action->execute($shoppingList);

        return $this->jsonApiNoContent();
    }
}
