<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

final class ProfileBadgeController extends Controller
{
    public function show(string $username, Request $request)
    {
        $profile = Profile::where('username', $username)->with('user')->firstOrFail();

        return view('public.profile.badge-frame', [
            'profile' => $profile,
            'user' => $profile->user,
            'theme' => $request->query('theme', 'dark'),
        ]);
    }
}
