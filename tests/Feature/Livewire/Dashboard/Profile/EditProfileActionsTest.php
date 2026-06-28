<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Profile;

use App\Enums\LinkVisibilityEnum;
use App\Livewire\Dashboard\Profile\EditProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Http::fake([
        '*' => Http::response([], 200),
    ]);
});

it('exposes computed profile, social platforms and ufs on mount', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    $component = Livewire::test(EditProfile::class);

    expect($component->get('profile'))->not->toBeNull()
        ->and($component->get('socialPlatforms'))->not->toBeEmpty();
});

it('adds a link up to the maximum of 8', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    $component = Livewire::test(EditProfile::class)
        ->set('form.links', [])
        ->call('addLink');

    expect($component->get('form.links'))->toHaveCount(1)
        ->and($component->get('form.links')[0]['visibility'])->toBe(LinkVisibilityEnum::PUBLIC->value);
});

it('does not add a link beyond the limit of 8', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    $links = array_fill(0, 8, ['platform' => 'instagram', 'url' => 'https://x.com', 'visibility' => LinkVisibilityEnum::PUBLIC->value]);

    $component = Livewire::test(EditProfile::class)
        ->set('form.links', $links)
        ->call('addLink');

    expect($component->get('form.links'))->toHaveCount(8);
});

it('removes a link and reindexes the array', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    $links = [
        ['platform' => 'instagram', 'url' => 'https://a.com', 'visibility' => LinkVisibilityEnum::PUBLIC->value],
        ['platform' => 'facebook', 'url' => 'https://b.com', 'visibility' => LinkVisibilityEnum::PUBLIC->value],
        ['platform' => 'github', 'url' => 'https://c.com', 'visibility' => LinkVisibilityEnum::PUBLIC->value],
    ];

    $component = Livewire::test(EditProfile::class)
        ->set('form.links', $links)
        ->call('removeLink', 1);

    $result = $component->get('form.links');

    expect($result)->toHaveCount(2)
        ->and(array_keys($result))->toBe([0, 1])
        ->and($result[1]['url'])->toBe('https://c.com');
});

it('reorders links from one position to another', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    $links = [
        ['platform' => 'instagram', 'url' => 'https://a.com', 'visibility' => LinkVisibilityEnum::PUBLIC->value],
        ['platform' => 'facebook', 'url' => 'https://b.com', 'visibility' => LinkVisibilityEnum::PUBLIC->value],
        ['platform' => 'github', 'url' => 'https://c.com', 'visibility' => LinkVisibilityEnum::PUBLIC->value],
    ];

    $component = Livewire::test(EditProfile::class)
        ->set('form.links', $links)
        ->call('reorderLinks', 0, 2);

    $result = $component->get('form.links');

    expect($result[0]['url'])->toBe('https://b.com')
        ->and($result[2]['url'])->toBe('https://a.com');
});

it('ignores reorder when indices are equal or out of range', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    $links = [
        ['platform' => 'instagram', 'url' => 'https://a.com', 'visibility' => LinkVisibilityEnum::PUBLIC->value],
        ['platform' => 'facebook', 'url' => 'https://b.com', 'visibility' => LinkVisibilityEnum::PUBLIC->value],
    ];

    $component = Livewire::test(EditProfile::class)
        ->set('form.links', $links)
        ->call('reorderLinks', 1, 1)
        ->call('reorderLinks', 0, 99);

    expect($component->get('form.links')[0]['url'])->toBe('https://a.com');
});

it('clears the location when the selected uf changes', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.location', 'Somewhere')
        ->set('selectedUf', 'SP')
        ->assertSet('form.location', '');
});

it('returns empty municipios without a selected uf', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    $component = Livewire::test(EditProfile::class)
        ->set('selectedUf', '');

    expect($component->get('municipios'))->toBe([]);
});

it('uploads a definitive avatar immediately', function () {
    Storage::fake('public');

    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.avatar', UploadedFile::fake()->image('avatar.jpg'))
        ->assertHasNoErrors()
        ->assertSet('form.avatar', null);

    expect($user->fresh()->profile->avatar_path)->not->toBeNull();
});

it('opens the cropper modal when a cover is uploaded', function () {
    Storage::fake('public');

    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.cover', UploadedFile::fake()->image('cover.jpg', 1200, 400))
        ->assertHasNoErrors()
        ->assertSet('isCoverCropped', false)
        ->assertDispatched('open-modal', name: 'cover-cropper-modal');
});

it('saves the cropped cover and closes the modal', function () {
    Storage::fake('public');

    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.cover', UploadedFile::fake()->image('cover.jpg', 1200, 400))
        ->call('saveCrop', ['x' => 0, 'y' => 0, 'width' => 1200, 'height' => 400])
        ->assertSet('isCoverCropped', true)
        ->assertSet('form.cover', null)
        ->assertDispatched('close-modal', name: 'cover-cropper-modal');

    expect($user->fresh()->profile->cover_path)->not->toBeNull();
});

it('removes the avatar and deletes the stored file', function () {
    Storage::fake('public');

    $user = User::factory()->writer()->withProfile()->create();
    Storage::disk('public')->put('avatars/old.jpg', 'content');
    $user->profile->update(['avatar_path' => 'avatars/old.jpg']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->call('removeAvatar');

    expect($user->fresh()->profile->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing('avatars/old.jpg');
});

it('removes the cover and deletes the stored file', function () {
    Storage::fake('public');

    $user = User::factory()->writer()->withProfile()->create();
    Storage::disk('public')->put('covers/old.webp', 'content');
    $user->profile->update(['cover_path' => 'covers/old.webp']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->call('removeCover');

    expect($user->fresh()->profile->cover_path)->toBeNull();
    Storage::disk('public')->assertMissing('covers/old.webp');
});

it('appends the selected uf to the location on save', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'City User')
        ->set('form.username', 'cityuser')
        ->set('form.email', 'city@example.com')
        ->set('selectedUf', 'SP')
        ->set('form.location', 'Campinas')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->profile->location)->toBe('Campinas, SP');
});
