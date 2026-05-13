<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Actions\Public\SubscribeNewsletterAction;
use App\DTOs\Public\NewsletterData;
use App\Traits\Livewire\HasStandardResponses;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NewsletterForm extends Component
{
    use HasStandardResponses;

    #[Validate('required|email|unique:newsletter_subscribers,email')]
    public string $email = '';

    public ?int $categoryId = null;

    public function subscribe(): void
    {
        $this->validate();

        // Sênior: Proteção contra Spam (Máximo 3 tentativas por minuto por IP)
        $executed = RateLimiter::attempt(
            'subscribe-newsletter:' . request()->ip(),
            $maxAttempts = 3,
            function () {
                app(SubscribeNewsletterAction::class)->exec(
                    new NewsletterData(email: $this->email, categoryId: $this->categoryId),
                );
            },
            decaySeconds: 60,
        );

        if (!$executed) {
            $this->notifyError('Muitas tentativas. Por favor, aguarde um momento.');

            return;
        }

        $this->reset('email');
        $this->notifySuccess('Bem-vindo ao Radar Drafto!');
    }

    public function render(): View
    {
        return view('livewire.public.newsletter-form');
    }
}
