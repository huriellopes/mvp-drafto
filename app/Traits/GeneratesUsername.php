<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Profile;
use Illuminate\Support\Str;

trait GeneratesUsername
{
    /**
     * Generate a unique username based on the provided name.
     */
    protected function generateUniqueUsername(string $name): string
    {
        $base = Str::slug(Str::replace('@', '', $name));
        $username = $base . '-' . Str::lower(Str::random(4));

        // Reavalia a existência a cada iteração; caso contrário uma colisão no
        // primeiro sorteio causaria loop infinito (a condição nunca mudaria).
        while (Profile::query()->where('username', $username)->exists()) {
            $username = $base . '-' . Str::lower(Str::random(4));
        }

        return $username;
    }
}
