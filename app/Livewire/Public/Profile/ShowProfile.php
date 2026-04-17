<?php

declare(strict_types=1);

namespace App\Livewire\Public\Profile;

use App\Models\User;
use App\Services\Profile\ProfileSeoGenerator;
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
        return User::query()
            ->whereHas('profile', fn ($q) => $q->whereRaw('LOWER(username) = ?', [$this->username]))
            ->with(['profile', 'followers', 'following'])
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->firstOrFail();
    }

    #[Computed]
    public function isProfileComplete(): bool
    {
        $profile = $this->user->profile;

        return !empty($profile?->name)
            && !empty($this->user->email)
            && !empty($profile?->bio);
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

    public function render(): View
    {
        $profile = $this->user->profile;

        return view('livewire.public.profile.show-profile')
            ->layoutData([
                'themeMode' => $profile->theme_mode->value ?? 'light',
                'primaryColor' => $profile->primary_color,
                'accentColor' => $profile->accent_color,
                'title' => $profile->display_name . ' (@' . $profile->username . ')',
                'seo' => ProfileSeoGenerator::generate($profile),
            ]);
    }
}
