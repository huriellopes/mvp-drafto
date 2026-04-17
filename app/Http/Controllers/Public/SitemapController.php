<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Enums\PostStatusEnum;
use App\Enums\RoleEnum;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::where('status', PostStatusEnum::PUBLISHED)
            ->latest()
            ->get();

        $writers = User::where('role', RoleEnum::WRITER)
            ->with('profile')
            ->get();

        return response()->view('public.sitemap', [
            'posts' => $posts,
            'writers' => $writers,
        ])->header('Content-Type', 'text/xml');
    }
}
