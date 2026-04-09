<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Traits\Auth\WithRateLimiting;
use Livewire\Form;

class ForgotPasswordForm extends Form
{
    use WithRateLimiting;

    public string $email = '';

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O endereço de e-mail é obrigatório para o acesso.',
            'email.email' => 'Por favor, insira um formato de e-mail válido.',
        ];
    }

    public function save(): void
    {
        $this->checkRateLimit($this->email, maxAttempts: 3, decaySeconds: 300);

        $this->validate();

        app(SendPasswordResetLinkAction::class)
            ->exec(
                email: $this->email,
            );

        $this->clearAttempts($this->email);
    }
}
