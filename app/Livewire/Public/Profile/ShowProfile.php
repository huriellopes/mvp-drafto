<?php

declare(strict_types=1);

namespace App\Livewire\Public\Profile;

use App\Enums\PostCollectionVisibilityEnum;
use App\Models\User;
use App\Services\Profile\ProfileSeoGenerator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.guest')]
class ShowProfile extends Component
{
    use WithPagination;

    public string $username;

    public function mount(string $username): void
    {
        $this->username = mb_strtolower(str_replace('@', '', $username));
    }

    #[Computed]
    public function user()
    {
        return Cache::tags(['profiles', "profile_{$this->username}"])
            ->remember("profile_view_data_{$this->username}", now()->addMinutes(60), function () {
                return User::query()
                    ->whereHas('profile', fn ($q) => $q->whereRaw('LOWER(username) = ?', [$this->username]))
                    ->with(['profile.settings', 'profile.links', 'followers', 'following'])
                    ->withCount(['posts' => fn ($q) => $q->published()])
                    ->firstOrFail();
            });
    }

    #[Computed]
    public function isProfileComplete(): bool
    {
        return $this->user->profile?->isComplete() ?? false;
    }

    #[Computed]
    public function isProfileEssentialComplete(): bool
    {
        return empty($this->user->profile?->getMissingFields());
    }

    #[Computed]
    public function isOwner(): bool
    {
        return auth()->check() && auth()->id() === $this->user->id;
    }

    #[Computed]
    public function posts()
    {
        return $this->user->posts()
            ->published()
            ->latest()
            ->paginate(12);
    }

    #[Computed]
    public function publicCollections()
    {
        return $this->user->postCollections()
            ->where('visibility', PostCollectionVisibilityEnum::PUBLIC)
            ->whereHas('posts', fn ($q) => $q->published())
            ->withCount(['posts as published_posts_count' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        $profile = $this->user->profile;
        $settings = $profile->settings;

        return view('livewire.public.profile.show-profile')
            ->layoutData([
                'themeMode' => $profile->theme_mode->value ?? 'light',
                'primaryColor' => $profile->primary_color,
                'accentColor' => $profile->accent_color,
                'secondaryColor' => $settings->secondary_color,
                'textColor' => $settings->text_color,
                'backgroundColor' => $settings->background_color,
                'buttonStyle' => $settings->button_style,
                'fontFamily' => $settings->font_family,
                'title' => $profile->display_name . ' (@' . $profile->username . ')',
                'seo' => ProfileSeoGenerator::generate($profile),
            ]);
    }
}
