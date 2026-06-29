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
            'enotifications.required' => 'O endereço de e-mail é obrigatório para o acesso.',
            'enotifications.email' => 'Por favor, insira um formato de e-mail válido.',
        ];
    }

    public function save(): void
    {
        $this->checkRateLimit($this->email, maxAttempts: 3, decaySeconds: 300);

        $this->validate();

        // Conta cada envio (não limpa) para que o limite de 3/5min seja efetivo
        // contra abuso/e-mail bombing.
        $this->incrementAttempts($this->email, decaySeconds: 300);

        resolve(SendPasswordResetLinkAction::class)
            ->exec(
                email: $this->email,
            );
    }
}
