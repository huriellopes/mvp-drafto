<?php

declare(strict_types=1);

namespace App\Livewire\Public\Profile;

use App\Models\User;
use App\Models\Profile;
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
        $this->username = str_replace('@', '', $username);
    }

    #[Computed]
    public function user(): User
    {
        return User::whereHas('profile', fn($q) => $q->where('username', $this->username))
            ->with(['profile', 'followers', 'following'])
            ->firstOrFail();
    }

    #[Computed]
    public function posts()
    {
        return $this->user->posts()
            ->published()
            ->latest()
            ->paginate(12);
    }

    #[Layout('layouts.guest')]
    public function render() : View
    {
        return view('livewire.public.profile.show-profile')
            ->title($this->user->name . " (@{$this->username})");
    }
}
