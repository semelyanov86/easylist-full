<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Folder\CreateFolderAction;
use App\Actions\Folder\DeleteFolderAction;
use App\Actions\Folder\GetFolderDataAction;
use App\Actions\Folder\GetUserFoldersAction;
use App\Actions\Folder\UpdateFolderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Http\Resources\Api\V1\FolderResource;
use App\Http\Resources\Api\V1\ShoppingListResource;
use App\Http\Traits\JsonApiResponses;
use App\Models\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

final class FolderController extends Controller
{
    use JsonApiResponses;

    private const array ALLOWED_INCLUDES = ['lists'];

    /**
     * Получить все папки пользователя.
     */
    public function index(Request $request, GetUserFoldersAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $includes = $this->parseIncludes($request);
        $this->validateIncludes($includes, self::ALLOWED_INCLUDES);

        $withLists = in_array('lists', $includes, true);
        $folders = $action->execute($user, $withLists);

        /** @var list<array<string, mixed>> $data */
        $data = $folders->map(
            fn ($folder): array => new FolderResource($folder)->toArray($request),
        )->all();

        $included = [];
        if ($withLists) {
            foreach ($folders as $folder) {
                if ($folder->lists !== null) {
                    foreach ($folder->lists as $list) {
                        $included[] = new ShoppingListResource($list)->toArray($request);
                    }
                }
            }
        }

        return $this->jsonApiList($data, $included);
    }

    /**
     * Создать папку.
     */
    public function store(
        StoreFolderRequest $request,
        CreateFolderAction $action,
        GetFolderDataAction $getFolderData,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{name: string, icon?: string|null} $attributes */
        $attributes = $request->validatedAttributes();

        $folder = $action->execute($user, $attributes);
        $folderData = $getFolderData->execute($folder);
        $resource = new FolderResource($folderData);

        return $this->jsonApiCreated($resource->toArray($request));
    }

    /**
     * Показать папку.
     */
    public function show(Request $request, Folder $folder, GetFolderDataAction $getFolderData): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($folder->user_id !== $user->id, 403);

        $includes = $this->parseIncludes($request);
        $this->validateIncludes($includes, self::ALLOWED_INCLUDES);

        $withLists = in_array('lists', $includes, true);

        if ($withLists) {
            $folder->load('lists');
        }

        $folderData = $getFolderData->execute($folder, $withLists);
        $resource = new FolderResource($folderData);

        $included = [];
        if ($withLists && $folderData->lists !== null) {
            foreach ($folderData->lists as $list) {
                $included[] = new ShoppingListResource($list)->toArray($request);
            }
        }

        return $this->jsonApiSingle($resource->toArray($request), $included);
    }

    /**
     * Обновить папку.
     */
    public function update(
        UpdateFolderRequest $request,
        Folder $folder,
        UpdateFolderAction $action,
        GetFolderDataAction $getFolderData,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        abort_if($folder->user_id !== $user->id, 403);

        /** @var array{name?: string, icon?: string|null, order_column?: int} $attributes */
        $attributes = $request->validatedAttributes();

        $action->execute($folder, $attributes);
        $folder->refresh();

        $folderData = $getFolderData->execute($folder);
        $resource = new FolderResource($folderData);

        return $this->jsonApiSingle($resource->toArray($request));
    }

    /**
     * Удалить папку.
     */
    public function destroy(Request $request, Folder $folder, DeleteFolderAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($folder->user_id !== $user->id, 403);

        $action->execute($folder);

        return $this->jsonApiNoContent();
    }
}
