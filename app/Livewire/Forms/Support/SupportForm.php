<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Support;

use App\Actions\Support\StoreSupportAction;
use App\DTOs\SupportData;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class SupportForm extends Form
{
    #[Validate('required|string|min:5|max:255')]
    public string $subject = '';

    #[Validate('required|string|min:10|max:2000')]
    public string $message = '';

    public function store(): void
    {
        $this->validate();

        app(StoreSupportAction::class)->exec(
            auth()->user(),
            SupportData::from($this->all()),
        );

        $this->reset(['subject', 'message']);
    }
}
