<?php

declare(strict_types=1);

namespace App\Actions\Modules;

use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class ResolveShortLinkAction
{
    public function exec(string $code): ?string
    {
        return Cache::remember("shortlink:{$code}", now()->addDays(7), function () use ($code) {
            $shortLink = ShortLink::query()
                ->with('shortable')
                ->where('code', $code)
                ->first();

            if (!$shortLink || !$shortLink->shortable) {
                return;
            }

            return $this->getDestinationUrl($shortLink->shortable);
        });
    }

    private function getDestinationUrl(mixed $model): string
    {
        if ($model instanceof User) {
            return route('profile.show', $model->profile->username);
        }

        if ($model instanceof Post) {
            return route('posts.show', $model->slug);
        }

        return url('/');
    }
}
