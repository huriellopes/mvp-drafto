<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Widgets;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProfileInfo extends Component
{
    #[Computed]
    public function user(): User
    {
        return auth()->user() ?? abort(403);
    }

    public function render(): View
    {
        return view('livewire.dashboard.widgets.profile-info');
    }
}
