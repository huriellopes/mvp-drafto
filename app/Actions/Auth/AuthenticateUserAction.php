<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthenticateUserAction
{
    /**
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws ValidationException
     * @throws AuthenticationException
     */
    public function exec(array $credentials, bool $remember = false): bool
    {
        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->isActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('Sua conta não está ativa no momento.'),
            ]);
        }

        session()->regenerate();

        $user->forceFill([
            'last_login_at' => Carbon::now(),
            'ip_address' => request()->ip(),
        ])->save();

        Auth::logoutOtherDevices($credentials['password']);

        return true;
    }
}
