<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\DTOs\CollectionData;
use App\Models\Collection;
use Illuminate\Support\Str;

final class UpdateCollectionAction
{
    public function exec(Collection $collection, CollectionData $data): Collection
    {
        $collection->update([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'description' => $data->description,
        ]);

        return $collection;
    }
}
