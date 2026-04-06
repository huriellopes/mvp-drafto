<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Admin;

use Livewire\Form;

class ReportFilterForm extends Form
{
    public ?string $search = '';
    public ?string $status = '';
    public ?string $reason = '';
    public string $sort = 'created_at';
    public string $direction = 'desc';

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'reason', 'sort', 'direction']);
    }
}
