<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\DTOs\SupportContactData;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SupportForm extends Form
{
    #[Validate(['required', 'string', 'min:3', 'max:255'])]
    public string $name = '';

    #[Validate(['required', 'email', 'max:255'])]
    public string $email = '';

    #[Validate(['required', 'string', 'min:5', 'max:255'])]
    public string $subject = '';

    #[Validate(['required', 'string', 'min:10'])]
    public string $message = '';

    public function toDTO(): SupportContactData
    {
        return SupportContactData::from([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);
    }
}
