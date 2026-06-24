<?php

declare(strict_types=1);

namespace App\Livewire\Public\Profile;

use App\Enums\PostCollectionVisibilityEnum;
use App\Models\PostCollection;
use App\Models\User;
use App\Services\Profile\ProfileSeoGenerator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.guest')]
class ShowProfileCollection extends Component
{
    use WithPagination;

    public string $username;

    public string $collection;

    public function mount(string $username, string $collection): void
    {
        $this->username = mb_strtolower(str_replace('@', '', $username));
        $this->collection = $collection;
    }

    #[Computed]
    public function user()
    {
        return User::query()
            ->whereHas('profile', fn ($q) => $q->whereRaw('LOWER(username) = ?', [$this->username]))
            ->with(['profile.settings'])
            ->firstOrFail();
    }

    #[Computed]
    public function postCollection(): PostCollection
    {
        return $this->user
            ->postCollections()
            ->where('slug', $this->collection)
            ->where('visibility', PostCollectionVisibilityEnum::PUBLIC)
            ->firstOrFail();
    }

    #[Computed]
    public function posts()
    {
        return $this->postCollection
            ->posts()
            ->published()
            ->latest()
            ->paginate(12);
    }

    public function render(): View
    {
        $profile = $this->user->profile;
        $settings = $profile->settings;

        return view('livewire.public.profile.show-profile-collection')
            ->layoutData([
                'themeMode' => $profile->theme_mode->value ?? 'light',
                'primaryColor' => $profile->primary_color,
                'accentColor' => $profile->accent_color,
                'secondaryColor' => $settings->secondary_color,
                'textColor' => $settings->text_color,
                'backgroundColor' => $settings->background_color,
                'buttonStyle' => $settings->button_style,
                'fontFamily' => $settings->font_family,
                'title' => $this->postCollection->name . ' — @' . $profile->username,
                'seo' => ProfileSeoGenerator::generate($profile),
            ]);
    }
}
