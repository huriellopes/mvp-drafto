<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Widgets;

use App\Actions\Dashboard\GetSuggestedWritersAction;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SuggestedWriters extends Component
{
    #[Computed]
    public function suggestions()
    {
        return app(GetSuggestedWritersAction::class)->exec(auth()->user());
    }

    public function render(): View
    {
        return view('livewire.dashboard.widgets.suggested-writers');
    }
}
