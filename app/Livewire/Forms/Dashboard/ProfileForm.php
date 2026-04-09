<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\Actions\Profile\UpdateProfileAction;
use App\DTOs\UpdateProfileData;
use App\Enums\ProfileVisibilityEnum;
use App\Enums\ThemePlatformEnum;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProfileForm extends Form
{
    public ?string $name = '';

    public string $username = '';

    public ?string $email = '';

    public ?string $bio = '';

    public ?string $website_url = '';

    public ?string $location = '';

    public string $primary_color = '#18181b';

    public string $accent_color = '#3f3f46';

    public string $theme_mode = 'system';

    public string $visibility = 'public';

    public bool $show_email_publicly = false;

    public bool $is_searchable = true;

    public $avatar;

    public $cover;

    public function setUser(User $user): void
    {
        $profile = $user->profile;
        $this->name = $profile->name ?? '';
        $this->username = $profile->username ?? '';
        $this->email = $profile->email ?? '';
        $this->bio = $profile->bio;
        $this->website_url = $profile->website_url;
        $this->location = $profile->location;
        $this->primary_color = $profile->primary_color ?? '#18181b';
        $this->accent_color = $profile->accent_color ?? '#3f3f46';
        $this->theme_mode = $profile->theme_mode->value ?? ThemePlatformEnum::SYSTEM->value;
        $this->visibility = $profile->visibility->value ?? ProfileVisibilityEnum::PUBLIC->value;
        $this->show_email_publicly = $profile->show_email_publicly ?? false;
        $this->is_searchable = $profile->is_searchable ?? true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'min:5'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.]+$/', Rule::unique('profiles', 'username')->ignore(auth()->user()->profile?->id)],
            'email' => ['nullable', 'string', 'email', Rule::unique('profiles', 'email')->ignore(auth()->user()->profile?->id)],
            'bio' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url'],
            'location' => ['nullable', 'string'],
            'primary_color' => ['required', 'hex_color'],
            'accent_color' => ['required', 'hex_color'],
            'theme_mode' => ['required', Rule::enum(ThemePlatformEnum::class)],
            'visibility' => ['required', Rule::enum(ProfileVisibilityEnum::class)],
            'show_email_publicly' => ['boolean'],
            'is_searchable' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:1024'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function update(): void
    {
        $this->validate();

        $dto = UpdateProfileData::from($this->all());

        app(UpdateProfileAction::class)->exec(
            user: auth()->user(),
            data: $dto,
        );
    }
}
