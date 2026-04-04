<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use App\Actions\Auth\RegisterUserAction;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Throwable;

class RegisterForm extends Form
{
    #[Validate(['required', 'string', 'max:255'])]
    public string $name = '';

    #[Validate(['required', 'email', 'unique:users,email'])]
    public string $email = '';

    #[Validate(['required', 'string'])] // Password::defaults() é excelente aqui
    public string $password = '';

    #[Validate(['required', 'string', 'in:writer,reader'])]
    public string $role = 'reader';

    /**
     * @throws Throwable
     */
    public function store(): void
    {
        $this->validate();

        $user = app(RegisterUserAction::class)
            ->exec(
                data: $this->all(),
            );

        auth()->login($user);
    }
}
