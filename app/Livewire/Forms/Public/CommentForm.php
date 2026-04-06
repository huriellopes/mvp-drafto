<?php

namespace App\Livewire\Forms\Public;

use Livewire\Form;
use Livewire\Attributes\Validate;

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
