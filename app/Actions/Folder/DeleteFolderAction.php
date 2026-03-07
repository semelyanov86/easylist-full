<?php

declare(strict_types=1);

namespace App\Actions\Folder;

use App\Models\Folder;

final readonly class DeleteFolderAction
{
    public function execute(Folder $folder): void
    {
        $folder->delete();
    }
}
