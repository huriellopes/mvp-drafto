<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\Actions\Profile\UpdateProfileAction;
use App\DTOs\UpdateProfileData;
use App\Enums\LinkVisibilityEnum;
use App\Enums\ProfileVisibilityEnum;
use App\Enums\SocialPlatformEnum;
use App\Enums\ThemePlatformEnum;
use App\Models\ProfileLink;
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

    public bool $show_subscriber_count = true;

    public bool $show_view_count = false;

    public string $seo_title = '';

    public string $seo_description = '';

    /** @var array<int, array{platform: string, url: string, visibility: string}> */
    public array $links = [];

    public $avatar;

    public $cover;

    public function setUser(User $user): void
    {
        $this->avatar = null;
        $this->cover = null;

        $profile = $user->profile;

        if (!$profile) {
            return;
        }

        $this->name = $profile->name ?? '';
        $this->username = $profile->username ?? '';
        $this->email = $profile->email ?? '';
        $this->bio = $profile->bio;
        $this->website_url = $profile->website_url ?? 'https://';
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
        $this->show_subscriber_count = (bool) $settings->show_subscriber_count;
        $this->show_view_count = (bool) $settings->show_view_count;

        $this->seo_title = $profile->seo?->title ?? '';
        $this->seo_description = $profile->seo?->description ?? '';

        $this->links = $profile->links
            ->map(fn (ProfileLink $link): array => [
                'platform' => $link->platformEnum()->value,
                'url' => $link->url,
                'visibility' => $link->visibilityEnum()->value,
            ])
            ->all();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.-]+$/', Rule::unique('profiles', 'username')->ignore(auth()->user()->profile?->id)],
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
            'show_subscriber_count' => ['boolean'],
            'show_view_count' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'avatar' => ['nullable', 'image', 'max:1024'],
            'cover' => ['nullable', 'image', 'max:2048'],
            'links' => ['array', 'max:8'],
            'links.*.platform' => ['required', Rule::enum(SocialPlatformEnum::class)],
            'links.*.url' => ['required', 'string', 'max:255', 'regex:#^https?://#i', 'url'],
            'links.*.visibility' => ['required', Rule::enum(LinkVisibilityEnum::class)],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'username' => 'nome de usuário',
            'email' => 'e-mail',
            'bio' => 'biografia',
            'website_url' => 'site',
            'location' => 'localização',
        ];
    }

    public function update(): void
    {
        $this->sanitizeWebsiteUrl();
        $this->sanitizeLinks();

        $this->validate();

        $dto = UpdateProfileData::from($this->all());

        app(UpdateProfileAction::class)->exec(
            user: auth()->user(),
            data: $dto,
        );
    }

    private function sanitizeWebsiteUrl(): void
    {
        $url = mb_trim($this->website_url ?? '');

        // Sênior: Se o campo estiver vazio ou for apenas o prefixo, limpamos para null
        if (empty($url) || in_array($url, ['https://', 'http://', 'https:/', 'http:/'], true)) {
            $this->website_url = null;

            return;
        }

        // Sênior: Se não possuir protocolo, forçamos https://
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }

        $url = str_replace(
            ['https://https://', 'https://http://', 'http://https://', 'http://http://'],
            ['https://', 'http://', 'https://', 'http://'],
            $url,
        );

        $this->website_url = $url;
    }

    private function sanitizeLinks(): void
    {
        $clean = [];

        foreach ($this->links as $link) {
            $url = mb_trim($link['url'] ?? '');

            if ($url === '') {
                continue; // ignora linhas sem URL
            }

            if (!preg_match('#^https?://#i', $url)) {
                $url = 'https://' . mb_ltrim($url, '/');
            }

            $clean[] = [
                'platform' => $link['platform'] ?? 'website',
                'url' => $url,
                'visibility' => $link['visibility'] ?? LinkVisibilityEnum::PUBLIC->value,
            ];
        }

        $this->links = $clean;
    }
}
