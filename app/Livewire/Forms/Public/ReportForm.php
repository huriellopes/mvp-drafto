<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Public;

use Livewire\Attributes\Validate;
use Livewire\Form;

class ReportForm extends Form
{
    public ?string $reportable_type = null;

    public ?int $reportable_id = null;

    #[Validate('required')]
    public string $reason = 'other';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    public function setTarget($type, $id)
    {
        $this->reportable_type = $type;
        $this->reportable_id = $id;
    }
}
