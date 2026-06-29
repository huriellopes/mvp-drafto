<?php

declare(strict_types=1);

namespace App\Actions\PostCollections;

use App\DTOs\PostCollectionData;
use App\Models\PostCollection;
use App\Models\User;
use Illuminate\Support\Str;

final class SavePostCollectionAction
{
    /**
     * Cria ou atualiza uma coleção de obras do escritor.
     */
    public function exec(User $user, PostCollectionData $data, ?PostCollection $collection = null): PostCollection
    {
        $attributes = [
            'name' => $data->name,
            'slug' => $data->slug ?: Str::slug($data->name),
            'description' => $data->description,
            'visibility' => $data->visibility,
        ];

        if ($collection instanceof PostCollection) {
            $collection->update($attributes);

            return $collection;
        }

        /** @var PostCollection $created */
        $created = $user->postCollections()->create($attributes);

        return $created;
    }
}
