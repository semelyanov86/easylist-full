<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shopping;

use App\Actions\Folder\CreateFolderAction;
use App\Actions\Folder\DeleteFolderAction;
use App\Actions\Folder\ReorderFoldersAction;
use App\Actions\Folder\UpdateFolderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shopping\ReorderRequest;
use App\Http\Requests\Shopping\StoreFolderRequest;
use App\Http\Requests\Shopping\UpdateFolderRequest;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class FolderController extends Controller
{
    /**
     * Создать папку.
     */
    public function store(StoreFolderRequest $request, CreateFolderAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array{name: string, icon?: string|null} $data */
        $data = $request->validated();

        $action->execute($user, $data);

        return back();
    }

    /**
     * Обновить папку.
     */
    public function update(UpdateFolderRequest $request, Folder $folder, UpdateFolderAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($folder->user_id !== $user->id, 403);

        /** @var array{name?: string, icon?: string|null} $data */
        $data = $request->validated();

        $action->execute($folder, $data);

        return back();
    }

    /**
     * Удалить папку.
     */
    public function destroy(Request $request, Folder $folder, DeleteFolderAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($folder->user_id !== $user->id, 403);

        $action->execute($folder);

        return back();
    }

    /**
     * Изменить порядок папок.
     */
    public function reorder(ReorderRequest $request, ReorderFoldersAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array<int, int> $ids */
        $ids = $request->validated('ids');

        $action->execute($user, $ids);

        return back();
    }
}
