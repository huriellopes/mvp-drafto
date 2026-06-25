<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Enums\ProfileVisibilityEnum;
use App\Enums\SocialPlatformEnum;
use App\Livewire\Dashboard\Profile\EditProfile;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(fn () => Cache::flush());

it('adds and removes link rows in the editor', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'username' => 'linkuser']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->call('addLink')
        ->call('addLink')
        ->assertCount('form.links', 2)
        ->call('removeLink', 0)
        ->assertCount('form.links', 1);
});

it('saves profile links and forces the https scheme', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'username' => 'linkuser']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'Link User')
        ->set('form.username', 'linkuser')
        ->set('form.email', 'link@example.com')
        ->call('addLink')
        ->set('form.links.0.platform', 'instagram')
        ->set('form.links.0.url', 'instagram.com/foo') // sem esquema
        ->call('save')
        ->assertHasNoErrors();

    $link = $user->refresh()->profile->links()->first();
    expect($link)->not->toBeNull()
        ->and($link->platform)->toBe('instagram')
        ->and($link->platformEnum())->toBe(SocialPlatformEnum::INSTAGRAM)
        ->and($link->url)->toBe('https://instagram.com/foo');
});

it('never stores a javascript: scheme url (xss protection)', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'username' => 'linkuser']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'Link User')
        ->set('form.username', 'linkuser')
        ->set('form.email', 'link@example.com')
        ->call('addLink')
        ->set('form.links.0.platform', 'website')
        ->set('form.links.0.url', 'javascript:alert(1)')
        ->call('save')
        ->assertHasErrors('form.links.0.url');

    // Em hipótese alguma um esquema javascript: pode ser persistido.
    expect($user->refresh()->profile->links()->where('url', 'like', 'javascript:%')->exists())->toBeFalse();
});

it('drops empty link rows on save', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'username' => 'linkuser']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'Link User')
        ->set('form.username', 'linkuser')
        ->set('form.email', 'link@example.com')
        ->call('addLink') // linha vazia, sem URL
        ->call('save')
        ->assertHasNoErrors();

    expect($user->refresh()->profile->links()->count())->toBe(0);
});

it('renders profile links as brand-colored icons with a tooltip on the public page', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    $writer->profile->update(['visibility' => ProfileVisibilityEnum::PUBLIC]);
    $writer->profile->links()->create([
        'platform' => 'instagram',
        'url' => 'https://instagram.com/foo',
        'sort_order' => 0,
    ]);

    $this->actingAs($writer)
        ->get(route('profile.show', $writer->profile->username))
        ->assertOk()
        ->assertSee('https://instagram.com/foo', false)
        ->assertSee('color: #E4405F', false)    // cor fixa da marca (não customizável)
        ->assertSee('title="Instagram"', false); // tooltip / nome da rede
});

it('reorders profile links via drag handle', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'username' => 'linkuser']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.links', [
            ['platform' => 'instagram', 'url' => 'https://a.com'],
            ['platform' => 'facebook', 'url' => 'https://b.com'],
            ['platform' => 'youtube', 'url' => 'https://c.com'],
        ])
        ->call('reorderLinks', 0, 2) // move o primeiro para o fim
        ->assertSet('form.links.0.platform', 'facebook')
        ->assertSet('form.links.1.platform', 'youtube')
        ->assertSet('form.links.2.platform', 'instagram');
});
