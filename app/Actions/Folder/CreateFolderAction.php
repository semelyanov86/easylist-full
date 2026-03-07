<?php

declare(strict_types=1);

namespace App\Actions\Folder;

use App\Models\Folder;
use App\Models\User;

final readonly class CreateFolderAction
{
    /**
     * @param  array{name: string, icon?: string|null}  $data
     */
    public function execute(User $user, array $data): Folder
    {
        /** @var Folder */
        return $user->folders()->create($data);
    }
}
