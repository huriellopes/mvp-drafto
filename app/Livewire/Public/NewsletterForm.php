<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Actions\Public\SubscribeNewsletterAction;
use App\DTOs\Public\NewsletterData;
use App\Traits\Livewire\HasStandardResponses;
use Illuminate\Contracts\View\View;
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

        resolve(SubscribeNewsletterAction::class)->exec(
            new NewsletterData(email: $this->email, categoryId: $this->categoryId),
        );

        $this->reset('email');
        $this->notifySuccess('Bem-vindo ao Radar Drafto!');
    }

    public function render(): View
    {
        return view('livewire.public.newsletter-form');
    }
}
