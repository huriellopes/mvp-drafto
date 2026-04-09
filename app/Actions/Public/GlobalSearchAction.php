<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Enums\RoleEnum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class GlobalSearchAction
{
    /**
     * Executa a busca global em posts (título, categoria, tags) e autores.
     */
    public function exec(string $term): array
    {
        if (mb_strlen($term) < 2) {
            return ['posts' => [], 'authors' => []];
        }

        return [
            'posts' => Post::query()
                ->published()
                ->public()
                ->with(['category', 'author'])
                ->where(function (Builder $query) use ($term) {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', Str::lower("%{$term}%"))
                        ->orWhereHas('category', function (Builder $queryCategory) use ($term) {
                            $queryCategory->where('name', 'like', "%{$term}%");
                        })
                        ->orWhereHas('tags', function (Builder $queryTag) use ($term) {
                            $queryTag->where('name', 'like', "%{$term}%");
                        });
                })
                ->latest('published_at')
                ->limit(6)
                ->get(),

            'authors' => User::query()
                ->where('role', RoleEnum::WRITER)
                ->where('name', 'like', "%{$term}%")
                ->with('profile')
                ->limit(3)
                ->get(),
        ];
    }
}
