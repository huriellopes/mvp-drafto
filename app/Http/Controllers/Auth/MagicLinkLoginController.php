<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginViaMagicLinkAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class MagicLinkLoginController extends Controller
{
    public function __invoke(string $token, LoginViaMagicLinkAction $action): RedirectResponse
    {
        return match ($action->exec($token)) {
            LoginViaMagicLinkAction::RESULT_TWO_FACTOR => to_route('auth.two-factor'),
            LoginViaMagicLinkAction::RESULT_SUCCESS => to_route('dashboard.index')
                ->with('success', __('auth.status.logged_in')),
            default => to_route('login')
                ->with('error', __('auth.magic_link.invalid')),
        };
    }
}
