<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\Models\Collection;
use App\Models\SavedPost;
use Illuminate\Support\Facades\Gate;

final class DeleteCollectionAction
{
    public function exec(Collection $collection): void
    {
        // Segurança (IDOR): defesa em profundidade — o componente Livewire
        // que chama esta Action já autoriza via CollectionPolicy, mas essa
        // Action reconfirma a posse para não depender só de quem a chama.
        if (Gate::denies('delete', $collection)) {
            return;
        }

        SavedPost::where('collection_id', $collection->id)
            ->update(['collection_id' => null]);

        $collection->delete();
    }
}
