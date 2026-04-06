<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Follows;

use App\Actions\Follows\ListFollowsAction;
use App\DTOs\FollowersFilterData;
use App\Models\Follower;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\{Computed, Layout, Title, Url};
use Livewire\{Component, WithPagination};
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Comunidade', 'subheading' => 'Gerencie suas conexões na rede'])]
#[Title('Seguidores e Seguindo')]
class FollowIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $tab = 'followers';

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sort = 'created_at';

    #[Url(history: true)]
    public string $direction = 'desc';

    public ?int $userIdToUnfollow = null;

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        $this->direction = ($this->sort === $column && $this->direction === 'asc') ? 'desc' : 'asc';
        $this->sort = $column;
    }

    public function confirmUnfollow(int $userId): void
    {
        $this->userIdToUnfollow = $userId;
        $this->dispatch('open-modal', name: 'confirm-unfollow-modal');
    }

    public function unfollow(): void
    {
        if (!$this->userIdToUnfollow) return;

        $user = User::findOrFail($this->userIdToUnfollow);
        auth()->user()->following()->detach($user->id);

        $this->userIdToUnfollow = null;
        Toaster::success("Você deixou de seguir {$user->name}.");
    }

    #[Computed]
    public function follows()
    {
        return app(ListFollowsAction::class)->exec(
            user: auth()->user(),
            filters: FollowersFilterData::fromArray(['search' => $this->search]),
            type: $this->tab
        );
    }

    public function render(): View
    {
        $this->authorize('viewAny', Follower::class);

        return view('livewire.dashboard.follows.follow-index');
    }
}
