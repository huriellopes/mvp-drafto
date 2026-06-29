<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\PostStatusEnum;
use App\Enums\PostVisibilityEnum;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // Apenas conteúdo realmente público e indexável entra no sitemap.
        // (Posts unlisted/followers_only e perfis privados/banidos vazavam antes.)
        $posts = Post::query()
            ->where('status', PostStatusEnum::PUBLISHED)
            ->where('visibility', PostVisibilityEnum::PUBLIC)
            ->where('seo_enabled', true)
            ->latest()
            ->get();

        $writers = User::query()
            ->where('role', RoleEnum::WRITER)
            ->where('status', UserStatusEnum::ACTIVE)
            ->whereNull('banned_until')
            ->whereHas('profile', fn ($query) => $query->where('is_searchable', true))
            ->with('profile')
            ->get();

        return response()->view('public.sitemap', [
            'posts' => $posts,
            'writers' => $writers,
        ])->header('Content-Type', 'text/xml');
    }
}
