<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Admin;

use Livewire\Form;

class NewsletterFilterForm extends Form
{
    public string $search = '';

    public ?int $category_id = null;

    public string $sort = 'created_at';

    public string $direction = 'desc';

    public function resetFilters(): void
    {
        $this->reset(['search', 'category_id', 'sort', 'direction']);
    }
}
