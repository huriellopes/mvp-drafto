<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\DTOs\CollectionData;
use App\Models\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final class UpdateCollectionAction
{
    public function exec(Collection $collection, CollectionData $data): Collection
    {
        // Segurança (IDOR): defesa em profundidade — o componente Livewire
        // que chama esta Action já autoriza via CollectionPolicy, mas essa
        // Action reconfirma a posse para não depender só de quem a chama.
        if (Gate::denies('update', $collection)) {
            return $collection;
        }

        $collection->update([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'description' => $data->description,
        ]);

        return $collection;
    }
}
