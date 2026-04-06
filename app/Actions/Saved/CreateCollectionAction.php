<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\DTOs\CollectionData;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Support\Str;

final class CreateCollectionAction
{
    public function exec(User $user, CollectionData $data): Collection
    {
        return $user->collections()->create([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'description' => $data->description,
        ]);
    }
}
