<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\AuthenticateData;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Notifications\Auth\AccountBlockedNotification;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Masmerise\Toaster\Toaster;

final class AuthenticateUserAction
{
    /**
     * @throws AuthenticationException
     */
    public function exec(AuthenticateData $data): bool
    {
        $user = User::query()
            ->where('email', $data->email)
            ->first();

        if ($user && $user->status === UserStatusEnum::BLOCKED) {
            $this->handleBlockedUser($user);
        }

        if (!Auth::attempt(['email' => $data->email, 'password' => $data->password], $data->remember)) {
            return false;
        }

        /** @var User $loggedUser */
        $loggedUser = Auth::user();

        if ($loggedUser->status === UserStatusEnum::INACTIVE) {
            Auth::logout();

            throw ValidationException::withMessages(['email' => 'Sua conta ainda não foi ativada.']);
        }

        if ($loggedUser->banned_until?->isFuture()) {
            Auth::logout();
            $days = now()->diffInDays($loggedUser->banned_until);

            throw ValidationException::withMessages(['email' => "Acesso suspenso por mais {$days} dias."]);
        }

        if (!empty($data->password)) {
            Auth::logoutOtherDevices($data->password);
        }

        session()->regenerate();

        $loggedUser->update([
            'last_login_at' => Carbon::now(),
            'ip_address' => request()->ip(),
        ]);

        return true;
    }

    private function handleBlockedUser(User $user): void
    {
        $user->notify(new AccountBlockedNotification());

        Toaster::error('Conta bloqueada pelo administrador da plataforma ou por excesso de tentativas.');

        throw ValidationException::withMessages([
            'email' => 'Esta conta foi bloqueada por segurança. Verifique seu e-mail para instruções.',
        ]);
    }
}
