<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\DTOs\AuthenticateData;
use App\Traits\Auth\WithRateLimiting;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class LoginForm extends Form
{
    use WithRateLimiting;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O endereço de e-mail é obrigatório para o acesso.',
            'email.email' => 'Por favor, insira um formato de e-mail válido.',
            'password.required' => 'A senha não pode estar em branco.',
        ];
    }

    /**
     * @throws AuthenticationException
     */
    public function authenticate(): void
    {
        $this->checkRateLimit($this->email);
        $this->validate();

        $dto = AuthenticateData::from([
            'email' => $this->email,
            'password' => $this->password,
            'remember' => (bool) $this->remember,
        ]);

        $success = app(AuthenticateUserAction::class)
            ->exec($dto);

        if ($success) {
            $this->clearAttempts($this->email);
        } else {
            $this->incrementAttempts($this->email);

            throw ValidationException::withMessages([
                'form.email' => __('auth.failed'),
            ]);
        }
    }
}
