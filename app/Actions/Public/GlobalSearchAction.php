<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Enums\RoleEnum;
use App\Models\Post;
use App\Models\User;

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

        $supportsFullText = config('database.default') !== 'sqlite';

        return [
            'posts' => Post::query()
                ->published()
                ->public()
                ->with(['category', 'author.profile', 'tags'])
                ->when(mb_strlen($term) <= 3 || !$supportsFullText,
                    fn ($q) => $q->where('title', 'like', "%{$term}%"),
                    fn ($q) => $q->whereFullText(['title', 'excerpt', 'content'], $term),
                )
                ->take(6)
                ->get(),

            'authors' => User::query()
                ->where('role', RoleEnum::WRITER)
                ->with('profile')
                ->when(mb_strlen($term) <= 3 || !$supportsFullText,
                    fn ($q) => $q->where('name', 'like', "%{$term}%"),
                    fn ($q) => $q->whereFullText(['name', 'email'], $term)
                        ->orWhereHas('profile', fn ($p) => $p->whereFullText(['username', 'name', 'bio'], $term)),
                )
                ->take(3)
                ->get(),
        ];
    }
}
