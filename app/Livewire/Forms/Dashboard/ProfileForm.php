<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\Actions\Profile\UpdateProfileAction;
use App\Enums\ThemePlatformEnum;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProfileForm extends Form
{
    public string $username = '';
    public ?string $bio = '';
    public ?string $website_url = '';
    public ?string $location = '';
    public string $primary_color = '#18181b';
    public string $accent_color = '#3f3f46';
    public string $theme_mode = 'system';
    public bool $show_email_publicly = false;

    public $avatar;
    public $cover;

    public function setUser(User $user): void
    {
        $profile = $user->profile;
        $this->username = $profile->username ?? '';
        $this->bio = $profile->bio;
        $this->website_url = $profile->website_url;
        $this->location = $profile->location;
        $this->primary_color = $profile->primary_color ?? '#18181b';
        $this->accent_color = $profile->accent_color ?? '#3f3f46';
        $this->theme_mode = $profile->theme_mode->value ?? 'system';
        $this->show_email_publicly = $profile->show_email_publicly ?? false;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:30', Rule::unique('profiles', 'username')->ignore(auth()->user()->profile?->id)],
            'bio' => ['nullable', 'string', 'max:160'],
            'website_url' => ['nullable', 'url'],
            'location' => ['nullable', 'string'],
            'primary_color' => ['required', 'hex_color'],
            'accent_color' => ['required', 'hex_color'],
            'theme_mode' => ['required', Rule::enum(ThemePlatformEnum::class)],
            'show_email_publicly' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:1024'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function update(): void
    {
        $this->validate();

        app(UpdateProfileAction::class)
            ->exec(
                user: auth()->user(),
                data: $this->all()
            );
    }
}
