<?php

declare(strict_types=1);

namespace App\Traits\Auth;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait WithRateLimiting
{
    /**
     * Verifica se as tentativas excederam o limite e bloqueia se necessário.
     */
    protected function checkRateLimit(string $key, int $maxAttempts = 5, int $decaySeconds = 60): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($key), $maxAttempts)) {
            $this->handleAccountLockdown($key);

            $seconds = RateLimiter::availableIn($this->throttleKey($key));

            throw ValidationException::withMessages([
                'form.email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }

    /**
     * Incrementa o contador de falhas.
     */
    protected function incrementAttempts(string $key, int $decaySeconds = 60): void
    {
        RateLimiter::hit($this->throttleKey($key), $decaySeconds);
    }

    /**
     * Limpa os contadores após sucesso.
     */
    protected function clearAttempts(string $key): void
    {
        RateLimiter::clear($this->throttleKey($key));
    }

    /**
     * Lógica Sênior: Se passar de 10 tentativas, bloqueia o usuário no banco.
     */
    private function handleAccountLockdown(string $key): void
    {
        $attempts = RateLimiter::attempts($this->throttleKey($key));

        // Se houver 10 tentativas falhas, buscamos o usuário e bloqueamos
        if ($attempts >= 10) {
            $user = User::where('email', $key)->first();

            if ($user && $user->status !== UserStatusEnum::BLOCKED) {
                $user->update(['status' => UserStatusEnum::BLOCKED]);
                // Aqui poderíamos disparar um Evento para notificar o usuário por e-mail
            }
        }
    }

    private function throttleKey(string $key): string
    {
        return Str::transliterate(Str::lower($key) . '|' . request()->ip());
    }
}
