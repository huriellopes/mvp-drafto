<?php

namespace App\Livewire\Public;

use App\Models\NewsletterSubscriber;
use Livewire\Component;
use Livewire\Attributes\Validate;

class NewsletterForm extends Component
{
    #[Validate('required|email|unique:newsletter_subscribers,email')]
    public string $email = '';
    public ?int $categoryId = null;

    public function subscribe()
    {
        $this->validate();
        NewsletterSubscriber::create(['email' => $this->email, 'category_id' => $this->categoryId]);
        $this->reset('email');
        $this->dispatch('notify', message: 'Inscrição realizada com sucesso!');
    }

    public function render()
    {
        return view('livewire.public.newsletter-form');
    }
}
