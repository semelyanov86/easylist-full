<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ShoppingList\CreateShoppingListAction;
use App\Actions\ShoppingList\DeleteShoppingListAction;
use App\Actions\ShoppingList\GetFolderShoppingListsAction;
use App\Actions\ShoppingList\GetPublicShoppingListAction;
use App\Actions\ShoppingList\GetShoppingListDataAction;
use App\Actions\ShoppingList\GetUserShoppingListsAction;
use App\Actions\ShoppingList\SendShoppingListEmailAction;
use App\Actions\ShoppingList\UpdateShoppingListAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendListEmailRequest;
use App\Http\Requests\StoreShoppingListRequest;
use App\Http\Requests\UpdateShoppingListRequest;
use App\Http\Resources\Api\V1\FolderResource;
use App\Http\Resources\Api\V1\ShoppingItemResource;
use App\Http\Resources\Api\V1\ShoppingListResource;
use App\Http\Traits\JsonApiResponses;
use App\Models\Folder;
use App\Models\ShoppingList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShoppingListController extends Controller
{
    use JsonApiResponses;

    private const array ALLOWED_INCLUDES = ['folder', 'items'];

    /**
     * Получить все списки пользователя.
     */
    public function index(Request $request, GetUserShoppingListsAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $includes = $this->parseIncludes($request);
        $this->validateIncludes($includes, self::ALLOWED_INCLUDES);

        $withFolder = in_array('folder', $includes, true);
        $withItems = in_array('items', $includes, true);

        $lists = $action->execute($user, $withFolder, $withItems);

        /** @var list<array<string, mixed>> $data */
        $data = $lists->map(
            fn ($list): array => new ShoppingListResource($list)->toArray($request),
        )->all();

        $included = $this->buildIncluded($lists, $withFolder, $withItems, $request);

        return $this->jsonApiList($data, $included);
    }

    /**
     * Создать список.
     */
    public function store(
        StoreShoppingListRequest $request,
        CreateShoppingListAction $action,
        GetShoppingListDataAction $getShoppingListData,
    ): JsonResponse {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array{folder_id: int, name: string, icon?: string|null, is_public?: bool} $attributes */
        $attributes = $request->validatedAttributes();

        $list = $action->execute($user, $attributes);
        $listData = $getShoppingListData->execute($list);
        $resource = new ShoppingListResource($listData);

        return $this->jsonApiCreated($resource->toArray($request));
    }

    /**
     * Показать список.
     */
    public function show(
        Request $request,
        ShoppingList $shoppingList,
        GetShoppingListDataAction $getShoppingListData,
    ): JsonResponse {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        $includes = $this->parseIncludes($request);
        $this->validateIncludes($includes, self::ALLOWED_INCLUDES);

        $withItems = in_array('items', $includes, true);
        $withFolder = in_array('folder', $includes, true);

        if ($withItems) {
            $shoppingList->load('items');
        }

        if ($withFolder) {
            $shoppingList->load('folder');
        }

        $listData = $getShoppingListData->execute($shoppingList, $withItems, $withFolder);
        $resource = new ShoppingListResource($listData);

        $included = [];
        if ($withItems && $listData->items !== null) {
            foreach ($listData->items as $item) {
                $included[] = new ShoppingItemResource($item)->toArray($request);
            }
        }

        if ($withFolder && $listData->folder !== null) {
            $included[] = new FolderResource($listData->folder)->toArray($request);
        }

        return $this->jsonApiSingle($resource->toArray($request), $included);
    }

    /**
     * Обновить список.
     */
    public function update(
        UpdateShoppingListRequest $request,
        ShoppingList $shoppingList,
        UpdateShoppingListAction $action,
        GetShoppingListDataAction $getShoppingListData,
    ): JsonResponse {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        /** @var array{name?: string, icon?: string|null, folder_id?: int, is_public?: bool, order_column?: int} $attributes */
        $attributes = $request->validatedAttributes();

        $action->execute($user, $shoppingList, $attributes);
        $shoppingList->refresh();

        $listData = $getShoppingListData->execute($shoppingList);
        $resource = new ShoppingListResource($listData);

        return $this->jsonApiSingle($resource->toArray($request));
    }

    /**
     * Удалить список.
     */
    public function destroy(Request $request, ShoppingList $shoppingList, DeleteShoppingListAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        $action->execute($shoppingList);

        return $this->jsonApiNoContent();
    }

    /**
     * Получить списки из папки.
     */
    public function fromFolder(Request $request, Folder $folder, GetFolderShoppingListsAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($folder->user_id !== $user->id, 403);

        $lists = $action->execute($folder);

        /** @var list<array<string, mixed>> $data */
        $data = $lists->map(
            fn ($list): array => new ShoppingListResource($list)->toArray($request),
        )->all();

        return $this->jsonApiList($data);
    }

    /**
     * Публичный просмотр списка по UUID (без авторизации).
     */
    public function publicShow(Request $request, string $uuid, GetPublicShoppingListAction $action): JsonResponse
    {
        $listData = $action->execute($uuid);
        $resource = new ShoppingListResource($listData);

        $included = [];
        if ($listData->items !== null) {
            foreach ($listData->items as $item) {
                $included[] = new ShoppingItemResource($item)->toArray($request);
            }
        }

        return $this->jsonApiSingle($resource->toArray($request), $included);
    }

    /**
     * Отправить список по email.
     */
    public function sendEmail(
        SendListEmailRequest $request,
        ShoppingList $shoppingList,
        SendShoppingListEmailAction $action,
    ): JsonResponse {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($shoppingList->user_id !== $user->id, 403);

        /** @var array{email: string} $attributes */
        $attributes = $request->validatedAttributes();

        $action->execute($shoppingList, $attributes['email']);

        return $this->jsonApiNoContent();
    }

    /**
     * Собрать included из DTO-коллекции.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Data\ShoppingListData>  $lists
     * @return list<array<string, mixed>>
     */
    private function buildIncluded(
        \Illuminate\Support\Collection $lists,
        bool $withFolder,
        bool $withItems,
        Request $request,
    ): array {
        $included = [];

        if ($withFolder) {
            $seenFolders = [];
            foreach ($lists as $list) {
                if ($list->folder !== null && $list->folder_id !== null && ! isset($seenFolders[$list->folder_id])) {
                    $included[] = new FolderResource($list->folder)->toArray($request);
                    $seenFolders[$list->folder_id] = true;
                }
            }
        }

        if ($withItems) {
            foreach ($lists as $list) {
                if ($list->items !== null) {
                    foreach ($list->items as $item) {
                        $included[] = new ShoppingItemResource($item)->toArray($request);
                    }
                }
            }
        }

        return $included;
    }
}
