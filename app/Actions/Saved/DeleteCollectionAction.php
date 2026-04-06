<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\Models\Collection;
use App\Models\SavedPost;

final class DeleteCollectionAction
{
    public function exec(Collection $collection): void
    {
        SavedPost::where('collection_id', $collection->id)
            ->update(['collection_id' => null]);

        $collection->delete();
    }
}
