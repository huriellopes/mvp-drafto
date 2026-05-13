<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\ProfileVisibilityEnum;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfileStatus extends Component
{
    /**
     * Get the public profile URL.
     */
    public function getProfileUrlProperty(): string
    {
        $username = Auth::user()->profile?->username;

        return $username ? route('profile.show', $username) : '#';
    }

    /**
     * Get the profile visibility status enum.
     */
    public function getProfileStatusProperty(): ProfileVisibilityEnum
    {
        return Auth::user()->profile?->visibility ?? ProfileVisibilityEnum::PUBLIC;
    }

    /**
     * Identify missing fields in the public profile.
     */
    public function getMissingFieldsProperty(): array
    {
        return Auth::user()->profile?->getMissingFields() ?? [];
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentageProperty(): int
    {
        return Auth::user()->profile?->getCompletionPercentage() ?? 0;
    }

    /**
     * Check if the profile is fully completed.
     */
    public function getIsCompleteProperty(): bool
    {
        return Auth::user()->profile?->isComplete() ?? false;
    }

    public function render()
    {
        return view('livewire.dashboard.profile-status');
    }
}
