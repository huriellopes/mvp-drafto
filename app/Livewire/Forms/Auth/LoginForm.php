<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use Illuminate\Auth\AuthenticationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate(['required', 'email'])]
    public string $email = '';

    #[Validate(['required', 'string'])]
    public string $password = '';

    #[Validate(['boolean'])]
    public bool $remember = false;

    /**
     * @throws AuthenticationException
     */
    public function authenticate(): void
    {
        $this->validate();

        app(AuthenticateUserAction::class)
            ->exec(
                credentials: $this->only(['email', 'password']),
                remember: $this->remember,
            );
    }
}
