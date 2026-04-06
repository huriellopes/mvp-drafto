<?php

namespace App\Livewire\Forms\Public;

use Livewire\Form;
use Livewire\Attributes\Validate;

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
