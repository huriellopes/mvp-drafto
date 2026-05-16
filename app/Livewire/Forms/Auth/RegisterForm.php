<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\DTOs\RegisterUserData;
use App\Traits\Auth\WithRateLimiting;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;
use Throwable;

class RegisterForm extends Form
{
    use WithRateLimiting;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'reader';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::default()],
            'role' => ['required', 'string', 'in:writer,reader'],
        ];
    }

    /**
     * @throws Throwable
     */
    public function store(): void
    {
        // Evita bot-spam de criação de conta por IP
        $this->checkRateLimit(request()->ip(), maxAttempts: 3, decaySeconds: 3600);

        $this->validate();

        $user = app(RegisterUserAction::class)
            ->exec(
                data: RegisterUserData::from($this->all()),
            );

        auth()->login($user);

        $this->clearAttempts(request()->ip());
    }
}
