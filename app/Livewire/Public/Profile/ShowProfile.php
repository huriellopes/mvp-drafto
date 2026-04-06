<?php

declare(strict_types=1);

namespace App\Livewire\Public\Profile;

use App\Models\User;
use App\Models\Profile;
use App\Services\Profile\ProfileSeoGenerator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ShowProfile extends Component
{
    use WithPagination;

    public string $username;

    public function mount(string $username): void
    {
        $this->username = strtolower(str_replace('@', '', $username));
    }

    #[Computed]
    public function user()
    {
        return User::query()
            ->whereHas('profile', function ($query) {
                $query->whereRaw('LOWER(username) = ?', [$this->username]);
            })
            ->with(['profile', 'followers', 'following'])
            ->first();
    }

    #[Computed]
    public function isProfileComplete(): bool
    {
        $profile = $this->user->profile;

        return !empty($profile?->name)
            && !empty($this->user->email)
            && !empty($profile?->username);
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

    public function render() : View
    {
        $user = $this->user;

        // Se o perfil não existe de fato no banco, aí sim lançamos o 404 manual
        if (!$user) {
            abort(404, 'Escritor não encontrado.');
        }

        $profile = $user->profile;
        $displayName = $profile->name ?? $profile->username;

        return view('livewire.public.profile.show-profile')
            ->layout('layouts.guest', [
                'themeMode' => $profile->theme_mode->value ?? 'light',
                'primaryColor' => $profile->primary_color ?? '#18181b',
                'accentColor' => $profile->accent_color ?? '#3f3f46',
                'title' => "{$displayName} (@{$profile->username}) | Drafto",
                'seo' => ProfileSeoGenerator::generate($profile),
            ]);
    }
}
