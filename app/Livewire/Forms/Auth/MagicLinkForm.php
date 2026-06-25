<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use App\Actions\Auth\SendMagicLinkAction;
use App\Traits\Auth\WithRateLimiting;
use Livewire\Form;

class MagicLinkForm extends Form
{
    use WithRateLimiting;

    public string $email = '';

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function send(): void
    {
        $this->checkRateLimit($this->email, maxAttempts: 3, decaySeconds: 300);

        $this->validate();

        // Conta cada envio (não limpa) para que o limite de 3/5min seja efetivo
        // contra abuso/e-mail bombing.
        $this->incrementAttempts($this->email, decaySeconds: 300);

        app(SendMagicLinkAction::class)->exec(email: $this->email);
    }
}
