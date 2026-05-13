<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Public;

use Livewire\Attributes\Validate;
use Livewire\Form;

class CommentForm extends Form
{
    #[Validate('required|string|min:3|max:1000')]
    public string $content = '';

    public ?int $parent_id = null;

    public function resetForm(): void
    {
        $this->reset(['content', 'parent_id']);
    }
}
