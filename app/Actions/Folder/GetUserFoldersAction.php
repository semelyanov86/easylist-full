<?php

declare(strict_types=1);

namespace App\Actions\Folder;

use App\Data\FolderData;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetUserFoldersAction
{
    public function __construct(
        private GetFolderDataAction $getFolderData,
    ) {}

    /**
     * Получить все папки пользователя.
     *
     * @return Collection<int, FolderData>
     */
    public function execute(User $user, bool $withLists = false): Collection
    {
        $query = $user->folders()->ordered();

        if ($withLists) {
            $query->with('lists');
        }

        return $query->get()->map(
            fn ($folder): FolderData => $this->getFolderData->execute($folder, $withLists),
        );
    }
}
