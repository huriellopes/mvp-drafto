<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\ProfileVisibilityEnum;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProfileStatus extends Component
{
    /**
     * Get the original public profile URL.
     */
    #[Computed]
    public function profileUrl(): string
    {
        $username = Auth::user()->profile?->username;

        return $username ? route('profile.show', $username) : '#';
    }

    /**
     * Get the shareable profile URL (shortened if module is active).
     */
    #[Computed]
    public function shareUrl(): string
    {
        return Auth::user()->getShareUrl();
    }

    /**
     * Get the profile visibility status enum.
     */
    #[Computed]
    public function profileStatus(): ProfileVisibilityEnum
    {
        return Auth::user()->profile?->visibility ?? ProfileVisibilityEnum::PUBLIC;
    }

    /**
     * Identify missing fields in the public profile (Recommended for full status).
     */
    #[Computed]
    public function missingFields(): array
    {
        return Auth::user()->profile?->getRecommendedMissingFields() ?? [];
    }

    /**
     * Get completion percentage.
     */
    #[Computed]
    public function completionPercentage(): int
    {
        return Auth::user()->profile?->getCompletionPercentage() ?? 0;
    }

    /**
     * Check if the profile is fully completed.
     */
    #[Computed]
    public function isComplete(): bool
    {
        return Auth::user()->profile?->isComplete() ?? false;
    }

    public function render()
    {
        return view('livewire.dashboard.profile-status');
    }
}
