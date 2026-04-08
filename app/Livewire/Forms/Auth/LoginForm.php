<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use Illuminate\Auth\AuthenticationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function rules() : array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages() : array
    {
        return [
            'email.required' => 'O campo email é obrigatório.'
        ];
    }

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
