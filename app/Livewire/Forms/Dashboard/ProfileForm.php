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

    public string $button_style = 'rounded-md';

    public string $card_style = 'bordered';

    public string $layout_type = 'default';

    public string $font_family = 'sans';

    public ?string $secondary_color = null;

    public ?string $text_color = null;

    public ?string $background_color = null;

    public bool $show_badges = true;

    public bool $show_subscriber_count = true;

    public bool $show_view_count = false;

    public string $seo_title = '';

    public string $seo_description = '';

    public $avatar;

    public $cover;

    public function setUser(User $user): void
    {
        $profile = $user->profile;

        if (!$profile) {
            return;
        }

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

        $settings = $profile->settings;
        $this->button_style = $settings->button_style;
        $this->card_style = $settings->card_style;
        $this->layout_type = $settings->layout_type;
        $this->font_family = $settings->font_family;
        $this->secondary_color = $settings->secondary_color;
        $this->text_color = $settings->text_color;
        $this->background_color = $settings->background_color;
        $this->show_badges = (bool) $settings->show_badges;
        $this->show_subscriber_count = (bool) $settings->show_subscriber_count;
        $this->show_view_count = (bool) $settings->show_view_count;

        $this->seo_title = $profile->seo?->title ?? '';
        $this->seo_description = $profile->seo?->description ?? '';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.]+$/', Rule::unique('profiles', 'username')->ignore(auth()->user()->profile?->id)],
            'email' => ['required', 'string', 'email', Rule::unique('profiles', 'email')->ignore(auth()->user()->profile?->id)],
            'bio' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url'],
            'location' => ['nullable', 'string'],
            'primary_color' => ['required', 'hex_color'],
            'accent_color' => ['required', 'hex_color'],
            'theme_mode' => ['required', Rule::enum(ThemePlatformEnum::class)],
            'visibility' => ['required', Rule::enum(ProfileVisibilityEnum::class)],
            'show_email_publicly' => ['boolean'],
            'is_searchable' => ['boolean'],
            'button_style' => ['required', 'string'],
            'card_style' => ['required', 'string'],
            'layout_type' => ['required', 'string'],
            'font_family' => ['required', 'string'],
            'secondary_color' => ['nullable', 'hex_color'],
            'text_color' => ['nullable', 'hex_color'],
            'background_color' => ['nullable', 'hex_color'],
            'show_badges' => ['boolean'],
            'show_subscriber_count' => ['boolean'],
            'show_view_count' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
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
