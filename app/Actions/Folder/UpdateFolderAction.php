<?php

declare(strict_types=1);

namespace App\Actions\Folder;

use App\Models\Folder;

final readonly class UpdateFolderAction
{
    /**
     * @param  array{name?: string, icon?: string|null, order_column?: int}  $data
     */
    public function execute(Folder $folder, array $data): void
    {
        $folder->update($data);
    }
}
