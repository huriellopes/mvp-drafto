<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Actions\Public\SubscribeNewsletterAction;
use App\DTOs\Public\NewsletterData;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class NewsletterForm extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    public ?int $categoryId = null;

    public function subscribe(): void
    {
        $this->validate();

        app(SubscribeNewsletterAction::class)->exec(
            new NewsletterData(email: $this->email, categoryId: $this->categoryId),
        );

        $this->reset('email');
        Toaster::success('Bem-vindo ao Radar Drafto!');
    }

    public function render(): View
    {
        return view('livewire.public.newsletter-form');
    }
}
