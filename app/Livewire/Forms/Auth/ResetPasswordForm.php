<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\DTOs\ResetPasswordData;
use App\Traits\Auth\WithRateLimiting;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class ResetPasswordForm extends Form
{
    use WithRateLimiting;

    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'O campo token é obrigatório',
            'enotifications.required' => 'O endereço de e-mail é obrigatório para o acesso.',
            'enotifications.email' => 'Por favor, insira um formato de e-mail válido.',
            'password.required' => 'A senha não pode estar em branco.',
            'password.min' => 'A senha tem que ter no mínimo 8 caracteres.',
            'password.confirmed' => 'As senhas não são idênticas.',
        ];
    }

    public function submit(): void
    {
        // Segurança: inconsistente sem isto — todo outro formulário de auth
        // do app (login, cadastro, magic link) já usa WithRateLimiting; o
        // token de reset tem entropia alta (força bruta é inviável na
        // prática), mas mantém o padrão de defesa em profundidade.
        $this->checkRateLimit($this->email, maxAttempts: 5, decaySeconds: 60);
        $this->validate();

        try {
            resolve(ResetPasswordAction::class)->exec(
                data: ResetPasswordData::from([
                    'token' => $this->token,
                    'email' => $this->email,
                    'password' => $this->password,
                    'password_confirmation' => $this->password_confirmation,
                ]),
            );
        } catch (ValidationException $e) {
            $this->incrementAttempts($this->email);

            throw $e;
        }

        $this->clearAttempts($this->email);
    }
}
