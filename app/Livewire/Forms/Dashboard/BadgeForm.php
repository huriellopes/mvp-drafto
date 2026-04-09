<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\DTOs\BadgeData;
use Livewire\Form;

class BadgeForm extends Form
{
    public string $theme = 'dark';

    public bool $showStats = true;

    public bool $showBio = true;

    public bool $showLocation = true;

    public string $borderRadius = 'rounded-[2.5rem]';

    public function getBadgeData(): BadgeData
    {
        return BadgeData::from($this->all());
    }
}
