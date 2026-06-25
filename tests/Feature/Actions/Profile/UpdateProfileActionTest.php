<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\UpdateProfileAction;
use App\DTOs\UpdateProfileData;
use App\Jobs\ProcessProfileMediaJob;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

function makeProfileData(array $overrides = []): UpdateProfileData
{
    return UpdateProfileData::from(array_merge([
        'name' => 'New Name',
        'username' => 'newhandle',
        'email' => 'new@example.com',
        'bio' => 'Bio',
        'location' => null,
        'website_url' => 'https://example.com',
        'primary_color' => '#111111',
        'accent_color' => '#222222',
        'theme_mode' => 'light',
        'visibility' => 'public',
        'show_email_publicly' => false,
        'is_searchable' => true,
    ], $overrides));
}

it('stores avatar and cover and dispatches the media processing job', function () {
    Storage::fake('public');
    Bus::fake();

    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    app(UpdateProfileAction::class)->exec($user, makeProfileData([
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        'cover' => UploadedFile::fake()->image('cover.jpg'),
    ]));

    $profile = $user->refresh()->profile;

    expect($profile->avatar_path)->not->toBeNull()
        ->and($profile->cover_path)->not->toBeNull();

    Storage::disk('public')->assertExists($profile->avatar_path);
    Storage::disk('public')->assertExists($profile->cover_path);

    Bus::assertDispatched(ProcessProfileMediaJob::class);
});

it('crops and stores the cover when crop data is provided', function () {
    Storage::fake('public');
    Bus::fake();

    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    app(UpdateProfileAction::class)->exec(
        $user,
        makeProfileData(['cover' => UploadedFile::fake()->image('cover.jpg', 1200, 400)]),
        ['width' => 600, 'height' => 200, 'x' => 0, 'y' => 0],
    );

    $profile = $user->refresh()->profile;

    expect($profile->cover_path)->not->toBeNull()
        ->and($profile->cover_path)->toContain('covers/');
    Storage::disk('public')->assertExists($profile->cover_path);
});

it('persists the SEO metadata when provided', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    app(UpdateProfileAction::class)->exec($user, makeProfileData([
        'seo_title' => 'Meu Título SEO',
        'seo_description' => 'Minha descrição SEO',
    ]));

    $seo = $user->refresh()->profile->seo;

    expect($seo)->not->toBeNull()
        ->and($seo->title)->toBe('Meu Título SEO')
        ->and($seo->description)->toBe('Minha descrição SEO');
});

it('syncs the user name when it changes', function () {
    $user = User::factory()->create(['name' => 'Old Name']);
    Profile::factory()->create(['user_id' => $user->id]);

    app(UpdateProfileAction::class)->exec($user, makeProfileData(['name' => 'Brand New Name']));

    expect($user->refresh()->name)->toBe('Brand New Name');
});

it('does not dispatch the media job when no images are uploaded', function () {
    Bus::fake();

    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    app(UpdateProfileAction::class)->exec($user, makeProfileData());

    Bus::assertNotDispatched(ProcessProfileMediaJob::class);
});
