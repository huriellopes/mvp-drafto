<?php

declare(strict_types=1);

namespace App\Actions\PostCollections;

use App\Models\PostCollection;

final class DeletePostCollectionAction
{
    /**
     * Remove a coleção. Os posts em si não são afetados — apenas o vínculo
     * no pivot é desfeito (cascade pela FK post_collection_id).
     */
    public function exec(PostCollection $collection): void
    {
        $collection->posts()->detach();
        $collection->delete();
    }
}
