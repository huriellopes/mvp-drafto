<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Modules;

use App\Actions\Modules\UpdateModuleSettingAction;
use App\Enums\ModuleEnum;
use App\Models\ShortLink;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Encurtador de Links', 'subheading' => 'Gerencie suas URLs curtas e acompanhe cliques.'])]
#[Title('Meus Links')]
final class LinkShortenerDashboard extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public bool $enableForProfile;

    public bool $enableForPosts;

    public function mount(): void
    {
        $this->enableForProfile = (bool) auth()->user()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_profile', true);
        $this->enableForPosts = (bool) auth()->user()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_posts', true);
    }

    public function updatedEnableForProfile($value): void
    {
        app(UpdateModuleSettingAction::class)->exec(
            user: auth()->user(),
            module: ModuleEnum::LINK_SHORTENER,
            key: 'enable_for_profile',
            value: (bool) $value,
        );
        Toaster::success('Configuração de perfil atualizada.');
    }

    public function updatedEnableForPosts($value): void
    {
        app(UpdateModuleSettingAction::class)->exec(
            user: auth()->user(),
            module: ModuleEnum::LINK_SHORTENER,
            key: 'enable_for_posts',
            value: (bool) $value,
        );
        Toaster::success('Configuração de posts atualizada.');
    }

    #[Computed]
    public function links()
    {
        return ShortLink::query()
            ->where('user_id', auth()->id())
            ->with('shortable')
            ->when($this->search, function ($query) {
                $query->where('code', 'like', "%{$this->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function stats()
    {
        return [
            'total_links' => ShortLink::where('user_id', auth()->id())->count(),
            'total_clicks' => ShortLink::where('user_id', auth()->id())->sum('clicks'),
            'most_clicked' => ShortLink::where('user_id', auth()->id())->orderBy('clicks', 'desc')->first(),
        ];
    }

    public function render(): View
    {
        return view('livewire.dashboard.modules.link-shortener-dashboard');
    }
}
