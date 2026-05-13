<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Profile;

use App\Enums\ModuleEnum;
use App\Livewire\Forms\Dashboard\BadgeForm;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Crachá do Escritor')]
class ProfileBadgeGenerator extends Component
{
    public BadgeForm $form;

    public function mount(): void
    {
        $canUseBrand = auth()->user()->getModuleSetting(
            ModuleEnum::PROFILE_BADGE,
            'themes_available',
        );

        $this->form->theme = (is_array($canUseBrand) && in_array('brand', $canUseBrand, true))
            ? 'brand'
            : 'dark';
    }

    #[Computed]
    public function user(): User
    {
        return auth()->user()->load('profile');
    }

    public function render(): View
    {
        return view('livewire.dashboard.profile.profile-badge-generator');
    }
}
